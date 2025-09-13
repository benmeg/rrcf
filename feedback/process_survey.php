<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

require '../assets/setup/env.php';
require '../assets/setup/db.inc.php';
require '../assets/includes/auth_functions.php';
require '../assets/includes/security_functions.php';

if (isset($_SESSION['auth']))
    $_SESSION['expire'] = ALLOWED_INACTIVITY_TIME;

generate_csrf_token();
check_remember_me();

check_verified();

// clearing cookies - TODO:
// https://stackoverflow.com/a/34944547
// may need THREE different techniques to ensure cookies are cleared!

// If a decision letter cookie is set (indicating the user clicked a link from a journal decision letter) clear this cookie

// N.B. We only ascribe the first piece of feedback a user provides as being linked to the journal - we do this be assuming that the journal the user chooses (after clicking the decision letter link) is the journal that sent the decision letter (with the link in)

if(isset($_COOKIE["decisionletter"])) {

  unset($_COOKIE['decisionletter']);
  setcookie("decisionletter", "", time() - 3600, "/");
}

require 'functions.php';

$user_id    = $_SESSION['id'];

$mysqltime  = date ('Y-m-d H:i:s', time());

$ip_address = $_SERVER['REMOTE_ADDR'];

if ($ip_address == "::1") {

  $ip_address = "127.0.0.1";
}

$user_agent = $_SERVER['HTTP_USER_AGENT'];

date_default_timezone_set('Europe/London');

// Connect to database

$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$strJsonFileContents = file_get_contents('php://input');

// First we need to split JSON into parts for:

// a) processing (particularly for stage 1 + 2)

// b) insertion of the raw JSON blob into the database
//    but without the metadata (which will be inserted as fields
//    e.g. stage, role, journal id)


// determine what stage(s) of response feedback are contained in the JSON


// Stage 1

if (strpos($strJsonFileContents, 'stage_id":"1')) {

  $failure = false;

  $stage_id = "1";
  $header_pos = strpos($strJsonFileContents, "stage1");
  $header     = substr($strJsonFileContents, 0, $header_pos-2) . "}";
  $responses  = "{" . substr($strJsonFileContents, strlen($header), strlen($strJsonFileContents)-strlen($header));

  $JSONstage1responsesArray = json_decode($responses, true);
  $JSONstage1headerArray    = json_decode($header, true);

  if (isset($JSONstage1headerArray['orphan_id'])) {

    $orphan_id = $JSONstage1headerArray['orphan_id'];
  }

  else

  {
    $orphan_id = 'NULL';
  }


  if (isset($JSONstage1headerArray['decision_letter'])) {

    $review_source = "1";
  }

  // Flag this feedback as having come from a co-author invite

  elseif (array_key_exists("uuid", $JSONstage1headerArray) && !array_key_exists("orphan_id", $JSONstage1headerArray)) {

    $review_source = "2";
  }

  else

  {
    $review_source = "0";
  }


  if (isset($JSONstage1headerArray['stage_1_academic_role_text'])) {

    $academic_role_text = addslashes($JSONstage1headerArray['stage_1_academic_role_text']);

  }

  else

  {
    $academic_role_text = 'NULL';
  }

  if (isset($JSONstage1headerArray['uuid'])) {

    $uuid = "'" . $JSONstage1headerArray['uuid'] . "'";

    if (isset($_COOKIE["invite_uuid"]) && $_COOKIE["invite_uuid"] == $JSONstage1headerArray['uuid']) {

      unset($_COOKIE['invite_uuid']);
      unset($_COOKIE['invite_stage']);

      setcookie("invite_uuid",  "", time() - 3600, "/");
      setcookie("invite_stage", "", time() - 3600, "/");
    }
  }

  else

  {
    $uuid = "UUID()";
  }

  // If the user has chosen the 'reviewer' role
  // they will not have selected any sub-stages

  if (isset($JSONstage1headerArray['stage_choices'])) {

    $stage_choices = $JSONstage1headerArray['stage_choices'];
  }

  else

  {
    $stage_choices = 'NULL';
  }


  // Add review metadata to `feedback_reviews` and get `review_id`
  // for later adding to response items

  $sql = "INSERT INTO `feedback_reviews` (`review_uuid`, `user_id`, `review_ref`, `stage_id`, `sub_stage_id`, `journal_id`, `start_date`, `end_date`, `share_date`, `academic_role`, `academic_role_text`, `doi`, `review_json`, `role_type`, `review_link_id`, `review_source`, `ip_address`, `user_agent`, `completed_on`, `completion_time`) VALUES (UUID_TO_BIN(" . $uuid . "), '" . $user_id . "', '" . addslashes($JSONstage1headerArray['ref_code']) . "', '" . $stage_id . "', " . $stage_choices . ", '" . $JSONstage1headerArray['journal_id'] . "', '" . $JSONstage1headerArray['stage_1_start_date'] . "', '" . $JSONstage1headerArray['stage_1_end_date'] . "', '" . $JSONstage1headerArray['stage_1_share_year_data'] . "', '" . $JSONstage1headerArray['stage_1_academic_role'] . "', '" . $academic_role_text . "', '', '" . addslashes($responses) . "', '" . $JSONstage1headerArray['role_id'] . "', " . $orphan_id . ", '" . $review_source . "', '" . $ip_address . "', '" . addslashes($user_agent) . "', '" . $mysqltime . "', '" . secondsDifference($JSONstage1responsesArray['stage1_start_time'], $JSONstage1responsesArray['stage1_end_time']) . "');";

  if (mysqli_query($db_handle, $sql)) {

    $last_id = mysqli_insert_id($db_handle);
  }

  else

  {
    $failure = true;
  }

  // If an orphan review was specified, update that orphan review to point to
  // this review, completing the pair (i.e. they point to each others' IDs)

  if (isset($JSONstage1headerArray['orphan_id'])) {

    // If no UUID has been passed (i.e. this is not invited co-author feedback)
    // ensure that both parts of the review share the same UUID

    if (!isset($JSONstage1headerArray['uuid'])) {

      // Get UUID from existing orphan Stage 2 review and copy it to
      // newly created Stage 1 review
      
      // This will later allow us to find/match papers across different
      // authors if they invite co-authors (after giving their own feedback
      // on their manuscript experience)

      $sql = "SELECT BIN_TO_UUID(`review_uuid`) AS s2uuid FROM `feedback_reviews` WHERE `review_id` = '" . $orphan_id . "';";

      if ($result = mysqli_query($db_handle, $sql)) {

        $stage2_uuid_array = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }

      $stage2_uuid = $stage2_uuid_array[0]['s2uuid'];

      $sql = "UPDATE `feedback_reviews` SET `review_uuid` = UUID_TO_BIN('" . $stage2_uuid . "') WHERE `review_id` = '" . $last_id . "';";

      if ($result = mysqli_query($db_handle, $sql)) {

        // UUID copied from Stage 2 orphan review to newly created Stage 1 review
      }

      else

      {
        // Error adding UUID from orphan Stage 2 review to newly created Stage 1 review
        $failure = true;
      }
    }

    // Add some integrity checks to SQL, so that we're only updating a Stage 2 review
    // and not updating a review with an existing linked (Stage 1) review

    $sql = "UPDATE `feedback_reviews` SET `review_link_id`='" . $last_id . "' WHERE `stage_id` = '2' AND `review_link_id` IS NULL AND `review_id` = '" . $JSONstage1headerArray['orphan_id'] . "'";

    if ($result = mysqli_query($db_handle, $sql)) {

      // Unlinked review updated
    }

    else

    {
      // Error updating unlinked account

      $failure = true;
    }
  }

  $sql = processJSONresponseData($stage_id, $last_id, $JSONstage1responsesArray, $JSONstage1headerArray);

  // Write responses to database

  if ($result = mysqli_query($db_handle, $sql)) {

    // Responses added
  }

  else

  {
    // Problem adding responses
    $failure = true;
  }

  mysqli_close($db_handle);

  if ($failure == false) {

    echo "success"; 
  }

  else

  {
    echo "failure";
  }
}


// Stage 2

else if (strpos($strJsonFileContents, 'stage_id":"2')) {

  $failure = false;

  $stage_id = "2";
  $header_pos = strpos($strJsonFileContents, "stage2");
  $header     = substr($strJsonFileContents, 0, $header_pos-2) . "}";
  $responses  = "{" . substr($strJsonFileContents, strlen($header), strlen($strJsonFileContents)-strlen($header));

  $JSONstage2responsesArray = json_decode($responses, true);
  $JSONstage2headerArray    = json_decode($header, true);

  if (isset($JSONstage2headerArray['orphan_id'])) {

    $orphan_id = $JSONstage2headerArray['orphan_id'];
  }

  else

  {
    $orphan_id = 'NULL';
  }


  if (array_key_exists("decision_letter", $JSONstage2headerArray)) {

    $review_source = "1";
  }

  // Flag this feedback as having come from a co-author invite

  elseif (array_key_exists("uuid", $JSONstage2headerArray) && !array_key_exists("orphan_id", $JSONstage2headerArray) ) {

    $review_source = "2";
  }

  else

  {
    $review_source = "0";
  }


  if (isset($JSONstage2headerArray['stage_2_academic_role_text'])) {

    $academic_role_text = addslashes($JSONstage2headerArray['stage_2_academic_role_text']);

  }

  else

  {
    $academic_role_text = 'NULL';
  }


  if (isset($JSONstage2headerArray['uuid'])) {

    $uuid = "'" . $JSONstage2headerArray['uuid'] . "'";

    if (isset($_COOKIE["invite_uuid"]) && $_COOKIE["invite_uuid"] == $JSONstage2headerArray['uuid']) {

      unset($_COOKIE['invite_uuid']);
      unset($_COOKIE['invite_stage']);

      setcookie("invite_uuid",  "", time() - 3600, "/");
      setcookie("invite_stage", "", time() - 3600, "/");
    }

  }

  else

  {
    $uuid = "UUID()";
  }


  if (isset($JSONstage2headerArray['doi']) && $JSONstage2headerArray['doi'] != '') {

    $doi = $JSONstage2headerArray['doi'];
  }

  else

  {
    $doi = '';
  }

  // If the user has chosen the 'reviewer' role
  // they will not have selected any sub-stages

  if (isset($JSONstage2headerArray['stage_choices'])) {

    $stage_choices = $JSONstage2headerArray['stage_choices'];
  }

  else

  {
    $stage_choices = 'NULL';
  }

  // Add review metadata to `feedback_reviews` and get `review_id`
  // for later adding to response items

  $sql = "INSERT INTO `feedback_reviews` (`review_uuid`, `user_id`, `review_ref`, `stage_id`, `sub_stage_id`, `journal_id`, `start_date`, `end_date`, `share_date`, `academic_role`, `academic_role_text`, `doi`, `review_json`, `role_type`, `review_link_id`, `review_source`, `ip_address`, `user_agent`, `completed_on`, `completion_time`) VALUES (UUID_TO_BIN(" . $uuid . "), '" . $user_id . "', '" . addslashes($JSONstage2headerArray['ref_code']) . "', '" . $stage_id . "'," . $stage_choices . ", '" . $JSONstage2headerArray['journal_id'] . "', '" . $JSONstage2headerArray['stage_2_start_date'] . "', '" . $JSONstage2headerArray['stage_2_end_date'] . "', '" . $JSONstage2headerArray['stage_2_share_year_data'] . "', '" . $JSONstage2headerArray['stage_2_academic_role'] . "', '" . $academic_role_text . "', '" . $doi . "', '" . addslashes($responses) . "', '" . $JSONstage2headerArray['role_id'] . "', " . $orphan_id . ", '" . $review_source . "', '" . $ip_address . "', '" . addslashes($user_agent) . "', '" . $mysqltime . "', '" . secondsDifference($JSONstage2responsesArray['stage2_start_time'], $JSONstage2responsesArray['stage2_end_time']) . "');";

  if ($result = mysqli_query($db_handle, $sql)) {

    $last_id = mysqli_insert_id($db_handle);
  }

  else

  {
    $failure = true;
  }

  // If an orphan review was specified, update that orphan review to point to
  // this review, completing the pair (i.e. they point to each others' IDs)

  if (isset($JSONstage2headerArray['orphan_id'])) {

    // If no UUID has been passed (i.e. this is not invited co-author feedback)
    // ensure that both parts of the review share the same UUID

    if (!isset($JSONstage2headerArray['uuid'])) {

      // Get UUID from existing orphan Stage 2 review and copy it to
      // newly created Stage 1 review
      
      // This will later allow us to find/match papers across different
      // authors if they invite co-authors (after giving their own feedback
      // on their manuscript experience)

      $sql = "SELECT BIN_TO_UUID(`review_uuid`) AS s1uuid FROM `feedback_reviews` WHERE `review_id` = '" . $orphan_id . "';";

      if ($result = mysqli_query($db_handle, $sql)) {

        $stage1_uuid_array = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }

      $stage1_uuid = $stage1_uuid_array[0]['s1uuid'];

      $sql = "UPDATE `feedback_reviews` SET `review_uuid` = UUID_TO_BIN('" . $stage1_uuid . "') WHERE `review_id` = '" . $last_id . "';";

      if ($result = mysqli_query($db_handle, $sql)) {

        // UUID copied from Stage 1 orphan review to newly created Stage 2 review
      }

      else

      {
        // Error adding UUID from orphan Stage 1 review to newly created Stage 2 review

        $failure = true;
      }
    }

    // Add some integrity checks to SQL, so that we're only updating a Stage 1 review
    // and not updating a review with an existing linked (Stage 2) review

    $sql = "UPDATE `feedback_reviews` SET `review_link_id`='" . $last_id . "' WHERE `stage_id` = '1' AND `review_link_id` IS NULL AND `review_id` = '" . $JSONstage2headerArray['orphan_id'] . "'";

    if ($result = mysqli_query($db_handle, $sql)) {

      // Unlinked review updated

    }

    else

    {
      // Error updating unlinked account

      $failure = true;
    }
  }

  $sql = processJSONresponseData($stage_id, $last_id, $JSONstage2responsesArray, $JSONstage2headerArray);

  // Write responses to database

  if ($result = mysqli_query($db_handle, $sql)) {

    // Responses added
  }

  else

  {
    // Problem adding responses

        $failure = true;
  }


  // Now, having inserted both response metadata and the responses themselves, we want to check if there is any orphan Stage 1 feedback by other users that share the same UUID as 

  // Get UUID of inserted record, for checking against other users' orphaned Stage 1 feedback

  if (isset($JSONstage2headerArray['uuid'])) {

    $stage1_uuid = $JSONstage2headerArray['uuid'];

  }

  else

  {
    $sql = "SELECT BIN_TO_UUID(`review_uuid`) AS s1uuid FROM `feedback_reviews` WHERE `review_id` = '" . $last_id . "';";

    if ($result = mysqli_query($db_handle, $sql)) {

      $stage1_uuid_array = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    $stage1_uuid = $stage1_uuid_array[0]['s1uuid'];
  }

  // Check if there are any orphan Stage 1 pieces of feedback (without corresponding Stage 2 feedback) which share the same UUID. These will exist if one co-author has invited another co-author - either:
  //
  // the current author giving feedback has invited coauthors, and one of those has previously given Stage 1 feedback (but not yet Stage 2)
  //
  // OR
  //
  // a co-author completed Stage 1 feedback, invited the current user, who then also completed Stage 1 feedback. Now that the current user has completed Stage /2/ feedback, the original co-author will be notified.
  //
  // Basically it doesn't matter who invited whom for Stage 1 feedback, but assuming both inviter and invitee completed it, whoever first completes Stage 2 will trigger an automatic email to the other co-author(s) linked by the shared invite UUID.

  $sql = "SELECT users.email, BIN_TO_UUID(review_uuid) AS linked_uuid, review_id, stage_id, user_id, review_ref, review_link_id FROM feedback_reviews INNER JOIN users ON feedback_reviews.user_id = users.id WHERE stage_id = '1' AND review_uuid = UUID_TO_BIN('" . $stage1_uuid . "') AND review_link_id IS NULL";

  if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $uuid_linked_orphans = mysqli_fetch_all($result, MYSQLI_ASSOC);

      $url = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/selector.php?uuid=" . $stage1_uuid . "&stage=2";

      foreach ($uuid_linked_orphans as $uuid_linked_orphan) {

        sendEmailToOrphanAuthor($uuid_linked_orphan['email'], $uuid_linked_orphan['review_ref'], $url);
      }
    }
  }

  mysqli_close($db_handle);

  if ($failure == false) {

    echo "success"; 
  }

  else

  {
    echo "failure";
  }
}

// Stage 1 & 2

else if (strpos($strJsonFileContents, 'stage_id":"3')) {

  $failure = false;

  $header_pos = strpos($strJsonFileContents, "stage1");
  $header     = substr($strJsonFileContents, 0, $header_pos-2) . "}";
  $responses  = "{" . substr($strJsonFileContents, strlen($header), strlen($strJsonFileContents)-strlen($header));

  // split JSON pair into 2 response sets

  $delimiter_str = ',"stage_delimiter":"true",';
  $delimiter_pos = strpos($responses, $delimiter_str);

  $stage1_responses = substr($responses, 0, $delimiter_pos) . "}";
  $stage2_responses = "{" . substr($responses, $delimiter_pos + strlen($delimiter_str), strlen($responses)-strlen($stage1_responses)-strlen($delimiter_str)) . "}";

  $JSONstage1responsesArray = json_decode($stage1_responses, true);
  $JSONstage2responsesArray = json_decode($stage2_responses, true);

  $JSONheaderArray          = json_decode($header, true);

  // If the user has chosen the 'reviewer' role
  // they will not have selected any sub-stages

  if (isset($JSONheaderArray['stage_choices'])) {

    $stage1_choices = $JSONheaderArray['stage_choices'][0];
    $stage2_choices = $JSONheaderArray['stage_choices'][2];

  }

  else

  {
    $stage1_choices = 'NULL';
    $stage2_choices = 'NULL';

  }


  if (isset($JSONheaderArray['decision_letter'])) {

    $review_source = "1";
  }

  // Flag this feedback as having come from a co-author invite

  elseif (array_key_exists("uuid", $JSONheaderArray) && !array_key_exists("orphan_id", $JSONheaderArray) ) {

    $review_source = "2";
  }

  else

  {
    $review_source = "0";
  }


  if (isset($JSONheaderArray['stage_1_academic_role_text'])) {

    $s1_academic_role_text = addslashes($JSONheaderArray['stage_1_academic_role_text']);

  }

  else

  {
    $s1_academic_role_text = 'NULL';
  }


  if (isset($JSONheaderArray['stage_2_academic_role_text'])) {

    $s2_academic_role_text = addslashes($JSONheaderArray['stage_2_academic_role_text']);

  }

  else

  {
    $s2_academic_role_text = 'NULL';
  }


  if (isset($JSONheaderArray['uuid'])) {

    $uuid = "'" . $JSONheaderArray['uuid'] . "'";

    if (isset($_COOKIE["invite_uuid"]) && $_COOKIE["invite_uuid"] == $JSONheaderArray['uuid']) {

      unset($_COOKIE['invite_uuid']);
      unset($_COOKIE['invite_stage']);

      setcookie("invite_uuid",  "", time() - 3600, "/");
      setcookie("invite_stage", "", time() - 3600, "/");
    }

  }

  else

  {
    $uuid = "UUID()";
  }


  if (isset($JSONheaderArray['doi']) && $JSONheaderArray['doi'] != '') {

    $doi = $JSONheaderArray['doi'];

  }

  else

  {
    $doi = '';
  }

  // Add 2 reviews, 1 for each stage (i.e. Stages 1 and 2)

  // Stage 1

  $sql = "INSERT INTO `feedback_reviews` (`review_uuid`, `user_id`, `review_ref`, `stage_id`, `sub_stage_id`, `journal_id`, `start_date`, `end_date`, `share_date`, `academic_role`, `academic_role_text`, `doi`, `review_json`, `role_type`, `review_link_id`, `review_source`, `ip_address`, `user_agent`, `completed_on`, `completion_time`) VALUES (UUID_TO_BIN(" . $uuid . "), '" . $user_id . "', '" . addslashes($JSONheaderArray['ref_code']) . "', '1', " . $stage1_choices . ", '" . $JSONheaderArray['journal_id'] . "', '" . $JSONheaderArray['stage_1_start_date'] . "', '" . $JSONheaderArray['stage_1_end_date'] . "', '" . $JSONheaderArray['stage_1_share_year_data'] . "', '" . $JSONheaderArray['stage_1_academic_role'] . "', '" . $s1_academic_role_text . "', '" . $doi . "', '" . addslashes($stage1_responses) . "', '" . $JSONheaderArray['role_id'] . "', NULL, '" . $review_source . "', '" . $ip_address . "', '" . addslashes($user_agent) . "', '" . $mysqltime . "', '" . secondsDifference($JSONstage1responsesArray['stage1_start_time'], $JSONstage1responsesArray['stage1_end_time']) . "');";

  if ($result = mysqli_query($db_handle, $sql)) {

    $stage1_id = mysqli_insert_id($db_handle);
  }

  else

  {
    $failure = true;
  }

  if (!isset($JSONheaderArray['uuid'])) {

    // Get UUID from Stage 1 and copy it to Stage 2
    
    // This will later allow us to find/match papers across different
    // authors if they invite co-authors (after giving their own feedback
    // on their manuscript experience)

    $sql = "SELECT BIN_TO_UUID(`review_uuid`) AS s1uuid FROM `feedback_reviews` WHERE `review_id` = '" . $stage1_id . "';";
  
    if ($result = mysqli_query($db_handle, $sql)) {

      $stage1_uuid = mysqli_fetch_all($result, MYSQLI_ASSOC);

      $uuid = "'" . $stage1_uuid[0]['s1uuid'] . "'";
    }
  }

  // Stage 2

  $sql = "INSERT INTO `feedback_reviews` (`review_uuid`, `user_id`, `review_ref`, `stage_id`, `sub_stage_id`, `journal_id`, `start_date`, `end_date`, `share_date`, `academic_role`, `academic_role_text`, `doi`, `review_json`, `role_type`, `review_link_id`, `review_source`, `ip_address`, `user_agent`, `completed_on`, `completion_time`) VALUES (UUID_TO_BIN(" . $uuid . "), '" . $user_id . "', '" . addslashes($JSONheaderArray['ref_code']) . "', '2', " . $stage2_choices . ", '" . $JSONheaderArray['journal_id'] . "', '" . $JSONheaderArray['stage_2_start_date'] . "', '" . $JSONheaderArray['stage_2_end_date'] . "', '" . $JSONheaderArray['stage_2_share_year_data'] . "', '" . $JSONheaderArray['stage_2_academic_role'] . "', '" . $s2_academic_role_text . "', '" . $doi . "', '" . addslashes($stage2_responses) . "', '" . $JSONheaderArray['role_id'] . "', " . $stage1_id . ", '" . $review_source . "', '" . $ip_address . "', '" . addslashes($user_agent) . "', '" . $mysqltime . "', '" . secondsDifference($JSONstage2responsesArray['stage2_start_time'], $JSONstage2responsesArray['stage2_end_time']) . "');";

  if ($result = mysqli_query($db_handle, $sql)) {

    $stage2_id = mysqli_insert_id($db_handle);
  }

  else

  {
    $failure = true;
  }

   $sql = "UPDATE `feedback_reviews` SET `review_link_id`='" . $stage2_id . "' WHERE `stage_id` = '1' AND `review_link_id` IS NULL AND `review_id` = '" . $stage1_id . "'";

  if ($result = mysqli_query($db_handle, $sql)) {

    // Unlinked review updated
  }

  else

  {
    // Error updating unlinked account

    $failure = true;
  }

  // For some reason live MySQL server can't handle the following 2
  // long SQL statements as a single string (it works on my dev server),
  // so we execute each one seperately

  // Stage 1 responses

  $sql = processJSONresponseData(1, $stage1_id, $JSONstage1responsesArray, $JSONheaderArray);

  if (mysqli_query($db_handle, $sql)) {

    // Responses added
  }

  else

  {
    // Problem adding responses

    $failure = true;
  }


  // Stage 2 responses

  $sql2 = processJSONresponseData(2, $stage2_id, $JSONstage2responsesArray, $JSONheaderArray);

  if (mysqli_query($db_handle, $sql2)) {

    // Responses added
  }

  else

  {
    // Problem adding responses

    $failure = true;
  }

  // N.B. Currently the below section which checks for orphan Stage 1 feedback by other users and sends email invitations to completed corresponding Stage 2 feedback will never find any orphan feedback which matches the UUID of the Stage 1 & 2 feedback just entered because any invited feedback completed by other users can only be completed one stage at a time (for reasons of data integrity and UI consistency). However, this section of code is being kept here in case future developments allow users to complete invited Stage 1 & 2 feedback in a single process (i.e. choosing 'Stage 1 & 2' for Q3. on selector.php)

  // N.B. As opposed to just Stage 2 processing (above), where we must find the UUID, for this path, where both Stage 1 and Stage 2 feedback is saved in the database, we already get the UUID, so we do not need to get it here.

  // Check if there are any orphan Stage 1 pieces of feedback (without corresponding Stage 2 feedback) which share the same UUID. These will exist if one co-author has invited another co-author - either:
  //
  // the current author giving feedback has invited coauthors, and one of those has previously given Stage 1 feedback (but not yet Stage 2)
  //
  // OR
  //
  // a co-author completed Stage 1 feedback, invited the current user, who then also completed Stage 1 feedback. Now that the current user has completed Stage /2/ feedback, the original co-author will be notified.
  //
  // Basically it doesn't matter who invited whom for Stage 1 feedback, but assuming both inviter and invitee completed it, whoever first completes Stage 2 will trigger an automatic email to the other co-author(s) linked by the shared invite UUID.

  // $sql = "SELECT users.email, BIN_TO_UUID(review_uuid) AS linked_uuid, review_id, stage_id, user_id, review_ref, review_link_id FROM feedback_reviews INNER JOIN users ON feedback_reviews.user_id = users.id WHERE stage_id = '1' AND review_uuid = UUID_TO_BIN('" . $uuid . "') AND review_link_id IS NULL";

  // if ($result = mysqli_query($db_handle, $sql)) {

  //   if (mysqli_num_rows($result) > 0) {

  //     $uuid_linked_orphans = mysqli_fetch_all($result, MYSQLI_ASSOC);

  //     $url = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/selector.php?uuid=" . $uuid . "&stage=2";

  //     foreach ($uuid_linked_orphans as $uuid_linked_orphan) {

  //       sendEmailToOrphanAuthor($uuid_linked_orphan['email'], $uuid_linked_orphan['review_ref'], $url);
  //     }
  //   }
  // }

  // N.B. End of section which will currently never find any orphan Stage 1 feedback matching the UUID of the Stage 1 & 2 feedback just completed by the current user (see comment above for longer explanation)

  mysqli_close($db_handle);

  if ($failure == false) {

    echo "success"; 
  }

  else

  {
    echo "failure";
  }
}


function processJSONresponseData ($stage_number, $review_id, &$JSONarray, &$JSONheader) {

  // Iterate through associative array of survey data
  // collating and inserting into MySQL table

  $prefix = "stage" . $stage_number . "_question_";

  $sql = "INSERT INTO `feedback_responses` (`review_id`, `question_id`, `sub_question_id`, `question_response_value`, `question_response_text`) VALUES ";

  foreach ($JSONarray as $key => $val) {

    // check if current array element is an individual response
    // or an array of response (for checkbox and matrix responses)

    // array of responses

    if (is_array($val)) {

      // Detect if array is checkbox or matrix responses.

      // Due to the way SurveyJS passes checkbox data
      // i.e. [1,2,5,8] for any chosen options
      // when we convert this JSON array to a PHP associative array
      // the checkbox array's initial key value is '0'
      // whereas the matrix array's initial key value is '1'

      if (isset($JSONarray[$key][0])) {

          // Checkbox response array

          // do checkbox processing here
          // including looking for _checkbox_$subval_text
          // to add as a comment

          // N.B. we will take the value and use it as the sub question ID in the database

          $root        = str_replace("_sub",'', $key);
          $question_id = str_replace($prefix,'', $root);

          foreach ($val as $checkboxkey => $checkboxvalue) {
            
            // build comment key to see if it exists
            // e.g. stage2_question_46_checkbox_11_text
            
            $comment = $root . "_checkbox_" . $checkboxvalue . "_text";

            if (isset($JSONarray[$comment])) {

              $sql = $sql . "('$review_id', '$question_id', '$checkboxvalue', '1', '" . addslashes($JSONarray[$comment]) . "'), ";

            }

            else

            {

              $sql = $sql . "('$review_id', '$question_id', '$checkboxvalue', '1', NULL), ";
            }

          }
      }

      else

      {
          // Matrix response array

          // N.B. look for and add comments for root
          // i.e. stage1_question_75_comment

          $question_id = str_replace($prefix, '', $key);
          $comment     = $key . "_comment";

          foreach ($val as $matrixkey => $matrixval) {

            $sql = $sql . "('$review_id', '$question_id', '$matrixkey', '$matrixval', NULL), ";

          }

          // add any comments

          if (isset($JSONarray[$comment])) {

            $sql = $sql . "('$review_id', '$question_id', NULL, NULL, '" . addslashes($JSONarray[$comment]) . "'), ";
          }

      }
    }

    // individual items

    else
    {

      
      // For items ending in digits (i.e. top level questions) this is a 
      // quick way to get the question number, by removing
      // the prefix (e.g. stage2_question_) from the $key
      
      // Is the current element a question response?
      // i.e. not stage/role metadata
      
      if (startsWith($key, $prefix)) {

        // classes of responses:

        // * keys that end _main
        //   - with optional _sub 
        //   - with optional _comment

        // * keys that end in a number (check if last character is digit)
        //   - with an optional accompanying _comment

        // N.B. _sub could be an array, so check before processing (and do nothing if it is - array function will process these!)

        // N.B. need to strip off _main and look for _comment

        if (endsWith($key, "_main")) {

          // get the root prefix (e.g. stage1_question_69)
          $root        = str_replace("_main",'', $key);

          $sub         = $root . "_sub";
          $comment     = $root . "_comment";
          $question_id = str_replace($prefix,'', $root);

          if (isset($JSONarray[$comment])) {

            $sql = $sql . "('$review_id', '$question_id', NULL, '$val', '" . addslashes($JSONarray[$comment]) . "'), ";

          }

          else

          {

             $sql = $sql . "('$review_id', '$question_id', NULL, '$val', NULL), ";
          }

          if (isset($JSONarray[$sub])) {

            // we only want to process individual _sub responses here
            // as we'll process _sub arrays above

            if (!is_array($JSONarray[$sub])) {

              $sql = $sql . "('$review_id', '$question_id', '1', '$JSONarray[$sub]', NULL), ";
            }
          }
        }

        // process response keys ending in a digit 
        // e.g. stage1_question_42

        if (endsWithNumber($key)) {

          $comment     = $key . "_comment";
          $question_id = str_replace($prefix,'', $key);

          if (isset($JSONarray[$comment])) {

            $sql = $sql . "('$review_id', '$question_id', NULL, '$val', '" . addslashes($JSONarray[$comment]) . "'), ";

          }

          else

          {
            $sql = $sql . "('$review_id', '$question_id', NULL, '$val', NULL), ";
          }
        }

        // Process any orphan comments - this exists solely for the
        // additional general comments section.
        // Because general comments will have a _comment suffix
        // but no accompanying parent key/response (with a _comment suffix)
        // the above routines would not process it

        // Check if the current key has a _comment suffix
        // AND has no accompanying parent key/response (either: blank or _main)

        if (endsWith($key, "_comment")) {
          
          $root = str_replace("_comment",'', $key);
          $main = $root . "_main";
          $question_id = str_replace($prefix,'', $root);

          
          if (!isset($JSONarray[$root]) && !isset($JSONarray[$main])) {

            $sql = $sql . "('$review_id', '$question_id', NULL, NULL, '" . addslashes($JSONarray[$key]) . "'), ";
          }
        }
      }
    }
  }

  // Remove final trailing delimiting comma, created from looping through each response item
  // and add semicolon for strictly correct SQL syntax
  $sql = rtrim($sql, ', ');
  $sql = $sql . ";";

  return $sql;
}


// https://css-tricks.com/snippets/php/test-if-string-starts-with-certain-characters-in-php/

function startsWith($string, $startString) { 
  $len = strlen($startString); 
  return (substr($string, 0, $len) === $startString); 
}


// https://www.geeksforgeeks.org/php-startswith-and-endswith-functions/

function endsWith($string, $endString)
{
    $len = strlen($endString);
    if ($len == 0) {
        return true;
    }
    return (substr($string, -$len) === $endString);
}


// https://stackoverflow.com/questions/4114609/check-if-a-string-ends-with-a-number-in-php/24547519

function endsWithNumber($string){
    $len = strlen($string);
    if($len === 0){
        return false;
    }
    return is_numeric($string[$len-1]);
}


// Takes 3 parameters (email, ref code, and invite URL) and sends
// any authors who have completed only Stage 1 author feedback which
// matches the UUID of the current piece of feedback being given by
// the current user

function sendEmailToOrphanAuthor($email_address, $ref_code, $url) {

  $body =         "Dear Colleague,\n\n";

  $body = $body . "You previously gave feedback on a Registered Report manuscript at Stage 1 - at the time you privately gave the manuscript the reference code:\n\n";

  $body = $body . "\"%%REF_CODE%%\"" . "\n\n";

  $body = $body . "We are now inviting you to give feedback on Stage 2 peer review of this manuscript. This is because one of your co-authors has just given Stage 2 feedback, so we are assuming there has been a change in the manuscript's status at Stage 2 of peer review.\n\n"; 

  $body = $body . "You can leave Stage 2 feedback here, which takes no more than 5-10 minutes:\n\n";

  $body = $body . "%%URL%%" . "\n\n";

  $body = $body . "A quick reminder - the broad goal of the website is to collect data regarding how well various aspects of the Registered Reports process are implemented across academic journals.\n\n";

  $body = $body . "This data will be aggregated and displayed publicly, showing how journals were rated across a range of categories by both manuscript authors and reviewers.\n\n";

  $body = $body . "We hope this will both help the community in choosing where to submit their Registered Report manuscripts, while also incentivising publishers to improve the Registered Reports process at their journals. Additionally, the data will be used as part of ethically-approved research for my PhD on metascience.\n\n";

  $body = $body . "Thanks in advance,\n\n";

  $body = $body . "Ben Meghreblian\n\n\n";


  $body = $body . "PhD student\n";
  $body = $body . "Cardiff University\n";
  $body = $body . "Email: MeghreblianBA@cardiff.ac.uk\n\n\n";


  $body = $body . "For any complaints or issues with this research, please contact:\n\n";

  $body = $body . "The Secretary,\n";
  $body = $body . "School Research Ethics Committee,\n";
  $body = $body . "School of Psychology,\n";
  $body = $body . "Cardiff University,\n";
  $body = $body . "Park Place,\n";
  $body = $body . "CF10 3AT";

  try {

    $body = str_replace("%%REF_CODE%%", $ref_code, $body);
    $body = str_replace("%%URL%%", $url, $body);

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = SMTP_AUTH;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = MAIL_ENCRYPTION;
    $mail->Port = MAIL_PORT;

    $mail->setFrom(MAIL_USERNAME, APP_NAME);
    $mail->addAddress($email_address);

    $mail->isHTML(false);
    $mail->Subject = "Invitation to give further feedback on Registered Reports peer review";
    $mail->Body    = $body; 

    $mail->send();
  }

  catch (Exception $e) {
    // some kind of error - currently do nothing, but catch so that we can continue if we fail to send the email for some reason
  }
}
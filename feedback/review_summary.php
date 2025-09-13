<?php

define('TITLE', "Review summary");

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

require 'functions.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$user_id = $_SESSION['id'];

// Connect to database

$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}


// get querystring input

if (isset($_GET['uuid'])) {

  $single_stage = false;
  $sql = "SELECT `feedback_reviews`.`review_id`, `feedback_reviews`.`completed_on`, `journals`.`journal_name`, `feedback_reviews`.`academic_role` AS academic_role_id, `feedback_academic_roles`.`role_text` AS academic_role, `feedback_reviews`.`academic_role_text` FROM `feedback_reviews` INNER JOIN `journals` ON `feedback_reviews`.`journal_id` = `journals`.`journal_id` LEFT JOIN `feedback_academic_roles` ON `feedback_reviews`.`academic_role` = `feedback_academic_roles`.`role_id` WHERE `review_uuid` = UUID_TO_BIN('" . $_GET['uuid'] . "') AND `user_id`  = '" . $user_id . "' ORDER BY `stage_id` ASC";

  if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $stages_metadata = mysqli_fetch_all($result, MYSQLI_ASSOC);
      $s1_review_id  = $stages_metadata[0]['review_id'];
      $s2_review_id  = $stages_metadata[1]['review_id'];
    }
  }
}

else

{
  $single_stage = true;
  $review_id = $_GET['review_id'];
}

// Get question categories/IDs

$sql = "SELECT `category_id`, `category_title` FROM `feedback_question_categories` ORDER BY `category_id` ASC"; 

if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}


// Get review metadata

// Stage 1 and 2

if ($single_stage == false) {

  // Stage 1 metadata

  $sql = "SELECT `feedback_reviews`.`review_ref`, `feedback_reviewer_roles`.`role_text`, `feedback_reviews`.`stage_id`, `feedback_reviews`.`doi`, `feedback_stages`.`stage_text`, `journals`.`journal_name`, `feedback_reviews`.`start_date`, `feedback_reviews`.`end_date`, `feedback_reviews`.`completed_on` FROM `feedback_reviews` INNER JOIN `journals` ON `feedback_reviews`.`journal_id` = `journals`.`journal_id` 
LEFT JOIN `feedback_stages` ON `feedback_reviews`.`sub_stage_id` = `feedback_stages`.`sub_stage_id` 
INNER JOIN `feedback_reviewer_roles` ON `feedback_reviews`.`role_type` = `feedback_reviewer_roles`.`role_id` WHERE `feedback_reviews`.`review_id` = '" . $s1_review_id . "' AND `feedback_reviews`.`user_id` = '" . $user_id . "';";

  if ($result = mysqli_query($db_handle, $sql)) {

      if (mysqli_num_rows($result) > 0) {

        $s1_review_metadata = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }
  }

  // Stage 2 metadata

  $sql = "SELECT `feedback_reviews`.`review_ref`, `feedback_reviewer_roles`.`role_text`, `feedback_reviews`.`stage_id`, `feedback_reviews`.`doi`, `feedback_stages`.`stage_text`, `journals`.`journal_name`, `feedback_reviews`.`start_date`, `feedback_reviews`.`end_date`, `feedback_reviews`.`completed_on` FROM `feedback_reviews` INNER JOIN `journals` ON `feedback_reviews`.`journal_id` = `journals`.`journal_id` 
LEFT JOIN `feedback_stages` ON `feedback_reviews`.`sub_stage_id` = `feedback_stages`.`sub_stage_id` 
INNER JOIN `feedback_reviewer_roles` ON `feedback_reviews`.`role_type` = `feedback_reviewer_roles`.`role_id` WHERE `feedback_reviews`.`review_id` = '" . $s2_review_id . "' AND `feedback_reviews`.`user_id` = '" . $user_id . "';";

  if ($result = mysqli_query($db_handle, $sql)) {

      if (mysqli_num_rows($result) > 0) {

        $s2_review_metadata = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }
  }

}

// Single stage (1 or 2)

else

{

  $sql = "SELECT `feedback_reviews`.`review_ref`, `feedback_reviewer_roles`.`role_text`, `feedback_reviews`.`stage_id`, `feedback_reviews`.`doi`, `feedback_stages`.`stage_text`, `journals`.`journal_name`, `feedback_reviews`.`start_date`, `feedback_reviews`.`end_date`, `feedback_reviews`.`academic_role` AS academic_role_id, `feedback_academic_roles`.`role_text` AS academic_role, `feedback_reviews`.`academic_role_text`, `feedback_reviews`.`completed_on` FROM `feedback_reviews` INNER JOIN `journals` ON `feedback_reviews`.`journal_id` = `journals`.`journal_id` 
LEFT JOIN `feedback_stages` ON `feedback_reviews`.`sub_stage_id` = `feedback_stages`.`sub_stage_id` 
INNER JOIN `feedback_reviewer_roles` ON `feedback_reviews`.`role_type` = `feedback_reviewer_roles`.`role_id` LEFT JOIN `feedback_academic_roles` ON `feedback_reviews`.`academic_role` = `feedback_academic_roles`.`role_id` WHERE `feedback_reviews`.`review_id` = '" . $review_id . "' AND `feedback_reviews`.`user_id` = '" . $user_id . "';";

  if ($result = mysqli_query($db_handle, $sql)) {

      if (mysqli_num_rows($result) > 0) {

        $review_metadata = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }
  }

}


if ($single_stage == false) {

  $stage_info = "Stages 1 & 2";
}

else {

  $stage_info = "Stage " . $review_metadata[0]['stage_id'];
}


// Get response data for stage(s):

if ($single_stage == false) {

  // Get de-duplicated, ordered, list of question IDs for stages 1 & 2

  // Stage 1 question IDs

  $sql = "SELECT DISTINCT `feedback_responses`.`question_id`, `feedback_questions`.`question_category`, `feedback_questions`.`question_type`, `feedback_questions`.`question_text`, `feedback_questions`.`question_helptext` FROM `feedback_responses` INNER JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` INNER JOIN `feedback_question_categories` ON `feedback_questions`.question_category = `feedback_question_categories`.`category_id` WHERE `feedback_responses`.`review_id` = '" . $stages_metadata[0]['review_id'] . "' ORDER BY `feedback_questions`.`question_category` ASC, `feedback_questions`.`question_order` ASC, `feedback_responses`.`question_id` ASC";

  if ($result = mysqli_query($db_handle, $sql)) {

      if (mysqli_num_rows($result) > 0) {

        $s1_question_ids = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }
  }

  // Stage 2 question IDs

  $sql = "SELECT DISTINCT `feedback_responses`.`question_id`, `feedback_questions`.`question_category`, `feedback_questions`.`question_type`, `feedback_questions`.`question_text`, `feedback_questions`.`question_helptext` FROM `feedback_responses` INNER JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` INNER JOIN `feedback_question_categories` ON `feedback_questions`.question_category = `feedback_question_categories`.`category_id` WHERE `feedback_responses`.`review_id` = '" . $stages_metadata[1]['review_id'] . "' ORDER BY `feedback_questions`.`question_category` ASC, `feedback_questions`.`question_order` ASC, `feedback_responses`.`question_id` ASC";

  if ($result = mysqli_query($db_handle, $sql)) {

      if (mysqli_num_rows($result) > 0) {

        $s2_question_ids = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }
  }


  // get stage 1 & 2 responses

  // Stage 1 responses

  $sql = "SELECT `feedback_questions`.`question_order`, `feedback_questions`.`question_text`, `feedback_questions`.`question_options`, `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, `feedback_responses`.`question_id`, `feedback_responses`.`sub_question_id`, `feedback_responses`.`question_response_value`, `feedback_responses`.`question_response_text`, `feedback_question_categories`.`category_title` FROM `feedback_responses` INNER JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` INNER JOIN `feedback_question_categories` ON `feedback_questions`.question_category = `feedback_question_categories`.`category_id` WHERE `feedback_responses`.`review_id` = '" . $stages_metadata[0]['review_id'] . "' ORDER BY `feedback_questions`.`question_category` ASC, `feedback_questions`.`question_order` ASC, `feedback_responses`.`question_id` ASC, `feedback_responses`.`sub_question_id` ASC;";

    if ($result = mysqli_query($db_handle, $sql)) {

      if (mysqli_num_rows($result) > 0) {

        $s1_responses = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }
  }

  // Stage 2 responses

  $sql = "SELECT `feedback_questions`.`question_order`, `feedback_questions`.`question_text`, `feedback_questions`.`question_options`, `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, `feedback_responses`.`question_id`, `feedback_responses`.`sub_question_id`, `feedback_responses`.`question_response_value`, `feedback_responses`.`question_response_text`, `feedback_question_categories`.`category_title` FROM `feedback_responses` INNER JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` INNER JOIN `feedback_question_categories` ON `feedback_questions`.question_category = `feedback_question_categories`.`category_id` WHERE `feedback_responses`.`review_id` = '" . $stages_metadata[1]['review_id'] . "' ORDER BY `feedback_questions`.`question_category` ASC, `feedback_questions`.`question_order` ASC, `feedback_responses`.`question_id` ASC, `feedback_responses`.`sub_question_id` ASC;";

  if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $s2_responses = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
  }

  $s1_html = getResponseStructure($s1_responses, $categories, $s1_question_ids, $s1_review_metadata[0]['stage_id'], $s1_review_metadata[0]['journal_name']);
  $s2_html = getResponseStructure($s2_responses, $categories, $s2_question_ids, $s2_review_metadata[0]['stage_id'], $s2_review_metadata[0]['journal_name']);
}


// Get single stage response info

else

{

  $single_stage_html = "";

  // Stage 1 or 2 (single stage) question IDs

  $sql = "SELECT DISTINCT `feedback_responses`.`question_id`, `feedback_questions`.`question_category`, `feedback_questions`.`question_type`, `feedback_questions`.`question_text`, `feedback_questions`.`question_helptext` FROM `feedback_responses` INNER JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` INNER JOIN `feedback_question_categories` ON `feedback_questions`.question_category = `feedback_question_categories`.`category_id` WHERE `feedback_responses`.`review_id` = '" . $review_id . "' ORDER BY `feedback_questions`.`question_category` ASC, `feedback_questions`.`question_order` ASC, `feedback_responses`.`question_id` ASC";

  if ($result = mysqli_query($db_handle, $sql)) {

      if (mysqli_num_rows($result) > 0) {

        $question_ids = mysqli_fetch_all($result, MYSQLI_ASSOC);
      }
  }

    // Stage 1 or 2 (single stage) responses

  $sql = "SELECT `feedback_questions`.`question_order`, `feedback_questions`.`question_text`, `feedback_questions`.`question_options`, `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, `feedback_responses`.`question_id`, `feedback_responses`.`sub_question_id`, `feedback_responses`.`question_response_value`, `feedback_responses`.`question_response_text`, `feedback_question_categories`.`category_title` FROM `feedback_responses` INNER JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` INNER JOIN `feedback_question_categories` ON `feedback_questions`.question_category = `feedback_question_categories`.`category_id` WHERE `feedback_responses`.`review_id` = '" . $review_id . "' ORDER BY `feedback_questions`.`question_category` ASC, `feedback_questions`.`question_order` ASC, `feedback_responses`.`question_id` ASC, `feedback_responses`.`sub_question_id` ASC;";

   if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $responses = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
  }

  $single_stage_html = getResponseStructure($responses, $categories, $question_ids, $review_metadata[0]['stage_id'], $review_metadata[0]['journal_name']);
}


function getSurveyResponse (&$response_array, $stage_id, $journal_name) {

  $question_html = "";
  $q_source_replace = ["{stage_number}", "{journal_name}"];
  $q_target_replace = [$stage_id,         $journal_name];

  switch($response_array['question_type']) {

    // 1: 5 star

    Case '1':

      $question_html = "<li>";

      switch($response_array['question_response_value']) {

        Case '999':

          $question_html = $question_html . 'Prefer not to answer';

        break;


        Case '888':

          $question_html = $question_html . 'N/A';

        break;


        Case '777':

          $question_html = $question_html . 'Don\'t know / don\'t recall';

        break;


        Case '1':

          $question_html = $question_html . '★☆☆☆☆';

        break;


        Case '2':

          $question_html = $question_html . '★★☆☆☆';

        break;


        Case '3':

          $question_html = $question_html . '★★★☆☆';

        break;


        Case '4':

          $question_html = $question_html . '★★★★☆';

        break;


        Case '5':

          $question_html = $question_html . '★★★★★';

        break;

      }

      $question_html = $question_html . "</li>";

      if ($response_array['question_response_text'] != "") {

        $question_html = $question_html . "<ul><li>Comment: " . stripslashes($response_array['question_response_text']) . "</li></ul>";
      }

      $question_html = $question_html . "";

    break;


    // 2: dropdown
      
    Case '2':

      $question_html = "<li>";

      if ($response_array['question_response_value'] == '999') {

        $question_html = $question_html . 'Prefer not to answer';
      }

      elseif ($response_array['question_response_value'] == '888') {

        $question_html = $question_html . 'N/A';

      }

      elseif ($response_array['question_response_value'] == '777') {

        $question_html = $question_html . 'Don\'t know / don\'t recall';

      }

      else

      {
        $question_html = $question_html . $response_array['question_response_value'];
      }

      $question_html = $question_html . "</li>";

      if ($response_array['question_response_text'] != "") {

        $question_html = $question_html . "<ul><li>Comment: " . $response_array['question_response_text'] . "</li></ul>";
      }

      $question_html = $question_html . "";

    break;


    // 3: radio

    Case '3':

      $question_html = "<li>";

      if ($response_array['question_response_value'] == '999') {

        $question_html = $question_html . 'Prefer not to answer';
      }

      elseif ($response_array['question_response_value'] == '888') {

        $question_html = $question_html . 'N/A';

      }

      elseif ($response_array['question_response_value'] == '777') {

        $question_html = $question_html . 'Don\'t know/don\'t recall';

      }

      else

      {
        $temp = ltrim(rtrim($response_array['question_options'],'"'), '"');
        $temp_array = explode(' ## ', $temp);

        foreach($temp_array as $items) {

          $temp_items = explode('::', $items);

          if ($response_array['question_response_value'] == $temp_items[0]) {

            $question_html = $question_html . stripslashes(str_replace($q_source_replace, $q_target_replace, $temp_items[1]));
          }
        }
      }

      $question_html = $question_html . "</li>";

      if ($response_array['question_response_text'] != "") {

        $question_html = $question_html . "<ul><li>Comment: " . $response_array['question_response_text'] . "</li></ul>";
      }

      $question_html = $question_html . "";

    break;


    // 4: radio conditional

    Case '4':

      // root question or sub question?

      // root question

      if ($response_array['sub_question_id'] == NULL) {

        $question_html = "<li>";

        $temp = ltrim(rtrim($response_array['question_options'],'"'), '"');
        $temp_array = explode(' @@ ', $temp);

        $temp_main_q_array = explode(' ## ', $temp_array[0]);

        foreach($temp_main_q_array as $items) {

          $temp_items = explode('::', $items);

          if ($response_array['question_response_value'] == $temp_items[0]) {

            $question_html = $question_html . $temp_items[1];
          }

        }

        $question_html = $question_html . "</li>";

        if ($response_array['question_response_text'] != "") {

          $question_html = $question_html . "<ul><li>Comment: " . stripslashes($response_array['question_response_text']) . "</li></ul><br />";
        }

        $question_html = $question_html . "";

      }

      // sub question

      else

      {
        $temp = ltrim(rtrim($response_array['question_text'],'"'), '"');
        $question_parts_array = explode(' @@ ', $temp);

        // strip possible answers of any root question dependent delimiters
        // (i.e. ~~  -n !!)
        // so we're left with just a string of possible answers, delimited by ##

        $temp = ltrim(rtrim($response_array['question_options'],'"'), '"');
        $temp_array = explode(' @@ ', $temp);

        // replace middle delimiter with standard ## option delimiter

        // Look for: ~~ -n !!
        $pattern = "/\~\~\s-\d+\s!!/";
        $replaced = preg_replace($pattern, "##", $temp_array[1]);

        ## remove initial header

        // Look for (at start of string): -n !! 
        $pattern = "/\A-\d+\s!!\s/";
        $replaced = preg_replace($pattern, "", $replaced);

        $temp_array = explode(' ## ', $replaced);

        // N.B. this regex matches any value/text options, returning matched group,
        // but it's overly complex: \d+\:\:(.+?)((\s\#\#|\~\~)|$)

        
        // If there's a sub question to display, show it as the sub question title
        // e.g. Please compare your experience...

        if (sizeof($question_parts_array) > 1 ) {

          $question_html = $question_html . "<ul><li><b>" . stripslashes(trim(str_replace($q_source_replace, $q_target_replace, $question_parts_array[1]), "\"")) . "</b><ul><br /><li>";
        }

        // Otherwise, just show 'sub-response' as the sub-question text

        else

        {
          $question_html = $question_html . "<ul><li>";

        }

        foreach($temp_array as $items) {

          $temp_items = explode('::', $items);

          if ($response_array['question_response_value'] == $temp_items[0]) {

            $question_html = $question_html . stripslashes(str_replace($q_source_replace, $q_target_replace, $temp_items[1])) . "</li></ul></ul>";
          }
        }
      }

    break;


    // 5: radio conditional checkbox

    Case '5':

      // root question or sub question?

      if ($response_array['sub_question_id'] == '') {

        $question_html = "<li>";

        // root question

        $temp = ltrim(rtrim($response_array['question_options'],'"'), '"');
        $temp_array = explode(' @@ ', $temp);

        $temp_main_q_array = explode(' ## ', $temp_array[0]);

        foreach($temp_main_q_array as $items) {

          $temp_items = explode('::', $items);

          if ($response_array['question_response_value'] == $temp_items[0]) {

            $question_html = $question_html . $temp_items[1];
          }

        }

        $question_html = $question_html . "</li>";

        if ($response_array['question_response_text'] != "") {

          $question_html = $question_html . "<ul><li>Comment: " . $response_array['question_response_text'] . "</li></ul>";
        }

        $question_html = $question_html . "<br />";

      }

      // sub question

      else

      {
        // We will grab all delimited sub responses and combine them into a single string, removing any additional variables/delimiters, so we're just left with something like:

        // 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other ## 12::Consideration of background literature ## 13::Study rationale and/or hypotheses ## 14::Study procedures and/or analysis plans ## 15::Obtained results ## 16::Implementation of preregistered analyses ## 17::Validity and appropriateness of unregistered analyses ## 18::Interpretation of results ## 19::Conclusions of the study ## 20::Research transparency (e.g. data availability, quality of data curation etc.) ## 21::Ethical concerns ## 22::Other

        $temp = ltrim(rtrim($response_array['question_options'],'"'), '"');
        
        // split question options into root and sub responses

        $temp_array = explode(' @@ ', $temp);

        // temp_array[1] contains sub responses

        $sub_responses = "";

        $sub_responses_array = explode(' ~~ ', $temp_array[1]);

        // [0] => -4 !! 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other ^^ 11

        // [1] => -3 !! 12::Consideration of background literature ## 13::Study rationale and/or hypotheses ## 14::Study procedures and/or analysis plans ## 15::Obtained results ## 16::Implementation of preregistered analyses ## 17::Validity and appropriateness of unregistered analyses ## 18::Interpretation of results ## 19::Conclusions of the study ## 20::Research transparency (e.g. data availability, quality of data curation etc.) ## 21::Ethical concerns ## 22::Other ^^ 22


        foreach ($sub_responses_array as $sub_response_part) {

          $temp_sub_response_array_first  = explode(' !! ', $sub_response_part);
          $temp_sub_response_array_second = explode(' ^^ ', $temp_sub_response_array_first[1]);

          $sub_responses = $sub_responses . $temp_sub_response_array_second[0] . " ## ";
        }

        $sub_responses = rtrim($sub_responses, ' ## ');

        $temp_array = explode(' ## ', $sub_responses);


        // Loop through possible sub responses, looking for any sub responses the user gave for this question

        foreach($temp_array as $items) {

          $temp_items = explode('::', $items);

          if ($response_array['sub_question_id'] == $temp_items[0] && $response_array['question_response_value'] == '1') {

            $question_html = $question_html . "<ul><li>" . stripslashes($temp_items[1]);

            if ($response_array['question_response_text'] != "") {

              $question_html = $question_html . "<ul><li>Comment: " . stripslashes($response_array['question_response_text']) . "</li></ul>";
            }

            $question_html = $question_html . "</li></ul>";
          }
        }
      }

    break;


    // 6: textbox

    Case '6':

      $question_html = "<li>Comment: ";

      if ($response_array['question_response_text'] != '') {

        $question_html = $question_html . $response_array['question_response_text'];
      }

      else

      {
        $question_html = $question_html . "No response";
      }

    $question_html = $question_html . "</li>";

    break;


    // 7: matrix

    Case '7':

      $temp = ltrim(rtrim($response_array['question_options'],'"'), '"');
      $temp_array = explode(' ## ', $temp);

      foreach($temp_array as $items) {

        $temp_items = explode('::', $items);

        if ($response_array['sub_question_id'] == $temp_items[0] && $response_array['question_response_value'] == '999') {

          $question_html = $question_html . "<li>" . $temp_items[1] . ": Prefer not to answer</li>";
        }

        elseif ($response_array['sub_question_id'] == $temp_items[0] && $response_array['question_response_value'] == '888') {

          $question_html = $question_html . "<li>" . $temp_items[1] . ": N/A</li>";

        }

        elseif ($response_array['sub_question_id'] == $temp_items[0] && $response_array['question_response_value'] == '777') {

          $question_html = $question_html . "<li>" . $temp_items[1] . ": Don't know / don't recall</li>";

        }

        elseif ($response_array['sub_question_id'] == $temp_items[0]) {

          $question_html = $question_html . "<li>" . $temp_items[1] . ': ' . $response_array['question_response_value'] . "</li>";
        }
      }

    break;


  }

  return $question_html;
}


function getResponseStructure (&$response_array, &$categories, &$question_ids, $stage_id, $journal_name) {

  $struct_html = "";
  $q_target_replace = [$stage_id, $journal_name];
  $q_source_replace = ["{stage_number}", "{journal_name}"];

  // For each question ID, look through response array
  // matching response(s)

  for ($j = 0; $j < count($categories); $j++) {

    // Add a header for this category

    $cat_title_displayed = 0;

    if ($cat_title_displayed == 0) {

      $cat_title_displayed = 1;

      // Add a horizontal above each survey category (except the first)

      if ($j != 0) {

        $struct_html = $struct_html . "<hr>";
      }

      $struct_html = $struct_html . "<h3>" . $categories[$j]['category_title'] . "</h3>";
    }

    if (isset($question_ids)) {

      foreach ($question_ids as $qid) {

        if ($qid['question_category'] == $categories[$j]['category_id']) {

          $q_root_displayed = false;

          // Display each question root only once.
          // This is important where there are multiple responses
          // to a single question (e.g. sub-options, checkboxes etc)

          if ($q_root_displayed == false) {

            // Only show initial question text - important for
            // condition questions, where initial and follow-up
            // questions are embedded in some question field
            // in database, delimited by @@

            $temp = ltrim(rtrim($qid['question_text'],'"'), '"');
            $question_parts_array = explode(' @@ ', $temp);

            $struct_html = $struct_html . "<b>" . stripslashes(trim(str_replace($q_source_replace, $q_target_replace, $question_parts_array[0]), "\"")) . "</b>";

            // Hack to display help text for matrix questions - important to give context to numerical responses

            if ($qid['question_type'] == '7' && $qid['question_helptext'] != '') {

              $struct_html = $struct_html . " - <i>" . stripslashes(trim($qid['question_helptext'], "\"")) . "</i>";
            }

            $q_root_displayed = true;

            $struct_html = $struct_html . "<ul>";

          }

          foreach ($response_array as $response) {

            $matrix_comment_displayed = false;

            if ($response['question_id'] == $qid['question_id']) {

              $struct_html = $struct_html . getSurveyResponse($response, $stage_id, $journal_name);

              // Hack to show any matrix comments - can't easily display within getSurveyResponse()
              // due to the comment not pertaining to any particular response
              // i.e. a matrix comment is its own response entry, with only a review ID and question ID

              if ($response['question_type'] == '7' && $response['sub_question_id'] == '' && $response['question_response_value'] == '' && $matrix_comment_displayed == false) {

                $struct_html = $struct_html . "<li>Comment: " . $response['question_response_text'] . "</li><br />";
                $matrix_comment_displayed = true;
              }
            }
          }

          $struct_html = $struct_html . "</ul>";
        }
      }
    }
  }

  return $struct_html;
}

?>
<html lang="en">
<head>
<meta charset="utf-8">

<title>Feedback summary</title>
</head>
<body>

<style>
x-tabs .tabs [tab-for] {min-width: auto;}
</style>
<main>
<h1>Feedback summary</h1>
<?php

// Both stages (1 and 2)

if ($single_stage == false) {

  $s1_year_range = "";
  $s2_year_range = "";

  echo $s1_review_metadata[0]['role_text'] . " feedback (" . $stage_info . ")";

  // Display per-stage journal information (for where manuscripts have been
  // transferred between Stage 1 and 2)

  if ($s1_review_metadata[0]['journal_name'] == $s2_review_metadata[0]['journal_name']){

    echo " at <i>" . $s1_review_metadata[0]['journal_name'] . "</i><br />";

    if (!in_array($s1_review_metadata[0]['start_date'], ['999', '777']) || !in_array($s2_review_metadata[0]['start_date'], ['999', '777'])) {

      echo "<ul>";

      if (!in_array($s1_review_metadata[0]['start_date'], ['999', '777'])) {

        if ($s1_review_metadata[0]['start_date'] == $s1_review_metadata[0]['end_date']) {

          $s1_year_range = " (" . $s1_review_metadata[0]['start_date'] . ")";
        }

        else

        {
          $s1_year_range = " (" . $s1_review_metadata[0]['start_date'] ." - " . $s1_review_metadata[0]['end_date'] . ")";
        }

        echo "<li>Stage 1 " . $s1_year_range . "</li>";
      }

      if (!in_array($s2_review_metadata[0]['start_date'], ['999', '777'])) {

        if ($s2_review_metadata[0]['start_date'] == $s2_review_metadata[0]['end_date']) {

          $s2_year_range = " (" . $s2_review_metadata[0]['start_date'] . ")";
        }

        else

        {
          $s2_year_range = " (" . $s2_review_metadata[0]['start_date'] ." - " . $s2_review_metadata[0]['end_date'] . ")";
        }

        echo "<li>Stage 2 " . $s2_year_range . "</li>";
      }

      echo "</ul>";
    }
  }

  else

  {
    // Display per-stage year range info (over what period the user specified
    // each stage took place)

    if (!in_array($s1_review_metadata[0]['start_date'], ['999', '777'])) {

      if ($s1_review_metadata[0]['start_date'] == $s1_review_metadata[0]['end_date']) {

        $s1_year_range = " (" . $s1_review_metadata[0]['start_date'] . ")";
      }

      else

      {
        $s1_year_range = " (" . $s1_review_metadata[0]['start_date'] ." - " . $s1_review_metadata[0]['end_date'] . ")";
      }
    }

    if (!in_array($s2_review_metadata[0]['start_date'], ['999', '777'])) {

      if ($s2_review_metadata[0]['start_date'] == $s2_review_metadata[0]['end_date']) {

        $s2_year_range = " (" . $s2_review_metadata[0]['start_date'] . ")";
      }

      else

      {
        $s2_year_range = " (" . $s2_review_metadata[0]['start_date'] ." - " . $s2_review_metadata[0]['end_date'] . ")";
      }
    }

    echo "<ul><li>Stage 1 at <i>" . $s1_review_metadata[0]['journal_name'] . "</i>" . $s1_year_range . "</li>";
    echo     "<li>Stage 2 at <i>" . $s2_review_metadata[0]['journal_name'] . "</i>" . $s2_year_range . "</li></ul>";
  }

  if ($s1_review_metadata[0]['role_text'] == "Author") {

    echo "Manuscript outcome:<ul>";
    echo "<li>Stage 1: " . $s1_review_metadata[0]['stage_text'] . "</li>";
    echo "<li>Stage 2: " . $s2_review_metadata[0]['stage_text'] . "</li></ul>";
  }

  if (!in_array($stages_metadata[0]['academic_role_id'], ['999', '777', '']) || !in_array($stages_metadata[1]['academic_role_id'], ['999', '777', ''])) {

    echo "Academic career status:<ul>";

    if (!in_array($stages_metadata[0]['academic_role_id'], ['999', '777', ''])) {

      echo "<li>Stage 1: ";

      if ($stages_metadata[0]['academic_role_id'] == 16) {

        echo $stages_metadata[0]['academic_role_text'];
      }

      else

      {
        echo $stages_metadata[0]['academic_role'];
      }

      echo "</li>";
    }

    if (!in_array($stages_metadata[1]['academic_role_id'], ['999', '777', ''])) {

      echo "<li>Stage 2: ";

      if ($stages_metadata[1]['academic_role_id'] == 16) {

        echo $stages_metadata[1]['academic_role_text'];
      }

      else

      {
        echo $stages_metadata[1]['academic_role'];
      }

      echo "</li>";
    }

    echo "</ul>";
  }

  echo "Your reference code: " . $s1_review_metadata[0]['review_ref'];

  if ($s2_review_metadata[0]['doi'] != '') {

    echo "<br /><br />Manuscript DOI: <a target='_new' href='https://doi.org/" . $s2_review_metadata[0]['doi'] . "'>" . $s2_review_metadata[0]['doi'] . "</a>";

  }
}

// Single stage (1 or 2)

else

{
  $year_range = "";

    if (!in_array($review_metadata[0]['start_date'], ['999', '777'])) {

      if ($review_metadata[0]['start_date'] == $review_metadata[0]['end_date']) {

        $year_range = " (" . $review_metadata[0]['start_date'] . ")";
      }

      else

      {
        $year_range = " (" . $review_metadata[0]['start_date'] ." - " . $review_metadata[0]['end_date'] . ")";
      }
    }

  echo $review_metadata[0]['role_text'] . " feedback (" . $stage_info . ") at <i>" . $review_metadata[0]['journal_name'] . "</i>" . $year_range . "<br /><br />";

  if ($review_metadata[0]['role_text'] == "Author") {

    echo "Manuscript outcome: " . $review_metadata[0]['stage_text'] . "<br /><br />";
  }

  if (!in_array($review_metadata[0]['academic_role_id'], ['999', '777', ''])) {

    echo "Academic career status:<ul>";
    echo "<li>Stage " . $review_metadata[0]['stage_id'] . ": ";

    if ($review_metadata[0]['academic_role_id'] == 16) {

      echo $review_metadata[0]['academic_role_text'];
    }

    else

    {
      echo $review_metadata[0]['academic_role'];
    }

    echo "</li></ul>";
  }

  echo "Your reference code: " . $review_metadata[0]['review_ref'];

  if ($review_metadata[0]['doi'] != '') {

    echo "<br /><br />Manuscript DOI: <a target='_new' href='https://doi.org/" . $review_metadata[0]['doi'] . "'>" . $review_metadata[0]['doi'] . "</a>";

  }
}


?>
<br /><br />
<h2>Your feedback</h2>

<div class="tabset">


<?php
if ($single_stage == false) {
?>

  <!-- Tab 1 -->
  <input type="radio" name="tabset" id="tab1" aria-controls="stage1" checked>
  <label for="tab1">Stage 1</label>
  <!-- Tab 2 -->
  <input type="radio" name="tabset" id="tab2" aria-controls="stage2">
  <label for="tab2">Stage 2</label>

<?php
}

else

{
?>
    
 <!-- Tab 1 -->
  <input type="radio" name="tabset" id="tab1" aria-controls="stage<?php echo $review_metadata[0]['stage_id'];?>" checked>
  <label for="tab1">Stage <?php echo $review_metadata[0]['stage_id'];?></label>

<?php
}
?>

<div class="tab-panels">

<?php

if ($single_stage == false) {
?>

<section id="stage1" class="tab-panel">
  <?php echo $s1_html; ?>
</section>

<section id="stage2" class="tab-panel">
  <?php echo $s2_html; ?>
</section>

<?php
}

else

{

?>

<section id="stage<?php echo $review_metadata[0]['stage_id'];?>" class="tab-panel">
  <?php echo $single_stage_html ?>
</section>

<?php
}
?>
  </div>
</div>
</div>
</main>
</body>
</html>
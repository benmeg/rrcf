<?php

header("Content-Type: application/json; charset=utf-8", true);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();

require '../assets/setup/env.php';
require '../assets/setup/db.inc.php';
require '../assets/includes/auth_functions.php';
require '../assets/includes/security_functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['auth']))
    $_SESSION['expire'] = ALLOWED_INACTIVITY_TIME;

generate_csrf_token();
check_remember_me();

check_verified();

require 'functions.php';

$user_id = $_SESSION['id'];

if (isset($_GET['uuid']) && !empty($_GET['uuid'])) {

	$uuid = $_GET['uuid'];
	$url  = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/selector.php?uuid=" . $uuid . "&stage=";
}

if (isset($_GET['stage']) && !empty($_GET['stage'])) {

	$stage = $_GET['stage'];
}

if (isset($_GET['email']) && !empty($_GET['email'])) {

	$emailstring = str_replace(' ', '', $_GET['email']);
	$emails      = explode(",", $emailstring);
}

if (isset($_GET['name']) && !empty($_GET['name'])) {

	$name = $_GET['name'];
}

if (isset($_GET['comment']) && !empty($_GET['comment'])) {

	$comment = $_GET['comment'];
}

if (isset($_GET['email_share']) && !empty($_GET['email_share'])) {

	$email_share = $_GET['email_share'];
}

if (isset($_GET['journal_name_1']) && !empty($_GET['journal_name_1'])) {

	$journal_name_1 = $_GET['journal_name_1'];
}

if (isset($_GET['journal_name_2']) && !empty($_GET['journal_name_2'])) {

	$journal_name_2 = $_GET['journal_name_2'];
}

if (isset($_GET['year_1']) && !empty($_GET['year_1'])) {

	$year_1 = $_GET['year_1'];
}

if (isset($_GET['year_2']) && !empty($_GET['year_2'])) {

	$year_2 = $_GET['year_2'];
}




$body = "Hello,\n\n";

$body = $body . "A co-author of yours";

if (isset($name) || !isset($email_share) ) {

 $body = $body . " (";

}

if (isset($name)) {

	$body = $body . $name;
}

if (isset($name) && !isset($email_share)) {

	$body = $body . " ";
}

if (!isset($email_share)) {

	$body = $body . $_SESSION['email'] . "";

}

if (isset($name) || !isset($email_share) ) {

 $body = $body . ")";

}

$body = $body . " has invited you to give feedback on the peer review process for a Registered Reports manuscript you both worked on.\n\n";

$body = $body . "The manuscript was peer reviewed at '" . $journal_name_1 . "'";

if (isset($year_1)) {

	$body = $body . " (" . $year_1;
}

if (isset($year_2) && !isset($journal_name_2)) {

	$body = $body . " and " . $year_2;
}

if (isset($year_1)) {

	$body = $body . ")";
}

if (isset($journal_name_2)) {

	$body = $body . " and " . $journal_name_2;

	if (isset($year_2)) {

		$body = $body . " (" . $year_2 . ")";
	}
}

$body = $body . ".\n\n";

if (isset($comment)) {

	$body = $body . "To help understand which manuscript this is, they have provided the following comment:\n\n";
	$body = $body . '"' . $comment . '"' . "\n\n";
}

$body = $body . "You have been invited to give feedback on the following stage(s) of peer review for the above manuscript:\n\n";

Switch($stage) {

	Case "1":

		$body = $body . "Stage 1: " . $url . "1\n\n";

	break;

	Case "2":

		$body = $body . "Stage 2: " . $url . "2\n\n";

	break;

	Case "3":

		$body = $body . "Stage 1: " . $url . "1\n";
		$body = $body . "Stage 2: " . $url . "2\n\n";

	break;
}

$body = $body . "Giving feedback requires creating an account - you can do this by clicking above, after which you can register and then give feedback.\n\n";

$body = $body . "If you've already given feedback for this manuscript/stage, don't do it again, but if you'd like to link your existing feedback (for the purposes of research) to this invitation, forward this email to us along with the reference code you chose when originally giving the feedback, and we'll link it.\n\n";

$body = $body . "The broad goal of the Registered Reports Community Feedback site is to collect data regarding how various aspects of the Registered Reports process are implemented across academic journals. Little is known about how the speed and quality of the Registered Reports peer review process varies across journals, or how, at a particular journal, the Registered Reports peer review process compares to traditional peer review.\n\n";

$body = $body . "Data collected will be aggregated and displayed in ranking form, showing how journals were rated across a range of ratings (by both manuscript authors and reviewers). We hope this will both assist the community in choosing where to submit manuscripts, and incentivise publishers to improve the implementation of the Registered Reports process at their journals.\n\n";

$body = $body . "Additionally, data collected will be used as part of my PhD on metascience.\n\n";

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
$body = $body . "CF10 3AT\n\n";
$body = $body . "Email: psychethics@cardiff.ac.uk\n\n";
$body = $body . "Tel: +44 (0) 029208 70707\n";

$success_emails = array();
$fail_emails    = array();

$send_success   = true;

foreach ($emails as $email) {

	try {

	    $mail = new PHPMailer(true);

	    $mail->isSMTP();
	    $mail->Host = MAIL_HOST;
	    $mail->SMTPAuth = SMTP_AUTH;
	    $mail->Username = MAIL_USERNAME;
	    $mail->Password = MAIL_PASSWORD;
	    $mail->SMTPSecure = MAIL_ENCRYPTION;
	    $mail->Port = MAIL_PORT;

	    $mail->setFrom(MAIL_USERNAME, APP_NAME);
	    $mail->addAddress($email);

	    $mail->isHTML(false);
	    $mail->Subject = "Invitation from your co-author to give feedback on Registered Report review process";
	    $mail->Body    = $body; 

	    $mail->send();

		// success sending email
	    array_push($success_emails, $email);		
	} 

	// failure sending email

	catch (Exception $e) {

		$send_success = false;
		array_push($fail_emails, $email);
	}
}

// Get a count of how many successful emails were sent out
// so we can keep a log of invites sent for each manuscript (UUID)

if (sizeof($success_emails) > 0 ) {

	// Connect to database

    $db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

    /* check connection */

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

	$sql = "INSERT INTO `feedback_invite_log` (`invite_uuid`, `invite_count`) VALUES (UUID_TO_BIN('" . $uuid . "'),'" . sizeof($success_emails) . "') ON DUPLICATE KEY UPDATE `invite_count`=`invite_count`+" . sizeof($success_emails) . ";";

	if ($result = mysqli_query($db_handle, $sql)) {

    	// insert successful!
  	}
}

if ($send_success == true) {

	$arr = array('emailsent' => 'true');

	array_push($arr, 'emails_success');
	$arr['emails_success'] = implode(', ', $success_emails);
}

else

{
	$arr = array('emailsent' => 'false');

	array_push($arr, 'emails_success');
	$arr['emails_success'] = implode(', ', $success_emails);

	array_push($arr, 'emails_fail');
	$arr['emails_fail'] = implode(', ', $fail_emails);
}


echo json_encode($arr);
?>
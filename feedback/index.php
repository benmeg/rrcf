<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('TITLE', "Feedback");

include '../assets/layouts/header.php';
check_verified();

require 'functions.php';

$latest_review_role = 0;

if(isset($_GET['survey_success']) && $_GET['survey_success'] == '1') {
	
	$survey_success = true;

	// Get review UUID of most recent review by this user (should be the review just entered into the database)

	// N.B. Due to the questions.php calling process_survey.php via XHR (XMLHttpRequest) and redirecting to this page (/feedback/index.php) there is no review UUID that is passed.

	// Connect to database

	$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

	/* check connection */

	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$user_id = $_SESSION['id'];

	$sql = "SELECT BIN_TO_UUID(`review_uuid`) AS review_uuid, review_ref, role_type FROM feedback_reviews WHERE `user_id` = '" . $user_id . "' ORDER BY review_id DESC LIMIT 1";

	if ($result = mysqli_query($db_handle, $sql)) {

		if (mysqli_num_rows($result) > 0) {

		$review_uuid_array = mysqli_fetch_all($result, MYSQLI_ASSOC);
		}
	}

	$latest_review_uuid = $review_uuid_array[0]['review_uuid'];
	$latest_review_ref  = $review_uuid_array[0]['review_ref'];
	$latest_review_role = $review_uuid_array[0]['role_type'];
}

?>


<main>
<?php
if (isset($survey_success) && $survey_success == true) { ?>
	<p style="color: green;" id="confirmDiv">&#9989; Feedback completed and saved successfully!</p>
<?php
}

// Only show this invite link if the most recent piece of feedback as given as an author

if ($latest_review_role == 1) {
?>
<img alt='Email icon' src='../assets/images/email-icon.svg' width='16' height='16' border='0' />&nbsp;<a title="Invite your co-authors" href="invite_coauthor.php?uuid=<?php echo $latest_review_uuid?>" rel="modal:open" title="Invite a co-author to give feedback on the manuscript (REF code: <?php echo $latest_review_ref ?>)">Please invite your co-authors</a> to give feedback on this manuscript (REF code: <?php echo $latest_review_ref ?>).<br /><br />
<?php
}
?>
<h2>My feedback</h2>


<a href="selector.php">Add feedback</a><br /><br />
<a href="my_feedback.php">View my previous feedback</a><br /><br />
</main>

<script type="text/javascript" src="../assets/js/clipboard.min.js"></script>
<script type="text/javascript" src="../assets/js/popper.min.js"></script>
<script type="text/javascript" src="../assets/js/tippy-bundle.umd.min.js"></script>

<?php
if (isset($survey_success) && $survey_success == true) { ?>

<script>
$(document).ready(()=>{
    $("#confirmDiv").effect( "highlight", {color:"green"}, 2000 );
});
</script>
<?php
}

include '../assets/layouts/footer.php'

?>
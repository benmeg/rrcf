<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

$user_id = $_SESSION['id'];

$journal1 = "";
$journal2 = "";
$year1    = "";
$year2    = "";

$refcode  = "";


$url = ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/selector.php?uuid=" . $_GET['uuid'] . "&stage=";

// Connect to database

$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// get journal info for specified UUID
//
// this gets info across all reviews for UUID:

$sql = "SELECT `feedback_reviews`.`review_ref`, `journals`.`journal_name`, stage_id, `feedback_stages`.`stage_text`, start_date, end_date FROM feedback_reviews INNER JOIN `journals` ON `feedback_reviews`.`journal_id` = `journals`.`journal_id` INNER JOIN `feedback_stages` ON `feedback_reviews`.`sub_stage_id` = `feedback_stages`.`sub_stage_id` WHERE review_uuid = UUID_TO_BIN('" . $_GET['uuid'] . "') AND `feedback_reviews`.`user_id` = '" . $user_id . "' GROUP BY stage_id ORDER BY stage_id ASC";

if ($result = mysqli_query($db_handle, $sql)) {

	if (mysqli_num_rows($result) > 0) {

		$user_reviews_by_stage = mysqli_fetch_all($result, MYSQLI_ASSOC);
		$refcode = $user_reviews_by_stage[0]['review_ref'];

		if (sizeof($user_reviews_by_stage) == 2) {

			if ($user_reviews_by_stage[0]['start_date'] != 777 && $user_reviews_by_stage[0]['start_date'] != 999) {

				if ($user_reviews_by_stage[0]['start_date'] == $user_reviews_by_stage[0]['end_date']) {

					$year1 = $user_reviews_by_stage[0]['start_date'];
				}

				else

				{
					$year1 = $user_reviews_by_stage[0]['start_date'] . "-" . $user_reviews_by_stage[0]['end_date'];
				}
			}

			if ($user_reviews_by_stage[1]['start_date'] != 777 && $user_reviews_by_stage[1]['start_date'] != 999) {

				if ($user_reviews_by_stage[1]['start_date'] == $user_reviews_by_stage[1]['end_date']) {

					$year2 = $user_reviews_by_stage[1]['start_date'];
				}

				else

				{
					$year2 = $user_reviews_by_stage[1]['start_date'] . "-" . $user_reviews_by_stage[1]['end_date'];
				}
			}

			if ($user_reviews_by_stage[0]['journal_name'] == $user_reviews_by_stage[1]['journal_name']) {

				$journal1 = $user_reviews_by_stage[0]['journal_name'];
			}

			else

			{
				$journal1 = $user_reviews_by_stage[0]['journal_name'];
				$journal2 = $user_reviews_by_stage[1]['journal_name'];
			}
		}

		else

		{
			$journal1 = $user_reviews_by_stage[0]['journal_name'];
			
			if ($user_reviews_by_stage[0]['start_date'] != 777 && $user_reviews_by_stage[0]['start_date'] != 999) {

				if ($user_reviews_by_stage[0]['start_date'] == $user_reviews_by_stage[0]['end_date']) {

					$year1 = $user_reviews_by_stage[0]['start_date'];
				}

				else

				{
					$year1 = $user_reviews_by_stage[0]['start_date'] . "-" . $user_reviews_by_stage[0]['end_date'];
				}
			}
		}
	}
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Invite co-authors</title>
</head>
<body>
<main>
<h1>Invite co-authors</h1>
Invite co-authors on this manuscript to give Stage 1 and/or Stage 2 feedback.<br />

<ul><li>Your reference code: <i><?php echo $refcode ?></i></li>
<li>Reviewed at:

<?php
echo "<i>" . $journal1 . "</i>";

if ($journal2 != "") {

	echo " and <i>" . $journal2 . "</i>";

}
?></li></ul>

<form method="post" id="inviteform" name="inviteform">
<label id="stage_label" for="stage">Which stage(s) of the manuscript do you wish to invite your co-author(s) to give feedback on?</label><br />
<select name="stage" id="stage">
  <option value="0">Please select...</option>
  <option value="1">Stage 1</option>
  <option value="2">Stage 2</option>
  <option value="3">Stage 1 and 2</option>
</select>&nbsp;<span id="tooltip_stage" class="survey-tooltip" tabindex="-1">?</span>
<br /><br />
<label id="email_label" for="email">Co-authors' email (required)</label>
<input type="text" id="email" name="email" width="80%">&nbsp;<span id="tooltip_email" class="survey-tooltip" tabindex="-1">?</span>
<br /><br />
<label id="name_label" for="name">Your name (optional)</label>
<input type="text" id="name" name="name">&nbsp;<span id="tooltip_name" class="survey-tooltip" tabindex="-1">?</span>
<br /><br />
<label id="comment_label" for="comment">Comment (optional, but strongly encouraged)</label>&nbsp;<span id="tooltip_comment" class="survey-tooltip" tabindex="-1">?</span><br />
<textarea id="comment" name="comment" rows="4" cols="30*"></textarea>
<br /><br />
<input type="checkbox" id="email_share" name="email_share" value="1"><label id="email_share_label" for="email_share">Don't share my email address in this invitation email</label><br /><br />
<input type="button" id="submitbutton" name="submitbutton" value="Send invite(s)">&nbsp;<span id="submit_span"></span>
<input type="hidden" name="journal_name_1" value="<?php echo $journal1 ?>">
<input type="hidden" name="journal_name_2" value="<?php echo $journal2 ?>">
<input type="hidden" name="year_1" value="<?php echo $year1 ?>">
<input type="hidden" name="year_2" value="<?php echo $year2 ?>">
<input type="hidden" name="uuid" value="<?php echo $_GET['uuid'] ?>">
</form>
<br />
<p>N.B.
<ul>
	<li>Anyone you invite will NOT be able to see feedback you have given on this or any other manuscript.</li>
	<li>Journal and year information will be shared as part of the invitation email.</li>
</ul></p><br />

<h2>Sharable links</h2>
<p>If you wish to invite co-authors without using our email system (e.g. for increased anonymity), here are links you can copy/paste into whatever medium you wish.</p>

<label id="stage1_label" for="stage1">Stage 1</label>
<input id="stage1" value="<?php echo $url ?>1" readonly>

<button class="btn" data-clipboard-target="#stage1">
    <img width="13" height="13" src="../assets/images/clippy.svg" alt="Copy icon" title="Copy to clipboard">
</button>
<br />
<label id="stage2_label" for="stage2">Stage 2</label>
<input id="stage2" value="<?php echo $url ?>2" readonly>

<button class="btn" data-clipboard-target="#stage2">
    <img width="13" height="13" src="../assets/images/clippy.svg" alt="Copy icon" title="Copy to clipboard">
</button>

<p>N.B. We would recommend sharing a short sentence or context to help the person receiving these links understand which manuscript you are referring to (although this is not required).</p>
</main>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js" integrity="sha512-XLo6bQe08irJObCc86rFEKQdcFYbGGIHVXcfMsxpbvF8ompmd1SNJjqVY5hmjQ01Ts0UmmSQGfqpt3fGjm6pGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tippy.js/6.3.1/tippy-bundle.umd.min.js" integrity="sha512-xULG5PJghLB+bsq9AuFbarjLtvtxDVjh47LlxXG25U2v3m+YB7OvNjA6m7pyampiwPVvrXv3Wupiv8oSX+5lRw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
// https://stackoverflow.com/a/46646052

$(document).ready(function (){
	$("#submitbutton").click(function (e){

		/* ensure the user has selected a stage */
		if ($('#stage').val() == "0") {
			$("#submit_span").html("&#9888;&#65039;&nbsp;Please select a stage");
		}

		else

		{
			/* ensure the user has entered a co-author email address */

			if ($('#email').val() == "") {
				$("#submit_span").html("&#9888;&#65039;&nbsp;Please enter at least one email address for a co-author");
			}

			else

			{
				/* check that each email address entered is in the correct format */

				if (validateEmailList($('#email').val()) == false) {
					$("#submit_span").html("&#9888;&#65039;&nbsp;Please ensure each email address you have entered is a valid address.");
				}

				else

				{
					$("#submit_span").html("<img src=\"../assets/images/ajax-loader.gif\">");
					$("#submitbutton").val('Please wait...');
					$("#submitbutton").prop('disabled', true);

					e.preventDefault();
					$.ajax({
						type: "GET",
						/* async: false, */
						url:"send_invitation.php",
						data: $('form#inviteform').serialize(),
						dataType: 'json',
						cache: false,
						success: function (response) {

							$("#submitbutton").prop('disabled', false);

							console.log(response.email);
							
							switch(response.emailsent) {

								case "true":
									$("#submitbutton").prop('disabled', false);
									$("#submitbutton").val('Send invite(s)');
									$("#submit_span").html("&#9989;&nbsp;Invitation sent successfully to: " + response.emails_success);
									$("#submit_span").effect( "highlight", {color:"green"}, 2000 );

								break;

								case "false":
					  				$("#submitbutton").prop('disabled', false);
									$("#submitbutton").val('Send invite(s)');
									$("#submit_span").html("&#9888;&#65039;&nbsp;Error sending invitation to: " + response.emails_fail);

									if (response.emails_success != '') {
										$("#submit_span").append("<br /><br />&#9989;&nbsp;Invitation sent successfully to: " + response.emails_success);
									}

								break;
							}

						},
						error: function () {

							$("#submitbutton").prop('disabled', false);
							$("#submitbutton").val('Send invite(s)');
							$("#submit_span").html("&#9888;&#65039;&nbsp;Error - try again");
						}

					});
				}
			}
		}
	});
});

new ClipboardJS('.btn');

// Create tooltips

tippy('#tooltip_email', { content: 'You may enter multiple authors\' email addresses, separated by commas.<br /><br />N.B. Any email addresses you provide are NOT saved in our system.', allowHTML: true});

tippy('#tooltip_stage', { content: 'If a Stage 1 manuscript did not progress to Stage 2, although you may send a Stage 2 invite link, it will not be usable.', allowHTML: true});

tippy('#tooltip_name', { content: 'Providing this will help customise the email to any co-authors you specify.<br /><br />N.B. Any name you provide is NOT saved in our system.', allowHTML: true});

tippy('#tooltip_comment', { content: 'Any comment entered will be added to the invitation email, providing context.<br /><br />Try to add something that will help the co-author identify the paper (e.g. Experimental manipulation of visual perception in babies).', allowHTML: true});

/* https://stackoverflow.com/a/48676962 */

function validateEmailList(raw){
    var emails = raw.split(',')

    var valid = true;
    var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    for (var i = 0; i < emails.length; i++) {
        if( emails[i] === "" || !regex.test(emails[i].replace(/\s/g, ""))){
            valid = false;
        }
    }
    return valid;
}

$("#stage").change(function() { 

	if ( $(this).val() != "0") {

		$("#submit_span").html("");
	}
});
</script>
</body>
</html>
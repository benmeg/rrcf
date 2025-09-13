<?php
// do a manual logged in check so we can set an invite cookie

if (!isset($_SESSION['auth'])) {

	// Set invite cookie for external invite links, not if the logged in user is completing
	// their own feedback

	if((isset($_GET['uuid']) && isset($_GET['stage'])) && !isset($_GET['self'])) {

		setcookie("invite_uuid",  $_GET['uuid'],  time()+30*24*60*60, "/");
		setcookie("invite_stage", $_GET['stage'], time()+30*24*60*60, "/");
	}
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header('Content-type: text/html; charset=utf-8');
header("Pragma: no-cache");

define('TITLE', "Add feedback");
include '../assets/layouts/header.php';

check_verified();

require 'functions.php';

$user_id = $_SESSION['id'];
parse_str($_SERVER['QUERY_STRING'], $params);

$show_survey     = true;
$warning_message = "";


// Connect to database

$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}


// Check if this is an invite review (i.e. with a querystring of ?UUID=)

if(isset($params['uuid']) && isset($params['stage'])) {

	$uuid  = $params['uuid'];
	$stage = $params['stage'];

	if (!isValidUuid($uuid)) {

		$warning_message = "This is not a valid invitation link";
		$show_survey     = false;
	}

	else

	{
		$journal_ids   = array();
		$sub_stage_ids = array();

		// check if any reviews exist with this UUID

		$sql = "SELECT review_id, stage_id, sub_stage_id, role_type, journal_id, start_date, end_date FROM feedback_reviews WHERE review_uuid = UUID_TO_BIN('" . $uuid . "') GROUP BY stage_id";

		if ($result = mysqli_query($db_handle, $sql)) {

			if (mysqli_num_rows($result) > 0) {

				$reviews_by_stage = mysqli_fetch_all($result, MYSQLI_ASSOC);
			}
		}

		if (!isset($reviews_by_stage)) {

			$warning_message = "No reviews were found for this invitation link";
			$show_survey = false;
		}

		else

		{
			// Check to see if reviews exist for this UUID for both stages (i.e. 2 records are returned)

			if (sizeof($reviews_by_stage) == 2) {

				$completed_reviews_by_uuid = 3;

				if ($reviews_by_stage[0]['journal_id'] == $reviews_by_stage[1]['journal_id']) {

					$journal_ids[] = $reviews_by_stage[0]['journal_id'];
				}

				else

				{
					array_push($journal_ids, $reviews_by_stage[0]['journal_id'], $reviews_by_stage[1]['journal_id']);
				}

				array_push($sub_stage_ids, $reviews_by_stage[0]['sub_stage_id'], $reviews_by_stage[1]['sub_stage_id']);
			}

			// Reviews exist for this UUID only for a single stage (Stage 1 or Stage 2)

			else

			{
				$completed_reviews_by_uuid = $reviews_by_stage[0]['stage_id'];

				$journal_ids[]   = $reviews_by_stage[0]['journal_id'];
				$sub_stage_ids[] = $reviews_by_stage[0]['sub_stage_id'];
			}

			// check if the logged in user has already completed any pieces of feedback
			// i.e. Stage 1, Stage 2, or Stage 1 & 2

			$sql = "SELECT review_id, stage_id, role_type, review_ref, journal_id, doi FROM `feedback_reviews` WHERE `review_uuid` = UUID_TO_BIN('" . $uuid . "') AND `user_id` = '" . $user_id . "' ORDER BY stage_id ASC;";

			if ($result = mysqli_query($db_handle, $sql)) {

				if (mysqli_num_rows($result) > 0) {

					$existing_uuid_reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
				}
			}

			// Determine which stage(s) of reviews the users has already completed.

			// This caters for users who may be returning via the invite link
			// to complete a second piece of feedback (and will select the corresponding
			// orphan radio - OR - create a hidden form field indicating this).

			// It will also deal with users trying to reuse the invite link

			// if there are 2 records, we know the user has completed both
			// halves (Stage 1 and Stage 2) of the feedback for this UUID

			if (!isset($existing_uuid_reviews)) {

				$completed_reviews_by_user = 0;
			}

			else if (sizeof($existing_uuid_reviews) == 2) {

				$completed_reviews_by_user = 3;
			}

			else

			{
				$completed_reviews_by_user = $existing_uuid_reviews[0]['stage_id'];
			}

			if ($completed_reviews_by_user == 3) {

				$warning_message = "You have already given Stage 1 and 2 feedback for this manuscript.";

				$show_survey = false;

				unset($_COOKIE['invite_uuid']);
				unset($_COOKIE['invite_stage']);

				setcookie("invite_uuid",  "", time() - 3600, "/");
				setcookie("invite_stage", "", time() - 3600, "/");
			}

			// otherwise, there is only 1 record, so we check to see if it's
			// Stage 1 or Stage 2

			else

			{
				// Grab ref code user specified for feedback already completed
				// for this stage - we will use this to populate the
				// ref code form field

				if ($completed_reviews_by_user != 0) {

					$orphan_ref_code_invite = $existing_uuid_reviews[0]['review_ref'];
				}

				// Check if stage requested by the user has already been completed

				// If existing manuscript outcome for the stage 1 feedback was either:
				//
			    // 1: Desk rejected prior to peer review
			    // or
			    // 2: Rejected after one or more rounds of specialist peer review
			    //
			    // and the user has either requested (via URL)
			    // a Stage 2 review, or they have already completed
			    // a Stage 1 review for this manuscript (in which case
			    // our system will generally offer the user the option
			    // to complete the corresponding Stage 2 feedback,
			    // assuming they have not yet completed it)
			    //
			    // we check to see if these conditions have been met,
			    // and do not allow them to provide any feedback,
			    // giving an appropriate message

				if ($stage == 2 && ($sub_stage_ids[0] == 1 || $sub_stage_ids[0] == 2 || $sub_stage_ids[0] == 8)) {

					$warning_message = "Sorry, you cannot give Stage 2 feedback for this manuscript because existing Stage 1 feedback indicates that this manuscript did not progress to Stage 2.";

					$show_survey = false;

					unset($_COOKIE['invite_uuid']);
					unset($_COOKIE['invite_stage']);

					setcookie("invite_uuid",  "", time() - 3600, "/");
					setcookie("invite_stage", "", time() - 3600, "/");
				}

				else if ($stage == $completed_reviews_by_user && ($sub_stage_ids[0] == 1 || $sub_stage_ids[0] == 2 || $sub_stage_ids[0] == 8)) {

					$warning_message = "You have already given Stage 1 feedback for this manuscript.";

					$show_survey = false;

					unset($_COOKIE['invite_uuid']);
					unset($_COOKIE['invite_stage']);

					setcookie("invite_uuid",  "", time() - 3600, "/");
					setcookie("invite_stage", "", time() - 3600, "/");
				}

				else if ($stage == $completed_reviews_by_user) {

					// Check if requested stage is valid re: a rejected manuscript at Stage 1

					$warning_message = "You have already given Stage " . $existing_uuid_reviews[0]['stage_id'] . " feedback for this manuscript.";

					$warning_message = $warning_message . "<br /><br />If you wish, you may provide Stage ";
					
					Switch($existing_uuid_reviews[0]['stage_id']) {

						Case "1":

							$warning_message = $warning_message . "2";
							
							// force Stage ID to uncompleted value

							$stage = 2;

						break;


						Case "2":

							$warning_message = $warning_message . "1";
							
							// force Stage ID to uncompleted value

							$stage = 1;

						break;
					}

					$warning_message = $warning_message . " feedback for this manuscript below.<br /><br />Your feedback will be automatically linked to your existing Stage " . $existing_uuid_reviews[0]['stage_id'] . " feedback for this manuscript.<br />";
				}

				if ($completed_reviews_by_user != 0) {

					$hidden_orphan_id = $existing_uuid_reviews[0]['review_id'];
					$role_id          = $existing_uuid_reviews[0]['role_type'];
				}
			}
		}
	}
}


// Get array of all orphan reviews for this user
// We will process them later by role and stage, creating
// 4 DIVs (role x stage) and associated orphan review lists

$sql = "SELECT `feedback_reviews`.`review_id`, BIN_TO_UUID(`feedback_reviews`.`review_uuid`) AS uuid, `feedback_reviews`.`review_ref`, `feedback_reviews`.`doi`, `feedback_reviews`.`completed_on`, `feedback_reviews`.`journal_id`, `journals`.`journal_name`, `feedback_reviews`.`stage_id`, `feedback_reviews`.`role_type` FROM `feedback_reviews` INNER JOIN `journals` ON `feedback_reviews`.`journal_id` = `journals`.`journal_id` WHERE `user_id` = " . $user_id . " AND `review_link_id` IS NULL AND (`sub_stage_id` IS NULL OR `sub_stage_id` IN (3,4,5,6,7))";


// We don't want the user's orphan feedback showing during the
// invitation process, as this would allow the possibility
// of linking unrelated existing reviews the user has previously completed.
//
// Therefore, the below is a hack, which adds an additional
// SQL statement which will never be met (review_ids start from 1), meaning zero records will be returned

if(isset($params['uuid']) && isset($params['stage'])) {

	$sql = $sql . " AND `review_id` = '0'";
}

$sql = $sql . " ORDER BY `stage_id` ASC, `role_type` ASC";

if ($result = mysqli_query($db_handle, $sql)) {

	if (mysqli_num_rows($result) > 0) {

		$orphans = mysqli_fetch_all($result, MYSQLI_ASSOC);

		// To ensure data integrity, build a javascript array of substage/year data
		// so that when a user chooses an orphan review (e.g. Stage 1), we search
		// for existing Stage 2 feedback by other users, extracting substage/year data to prepopulate the form.

		// Additionally, build an array of orphan ref codes, so we can later check if the user has tried to reuse an existing orphan ref code
		// which may lead to ambiguous situations when later choosing an orphan review

		$existing_feedback = array();
		$ref_codes         = array();

		foreach ($orphans as $orphan) {

			// Loop through each orphan review for this user, checking if any reviews by other users exist for each orphan review's UUID

			$sql = "SELECT `review_id`, `stage_id`, `sub_stage_id`,`start_date`,`end_date`, `journal_id` FROM `feedback_reviews` WHERE `review_uuid` = UUID_TO_BIN('" . $orphan['uuid'] . "') AND `review_id` <> " . $orphan['review_id'] . " AND stage_id = ";

			// We want to opposite Stage

			Switch($orphan['stage_id']) {

				Case "1":

					$sql = $sql . "2";

				break;

				Case "2":

					$sql = $sql . "1";

				break;
			}

			$sql = $sql . " LIMIT 1";

			if ($result2 = mysqli_query($db_handle, $sql)) {

				if (mysqli_num_rows($result2) > 0) {

					$review_exists = mysqli_fetch_all($result2, MYSQLI_ASSOC);

					// Build associative array for this orphan info

					$temp = array(
							"stage_id" 	   => $review_exists[0]['stage_id'], 
							"sub_stage_id" => $review_exists[0]['sub_stage_id'], 
							"start_date"   => $review_exists[0]['start_date'], 
							"end_date"     => $review_exists[0]['end_date'], 
							"journal_id"   => $review_exists[0]['journal_id']);
					
					// add built array to multidimensional array of all orphan info

					$existing_feedback[$orphan['review_id']] = $temp;
				}
			}

			// Add current orphan review ref to the $ref_codes array

			$ref_codes[] = $orphan['review_ref'];
		}

		if (!empty($existing_feedback)) {
			?>

			<script type="text/javascript">
			var orphans = <?php echo json_encode($existing_feedback); ?>;
			var refcodes = <?php echo json_encode($ref_codes); ?>;
			</script>

		<?php

		}
	}
}

$sql = "SELECT journal_id, journal_name FROM `journals` ORDER BY `journal_order` DESC, `journal_name` ASC";

if ($result = mysqli_query($db_handle, $sql)) {

		if (mysqli_num_rows($result) > 0) {

			$journals = mysqli_fetch_all($result, MYSQLI_ASSOC);
		}
}

// Create year string for year range dropdowns
// string will be inserted inside <select> tags for each dropdown.
// Minimum year is when RRs started - Maximum year is current year

$rr_start_year = 2012;
$current_year  = intval(date("Y"));
$year_string   = '<option value="0">Please select...</option>';

for ($y = $rr_start_year; $y <= $current_year; $y++) {

	$year_string = $year_string . '<option value="' . $y . '">' . $y . '</option>';
}

?>
<main>
<h2>Add feedback</h2>

<?php

if ($warning_message != "") { ?>

	<p>&#9888;&#65039;&nbsp;<span style="color: red;"><?php echo $warning_message ?></span></p>

<?php	
}

if ($show_survey == true) {	?>

	<h3>Guidance</h3>
<div id="guidance_div" class="guidance_div">
<?php

// Display invite information, except if the user is using an invite link to complete their own feedback (i.e. if the 'self' querystring parameter is present)

if((isset($params['uuid']) && isset($params['stage']) && !isset($params['self']))) { ?>

	<img alt="Info icon" title="Info icon" src="../assets/images/info-icon.svg" height="16" width="16">&nbsp;You're about to give feedback via an invitation from a manuscript co-author. For more information, refer to the invitation email you received.

<?php
}
?>
	<p>Before beginning this survey, please ensure you have the following information to hand:
	<ul>
		<li>Year(s) during which the <i>review</i> process of the manuscript took place (not the publication year)</li>
		<li>Manuscript status (for authors) - e.g. accepted, rejected. Please only complete feedback for submissions that have reached their <b>final</b> status at either Stage 1 (in principle acceptance, rejected, withdrawn) or Stage 2 (accepted, rejected, withdrawn). </li>
	</ul>
	
	This survey will ask about your experience of Registered Reports and take approximately 5 minutes per stage. Please note that data will not be saved until the very end of the survey.&nbsp;<span id="tooltip_survey_details" class="survey-tooltip" tabindex="-1">?</span><br /><br />To protect your anonymity, aggregate data for each journal will only be available on our dashboard after a minimum number of completed surveys has been reached.</p>
</div>
	  <h3>General manuscript information</h3>
	  <div id="form_div" class="form_div">
		<form action="questions.php" method="get" id="RRform">
<?php

if(!isset($params['uuid']) && !isset($params['stage'])) { 

	if(isset($_COOKIE["decisionletter"])) { ?>

	<input type="checkbox" name="decision_letter" id="decision_letter" value="1" checked>
	<label for="decision_letter">The feedback I'm about to give is via an invitation from a journal decision letter.</label>&nbsp;<span id="tooltip_decision_letter" class="survey-tooltip" tabindex="-1">?</span><br /><br />
<?php

	}
}

else if (isset($params['self'])) { ?>

&#9888;&#65039;&nbsp;As you are adding feedback to previous feedback you gave for a manuscript, some responses may be preselected.<br /><br />

<?php
}

else

{ ?>

&#9888;&#65039;&nbsp;As you are completing feedback from an invitation, some responses below have been preselected. They have been provided by another user (most likely the person who invited you) and to ensure data integrity they cannot be changed.<br /><br />

<?php
}

?>
		<div id="reviewer_div">
			<label id="reviewer_label" for="reviewer_role">1. Do you want to give feedback for your role as:</label>

			<select name="reviewer_role" id="reviewer_role">
			  <option value="0">Please select...</option>
			  <option value="1">Author</option>
			  <option value="2">Reviewer</option>
			</select>

			<br />
			<br />
		</div>

		<div id="journal_div" style="display: none;">
			<label id="journal_label" for="journal">2. Which journal or platform managed the peer review process for this manuscript?</label>

			<select name="journal" id="journal" style="width:31.25rem; font-family: Arial; box-sizing: border-box;">
			  	<option value="0">Please select...</option>
	<?php

	foreach ($journals as $journal_array) {

		echo "<option value='" . $journal_array["journal_id"] . "'>" . $journal_array["journal_name"] . "</option>";
	}
	?>
			</select>
			&nbsp;<span id="tooltip_journal" class="survey-tooltip" tabindex="-1">?</span>
			<br />
			<br />
		</div>

		<div id="stage_div" style="display: none;">
			<label id="stage_label" for="stage">3. What stage(s) do you want to give feedback on for this manuscript at this journal?</label>

			<select name="stage" id="stage">
			  <option value="0">Please select...</option>
			  <option value="1">Stage 1</option>
			  <option value="2">Stage 2</option>
			  <option value="3">Stage 1 and 2</option>
			</select>
			&nbsp;<span id="tooltip_stage" class="survey-tooltip" tabindex="-1">?</span>
			<br />
			<br />
		</div>
		<div id="orphantop"></div>
		<input type="hidden" name="orphan_selected" id="orphan_selected" value="0">

		<?php

		// Add orphan reviews in corresponding DIVs
		//
		// $orphans array is ordered by stage, then role
		// so we'll use nested loops, with:
		//
		// stage as the outer loop
		// role as the inner loop 

		for ($s = 1; $s < 3; $s++) {

			for ($r = 1; $r < 3; $r++) {

				echo '<div id="stage' . $s . '_role' . $r . '_orphans_div" style="display: none;">';

				// Add a header if there were any orphan reviews in this section

				$display_div = false;
				$section_count = 1;

				if (isset($orphans)) {

					foreach ($orphans as $orphan_row) {

						if ($orphan_row['stage_id'] == $s && $orphan_row['role_type'] == $r) {

							if ($display_div == false) {

								switch($s) {

									Case "1":

										$other_stage = "2";
										break;

									Case "2":

										$other_stage = "1";
										break;
								}

								switch ($r) {

									Case "1":

										$role_name = "author";

									break;

									Case "2":

										$role_name = "reviewer";

									break;
								}

								?>

								<fieldset>
									<legend><h3>Unlinked Stage <?php echo $s . " " . $role_name;?> feedback</h3></legend>
								
								Below is a list of unlinked Stage <?php echo $s . " " . $role_name;?> feedback you have previously given.<br /><br />
								<div class="table-wrapper">
							  	<table class="altrows orphan-table">
							  	<colgroup>
							       	<col span="1" style="width: 0.625rem;">
							       	<col span="1" style="width: 9rem;">
							       	<col span="1" style="width: auto;">
							       	<col span="1" style="width: auto;">
							       	<col span="1" style="width: 1.875rem;">
								</colgroup>
								<tbody>
								  <tr>
								    <td>&nbsp;</th>
								    <th>Date of feedback</th>
								    <th>Journal</th>
								    <th>Your identifier</th>
								    <th>View</th>
								  </tr>
								<?php
								$display_div = true;

							}

							?>
							<tr>
							    <td><input aria-label="Link your previous feedback (REF code: <?php echo $orphan_row['review_ref'];?>) to this feedback" title="Link your previous feedback (REF code: <?php echo $orphan_row['review_ref'];?>) to this feedback" type="radio" value="<?php echo $orphan_row['review_id'];?>" name="stage<?php echo $s . "_role" . $r;?>_orphan_id" id="stage<?php echo $s . "_role" . $r;?>_orphan_<?php echo $section_count;?>" onclick="<?php echo array_key_exists($orphan_row['review_id'], $existing_feedback) ? "set_existing_orphan_details('" . $existing_feedback[$orphan_row['review_id']]['stage_id'] . "', '" . $existing_feedback[$orphan_row['review_id']]['sub_stage_id'] . "', '" . $existing_feedback[$orphan_row['review_id']]['start_date'] . "', '" . $existing_feedback[$orphan_row['review_id']]['end_date'] . "', '" . $existing_feedback[$orphan_row['review_id']]['journal_id'] . "'); " : "clearSelection(" . $s . ", " . $r . ", false); switchJournal('" . $orphan_row['journal_id'] . "');"; ?> $('#ref_code').val('<?php echo addslashes($orphan_row['review_ref']);?>'); $('#orphan_selected').val('1'); $('#ref_code').prop({'readonly': true}, {'disabled': true}); /* $('#ref_code').trigger('change'); */"></td>
							    <td><!--<label for="stage<?php echo $s . "_role" . $r;?>_orphan_<?php echo $section_count;?>">--><?php echo date('jS M Y', strtotime($orphan_row['completed_on']));?><!--</label>--></td>
							    <td><!--<label for="stage<?php echo $s . "_role" . $r;?>_orphan_<?php echo $section_count;?>">--><?php echo $orphan_row['journal_name'];?><!--</label>--></td>
							    <td><!--<label for="stage<?php echo $s . "_role" . $r;?>_orphan_<?php echo $section_count;?>">--><?php echo $orphan_row['review_ref'];?><!--</label>--></td>
							    <td align='center'><a title="View your completed Stage <?php echo $s . " ". $role_name . " feedback titled: '" . $orphan_row['review_ref'] . "'"; ?>" href="review_summary.php?review_id=<?php echo $orphan_row['review_id']?>" rel="modal:open"><img title="View your completed Stage <?php echo $s . " ". $role_name . " feedback titled: '" . $orphan_row['review_ref'] . "'"; ?>" alt="Eye icon" src='../assets/images/eye-icon.svg' width='22' height='22' border='0' /></a></td>
						    </tr>

							<?php
						
							$section_count++;
						}
					}
				}

				// Add a footer if there were any orphan reviews in this section

				if ($display_div == true) {

					?>

				</tbody>
				</table>
				</div>
				<a href="#" onclick="clearSelection(<?php echo $s . ", " . $r;?>, true);" title="Clear selection">clear selection</a>
				<br /><br />
				If you would like to link the Stage <?php echo $other_stage . " " . $role_name;?> feedback you are about to give to one of the unlinked pieces of Stage <?php echo $s . " " . $role_name; ?> feedback above, please select it.
				</fieldset>
				<?php
				}
				?>
				<hr>
				</div>

				<?php
			}
		}

		// If the user is using an invitation link, and has previously
		// given either Stage 1 or Stage 2 feedback on a manuscript
		// and is now giving corresponding feedback for the other
		// stage (i.e. Stage 1 complete, now giving Stage 2)
		// then we want to automatically link the new feedback
		// to the existing feedback, without giving the user a
		// chance to link it to any other existing orphan feedback

		if (isset($hidden_orphan_id)) {

		?>

		<input type="hidden" name="stage<?php echo $stage == 1 ? "2" : "1"; ?>_role<?php echo $role_id ?>_orphan_id" value="<?php echo $hidden_orphan_id ?>">

		<?php }

		if (isset($orphan_ref_code_invite) && $orphan_ref_code_invite != "") { ?>

		<input type="hidden" name="ref_code" value="<?php echo $orphan_ref_code_invite ?>">

		<?php

		}

		?>

		<div id="ex1" class="modal">
			<a href="#" rel="modal:close">Close</a>
		</div>

		<div id="stage1_div" style="display: none;">
		  	<fieldset>
		  		<legend>
		  			<h3>Manuscript outcome</h3>
		  		</legend>
		  	
			  	<b>What was the outcome of your manuscript at Stage 1?</b><br /><br />
			    <input type="radio" value="1" name="stage1" id="1">
			    <label for="1">Desk rejected prior to peer review</label>
			    <br />
			    <input type="radio" value="2" name="stage1" id="2">
			    <label for="2">Rejected after one or more rounds of specialist peer review</label>
			    <br />
			    <input type="radio" value="8" name="stage1" id="13">
			    <label for="13">Withdrawn by author before IPA granted</label>
			    <br />
			    <input type="radio" value="3" name="stage1" id="3">
			    <label for="3">Granted IPA and proceeded to Stage 2</label>
				<br />
			    <input type="radio" value="4" name="stage1" id="4">
			    <label for="4">Granted IPA but then withdrawn from this journal by authors prior to Stage 2</label>
			</fieldset>

			<br />
		</div>

		<div id="stage2_div" style="display: none;">
		  	<fieldset>
		  		<legend>
		  			<h3>Manuscript outcome</h3>
		  		</legend>
			
				<b>What was the outcome of your manuscript at Stage 2?</b><br /><br />
			    <input type="radio" value="5" name="stage2" id="5">
			    <label for="5">Desk rejected prior to peer review</label>
				<br />
			    <input type="radio" value="6" name="stage2" id="6">
			    <label for="6">Rejected after one or more rounds of specialist peer review</label>
				<br />
			    <input type="radio" value="7" name="stage2" id="7">
			    <label for="7">Accepted</label>
			</fieldset>

			<br />
		</div>

		<div id="stageboth_div" style="display: none;">
			<h3>Manuscript outcome</h3>

			<fieldset>
				<legend><b>What was the outcome of your manuscript at Stage 1?</b></legend>

				<br />
				<input type="radio" value="3" name="stage1_both" id="8">
			    <label for="8">Granted IPA and proceeded to Stage 2</label>
				<br />
				<input type="radio" value="4" name="stage1_both" id="9">
			    <label for="9">Granted IPA but then withdrawn by authors prior to Stage 2</label>
			</fieldset>

			<br />
			<br />
			
			<fieldset>
				<legend><b>What was the outcome of your manuscript at Stage 2?</b></legend>

				<br />
			    <input type="radio" value="5" name="stage2_both" id="10">
			    <label for="10">Desk rejected prior to peer review</label>
				<br />
			    <input type="radio" value="6" name="stage2_both" id="11">
			    <label for="11">Rejected after one or more rounds of specialist peer review</label>
				<br />
			    <input type="radio" value="7" name="stage2_both" id="12">
			    <label for="12">Accepted </label>
		    </fieldset>
			<br />
		</div>

		<div id="doi_div" style="display: none;">
			<br />
			<label for="doi">Optional: enter the DOI of the published <u>STAGE 2</u> manuscript:</label>
			<input type="text" value="" name="doi" id="doi" maxlength="500">
			&nbsp;<input id="doi_search" type="button" value="Lookup this DOI" onclick="lookupDOI( $('#doi').val() );">&nbsp;<span id="tooltip_doi" class="survey-tooltip" tabindex="-1">?</span>
			<span id="doi_lookup"></span>
			<br />
			<br />
		</div>	

	<div id="s1y_div" style="display: none;">
		<hr>
		<h3>Year range - Stage 1</h3>
		<b>In what year/over what range of years did this <u>STAGE 1</u> review process take place?</b><br /><br />

		<input type="radio" name="s1year_choice" id="s1single" value="1" aria-label="Single year">
		<label for="s1singleyear">Single year - </label>
		<select name="s1singleyear" id="s1singleyear" onfocus="$('#s1single').prop('checked', true);" onchange="$('#s1single').prop('checked', true).trigger('click');"><?php echo $year_string ?></select><br />

		<input type="radio" name="s1year_choice" id="s1range" value="2">
		<label for="s1range">Range of years - </label>
		from <select name="s1startyear" id="s1startyear" onfocus="$('#s1range').prop('checked', true);" onchange="$('#s1range').prop('checked', true).trigger('click');" aria-label="Stage 1 - Start year"><?php echo $year_string ?></select>&nbsp;to <select name="s1endyear" id="s1endyear" onfocus="$('#s1range').prop('checked', true);" onchange="$('#s1range').prop('checked', true).trigger('click');" aria-label="Stage 1 - End year"><?php echo $year_string ?></select><br />

		<input type="radio" name="s1year_choice" id="s1pnta" value="3">
		<label for="s1pnta">Prefer not to answer</label><br />

		<input type="radio" name="s1year_choice" id="s1dkdr" value="4">
		<label for="s1dkdr">Don't know / don't recall</label>
		<br />
		<br />
		<input type="checkbox" name="s1share_year_data" id="s1share_year_data" value="1">
		<label for="s1share_year_data">I only want this answer to be used for research purposes (and not contribute to the publicly displayed per-year aggregate statistics)</label>
		<br />
	</div>

	<div id="s2y_div" style="display: none;">
		<hr>
		<h3>Year range - Stage 2</h3>
		<b>In what year/over what range of years did this <u>STAGE 2</u> review process take place?</b><br /><br />

		<input type="radio" name="s2year_choice" id="s2single" value="1" aria-label="Single year">
		<label for="s2singleyear">Single year - </label>
		<select name="s2singleyear" id="s2singleyear" onfocus="$('#s2single').prop('checked', true);" onchange="$('#s2single').prop('checked', true).trigger('click');"><?php echo $year_string ?></select><br />

		<input type="radio" name="s2year_choice" id="s2range" value="2">
		<label for="s2range">Range of years - </label>
		from <select name="s2startyear" id="s2startyear" onfocus="$('#s2range').prop('checked', true);" onchange="$('#s2range').prop('checked', true).trigger('click');" aria-label="Stage 2 - Start year"><?php echo $year_string ?></select>&nbsp;to <select name="s2endyear" id="s2endyear" onfocus="$('#s2range').prop('checked', true);" onchange="$('#s2range').prop('checked', true).trigger('click');" aria-label="Stage 2 - End year"><?php echo $year_string ?></select><br />

		<input type="radio" name="s2year_choice" id="s2pnta" value="3">
		<label for="s2pnta">Prefer not to answer</label><br />

		<input type="radio" name="s2year_choice" id="s2dkdr" value="4">
		<label for="s2dkdr">Don't know / don't recall</label>
		<br />
		<br />
		<input type="checkbox" name="s2share_year_data" id="s2share_year_data" value="1">
		<label for="s2share_year_data">I only want this answer to be used for research purposes (and not contribute to the publicly displayed per-year aggregate statistics)</label>
		<br />
	</div>

	<div id="s1role_div" style="display: none;">	
		<hr>
		<h3>Academic career status - Stage 1</h3>
		<b>What was your academic career status during the <u>STAGE 1</u> review process?&nbsp;<span id="tooltip_s1_academic" class="survey-tooltip" tabindex="-1">?</span>
	</b><br /><br />
		<input type="radio" id="s1_academic_radio_1" name="s1_academic_radio" class="custom-control-input" value="1" aria-label="Roles">
		<label for="s1_academic_role">Roles</label>
	  <select name="s1_academic_role" id="s1_academic_role" onfocus="$('#s1_academic_radio_1').prop('checked', true);" onchange="$('#s1_academic_radio_1').prop('checked', true).trigger('click');">
	      <option value="0">Please select...</option>
	      <option value="1">Undergraduate</option>
	      <option value="2">Postgraduate/graduate</option>
	      <option value="3">PhD student</option>
	      <option value="4">Post-doc</option>
	      <option value="5">Early career fellow</option>
	      <option value="6">Senior research fellow</option>
	      <option value="7">Lecturer</option>
	      <option value="8">Senior lecturer</option>
	      <option value="9">Reader</option>
	      <option value="10">Assistant professor</option>
	      <option value="11">Associate professor</option>
	      <option value="12">Full professor</option>
	      <option value="13">Independent researcher (unaffiliated)</option>
	      <option value="14">Industry scientist/researcher</option>
	      <option value="15">Government researcher</option>
	      <option value="17">Non-profit sector</option>
	      <option value="16">Other (please specify)</option>
	  </select>
	  <br />
	  <br />
	  <label for="s1_academic_role_text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</label>
	  <input type="text" id="s1_academic_role_text" name="s1_academic_role_text" maxlength="255" placeholder="" onfocus="$('#s1_academic_role').val('16'); $('#s1_academic_radio_1').prop('checked', true);" value=""><span id="s1_academic_role_text_span" style="color: red;"></span>
	  <br /><br />

	  <input type="radio" id="s1_academic_radio_2" name="s1_academic_radio" class="custom-control-input" value="2"><label for="s1_academic_radio_2" class="sr-only">Prefer not to answer</label>
	  <br />
	  <input type="radio" id="s1_academic_radio_3" name="s1_academic_radio" class="custom-control-input" value="3"><label for="s1_academic_radio_3" class="sr-only">Don't know / don't recall</label>
	  <br />
	  <br />
	  N.B. This information will not be shared publicly, and only used for research purposes.
	</div>

	<div id="s2role_div" style="display: none;">	
		<hr>
		<h3>Academic career status - Stage 2</h3>
		<b>What was your academic career status during the <u>STAGE 2</u> review process?&nbsp;<span id="tooltip_s2_academic" class="survey-tooltip" tabindex="-1">?</span>
	</b><br /><br />
		<input type="radio" id="s2_academic_radio_1" name="s2_academic_radio" class="custom-control-input" value="1" aria-label="Roles">
		<label for="s2_academic_role">Roles</label>
	  <select name="s2_academic_role" id="s2_academic_role" onfocus="$('#s2_academic_radio_1').prop('checked', true);" onchange="$('#s2_academic_radio_1').prop('checked', true).trigger('click');">
	      <option value="0">Please select...</option>
	      <option value="1">Undergraduate</option>
	      <option value="2">Postgraduate/graduate</option>
	      <option value="3">PhD student</option>
	      <option value="4">Post-doc</option>
	      <option value="5">Early career fellow</option>
	      <option value="6">Senior research fellow</option>
	      <option value="7">Lecturer</option>
	      <option value="8">Senior lecturer</option>
	      <option value="9">Reader</option>
	      <option value="10">Assistant professor</option>
	      <option value="11">Associate professor</option>
	      <option value="12">Full professor</option>
	      <option value="13">Independent researcher (unaffiliated)</option>
	      <option value="14">Industry scientist/researcher</option>
	      <option value="15">Government researcher</option>
	      <option value="17">Non-profit sector</option>
	      <option value="16">Other (please specify)</option>
	  </select>
	  <br />
	  <br />
	  <label for="s2_academic_role_text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other</label>
	  <input type="text" id="s2_academic_role_text" name="s2_academic_role_text" maxlength="255" placeholder="" onfocus="$('#s2_academic_role').val('16'); $('#s2_academic_radio_1').prop('checked', true);" value=""><span id="s2_academic_role_text_span" style="color: red;"></span>
	  <br /><br />

	  <input type="radio" id="s2_academic_radio_2" name="s2_academic_radio" class="custom-control-input" value="2"><label for="s2_academic_radio_2" class="sr-only">Prefer not to answer</label>
	  <br />
	  <input type="radio" id="s2_academic_radio_3" name="s2_academic_radio" class="custom-control-input" value="3"><label for="s2_academic_radio_3" class="sr-only">Don't know / don't recall</label>
	  <br />
	  <br />
	  N.B. This information will not be shared publicly, and only used for research purposes.
	</div>


		<div id="ref_code_div" style="display: none;">		
			<hr>
			<label for="ref_code">4. Please enter a short, descriptive reference code to identify the manuscript e.g. <b>Meta-analysis anxiety treatment</b></label>
			<input type="text" value="" name="ref_code" id="ref_code" minlength="10" maxlength="60">
			&nbsp;<span id="tooltip_ref_code" class="survey-tooltip" tabindex="-1">?</span>
			<span id="ref_code_span" style="color: red;"></span>
			<br />
			<br />
		</div>

		<div id="submit_button_div" style="display: none;">
			<input type="submit" name="submit" id="submitbutton" value="Start">
		</div>

	<?php

	// If this is an invited link from a co-author, pass the UUID
	// and the reviewer role ID - the latter is not automatically
	// passed as we disable the role selector, fixing it to the invited role (1 or 2)

	if(isset($params['uuid']) && isset($params['stage'])) {

		echo '<input type="hidden" name="uuid" value="' . $params['uuid'] . '">';
	}	

	?>

		</form>
	</div>
</main>
	<script src="selector.js"></script>
	<script type="text/javascript" src="../assets/js/popper.min.js"></script>
	<script type="text/javascript" src="../assets/js/tippy-bundle.umd.min.js"></script>
	<script>	

	  // Create tooltips

	  tippy('#tooltip_survey_details', { content: 'For clarity, if you are completing both stages of feedback, your responses will only be saved when you complete the Stage 2 section (having completed the Stage 1 section). We would like to implement saving progress in a later version of this feedback system, but until then, caveat emptor!<br /><br />Therefore, please complete the entire piece of feedback in one sitting and make sure you have sufficient battery if you are using a portable device.', allowHTML: true});
	  tippy('#tooltip_journal', { content: 'These journals and platforms either currently offer the Registered Reports format or have done so in the past.<br /><br />If you ONLY used Peer Community in Registered Reports (PCI RR) for peer review then please enter that as the "journal", not the journal that eventually published your RR. This is because we want to evaluate the journal or platform that undertook the peer review. If you used PCI RR AND a journal for peer review, then please create separate feedback entries for PCI RR and the journal.<br /><br />If the journal or platform that undertook the peer review is not listed, contact us with details using the \'Contact Us\' link at the bottom of the page.', allowHTML: true});
	  tippy('#tooltip_doi', { content: 'If your Stage 2 manuscript has been published, please enter its DOI - this will help us collate feedback from different sources about the Registered Reports process for this specific manuscript you were involved in as an author.<br /><br />N.B. This information will be used for research only - no part of your feedback will be publicly linked to this DOI.', allowHTML: true});
	  tippy('#tooltip_stage', { content: 'Only choose \'Stage 1 and 2\' if the SAME journal or platform reviewed your manuscript at both Stage 1 and 2.<br /><br />If your manuscript was transferred between journals or platforms during the review process, please complete Stage 1 separately, then come back and link your Stage 2 feedback to the Stage 1 feedback you have just given.<br /><br />If you want to give feedback on two different manuscripts (one at Stage 1, one at Stage 2) choose just Stage 1, then come back and choose just Stage 2.', allowHTML: true});
	  tippy('#tooltip_ref_code', { content: 'Choose an informative reference code that will distinguish your entry from other feedback you may complete, either now or in the future.<br /><br />When you return to give follow-up feedback, this reference code will be shown to help link your Stage 1 and Stage 2 feedback.<br /><br />N.B. This code will NOT be published along with your feedback - only you and the site administrators will see it.', allowHTML: true});
	  tippy('#tooltip_s1_academic', { content: 'If your academic role changed during this Stage 1 process, choose your role at the beginning of the process.', allowHTML: true});
	  tippy('#tooltip_s2_academic', { content: 'If your academic role changed during this Stage 2 process, choose your role at the beginning of the process.', allowHTML: true});
	  tippy('#tooltip_decision_letter', { content: 'You\'re seeing this because you clicked on a link from a journal decision letter, asking for feedback on the manuscript that was reviewed.<br /><br />We typically assume that the first piece of feedback you will give after clicking this link will be about this manuscript. If this is not the case, untick the box, and complete feedback for another manuscript.<br /><br />N.B. This checkbox and message will continue to appear for 5 days from when you clicked the link in the journal decision letter, after which time it will disappear.', allowHTML: true});

	</script>

	<!-- Prefetch survey resources, so they are loaded while user is choosing pre-survey options - will speed up UX when user clicks 'Start' to being survey (~3MB of resources need loading on actual survey page!) // -->

	<link rel="prefetch" href="../assets/js/survey.jquery.min.js" as="script" />
	<link rel="prefetch" href="../assets/js/surveyjs-widgets.js" as="script" />
	<link rel="prefetch" href="../assets/css/survey.min.css" as="style" />

	<link rel="prefetch" href="../assets/js/jquery.barrating.js" as="script" />

	<link rel="prefetch" href="../assets/css/font-awesome.min.css" as="style" />
	<link rel="prefetch" href="../assets/css/fontawesome-stars.css" as="style"  />
	<link rel="prefetch" href="../assets/fonts/fontawesome-webfont.woff2?v=4.7.0" as="font" type="font/woff2" crossorigin />

	<link rel="prefetch" href="../assets/js/showdown.min.js" as="script" />

	<?php

	// If the querystring 'id' is set, change the journal to this ID
	// This is to allow deeplinking from a specific journal page to add
	// feedback for that specific journal, pre-selecting that journal

	if ( isset($_GET['id']) && $_GET['id'] != "" && $_GET['id'] <= sizeof($journals)) {
	?>

	<script>
	$(document).ready(()=>{
		$("#journal").val('<?php echo $_GET['id']; ?>');
		$("#journal").select2().val('<?php echo $_GET['id']; ?>');
	});
	</script>

	<?php
	}


	// If an invitation code has been specified, selectively restrict which options
	// the user can make (i.e. author will always be restricted
	// journal will be restricted for now, as will stage)

	if(isset($params['uuid']) && isset($params['stage']) && isset($completed_reviews_by_user) && $completed_reviews_by_user != 3) {

	?>
		<script>
		/* Set various options as user has been invited by a manuscript co-author */

		$(document).ready(()=>{

	  	  /* Fix user role as 'author' and disable dropdown */

		  $("#reviewer_role").val('<?php echo $reviews_by_stage[0]['role_type']; ?>');
		  $('#reviewer_role option[value!="<?php echo $reviews_by_stage[0]['role_type']; ?>"]').prop('disabled', true);
		  $('#reviewer_role').trigger('change');

		  /* Select journal */

		  <?php
		  // First deal with cases where the manuscript has moved journal between Stage 1 and 2

		  if (sizeof($journal_ids) > 1) {

		  	Switch($stage) {

		  		Case "1": ?>

	$jselect2 = $("#journal").select2();
	$jselect2.val('<?php echo $journal_ids[0]?>');
	$('#journal option[value!="<?php echo $journal_ids[0]?>"]').prop('disabled', true);
	$('#journal').trigger('change');

		  		<?php

		  		break;

		  		Case "2": ?>

	$jselect2 = $("#journal").select2();
	$jselect2.val('<?php echo $journal_ids[1]?>');
	$('#journal option[value!="<?php echo $journal_ids[1]?>"]').prop('disabled', true);
	$('#journal').trigger('change');

		  		<?php

		  		break;
		  	}
		  }

		  // Otherwise, we are dealing with a manuscript that has either:
		  //
		  // a) the same journal for Stage 1 and 2
		  // 
		  // or
		  //
		  // b) no entry for one stage of feedback, for this manuscript (across all users)

		  else

		  { ?>

	$jselect2 = $("#journal").select2();
	$jselect2.val('<?php echo $journal_ids[0]?>');
	<?php
	
	// check if 1 or 2 stages complete across all users
	//         if 1, we don't disable the journal picker, meaning
	//         that it can be changed (i.e. manuscript changing
	//         journals between stages), but we do preselect it
	//         based on the journal existing stage's feedback

	if (sizeof($reviews_by_stage) == 2 || $stage == $completed_reviews_by_uuid) { ?>

	$('#journal option[value!="<?php echo $journal_ids[0]?>"]').prop('disabled', true);
	
	<?php
	}
	?>
	$('#journal').trigger('change');

	<?php } ?>
		  
	/* Select chosen stage and disable other stage choices */

		$("#stage").val('<?php echo $stage ?>');
		$('#stage option[value!="<?php echo $stage ?>"]').prop('disabled', true);
		$('#stage').trigger('change');

			<?php

			// Select existing sub stage(s)

			if (isset($completed_reviews_by_uuid) && $completed_reviews_by_uuid == 1) { ?>

				/* Set Stage 1 (single stage) sub stage from existing review */

				$("input[name=stage1][value='1']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 1 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='2']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 2 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='3']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 3 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='4']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 4 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='8']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 8 ? "checked" : "disabled"; ?>", true);

				/* Set Stage 1 (both stages) sub stage from existing review */

				$("input[name=stage1_both][value='3']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 3 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1_both][value='4']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 4 ? "checked" : "disabled"; ?>", true);

			<?php

			}

			else if (isset($completed_reviews_by_uuid) && $completed_reviews_by_uuid == 2) { ?>

				/* Set Stage 2 (single stage) sub stage from existing review */

				$("input[name=stage2][value='5']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 5 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2][value='6']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 6 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2][value='7']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 7 ? "checked" : "disabled"; ?>", true);

				/* Set Stage 2 (both stages) sub stage from existing review */

				$("input[name=stage2_both][value='5']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 5 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2_both][value='6']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 6 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2_both][value='7']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 7 ? "checked" : "disabled"; ?>", true);

				/* Disable first 2 manuscript outcomes at Stage 1, as manuscript
				could not have progressed to Stage 2 with either of these */

				$("input[name=stage1][value='1']").prop("disabled", true);
				$("input[name=stage1][value='2']").prop("disabled", true);
				$("input[name=stage1][value='8']").prop("disabled", true);

				<?php

			}

			else if (isset($completed_reviews_by_uuid) && $completed_reviews_by_uuid == 3) { ?>

				/* Set Stage 1 (single stage) sub stage from existing review */

				$("input[name=stage1][value='1']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 1 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='2']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 2 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='3']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 3 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='4']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 4 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1][value='8']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 8 ? "checked" : "disabled"; ?>", true);

				/* Set Stage 1 (both stages) sub stage from existing review */

				$("input[name=stage1_both][value='3']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 3 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage1_both][value='4']").prop("<?php echo $reviews_by_stage[0]['sub_stage_id'] == 4 ? "checked" : "disabled"; ?>", true);

				/* Set Stage 2 (single stage) sub stage from existing review */

				$("input[name=stage2][value='5']").prop("<?php echo $reviews_by_stage[1]['sub_stage_id'] == 5 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2][value='6']").prop("<?php echo $reviews_by_stage[1]['sub_stage_id'] == 6 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2][value='7']").prop("<?php echo $reviews_by_stage[1]['sub_stage_id'] == 7 ? "checked" : "disabled"; ?>", true);

				/* Set Stage 2 (both stages) sub stage from existing review */

				$("input[name=stage2_both][value='5']").prop("<?php echo $reviews_by_stage[1]['sub_stage_id'] == 5 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2_both][value='6']").prop("<?php echo $reviews_by_stage[1]['sub_stage_id'] == 6 ? "checked" : "disabled"; ?>", true);
				$("input[name=stage2_both][value='7']").prop("<?php echo $reviews_by_stage[1]['sub_stage_id'] == 7 ? "checked" : "disabled"; ?>", true);

			<?php

			}

			// Set correct substage change
			// 
			// N.B. Can't assume that the stage requested in the URL is the stage we'll offer
			// e.g. if the user provided URL contains stage=1, but they have already
			// completed Stage 1 feedback for this manuscript (but not Stage 2),
			// we will offer them the option to complete the corresponding Stage 2
			// feedback

			Switch($stage) {

				Case "1":

					if ($completed_reviews_by_uuid == 1 || $completed_reviews_by_uuid == 3) { ?>
	
				$('#s1y_div').show();

					<?php
					}

				break;



				Case "2":

					if ($completed_reviews_by_uuid == 2 || $completed_reviews_by_uuid == 3) { ?>
	
				$('#s2y_div').show();

					<?php
					}

				break;
			}


			// Set year data

			if (isset($completed_reviews_by_uuid) && $completed_reviews_by_uuid == 1) {

				// Don't know / don't recall

				if ($reviews_by_stage[0]['start_date'] == 777 && $reviews_by_stage[0]['end_date'] == 777) { ?>

					//$("input[name='s1year_choice'][value='4']").prop("checked", true);
				
				<?php
				}

				// Prefer not to answer

				else if ($reviews_by_stage[0]['start_date'] == 999 && $reviews_by_stage[0]['end_date'] == 999) { ?>

					//$("input[name='s1year_choice'][value='3']").prop("checked", true);
				
				<?php
				}

				// Range of years

				else if ($reviews_by_stage[0]['start_date'] != $reviews_by_stage[0]['end_date']) { ?>

					$("input[name='s1year_choice'][value='2']").prop("checked", true);
					$("#s1startyear").val('<?php echo $reviews_by_stage[0]['start_date'] ?>');
					$("#s1endyear").val('<?php echo $reviews_by_stage[0]['end_date'] ?>');

					$('#s1startyear option[value!="<?php echo $reviews_by_stage[0]['start_date'] ?>"]').prop('disabled', true);
					$('#s1endyear option[value!="<?php echo $reviews_by_stage[0]['end_date'] ?>"]').prop('disabled', true);

					$("input[name='s1year_choice'][value='1']").prop("disabled", true);
					$("input[name='s1year_choice'][value='3']").prop("disabled", true);
					$("input[name='s1year_choice'][value='4']").prop("disabled", true);

					$("#s1singleyear").prop('disabled', true);
				
				<?php
				}

				// Single year

				else if ($reviews_by_stage[0]['start_date'] == $reviews_by_stage[0]['end_date']) { ?>

					$("input[name='s1year_choice'][value='1']").prop("checked", true);
					$("#s1singleyear").val('<?php echo $reviews_by_stage[0]['start_date'] ?>');
					$('#s1singleyear option[value!="<?php echo $reviews_by_stage[0]['start_date'] ?>"]').prop('disabled', true);
					
					$("input[name='s1year_choice'][value='2']").prop("disabled", true);
					$("input[name='s1year_choice'][value='3']").prop("disabled", true);
					$("input[name='s1year_choice'][value='4']").prop("disabled", true);

					$("#s1startyear").prop('disabled', 'true');
					$("#s1endyear").prop('disabled', 'true');

				<?php

				}
			}

			else if (isset($completed_reviews_by_uuid) && $completed_reviews_by_uuid == 2) { 
				// Don't know / don't recall
				
				if ($reviews_by_stage[0]['start_date'] == 777 && $reviews_by_stage[0]['end_date'] == 777) { ?>
				
				<?php
				}

				// Prefer not to answer

				else if ($reviews_by_stage[0]['start_date'] == 999 && $reviews_by_stage[0]['end_date'] == 999) { ?>
				
				<?php
				}

				// Range of years

				else if ($reviews_by_stage[0]['start_date'] != $reviews_by_stage[0]['end_date']) { ?>

					$("input[name='s2year_choice'][value='2']").prop("checked", true);
					$("#s2startyear").val('<?php echo $reviews_by_stage[0]['start_date'] ?>');
					$("#s2endyear").val('<?php echo $reviews_by_stage[0]['end_date'] ?>');

					$('#s2startyear option[value!="<?php echo $reviews_by_stage[0]['start_date'] ?>"]').prop('disabled', true);
					$('#s2endyear option[value!="<?php echo $reviews_by_stage[0]['end_date'] ?>"]').prop('disabled', true);

					$("input[name='s2year_choice'][value='1']").prop("disabled", true);
					$("input[name='s2year_choice'][value='3']").prop("disabled", true);
					$("input[name='s2year_choice'][value='4']").prop("disabled", true);

					$("#s2singleyear").prop('disabled', true);

					<?php

					// Don't show next (year) form element until user has
					// chosen a sub stage for Stage 1
					//
					// This is necessary for cases where Stage 2 feedback
					// exists, and the user is giving Stage 1 feedback,
					// and therefore the first 2 sub stage options for Stage 1
					// are disabled, and neither of the remaining choices
					// has been selected


				}

				// Single year

				else if ($reviews_by_stage[0]['start_date'] == $reviews_by_stage[0]['end_date']) { ?>

					$("input[name='s2year_choice'][value='1']").prop("checked", true);
					$("#s2singleyear").val('<?php echo $reviews_by_stage[0]['start_date'] ?>');
					$('#s2singleyear option[value!="<?php echo $reviews_by_stage[0]['start_date'] ?>"]').prop('disabled', true);
					
					$("input[name='s2year_choice'][value='2']").prop("disabled", true);
					$("input[name='s2year_choice'][value='3']").prop("disabled", true);
					$("input[name='s2year_choice'][value='4']").prop("disabled", true);

					$("#s2startyear").prop('disabled', 'true');
					$("#s2endyear").prop('disabled', 'true');

			<?php
				}
			}

			else if (isset($completed_reviews_by_uuid) && $completed_reviews_by_uuid == 3) {

				// Stage 1 data

				// Don't know / don't recall

				if ($reviews_by_stage[0]['start_date'] == 777 && $reviews_by_stage[0]['end_date'] == 777) { ?>
				
				<?php
				}

				// Prefer not to answer

				else if ($reviews_by_stage[0]['start_date'] == 999 && $reviews_by_stage[0]['end_date'] == 999) { ?>
				
				<?php
				}

				// Range of years

				else if ($reviews_by_stage[0]['start_date'] != $reviews_by_stage[0]['end_date']) { ?>

					$("input[name='s1year_choice'][value='2']").prop("checked", true);
					$("#s1startyear").val('<?php echo $reviews_by_stage[0]['start_date'] ?>');
					$("#s1endyear").val('<?php echo $reviews_by_stage[0]['end_date'] ?>');

					$('#s1startyear option[value!="<?php echo $reviews_by_stage[0]['start_date'] ?>"]').prop('disabled', true);
					$('#s1endyear option[value!="<?php echo $reviews_by_stage[0]['end_date'] ?>"]').prop('disabled', true);

					$("input[name='s1year_choice'][value='1']").prop("disabled", true);
					$("input[name='s1year_choice'][value='3']").prop("disabled", true);
					$("input[name='s1year_choice'][value='4']").prop("disabled", true);

					$("#s1singleyear").prop('disabled', true);
				
				<?php
				}

				// Single year

				else if ($reviews_by_stage[0]['start_date'] == $reviews_by_stage[0]['end_date']) { ?>

					$("input[name='s1year_choice'][value='1']").prop("checked", true);
					$("#s1singleyear").val('<?php echo $reviews_by_stage[0]['start_date'] ?>');
					$('#s1singleyear option[value!="<?php echo $reviews_by_stage[0]['start_date'] ?>"]').prop('disabled', true);
					
					$("input[name='s1year_choice'][value='2']").prop("disabled", true);
					$("input[name='s1year_choice'][value='3']").prop("disabled", true);
					$("input[name='s1year_choice'][value='4']").prop("disabled", true);

					$("#s1startyear").prop('disabled', 'true');
					$("#s1endyear").prop('disabled', 'true');

				<?php

				}


				// Stage 2 data

				// Don't know / don't recall

				if ($reviews_by_stage[1]['start_date'] == 777 && $reviews_by_stage[1]['end_date'] == 777) { ?>
				
				<?php
				}

				// Prefer not to answer

				else if ($reviews_by_stage[1]['start_date'] == 999 && $reviews_by_stage[1]['end_date'] == 999) { ?>
				
				<?php
				}

				// Range of years

				else if ($reviews_by_stage[1]['start_date'] != $reviews_by_stage[1]['end_date']) { ?>

					$("input[name='s2year_choice'][value='2']").prop("checked", true);
					$("#s2startyear").val('<?php echo $reviews_by_stage[1]['start_date'] ?>');
					$("#s2endyear").val('<?php echo $reviews_by_stage[1]['end_date'] ?>');

					$('#s2startyear option[value!="<?php echo $reviews_by_stage[1]['start_date'] ?>"]').prop('disabled', true);
					$('#s2endyear option[value!="<?php echo $reviews_by_stage[1]['end_date'] ?>"]').prop('disabled', true);

					$("input[name='s2year_choice'][value='1']").prop("disabled", true);
					$("input[name='s2year_choice'][value='3']").prop("disabled", true);
					$("input[name='s2year_choice'][value='4']").prop("disabled", true);

					$("#s2singleyear").prop('disabled', true);

					<?php

					// Don't show next (academic role) form element until user has
					// chosen a sub stage for Stage 1
					//
					// This is necessary for cases where Stage 2 feedback
					// exists, and the user is giving Stage 1 feedback,
					// and therefore the first 2 sub stage options for Stage 1
					// are disabled, and neither of the remaining choices
					// has been selected
				}

				// Single year

				else if ($reviews_by_stage[1]['start_date'] == $reviews_by_stage[1]['end_date']) { ?>

					$("input[name='s2year_choice'][value='1']").prop("checked", true);
					$("#s2singleyear").val('<?php echo $reviews_by_stage[1]['start_date'] ?>');
					$('#s2singleyear option[value!="<?php echo $reviews_by_stage[1]['start_date'] ?>"]').prop('disabled', true);
					
					$("input[name='s2year_choice'][value='2']").prop("disabled", true);
					$("input[name='s2year_choice'][value='3']").prop("disabled", true);
					$("input[name='s2year_choice'][value='4']").prop("disabled", true);

					$("#s2startyear").prop('disabled', 'true');
					$("#s2endyear").prop('disabled', 'true');

			<?php
				}

			}

			// If the user chosen stage exists, show the next (academic role) form element

			Switch($stage) {

				Case "1":

					if ($completed_reviews_by_uuid == 1 || $completed_reviews_by_uuid == 3) { ?>
	
						if ($("input[name='s1year_choice']:checked").val()) {

							$('#s1role_div').show();
						}

					<?php
					}

				break;


				Case "2":

					if ($completed_reviews_by_uuid == 2 || $completed_reviews_by_uuid == 3) { ?>
	
						$('#s2role_div').show();

					<?php
					}

				break;
			}

			

			if (isset($orphan_ref_code_invite) && $orphan_ref_code_invite != "") { ?>

			$("#ref_code").val('<?php echo $orphan_ref_code_invite ?>');
			$("#ref_code").prop('disabled', 'true');

			<?php

			}

			?>
		});

		</script>

	<?php

	}
}

include '../assets/layouts/footer.php'
?>
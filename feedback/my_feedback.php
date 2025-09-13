<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('TITLE', "My reviews");

include '../assets/layouts/header.php';
check_verified();

require 'functions.php';

$user_id = $_SESSION['id'];

// Connect to database

$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */

if (mysqli_connect_errno()) {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
}


// get unique list of UUIDs for user's reviews:

$sql = "SELECT BIN_TO_UUID(`review_uuid`) AS review_uuid, `review_ref`, role_type FROM `feedback_reviews` WHERE `user_id` = '" . $user_id . "' GROUP BY `feedback_reviews`.`review_uuid` ORDER BY role_type ASC, completed_on DESC, review_uuid ASC, stage_id ASC";

if ($result = mysqli_query($db_handle, $sql)) {

  if (mysqli_num_rows($result) > 0) {

    $reviews_uuids = mysqli_fetch_all($result, MYSQLI_ASSOC);
  }
}

// get review metadata

$sql = "SELECT `review_id`, BIN_TO_UUID(`review_uuid`) AS review_uuid, role_type, `review_ref`, `stage_id`, `sub_stage_id`, `journals`.`journal_name`, `feedback_reviews`.`completed_on` FROM `feedback_reviews` INNER JOIN `journals` ON `feedback_reviews`.`journal_id` = `journals`.`journal_id` WHERE `user_id` = '" . $user_id . "' ORDER BY role_type ASC, review_uuid ASC, stage_id ASC, completed_on DESC";

if ($result = mysqli_query($db_handle, $sql)) {

  if (mysqli_num_rows($result) > 0) {

    $review_metadatas = mysqli_fetch_all($result, MYSQLI_ASSOC);
  }
}
?>
<main>
<h2>My feedback</h2>

<?php

for ($role_type = 1; $role_type <= 2; $role_type++) {

	$table_contents = "";

	switch ($role_type) {

		case 1:

			echo "<h3>As an author</h3>";

		break;


		case 2:

			echo "<h3>As a reviewer</h3>";

		break;
	}

	if (isset($reviews_uuids)) {

		foreach ($reviews_uuids as $reviews_uuid) {

			if ($reviews_uuid['role_type'] == $role_type) {

				$contains_stage1  = false;
				$contains_stage2  = false;

				$stage1_review_id = '';
				$stage2_review_id = '';

				$stage1_sub_stage_id = '';
				$stage2_sub_stage_id = '';

				$journal1         = '';
				$journal2         = '';
				$journal_name     = '';

				foreach ($review_metadatas as $reviews_metadata) {

					if ($reviews_uuid['review_uuid'] == $reviews_metadata['review_uuid']) {

						if ($reviews_metadata['stage_id'] == '1') {

							$contains_stage1     = true;
							$stage1_review_id    = $reviews_metadata['review_id'];
							$stage1_sub_stage_id = $reviews_metadata['sub_stage_id'];
							$journal1            = $reviews_metadata['journal_name'];
						}

						if ($reviews_metadata['stage_id'] == '2') {

							$contains_stage2     = true;
							$stage2_review_id    = $reviews_metadata['review_id'];
							$stage2_sub_stage_id = $reviews_metadata['sub_stage_id'];
							$journal2            = $reviews_metadata['journal_name'];
						}
					}
				}
				
				$table_contents = $table_contents . "<tr><td>";

				// Check if both journal names have a value, and if they are disimilar.
				// If so, we add both to the journal column, one on each line

				if (($journal1 != "" && $journal2 != "") && $journal1 != $journal2) {

					$journal_name = $journal1 . " +<br />" . $journal2;
				}

				// Both journal names have a value, and they are the same

				else if (($journal1 != "" && $journal2 != "") && $journal1 == $journal2) {

					$journal_name = $journal1;
				}

				else

				{					
					// This works because we've determined that one journal name is empty, so we
					// can simply concatenate both strings, ending up with a single string,
					// which is either prefixed or suffixed by a blank string!

					$journal_name = $journal1 . $journal2;
				}

				$table_contents = $table_contents . $journal_name . "</td><td>" . $reviews_uuid['review_ref'] . "</td>";

				// Only stage 1 feedback exists

				if ($contains_stage1 == true && $contains_stage2 == false) {

					$table_contents = $table_contents . "<td align='center'><img src='../assets/images/tick-icon.png' alt='Tick icon' title='Feedback completed for Stage 1 for this manuscript' width='17' height='17' border='0' /></td>";

					// If the Stage 1 manuscript was rejected, don't show the option to give Stage 2 feedback (and show an X instead)

					if ($stage1_sub_stage_id == '1' || $stage1_sub_stage_id == '2' || $stage1_sub_stage_id == '8') {
							$table_contents = $table_contents .  "<td align='center'><img src='../assets/images/na-icon.png' alt='Not applicable icon' title='Not applicable - manuscript did not reach Stage 2' width='25' height='12' border='0' /></td>";
					}

					// If the Stage 1 manuscript was recorded as having progressed to
					// Stage 2, add a link, allowing the user to add Stage 2 feedback

					// N.B. This is an alternative route to linking to orphan feedback
					//      but has the same effect in terms of creating pairs of feedback
					//      for a specific user/manuscript

					else

					{
						$table_contents = $table_contents .  "<td align='center'><a title='Add Stage 2 feedback for this manuscript (REF code: " . $reviews_uuid['review_ref'] . ")' href='selector.php?uuid=" . $reviews_uuid['review_uuid'] . "&stage=2&self=1'><img alt='Edit icon' src='../assets/images/edit-icon.svg' width='22' height='22' border='0' /></a></td>";
					}

					// View link

					$table_contents = $table_contents . "<td align='center'><a href='review_summary.php?review_id=" . $stage1_review_id . "' rel='modal:open' title='View your feedback (REF code: " . $reviews_uuid['review_ref'] . ")'><img alt='Eye icon' src='../assets/images/eye-icon.svg' width='22' height='22' border='0' /></a></td>";
				}
 
				// Only stage 2 feedback exists

				elseif ($contains_stage1 == false && $contains_stage2 == true) {

					$table_contents = $table_contents . "<td align='center'><a title='Add Stage 1 feedback for this manuscript (REF code: " . $reviews_uuid['review_ref'] . ")' href='selector.php?uuid=" . $reviews_uuid['review_uuid'] . "&stage=1&self=1'><img alt='Edit icon' src='../assets/images/edit-icon.svg' width='22' height='22' border='0' /></a></td>";

					$table_contents = $table_contents . "<td align='center'><img src='../assets/images/tick-icon.png' alt='Tick icon' title='Feedback completed for Stage 2 for this manuscript' width='17' height='17' border='0' /></td>";

					$table_contents = $table_contents . "<td align='center'><a href='review_summary.php?review_id=" . $stage2_review_id . "' rel='modal:open' title='View your feedback (REF code: " . $reviews_uuid['review_ref'] . ")'><img alt='Eye icon' src='../assets/images/eye-icon.svg' width='22' height='22' border='0' /></a></td>";
				}

				// Both stage 1 and 2 feedback exists

				elseif ($contains_stage1 == true && $contains_stage2 == true) {

					$table_contents = $table_contents . "<td align='center'><img src='../assets/images/tick-icon.png' alt='Tick icon' title='Feedback completed for Stage 1 for this manuscript' width='17' height='17' border='0' /></td><td align='center'><img src='../assets/images/tick-icon.png' alt='Tick icon' title='Feedback completed for Stage 2 for this manuscript' width='17' height='17' border='0' /></td>";

					$table_contents = $table_contents . "<td align='center'><a href='review_summary.php?uuid=" . $reviews_uuid['review_uuid'] . "' rel='modal:open' title='View your feedback (REF code: " . $reviews_uuid['review_ref'] . ")'><img alt='Eye icon' src='../assets/images/eye-icon.svg' width='22' height='22' border='0' /></a></td>";
				}

				if ($role_type == 1) {

					$table_contents = $table_contents . "<td align='center'><a href='invite_coauthor.php?uuid=" . $reviews_uuid['review_uuid'] . "' rel='modal:open' title='Invite a co-author to give feedback on the manuscript (REF code: " . $reviews_uuid['review_ref'] . ")'><img alt='Email icon' src='../assets/images/email-icon.svg' width='16' height='16' border='0' /></a></td></tr>";
				}

				else

				{
					$table_contents = $table_contents . "</tr>";
				}
			}
		}
	}

	// Check to see if there is any table content i.e. any feedback in this section (author or reviewer)

	if ($table_contents != "") {

		echo "<div class='table-wrapper'><table class='altrows reviews-table'><tbody><tr><th><b>Journal</b></th><th><b>REF code</b></th><th align='center'><b>Stage 1</b></th><th align='center'><b>Stage 2</b></th><th align='center'><b>View</b></th>";

			// For any author feedback, add a link so that users can invite
			// manuscript co-authors to provide feedback on the peer review
			// process for a particular manuscript
			// 
			// N.B. we use the same UUID to link feedback across different users

		if ($role_type == 1) {

			echo "<th align='center'><b>Invite</b></th></tr>" . $table_contents . "</tbody></table></div>";
		}

		else

		{
			echo "</tr>" . $table_contents . "</tbody></table></div>";
		}
	}
}
?>
<br />

<div id="ex1" class="modal">
	<a href="#" rel="modal:close">Close</a>
</div>
</main>

<script type="text/javascript" src="../assets/js/clipboard.min.js"></script>
<script type="text/javascript" src="../assets/js/popper.min.js"></script>
<script type="text/javascript" src="../assets/js/tippy-bundle.umd.min.js"></script>
<?php
include '../assets/layouts/footer.php'
?>
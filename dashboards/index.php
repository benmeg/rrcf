<?php

// Starting clock time in seconds
$start_time = microtime(true);

define('TITLE', "Dashboards");
include '../assets/layouts/header.php';

require '../feedback/functions.php';

$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// Get list of all journals
// Thanks to David Kane for optimising this statement

$sql = 'SELECT `journals`.journal_id, `journals`.journal_name, COUNT(review_id) AS review_count, CASE WHEN COUNT(review_id) > 0 AND COUNT(review_id) < 5 THEN "not enough reviews" WHEN COUNT(review_id) >= 5 THEN "enough reviews" ELSE "no reviews" END AS review_status, CASE WHEN COUNT(review_id) > 0 AND COUNT(review_id) < 5 THEN 2 WHEN COUNT(review_id) >= 5 THEN 1 ELSE 3 END AS review_cat FROM `journals` LEFT JOIN `feedback_reviews` ON `journals`.journal_id = `feedback_reviews`.journal_id GROUP BY `journals`.journal_id ORDER BY review_cat ASC, journal_name ASC';

if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $journals = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

// Get speed and quality average rating data where it exists

$sql = "SELECT feedback_reviews.journal_id, feedback_question_categories.category_id, AVG(question_response_value) AS average_rating_mean, COUNT(question_response_value) AS item_count FROM `feedback_responses` INNER JOIN feedback_questions ON feedback_responses.question_id = feedback_questions.question_id INNER JOIN feedback_reviews ON feedback_responses.review_id = feedback_reviews.review_id INNER JOIN feedback_question_categories ON feedback_questions.question_category = feedback_question_categories.category_id INNER JOIN journals ON feedback_reviews.journal_id = journals.journal_id WHERE feedback_questions.question_type = 1 AND feedback_responses.question_response_value != '777' AND feedback_responses.question_response_value != '888' AND feedback_responses.question_response_value != '999' GROUP BY feedback_reviews.journal_id, feedback_question_categories.category_title ORDER BY `feedback_reviews`.`journal_id` ASC, feedback_reviews.role_type ASC, feedback_question_categories.category_id ASC";

if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $journal_rating_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

?>

<link rel="stylesheet" type="text/css" href="../assets/css/jquery.dataTables.min.css" />
  
<script type="text/javascript" charset="utf8" src="../assets/js/jquery.dataTables.min.js"></script>

<link rel="stylesheet" type="text/css" href="../assets/css/switchbox.css" />

<style>

/* https://stackoverflow.com/questions/70077403/css-for-star-ratings */

[data-star] {
  text-align:left;
  font-style:normal;
  display:inline-block;
  position: relative;
  unicode-bidi: bidi-override;
}
[data-star]::before { 
  display:block;
  content: '★★★★★';
  color: #ddd;
}
[data-star]::after {
  white-space:nowrap;
  position:absolute;
  top:0;
  left:0;
  content: '★★★★★';
  width: 0;
  color: orange;
  overflow:hidden;
  height:100%;
}

[data-star^="0.1"]::after,[data-star^=".1"]::after{width:2%}
[data-star^="0.2"]::after,[data-star^=".2"]::after{width:4%}
[data-star^="0.3"]::after,[data-star^=".3"]::after{width:6%}
[data-star^="0.4"]::after,[data-star^=".4"]::after{width:8%}
[data-star^="0.5"]::after,[data-star^=".5"]::after{width:10%}
[data-star^="0.6"]::after,[data-star^=".6"]::after{width:12%}
[data-star^="0.7"]::after,[data-star^=".7"]::after{width:14%}
[data-star^="0.8"]::after,[data-star^=".8"]::after{width:16%}
[data-star^="0.9"]::after,[data-star^=".9"]::after{width:18%}
[data-star^="1"]::after{width:20%}
[data-star^="1.1"]::after{width:22%}
[data-star^="1.2"]::after{width:24%}
[data-star^="1.3"]::after{width:26%}
[data-star^="1.4"]::after{width:28%}
[data-star^="1.5"]::after{width:30%}
[data-star^="1.6"]::after{width:32%}
[data-star^="1.7"]::after{width:34%}
[data-star^="1.8"]::after{width:36%}
[data-star^="1.9"]::after{width:38%}
[data-star^="2"]::after{width:40%}
[data-star^="2.1"]::after{width:42%}
[data-star^="2.2"]::after{width:44%}
[data-star^="2.3"]::after{width:46%}
[data-star^="2.4"]::after{width:48%}
[data-star^="2.5"]::after{width:50%}
[data-star^="2.6"]::after{width:52%}
[data-star^="2.7"]::after{width:54%}
[data-star^="2.8"]::after{width:56%}
[data-star^="2.9"]::after{width:58%}
[data-star^="3"]::after{width:60%}
[data-star^="3.1"]::after{width:62%}
[data-star^="3.2"]::after{width:64%}
[data-star^="3.3"]::after{width:66%}
[data-star^="3.4"]::after{width:68%}
[data-star^="3.5"]::after{width:70%}
[data-star^="3.6"]::after{width:72%}
[data-star^="3.7"]::after{width:74%}
[data-star^="3.8"]::after{width:76%}
[data-star^="3.9"]::after{width:78%}
[data-star^="4"]::after{width:80%}
[data-star^="4.1"]::after{width:82%}
[data-star^="4.2"]::after{width:84%}
[data-star^="4.3"]::after{width:86%}
[data-star^="4.4"]::after{width:88%}
[data-star^="4.5"]::after{width:90%}
[data-star^="4.6"]::after{width:92%}
[data-star^="4.7"]::after{width:94%}
[data-star^="4.8"]::after{width:96%}
[data-star^="4.9"]::after{width:98%}
[data-star^="5"]::after{width:100%}

/* tooltip css for response count */

/* https://www.w3schools.com/css/css_tooltip.asp */

/* Tooltip container */
.tooltip {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black; 
  cursor: help;
}

/* Tooltip text */
.tooltip .tooltiptext {
  visibility: hidden;
  width: 160px;
  bottom: 150%;
  left: 50%;
  margin-left: -80px;
  background-color: black;
  color: #fff;
  text-align: center;
  padding: 5px 0;
  border-radius: 6px;
  font-size: 1.2em;
 
  /* Position the tooltip text - see examples below! */
  position: absolute;
  z-index: 1;
}

.tooltip .tooltiptext::after {
  content: " ";
  position: absolute;
  top: 100%; /* At the bottom of the tooltip */
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: black transparent transparent transparent;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
  visibility: visible;
}
</style>

<main>

<h2 align="center">Ratings of Registered Reports peer review process</h2>

<div id="table_wrapper" style="margin:0 auto; width: 100%; max-width: 1024px;">

<!-- https://github.com/Twikito/easy-toggle-state // -->

<button id="toggle_rated_journals" data-toggle-class="is-pressed" class="example-switchbox" type="button" aria-pressed="false" title="Toggle this box">
  <span class="example-switchbox-yes">show</span>
  <span class="example-switchbox-no">hide</span>

  <!-- The class `sr-only` is for hiding content for accessibility -->
  <span class="sr-only">Toggle</span>
</button>

<label for="toggle_rated_journals">Show/hide journals with insufficient ratings</label>

<br />
<br />

<table id="table_id" class="display" data-order="[]" width="100%">
	<thead>
		<tr>
			<th>Journal</th>
			<th>Speed <span id='tooltip_combined-ratings' class='survey-tooltip' tabindex='-1'>?</span></th>
			<th>Quality <span id='tooltip_combined-ratings' class='survey-tooltip' tabindex='-1'>?</span></th>
      <th>Ratings</th>
		</tr>
	</thead>
  <tbody>
<?php

	// iterate through each journal, so we list them all in the datatable, not just ones with average ratings

  foreach ($journals as $journal_array) { 

    $speed_html                  = "";
    $quality_html                = "";
 
    $speed_avg                   = 0;
    $quality_avg                 = 0;

    $quality_star_rating_count   = 0;
    $quality_star_rating_average = 0;

    // We want at least 5 reviews (stage 1 and/or stage 2) for this journal before we show summary statistics in the main table

    if ($journal_array['review_count'] >= 5 ) {

      // this foreach loop is slow (but it works!) - it iterates over each row in the rating array, looking to match against speed or quality.
      // Ideally there would be a single SQL query that would grab all journal IDs, names, and speed/quality

      foreach ($journal_rating_data as $journal_rating_data_item) {

        if ($journal_rating_data_item["journal_id"] == $journal_array["journal_id"]) {        

          // Speed

          if ($journal_rating_data_item["category_id"] == 1) {

            // check there are at least 5 data points which make up the average for the speed category for this journal

            if ($journal_rating_data_item["item_count"] >= 5) {

              $speed_avg        = round($journal_rating_data_item["average_rating_mean"], 1);
              $speed_percentage = round((((($speed_avg - 1) * 80) / (5-1)) + 20));

              $speed_html = $speed_avg . " <i data-star='" . $speed_avg . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $journal_rating_data_item["item_count"] . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
            }
          }

          // Quality

          elseif ($journal_rating_data_item["category_id"] == 2) {

            // If there are quality responses for this journal (there likely are, given we should not reach this section unless there are >5 reviews for this journal) then at this point we won't perform any calculations yet.

            // This is because we want to sum this item count with the item count of any non-type 1 questions to ensure we have > 5 items
            // N.B. Perhaps the minimum item count for a category could be 25 (i.e. 5 reviews, each with an average of at least 5 items)?

            $quality_star_rating_average = $journal_rating_data_item['average_rating_mean'];
            $quality_star_rating_count   = $journal_rating_data_item['item_count'];
          }
        }
      }

      $temp_quality_array = getCombinedOverallQualityAverage ($journal_array["journal_id"], $quality_star_rating_average, $quality_star_rating_count);

      if ($temp_quality_array[0] == 1) {

          $quality_avg = round($temp_quality_array[1], 1);
          $quality_percentage = round((((($quality_avg - 1) * 80) / (5-1)) + 20));

          $quality_html = $quality_avg . " <i data-star='" . $quality_avg . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $temp_quality_array[2] . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
      }

      $empty_row = false;

      if ($speed_html == "" && $quality_html == "") {

        // if no data/not enough data exists for this journal for either category (speed or quality) then set this variable, which is later added to the table row, and used to toggle empty rows via javascript - see $("#hide").click(function()

        $empty_row = true;
      }

      if ($speed_html == "") {

        $speed_html = "Not enough data <span id='tooltip_not-enough-data' class='survey-tooltip' tabindex='-1'>?</span>";
      }

      if ($quality_html == "") {

        $quality_html = "Not enough data <span id='tooltip_not-enough-data' class='survey-tooltip' tabindex='-1'>?</span>";
      }
    }

    // no reviews for this journal, so show no data

    elseif ($journal_array['review_count'] == 0 ) {

      $empty_row = true;
      $speed_html = "No data yet <span id='tooltip_no-data' class='survey-tooltip' tabindex='-1'>?</span>";
      $quality_html = "No data yet <span id='tooltip_no-data' class='survey-tooltip' tabindex='-1'>?</span>";
    }

    // review count is between 1 and 4

    else

    {
      $empty_row = true;
      $speed_html = "Not enough data <span id='tooltip_not-enough-data' class='survey-tooltip' tabindex='-1'>?</span>";
      $quality_html = "Not enough data <span id='tooltip_not-enough-data' class='survey-tooltip' tabindex='-1'>?</span>";
    }

    ?>
	<tr<?php if ($empty_row == true) echo " empty-row='1'"; ?>>
      <td><a href="journal_info.php?id=<?php echo $journal_array["journal_id"]; ?>"><?php echo $journal_array["journal_name"]; ?></a> (<?php echo $journal_array['review_count'];?> rating<?php if ($journal_array['review_count'] != 1) echo "s" ?>)</td>
      <td <?php echo ($speed_avg == 0) ? "data-order='0'" : "data-order='" . $speed_avg . "'"; ?>><?php echo $speed_html ?></td>
      <td <?php echo ($quality_avg == 0) ? "data-order='0'" : "data-order='" . $quality_avg . "'"; ?>><?php echo $quality_html ?></td>
      <td <?php echo "data-order='" . $journal_array['review_count'] . "'"; ?>><?php echo $journal_array['review_count']; ?></td>
    </tr>
<?php } ?>

  </tbody>
</table>

</div>

<br />
<br />

<div align="center"><a href="rankings.php" rel="modal:open">How are these ratings calculated?</a></div>

</main>

<script type="text/javascript" src="../assets/js/popper.min.js"></script>
<script type="text/javascript" src="../assets/js/tippy-bundle.umd.min.js"></script>
<script type="text/javascript" src="../assets/js/easy-toggle-state.min.js"></script>

<script>

var table = $('#table_id').DataTable({
  "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
  columnDefs: [
    {
        targets: [ 1, 2 ],
        className: 'dt-center'
    },
    {
        targets: 1,
        "orderData": [1, 3]
    },
    {
        targets: 2,
        "orderData": [2, 3]
    }
  ],
  "pageLength": 50,
  "autoWidth": true,
  "columns": [
    { "width": "60%" },
    {"orderSequence": ["desc", "asc" ], "targets": [ 1] },
    {"orderSequence": ["desc", "asc" ], "targets": [ 2] },
    {"visible": false, searchable: false, "targets": 3}
  ]
  /* , add pagination to top   "dom": 'lpftrip' */
} );


$("#hide").click(function() {
    $.fn.dataTable.ext.search.push(
      function(settings, data, dataIndex) {
          return $(table.row(dataIndex).node()).attr('empty-row') != 1;
        }
    );
    table.draw();
});

$("#reset").click(function() {
    $.fn.dataTable.ext.search.pop();
    table.draw();
});

table.on( 'draw', function () {
    create_tippy();
} );

// Create tooltip

create_tippy();

function create_tippy() {

  tippy('#tooltip_no-data', { content: 'There is currently no feedback for this journal<br /><br />Once a minimum of 5 pieces of feedback has been given by authors or reviewers for this journal\'s category, an average rating will appear.', allowHTML: true});
  tippy('#tooltip_not-enough-data', { content: 'There is not yet enough data available to calculate an average (mean) rating for this journal\'s category.<br /><br />Once a minimum of 5 pieces of feedback (and at least 5 response ratings) has been completed by authors or reviewers for this journal\'s category, an average rating will appear.', allowHTML: true});
  tippy('#tooltip_combined-ratings', { content: 'Category ratings on this page are combined across author and reviewer feedback.<br /><br />Please click on a specific journal name to see more detailed ratings broken down by author/reviewer, stage, and category.', allowHTML: true});
};

document.querySelector("#toggle_rated_journals").addEventListener("toggleAfter", event => {

  if (window.easyToggleState.isActive(event.target) == true) {

    $.fn.dataTable.ext.search.push(
      function(settings, data, dataIndex) {
          return $(table.row(dataIndex).node()).attr('empty-row') != 1;
        }
    );
    table.draw();
  }

  else

  {
    $.fn.dataTable.ext.search.pop();
    table.draw();
  }

}, false);

</script>

<?php
include '../assets/layouts/footer.php';

// End clock time in seconds
$end_time = microtime(true);
  
// Calculate script execution time
$execution_time = ($end_time - $start_time);

echo "<!-- Execution time:".$execution_time." sec // -->";
?>
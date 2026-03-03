<?php

define('TITLE', "Question ranking");

session_start();

require '../assets/setup/env.php';
require '../assets/setup/db.inc.php';
require '../assets/includes/security_functions.php';

if (isset($_SESSION['auth']))
    $_SESSION['expire'] = ALLOWED_INACTIVITY_TIME;

//generate_csrf_token();
//check_remember_me();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Connect to database

$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

parse_str($_SERVER['QUERY_STRING'], $params);

$question_id = $params['id'];

if (!is_numeric($question_id)) {

    $question_id = 1; // default to Stage 1 speed (author)
}

$sql = "SELECT fq.question_role_type AS role_type, SUBSTRING( fq.question_text, 2, LENGTH(fq.question_text) - 2 ) AS question_text, SUBSTRING( fq.question_text_summary, 2, LENGTH(fq.question_text_summary) - 2 ) AS question_text_summary, j.journal_name, CASE WHEN fq.question_role_type = 1 AND fq.question_sub_stage_1 + fq.question_sub_stage_2 + fq.question_sub_stage_3 + fq.question_sub_stage_4 + fq.question_sub_stage_8 = 0 THEN 'NP' WHEN fq.question_role_type = 2 AND fq.q_stage_1 = 0 THEN 'NP' ELSE CAST( AVG( CASE WHEN rv.stage_id = 1 AND fr.question_response_value BETWEEN 1 AND 5 THEN fr.question_response_value END ) AS CHAR ) END AS avg_rating_stage_1, COUNT( CASE WHEN rv.stage_id = 1 AND fr.question_response_value BETWEEN 1 AND 5 THEN 1 END ) AS response_count_stage_1, CASE WHEN fq.question_role_type = 1 AND fq.question_sub_stage_5 + fq.question_sub_stage_6 + fq.question_sub_stage_7 = 0 THEN 'NP' WHEN fq.question_role_type = 2 AND fq.q_stage_2 = 0 THEN 'NP' ELSE CAST( AVG( CASE WHEN rv.stage_id = 2 AND fr.question_response_value BETWEEN 1 AND 5 THEN fr.question_response_value END ) AS CHAR ) END AS avg_rating_stage_2, COUNT( CASE WHEN rv.stage_id = 2 AND fr.question_response_value BETWEEN 1 AND 5 THEN 1 END ) AS response_count_stage_2, CASE WHEN fq.question_sub_stage_1 = 1 OR fq.question_sub_stage_2 = 1 OR fq.question_sub_stage_3 = 1 OR fq.question_sub_stage_4 = 1 OR fq.question_sub_stage_8 = 1 THEN 1 ELSE 0 END AS author_stage_1, CASE WHEN fq.question_sub_stage_5 = 1 OR fq.question_sub_stage_6 = 1 OR fq.question_sub_stage_7 = 1 THEN 1 ELSE 0 END AS author_stage_2, fq.q_stage_1 AS reviewer_stage_1, fq.q_stage_2 AS reviewer_stage_2 FROM feedback_questions fq JOIN feedback_responses fr ON fq.question_id = fr.question_id JOIN feedback_reviews rv ON fr.review_id = rv.review_id JOIN journals j ON rv.journal_id = j.journal_id WHERE fq.question_id ='" . $question_id . "' AND fq.question_type = 1 AND fr.question_response_value BETWEEN 1 AND 5 GROUP BY fq.question_id, j.journal_name HAVING COUNT(fr.question_response_value) >= 5;";

  if ($result = mysqli_query($db_handle, $sql)) {

    if (mysqli_num_rows($result) > 0) {

      $question_by_journal_averages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
  }
?>
<html lang="en">
<head>
<meta charset="utf-8">

<title>Feedback summary</title>
</head>

<body>
<main>
<?php if (mysqli_num_rows($result) == 0) { 

    echo "No data for this question.";
}

else { ?>
<h2><?php 

if ($question_by_journal_averages[0]['question_text_summary'] <> '') {

  echo $question_by_journal_averages[0]['question_text_summary'];
}

else

{
  echo $question_by_journal_averages[0]['question_text'];
}

echo "<i>";

if ($question_by_journal_averages[0]['role_type'] == 1) {
  echo " - Author responses";
}

else

{
  echo " - Reviewer responses";
}

?></i></h2>
<h3>Average ratings for above question, by journal/platform</h3>
 
<table id="overall_table" class="display">
    <thead>
        <tr>
            <th>Journal/platform</th>
            <th>Stage 1</th>
            <th>Stage 2</th>
            <th>Overall</th>
        </tr>
    </thead>
    <tbody>
<?php

foreach ($question_by_journal_averages as $question_by_journal_average) { 

    $stage_1_valid     = false;
    $stage_1_possible  = false;
    $stage_2_valid     = false;
    $stage_2_possible  = false;
    $overall_valid     = false;
    $overall_possible  = false;
       
    $stage_1_value     = "";
    $stage_2_value     = "";

    $overall_value     = "";
    $overall_count     = "";
   
    $error_msg_s1      = "";
    $error_msg_s2      = "";
    $error_msg_overall = "";

    // Test to see if a question is possible at Stage 1

    if ($question_by_journal_average['author_stage_1'] == '1' || $question_by_journal_average['reviewer_stage_1'] == '1') {

        $stage_1_possible = true;

        // Are there 5 or more pieces of data for stage 1?
        
        if ($question_by_journal_average['response_count_stage_1'] >= 5) {

            $stage_1_valid = true;
            $stage_1_value = round($question_by_journal_average['avg_rating_stage_1'], 1);
        }

        // if there no data at all (though it is possible)?

        else if ($question_by_journal_average['response_count_stage_1'] == 0) {

            $error_msg_s1  = "No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
        }

        // Otherwise there was some amount of data (from 1-4)

        else

        {
            $error_msg_s1  = "Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
        }
    }

    // Test to see if a question is possible at Stage 2

    if ($question_by_journal_average['author_stage_2'] == '1' || $question_by_journal_average['reviewer_stage_2'] == '1') {

        $stage_2_possible = true;

        // Are there 5 or more pieces of data for stage 1?
        
        if ($question_by_journal_average['response_count_stage_2'] >= 5) {

            $stage_2_valid = true;
            $stage_2_value = round($question_by_journal_average['avg_rating_stage_2'], 1);
        }

        // if there no data at all (though it is possible)?

        else if ($question_by_journal_average['response_count_stage_2'] == 0) {

            $error_msg_s2  = "No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
        }

        // Otherwise there was some amount of data (from 1-4)

        else

        {
            $error_msg_s2  = "Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
        }
    }

    // if we have data for both stage 1 AND stage 2, let's calculate a weighted average, taking into account likely a different number of samples in each stage group

    if ($stage_1_possible && $stage_2_possible) {

        $overall_possible = true;
        
        // check if there are actually data for both Stage 1 AND Stage 2 - i.e. there's no point is displaying a weighted average if there is, for example, Stage 1 data, but no stage 2 (we could end up just showing the same Stage 1 value and count)

        if ($question_by_journal_average['response_count_stage_1'] > 0 && $question_by_journal_average['response_count_stage_2'] > 0) {

            $overall_count = $question_by_journal_average['response_count_stage_1'] + $question_by_journal_average['response_count_stage_2'];

            if ($overall_count >= 5) {

                $overall_valid = true;
                $stage_1_value = round($question_by_journal_average['avg_rating_stage_1'], 1);
                $stage_2_value = round($question_by_journal_average['avg_rating_stage_2'], 1);                
                $overall_value = round((($stage_1_value * $question_by_journal_average['response_count_stage_1']) + ($stage_2_value * $question_by_journal_average['response_count_stage_2'])) / ($question_by_journal_average['response_count_stage_1'] + $question_by_journal_average['response_count_stage_2']), 1);
            }

            else if ($overall_count == 0) {

                $error_msg_overall  = "No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
            }

            else

            {
                $error_msg_overall  = "Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
            }
        }        
    }

    ?>
        <tr>
            <td><?php echo $question_by_journal_average['journal_name']; ?></td>
            <td <?php if ($stage_1_possible && $stage_1_valid) { echo "data-order='" . $stage_1_value . "'"; } else if ($stage_1_possible && !$stage_1_valid) { echo "data-order='0'"; } ?>><?php if (!$stage_1_possible) { echo "&#8212;"; } else if ($stage_1_possible && !$stage_1_valid) { echo $error_msg_s1; } else { echo $stage_1_value; ?> <i data-star='<?php echo $stage_1_value; ?>'></i> <span class='tooltip' style='font-size: 0.7em'>(<?php echo $question_by_journal_average['response_count_stage_1']; ?>)<span class='tooltiptext'>Number of question responses this average is based on</span></span> <?php } ?></td>

            <td <?php if ($stage_2_possible && $stage_2_valid) { echo "data-order='" . $stage_2_value . "'"; } else if ($stage_2_possible && !$stage_2_valid) { echo "data-order='0'"; } ?>><?php if (!$stage_2_possible) { echo "&#8212;"; } else if ($stage_2_possible && !$stage_2_valid) { echo $error_msg_s2; } else { echo $stage_2_value; ?> <i data-star='<?php echo $stage_2_value; ?>'></i> <span class='tooltip' style='font-size: 0.7em'>(<?php echo $question_by_journal_average['response_count_stage_2']; ?>)<span class='tooltiptext'>Number of question responses this average is based on</span></span> <?php } ?></td>

            <td <?php if ($overall_possible && $overall_valid) { echo "data-order='" . $overall_value . "'"; } ?>><?php if (!$overall_valid) { echo "&#8212;"; } else if ($overall_possible && !$overall_valid) { echo $error_msg_overall; } else { echo $overall_value; ?> <i data-star='<?php echo $overall_value; ?>'></i> <span class='tooltip' style='font-size: 0.7em'>(<?php echo $overall_count; ?>)<span class='tooltiptext'>Number of question responses this average is based on</span></span> <?php } ?></td>
            <td data-order='<?php if (!$stage_1_valid) { echo "0"; } else { echo $question_by_journal_average['response_count_stage_1']; } ?>'><?php if (!$stage_1_valid) { echo "0"; } else { echo $question_by_journal_average['response_count_stage_1']; } ?></td>
            <td data-order='<?php if (!$stage_2_valid) { echo "0"; } else { echo $question_by_journal_average['response_count_stage_2']; } ?>'><?php if (!$stage_1_valid) { echo "0"; } else { echo $question_by_journal_average['response_count_stage_2']; } ?></td>
            <td data-order='<?php if (!$overall_valid) { echo "0"; } else { echo $overall_count; } ?>'><?php if (!$overall_valid) { echo "0"; } else { echo $overall_count; } ?></td>
        </tr>
<?php
}
?>
    </tbody>
</table>
<?php } ?>
</main>
<?php
if (mysqli_num_rows($result) > 0) { ?>
<script>
<?php
    // decide which column to order by when the table is loaded - for a question with a single stage, we will order by that column. For questions which span both stages, we will order by the 'overall' column

    if ($overall_possible) {
        $column_to_sort = 3;
        $helper_column  = 6;
    }

    else if ($stage_1_possible && !$stage_2_possible) {
        $column_to_sort = 1;
        $helper_column  = 4;
    }

    else if (!$stage_1_possible && $stage_2_possible) {
        $column_to_sort = 2;
        $helper_column  = 5;
    }
    ?>
      $('#overall_table').DataTable({
            searching: false, 
            paging: true, 
            pageLength: 15, 
            info: false, 
            autoWidth: false, 
            order: [
                [<?php echo $column_to_sort; ?>, 'desc'], [<?php echo $helper_column; ?>, 'desc']
            ], 
            columnDefs: [ 
                {
                    targets: [ 1, 2, 3 ], 
                    className: 'dt-center'
                },
                {
                    targets: [4, 5, 6],
                    visible: false,
                    searchable: false
                },
                {
                    target: 1,
                    "orderData": [1, 4]
                },
                {
                    target: 2,
                    "orderData": [2, 5]
                },
                {
                    target: 3,
                    "orderData": [3, 6]
                },
                {
                    orderSequence: ['desc', 'asc'], targets: [1, 2, 3]
                }
            ]
        });
      tippy('#tooltip_no-data-item', { content: 'There are currently no responses for this question at this stage.<br /><br />Once a minimum of 5 responses has been completed for this question at this stage, an average rating or summary of responses will appear.', allowHTML: true});
      tippy('#tooltip_not-enough-data-item', { content: 'There are currently not enough responses for this question at this stage.<br /><br />Once a minimum of 5 responses has been completed for this question at this stage, an average rating or summary of responses will appear.', allowHTML: true});
    </script>
<?php } ?>
</body>
</html>
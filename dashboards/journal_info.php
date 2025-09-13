<?php

// Starting clock time in seconds
$start_time = microtime(true);

// Manually add part of header.php (without header_html.php), so that we can first do a database lookup of the journal name, and add it to page title/description

require '../assets/setup/env.php';
require '../assets/setup/db.inc.php';
require '../assets/includes/auth_functions.php';
require '../assets/includes/security_functions.php';

if (isset($_SESSION['auth']))
    $_SESSION['expire'] = ALLOWED_INACTIVITY_TIME;

generate_csrf_token();
check_remember_me();

require '../feedback/functions.php';

parse_str($_SERVER['QUERY_STRING'], $params);

$journal_id = $params['id'];

// attempt to prevent SQL injections by setting the journal ID to 0 if anything but a number is passed in the querystring. Journal ID 0 does not exist in the database, so nothing is displayed, but it will fail relatively gracefully (no info on the screen, showing 'No data yet' etc)

if (!is_numeric($journal_id)) {

    $journal_id = 0;
}


$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

/* check connection */

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}


$overall_records_exist = false;

// Get journal name from journal ID (which is passed in querystring)

$sql = "SELECT journal_name, journal_url, COUNT(review_id) AS review_count FROM `journals` LEFT JOIN `feedback_reviews` ON `journals`.journal_id = `feedback_reviews`.journal_id WHERE journals.journal_id = '" . $journal_id . "'";

if ($result = mysqli_query($db_handle, $sql)) {

		if (mysqli_num_rows($result) > 0) {

			$journals = mysqli_fetch_all($result, MYSQLI_ASSOC);
		}
}

define('TITLE', $journals[0]['journal_name']);
$page_description = 'Community feedback of Registered Reports peer review at the journal: ' . $journals[0]['journal_name'];

require '../assets/layouts/header_html.php';

// Set defaults, and change based on SQL queries below

$speed_rating   = false;
$quality_rating = false;

$speed_avg     = 0;
$speed_count   = 0;

$quality_avg   = 0;
$quality_count = 0;

// We want at least 5 reviews (stage 1 and/or stage 2) for this journal before we show summary statistics in the main table

if ($journals[0]['review_count'] >= 5 ) {

    // Get overall speed and quality average scores for this journal

    $sql = "SELECT feedback_question_categories.category_id, AVG(question_response_value) AS average_rating_mean, COUNT(question_response_value) AS item_count FROM `feedback_responses` INNER JOIN feedback_questions ON feedback_responses.question_id = feedback_questions.question_id INNER JOIN feedback_reviews ON feedback_responses.review_id = feedback_reviews.review_id INNER JOIN feedback_question_categories ON feedback_questions.question_category = feedback_question_categories.category_id WHERE feedback_questions.question_type = 1 AND feedback_responses.question_response_value != '777' AND feedback_responses.question_response_value != '888' AND feedback_responses.question_response_value != '999' AND feedback_reviews.journal_id = '" . $journal_id . "' GROUP BY feedback_questions.question_category ORDER BY feedback_questions.question_id ASC";

    if ($result = mysqli_query($db_handle, $sql)) {

            if (mysqli_num_rows($result) > 0) {

                $journal_rating_data       = mysqli_fetch_all($result, MYSQLI_ASSOC);
                $overall_records_exist = true;
            }

            else

            {
                $overall_records_exist = false;
                $speed_rating          = false;
                $quality_rating        = false;
            }
    }


    if ($overall_records_exist == true) {

        $quality_star_rating_average = 0;
        $quality_star_rating_count   = 0;

        foreach ($journal_rating_data as $journal_rating_data_item) {

            // Speed

            if ($journal_rating_data_item["category_id"] == 1) {

                $speed_count = $journal_rating_data_item["item_count"];

                // check there are at least 5 data points which make up the average for the speed category for this journal

                if ( $speed_count >= 5) {

                    $speed_avg        = round($journal_rating_data_item["average_rating_mean"], 1);
                    $speed_percentage = round((((($speed_avg - 1) * 80) / (5-1)) + 20));
                    $speed_rating     = true;
                    $speed_html       = $speed_avg . " <i data-star='" . $speed_avg . "'></i>";
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

        
        // Using a weighted average, combine average of Type 1 (1-5 ratings) with Types 3,4,5 (which are standardised to 1-5 ratings by getCombinedOverallQualityAverage 

        $temp_quality_array = getCombinedOverallQualityAverage ($journal_id, $quality_star_rating_average, $quality_star_rating_count);

        if ($temp_quality_array[0] == 1) {

            $quality_rating_avg = $temp_quality_array[1];
            $quality_count      = $temp_quality_array[2];
            $quality_rating     = true;
        }


        // To calculate all proceeding tables (quality column) we'll calculate Role x Stage components using a nested loop
        // which will call a NEW function, similar to the overall quality one just created

        // N.B. Must also get item counts for each Role x Stage

        // Then, to calculate 'By role' and 'By stage' we can combine the relevant averages (using weight average)

        // First we'll get all Type 1 Speed/Quality averages, by Role x Stage
        // For both Speed and Quality we'll use weight averages to calculate supra categories (e.g. By Role, By Stage) by combining across stage/role
        // For Quality we'll first combine these scores with non-Type 1 responses
        // using getCombinedQualityAverageByRoleXstage which standardises 

        // Return up to 8 rows of averages (Role x Stage) for this journal

        $sql = "SELECT feedback_reviews.role_type AS role_id, feedback_reviews.stage_id, feedback_question_categories.category_id, AVG(question_response_value) AS average_rating_mean, COUNT(question_response_value) As frequency FROM `feedback_responses` INNER JOIN feedback_questions ON feedback_responses.question_id = feedback_questions.question_id INNER JOIN feedback_reviews ON feedback_responses.review_id = feedback_reviews.review_id INNER JOIN feedback_question_categories ON feedback_questions.question_category = feedback_question_categories.category_id INNER JOIN journals ON feedback_reviews.journal_id = journals.journal_id INNER JOIN feedback_reviewer_roles ON feedback_reviews.role_type = feedback_reviewer_roles.role_id WHERE feedback_questions.question_type = 1 AND feedback_responses.question_response_value != '777' AND feedback_responses.question_response_value != '888' AND feedback_responses.question_response_value != '999' AND feedback_reviews.journal_id = '" . $journal_id . "' GROUP BY feedback_question_categories.category_id, feedback_reviews.role_type, feedback_reviews.stage_id ORDER BY feedback_reviews.role_type ASC, `feedback_reviews`.stage_id ASC, feedback_question_categories.category_id ASC";

        if ($result = mysqli_query($db_handle, $sql)) {

            if (mysqli_num_rows($result) > 0) {

                $by_roleXstage_rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
        }


        // Set all defaults to zero, so if <8 rows are returned by the above query, later code will work

        // N.B. first number = role, second number = stage - e.g: 12 = author X stage 2

        // Author

        // Stage 1

        $roleXstage11_speed_average   = 0;
        $roleXstage11_speed_count     = 0;

        $roleXstage11_quality_average = 0;
        $roleXstage11_quality_count   = 0;

        // Stage 2

        $roleXstage12_speed_average   = 0;
        $roleXstage12_speed_count     = 0;

        $roleXstage12_quality_average = 0;
        $roleXstage12_quality_count   = 0;

        // Reviewer

        // Stage 1

        $roleXstage21_speed_average   = 0;
        $roleXstage21_speed_count     = 0;

        $roleXstage21_quality_average = 0;
        $roleXstage21_quality_count   = 0;

        // Stage 2

        $roleXstage22_speed_average   = 0;
        $roleXstage22_speed_count     = 0;

        $roleXstage22_quality_average = 0;
        $roleXstage22_quality_count   = 0;


        foreach ($by_roleXstage_rows as $by_roleXstage_rows_item) {

            // Author - Stage 1 - Speed

            if ($by_roleXstage_rows_item["role_id"] == 1 && $by_roleXstage_rows_item["stage_id"] == 1 && $by_roleXstage_rows_item["category_id"] == 1) {

                $roleXstage11_speed_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage11_speed_count   = $by_roleXstage_rows_item["frequency"];
            }

            // Author - Stage 1 - Quality

            if ($by_roleXstage_rows_item["role_id"] == 1 && $by_roleXstage_rows_item["stage_id"] == 1 && $by_roleXstage_rows_item["category_id"] == 2) {

                $roleXstage11_quality_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage11_quality_count   = $by_roleXstage_rows_item["frequency"];
            }

            // Author - Stage 2 - Speed

            if ($by_roleXstage_rows_item["role_id"] == 1 && $by_roleXstage_rows_item["stage_id"] == 2 && $by_roleXstage_rows_item["category_id"] == 1) {

                $roleXstage12_speed_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage12_speed_count   = $by_roleXstage_rows_item["frequency"];
            }

            // Author - Stage 2 - Quality

            if ($by_roleXstage_rows_item["role_id"] == 1 && $by_roleXstage_rows_item["stage_id"] == 2 && $by_roleXstage_rows_item["category_id"] == 2) {

                $roleXstage12_quality_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage12_quality_count   = $by_roleXstage_rows_item["frequency"];
            }

            // Reviewer - Stage 1 - Speed

            if ($by_roleXstage_rows_item["role_id"] == 2 && $by_roleXstage_rows_item["stage_id"] == 1 && $by_roleXstage_rows_item["category_id"] == 1) {

                $roleXstage21_speed_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage21_speed_count   = $by_roleXstage_rows_item["frequency"];
            }

            // Reviewer - Stage 1 - Quality

            if ($by_roleXstage_rows_item["role_id"] == 2 && $by_roleXstage_rows_item["stage_id"] == 1 && $by_roleXstage_rows_item["category_id"] == 2) {

                $roleXstage21_quality_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage21_quality_count   = $by_roleXstage_rows_item["frequency"];
            }

            // Reviewer - Stage 2 - Speed

            if ($by_roleXstage_rows_item["role_id"] == 2 && $by_roleXstage_rows_item["stage_id"] == 2 && $by_roleXstage_rows_item["category_id"] == 1) {

                $roleXstage22_speed_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage22_speed_count   = $by_roleXstage_rows_item["frequency"];
            }

            // Reviewer - Stage 2 - Quality

            if ($by_roleXstage_rows_item["role_id"] == 2 && $by_roleXstage_rows_item["stage_id"] == 2 && $by_roleXstage_rows_item["category_id"] == 2) {

                $roleXstage22_quality_average = round($by_roleXstage_rows_item["average_rating_mean"], 1);
                $roleXstage22_quality_count   = $by_roleXstage_rows_item["frequency"];
            }
        }

        // Loop through Stage x Role and get combined scores for each of the 8 above variables

        for ($role = 1; $role < 3; $role++) {

            for ($stage = 1; $stage < 3; $stage++) {

                $temp_quality_array = getCombinedQualityAverageByRoleXstage($journal_id, ${"roleXstage" . $role . $stage . "_quality_average"}, ${"roleXstage" . $role . $stage . "_quality_count"}, $role, $stage);

                if ($temp_quality_array[0] == 1) {

                    ${"roleXstage" . $role . $stage . "_quality_average"} = $temp_quality_array[1];
                    ${"roleXstage" . $role . $stage . "_quality_count"} = $temp_quality_array[2];
                }

                else

                {
                    // no data or not enough
                    ${"roleXstage" . $role . $stage . "_quality_average"} = 0;
                }

                
            }
        }


        // Calculate 'by stage' data
        // This is done by merging Role x Stage variables, using a weighted average

        $stage1_speed         = 0;
        $stage1_speed_count   = 0;

        $stage1_quality       = 0;
        $stage1_quality_count = 0;

        $stage2_speed         = 0;
        $stage2_speed_count   = 0;

        $stage2_quality       = 0;
        $stage2_quality_count = 0;


        $stage1_speed_count = $roleXstage11_speed_count + $roleXstage21_speed_count;

        if ( $stage1_speed_count >= 5) {

            $stage1_speed = round((($roleXstage11_speed_average * $roleXstage11_speed_count)+($roleXstage21_speed_average * $roleXstage21_speed_count)) / ($roleXstage11_speed_count + $roleXstage21_speed_count), 1);
        }

        
        $stage1_quality_count = $roleXstage11_quality_count + $roleXstage21_quality_count;

        if ( $stage1_quality_count >= 5) {

            $stage1_quality = round((($roleXstage11_quality_average * $roleXstage11_quality_count)+($roleXstage21_quality_average * $roleXstage21_quality_count)) / ($roleXstage11_quality_count + $roleXstage21_quality_count), 1);
        }

        
        $stage2_speed_count = $roleXstage12_speed_count + $roleXstage22_speed_count;

        if ( $stage2_speed_count >= 5) {

            $stage2_speed = round((($roleXstage12_speed_average * $roleXstage12_speed_count)+($roleXstage22_speed_average * $roleXstage22_speed_count)) / ($roleXstage12_speed_count + $roleXstage22_speed_count), 1);
        }

        
        $stage2_quality_count = $roleXstage12_quality_count + $roleXstage22_quality_count;

        if ( $stage2_quality_count >= 5) {

            $stage2_quality = round((($roleXstage12_quality_average * $roleXstage12_quality_count)+($roleXstage22_quality_average * $roleXstage22_quality_count)) / ($roleXstage12_quality_count + $roleXstage22_quality_count), 1);
        }


        // Calculate 'by role' data
        // This is done by merging Role x Stage variables, using a weighted average

        $role1_speed         = 0;
        $role1_speed_count   = 0;

        $role1_quality       = 0;
        $role1_quality_count = 0;

        $role2_speed         = 0;
        $role2_speed_count   = 0;

        $role2_quality       = 0;
        $role2_quality_count = 0;

        
        $role1_speed_count = $roleXstage11_speed_count + $roleXstage12_speed_count;

        if ( $role1_speed_count >= 5) {

            $role1_speed = round((($roleXstage11_speed_average * $roleXstage11_speed_count)+($roleXstage12_speed_average * $roleXstage12_speed_count)) / ($roleXstage11_speed_count + $roleXstage12_speed_count), 1);
        }

        
        $role1_quality_count = $roleXstage11_quality_count + $roleXstage12_quality_count;

        if ( $role1_quality_count >= 5) {

            $role1_quality = round((($roleXstage11_quality_average * $roleXstage11_quality_count)+($roleXstage12_quality_average * $roleXstage12_quality_count)) / ($roleXstage11_quality_count + $roleXstage12_quality_count), 1);
        }


        $role2_speed_count = $roleXstage21_speed_count + $roleXstage22_speed_count;

        if ( $role2_speed_count >= 5) {

            $role2_speed = round((($roleXstage21_speed_average * $roleXstage21_speed_count)+($roleXstage22_speed_average * $roleXstage22_speed_count)) / ($roleXstage21_speed_count + $roleXstage22_speed_count), 1);
        }

        
        $role2_quality_count = $roleXstage21_quality_count + $roleXstage22_quality_count;

        if ( $role2_quality_count >= 5) {

            $role2_quality = round((($roleXstage21_quality_average * $roleXstage21_quality_count)+($roleXstage22_quality_average * $roleXstage22_quality_count)) / ($roleXstage21_quality_count + $roleXstage22_quality_count), 1);
        }


        // Get per question averages/frequency distributions

        // Get question categories/IDs (excluding comments: ID=5)

        $sql = "SELECT `category_id`, `category_title` FROM `feedback_question_categories` WHERE `category_id` != 5 ORDER BY `category_id` ASC"; 

        if ($result = mysqli_query($db_handle, $sql)) {

            if (mysqli_num_rows($result) > 0) {

              $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
        }


      // Get all question IDs

      // N.B. Exclude CReDiT (question type 7), general comments (category 5), and any free text (question type 6)

      $sql = "SELECT `feedback_questions`.`question_id`, `feedback_questions`.`question_role_type`, `feedback_questions`.`question_category`, `feedback_questions`.`question_type`, `feedback_questions`.`question_text`, `feedback_questions`.`question_text_summary`, `feedback_questions`.`question_options`, q_stage_1, q_stage_2, question_sub_stage_1, question_sub_stage_2, question_sub_stage_3, question_sub_stage_4, question_sub_stage_5, question_sub_stage_6, question_sub_stage_7, question_sub_stage_8 FROM `feedback_questions` WHERE `feedback_questions`.`question_category` != 7 AND `feedback_questions`.`question_type` NOT IN (6,7) ORDER BY `feedback_questions`.`question_category` ASC, `feedback_questions`.`question_type` ASC, `feedback_questions`.`question_order` ASC, `feedback_questions`.`question_text` ASC";

      if ($result = mysqli_query($db_handle, $sql)) {

          if (mysqli_num_rows($result) > 0) {

            $question_ids = mysqli_fetch_all($result, MYSQLI_ASSOC);
          }
      }

        // SQL for getting speed/quality averages per-question

        $sql = "SELECT `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, feedback_questions.question_role_type, feedback_reviews.stage_id, feedback_questions.question_id, feedback_questions.question_text, feedback_questions.question_category, AVG(question_response_value) AS average_rating_mean, COUNT(question_response_value) AS frequency FROM `feedback_responses` INNER JOIN feedback_questions ON feedback_responses.question_id = feedback_questions.question_id INNER JOIN feedback_reviews ON feedback_responses.review_id = feedback_reviews.review_id INNER JOIN feedback_question_categories ON feedback_questions.question_category = feedback_question_categories.category_id WHERE feedback_questions.question_type = '1' AND feedback_questions.question_category IN (1,2) AND feedback_responses.question_response_value != '777' AND feedback_responses.question_response_value != '888' AND feedback_responses.question_response_value != '999' AND feedback_reviews.journal_id = '" . $journal_id . "' GROUP BY feedback_questions.question_role_type, feedback_reviews.stage_id, feedback_questions.question_category, feedback_questions.question_id ORDER BY feedback_questions.question_id ASC";

        if ($result = mysqli_query($db_handle, $sql)) {

          if (mysqli_num_rows($result) > 0) {

            $average_responses = mysqli_fetch_all($result, MYSQLI_ASSOC);
          }
        }

        // SQL for getting all other data (for frequency distributions)

        $sql = "SELECT `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, feedback_questions.question_role_type, feedback_reviews.stage_id, `feedback_questions`.`question_id`, `feedback_questions`.question_text, `feedback_questions`.question_text_summary, `feedback_questions`.question_helptext, `feedback_responses`.sub_question_id, `feedback_responses`.question_response_value, COUNT(question_response_value) As frequency FROM `feedback_responses` INNER JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` INNER JOIN feedback_reviews ON feedback_responses.review_id = feedback_reviews.review_id WHERE ((`feedback_questions`.`question_category` IN (1,2) AND `feedback_questions`.`question_type` != 1) OR (`feedback_questions`.`question_category` IN (1,2,3,4) AND `feedback_questions`.`question_type` IN (2,3,4,5))) AND feedback_reviews.journal_id = '" . $journal_id . "' GROUP BY `feedback_questions`.question_id, feedback_questions.question_role_type, feedback_reviews.stage_id, feedback_questions.question_category, `feedback_responses`.`sub_question_id`, question_response_value ORDER BY question_type ASC, question_category ASC, question_role_type ASC, feedback_reviews.stage_id ASC, question_id ASC, feedback_responses.question_response_value ASC";

        if ($result = mysqli_query($db_handle, $sql)) {

          if (mysqli_num_rows($result) > 0) {

            $other_responses = mysqli_fetch_all($result, MYSQLI_ASSOC);
          }
        }

      $per_question_html = getResponseStructure($categories, $question_ids, $average_responses, $other_responses, $journals[0]['journal_name']);
    }
}


function frand($min, $max, $decimals = 0) {
  $scale = pow(10, $decimals);
  return mt_rand($min * $scale, $max * $scale) / $scale;
}
?>
<link rel="stylesheet" type="text/css" href="../assets/css/jquery.dataTables.min.css" />
  
<script type="text/javascript" charset="utf8" src="../assets/js/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="../assets/js/popper.min.js"></script>
<script type="text/javascript" src="../assets/js/tippy-bundle.umd.min.js"></script>

<style>
.dataTables_filter {
    display: none;
}

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

/* https://stackoverflow.com/a/52454319 */

.graph-container {
  width: 95%;
  max-width: 1024px;
 /* margin: 20px; */
  /* background: #fff; 
  padding: 20px; */
  overflow: hidden;
  /* float: left; */
}

.horizontal .progress-bar {
  float: left;
  height: 18px;
  /* width: 100%; */
  flex-grow: 1;
  padding: 3px 0;
  align-self: center;
}

.horizontal .progress-track {
  position: relative;
  width: 60%;
  height: 20px;
  background: #ebebeb;
  flex-grow: 1;
}

.horizontal .progress-fill {
  position: relative;
  background: #444;
  height: 20px;
  width: 50%;
  color: #fff;
  text-align: center;
  font-family: "Lato","Verdana",sans-serif;
  font-size: 12px;
  line-height: 20px;
}

.progress-group {
  display: flex;
}

.progress-label {
  width: 60%;
  max-width: 400px; 
  text-align: right;
  padding-right: 0.6250em;
  padding-top: 0.3125em;
  padding-bottom: 0.3125em;
  font-size: 0.8em;
  /* margin-top: auto;
  margin-bottom: auto; */
}

.progress-label2 {
  width: 60%;
  max-width: 400px; 
  text-align: right;
  padding-right: 0.6250em;
  padding-top: 0.3125em;
  padding-bottom: 0.3125em;
  font-size: 0.8em;
}

.progress-label::after {
  content: ":";
}

/* https://www.digitalocean.com/community/tutorials/css-collapsible
https://codepen.io/alligatorio/pen/KKzWqVX */

/* second level collapsible background colour:  #5f9ea0 */

input[type='checkbox'] { display: none; }

.wrap-collabsible { margin: 1.2rem 0; }

.lbl-toggle { display: block; font-weight: bold; font-family: monospace; font-size: 1.2rem; text-transform: uppercase; text-align: center; padding: 0.25rem; color: #DDD; background: #0069ff; cursor: pointer; border-radius: 7px; transition: all 0.25s ease-out; }

.lbl-toggle-2nd { display: block; font-weight: bold; font-family: monospace; font-size: 1.2rem; text-transform: uppercase; text-align: center; padding: 0.25rem; color: #DDD; background: #38888b; cursor: pointer; border-radius: 7px; transition: all 0.25s ease-out; }

.lbl-toggle-3rd { display: block; font-weight: bold; font-family: monospace; font-size: 1.2rem; text-transform: uppercase; text-align: center; padding: 0.25rem; color: #DDD; background: #6d799d; cursor: pointer; border-radius: 7px; transition: all 0.25s ease-out; }

.lbl-toggle:hover { color: #FFF; } .lbl-toggle::before { content: ' '; display: inline-block; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid currentColor; vertical-align: middle; margin-right: .7rem; transform: translateY(-2px); transition: transform .2s ease-out; }

.lbl-toggle-2nd:hover { color: #FFF; } .lbl-toggle-2nd::before { content: ' '; display: inline-block; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid currentColor; vertical-align: middle; margin-right: .7rem; transform: translateY(-2px); transition: transform .2s ease-out; }

.lbl-toggle-3rd:hover { color: #FFF; } .lbl-toggle-3rd::before { content: ' '; display: inline-block; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid currentColor; vertical-align: middle; margin-right: .7rem; transform: translateY(-2px); transition: transform .2s ease-out; }

.toggle:checked+.lbl-toggle::before { transform: rotate(90deg) translateX(-3px); }

.toggle-2nd:checked+.lbl-toggle-2nd::before { transform: rotate(90deg) translateX(-3px); }

.toggle-3rd:checked+.lbl-toggle-3rd::before { transform: rotate(90deg) translateX(-3px); }

.collapsible-content { max-height: 0px; overflow: hidden; transition: max-height .25s ease-in-out; } .toggle:checked + .lbl-toggle + .collapsible-content { max-height: 100%; }

.collapsible-content-2nd { max-height: 0px; overflow: hidden; transition: max-height .25s ease-in-out; } .toggle-2nd:checked + .lbl-toggle-2nd + .collapsible-content-2nd { max-height: 100%; }

.collapsible-content-3rd { max-height: 0px; overflow: hidden; transition: max-height .25s ease-in-out; } .toggle-3rd:checked + .lbl-toggle-3rd + .collapsible-content-3rd { max-height: 100%; }

.toggle:checked+.lbl-toggle { border-bottom-right-radius: 0; border-bottom-left-radius: 0; }

.toggle-2nd:checked+.lbl-toggle-2nd { border-bottom-right-radius: 0; border-bottom-left-radius: 0; }

.toggle-3rd:checked+.lbl-toggle-3rd { border-bottom-right-radius: 0; border-bottom-left-radius: 0; }

.collapsible-content .content-inner { background: rgba(0, 105, 255, .2); border-bottom: 1px solid rgba(0, 105, 255, .45); border-bottom-left-radius: 7px; border-bottom-right-radius: 7px; padding: .5rem 1rem; }

.collapsible-content-2nd .content-inner-2nd { background: rgba(0, 105, 255, .2); border-bottom: 1px solid rgba(0, 105, 255, .45); border-bottom-left-radius: 7px; border-bottom-right-radius: 7px; padding: .5rem 1rem; }

.collapsible-content-3rd .content-inner-3rd { background: rgba(0, 105, 255, .2); border-bottom: 1px solid rgba(0, 105, 255, .45); border-bottom-left-radius: 7px; border-bottom-right-radius: 7px; padding: .5rem 1rem; }

.collapsible-content p { margin-bottom: 0; }

.collapsible-content-2nd p { margin-bottom: 0; }

.collapsible-content-3rd p { margin-bottom: 0; }

.dotted-round {
   background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='25' ry='25' stroke='%233499E0FF' stroke-width='7' stroke-dasharray='22' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
border-radius: 25px;
}

.centered-text {

    display: flex;
    justify-content: center;
    align-items: center;
}

.add-feedback {

  position: absolute;
  top: 220px;
  left: 420px;

  width: 180px;
  height: 80px;

  padding: 10px;
}

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


<body>
<main>
<h2><?php echo $journals[0]["journal_name"]; ?><?php if (!empty($journals[0]["journal_url"])) { ?>&nbsp;<a href="<?php echo $journals[0]["journal_url"]; ?>" target="_blank"><img style="vertical-align:middle" title="Open website of '<?php echo $journals[0]["journal_name"]; ?>' in new tab" alt='Link icon' src='../assets/images/link_icon.png' width='22' height='22' border='0' /></a><?php } ?></h2>

<h3>Overall rating of Registered Reports peer review process</h3>

<?php

if ($journals[0]['review_count'] >= 5 ) { ?>

<p><img alt="Info icon" src="../assets/images/info-icon.svg" height="16" width="16">&nbsp;Based on <?php echo $journals[0]['review_count']; ?> ratings from authors/reviewers</p>

<?php 
}

elseif ($journals[0]['review_count'] > 0 && $journals[0]['review_count'] < 5 ) { 
?>

<p><img alt="Info icon" src="../assets/images/info-icon.svg" height="16" width="16">&nbsp;Author/reviewers have so far provided <?php echo $journals[0]['review_count']; ?> rating<?php if ($journals[0]['review_count'] > 1) echo "s" ?> for this journal</p>

<?php } ?>

    <p style="font-size: 1.2em;">Speed: 

<?php

if ($journals[0]['review_count'] == 0) {

    echo "No data yet <span id='tooltip_no-data-overall' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($journals[0]['review_count'] < 5 || $speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-overall' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $speed_avg;
    echo " <i data-star='" . $speed_avg . "'></i> <span class='tooltip' style='font-size: 0.6em'>(" . $speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
</p>
    <p style="font-size: 1.2em;">Quality: 
<?php

if ($journals[0]['review_count'] == 0) {

    echo "No data yet <span id='tooltip_no-data-overall' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($journals[0]['review_count'] < 5 || $quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-overall' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $quality_rating_avg;
    echo " <i data-star='" . $quality_rating_avg . "'></i> <span class='tooltip' style='font-size: 0.6em'>(" . $quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}



?>
</p>

<p><a href="../dashboards/"><img style="vertical-align:middle" alt='Rank icon' src='../assets/images/rank-icon.svg' width='22' height='22' border='0' /> View dashboard of all journals</a></p>

<p><a href="../feedback/selector.php?id=<?php echo $journal_id; ?>" rel="nofollow"><img style="vertical-align:middle" alt='Add icon' src='../assets/images/edit-icon.svg' width='20' height='20' border='0' /> Add your peer review feedback for this journal</a><?php if (!isset($_SESSION['auth'])) echo " (needs login/registration)"; ?></p>
<?php

// Only try to calculate/show breakdowns (stage/role/stage x role) if overall rating data is available i.e. if overall data is not available, sub categories definitely won't be available!

if ($overall_records_exist != false) {

?>

<br />

<h3>By stage</h3>

<div id="stage_table_wrapper" style="width:100%; max-width: 660px;">

<table id="stage_table_id" class="display">
    <thead>
        <tr>
            <th>Stage</th>
            <th>Speed</th>
            <th>Quality</th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td>Stage 1</td>
            <td data-order='<?php echo $stage1_speed == 0 ? "0" : $stage1_speed; ?>'>
<?php

if ($stage1_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($stage1_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $stage1_speed;
    echo " <i data-star='" . $stage1_speed . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $stage1_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $stage1_quality == 0 ? "0" : $stage1_quality; ?>'>
<?php

if ($stage1_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($stage1_quality_count <5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $stage1_quality;
    echo " <i data-star='" . $stage1_quality . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $stage1_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

        <tr>
            <td>Stage 2</td>
            <td data-order='<?php echo $stage2_speed == 0 ? "0" : $stage2_speed; ?>'>
<?php

if ($stage2_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($stage2_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $stage2_speed;
    echo " <i data-star='" . $stage2_speed . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $stage2_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $stage2_quality == 0 ? "0" : $stage2_quality; ?>'>
<?php

if ($stage2_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($stage2_quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $stage2_quality;
    echo " <i data-star='" . $stage2_quality . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $stage2_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

    </tbody>
</table>

</div>

<br />

<h3>By role</h3>

<div id="role_table_wrapper" style="width:100%; max-width: 660px;">

<table id="role_table_id" class="display">
    <thead>
        <tr>
            <th>Role</th>
            <th>Speed</th>
            <th>Quality</th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td>Author</td>
            <td data-order='<?php echo $role1_speed == 0 ? "0" : $role1_speed; ?>'>
<?php

if ($role1_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($role1_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $role1_speed;
    echo " <i data-star='" . $role1_speed . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $role1_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $role1_quality == 0 ? "0" : $role1_quality; ?>'>
<?php

if ($role1_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($role1_quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $role1_quality;
    echo " <i data-star='" . $role1_quality . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $role1_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

        <tr>
            <td>Reviewer</td>
            <td data-order='<?php echo $role2_speed == 0 ? "0" : $role2_speed; ?>'>
<?php

if ($role2_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($role2_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $role2_speed;
    echo " <i data-star='" . $role2_speed . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $role2_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $role2_quality == 0 ? "0" : $role2_quality; ?>'>
<?php

if ($role2_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($role2_quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $role2_quality;
    echo " <i data-star='" . $role2_quality . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $role2_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

    </tbody>
</table>

</div>

<br />

<h3>By role x Stage</h3>

<div id="role_x_stage_table_wrapper" style="width:100%; max-width: 660px;">

<table id="role_x_stage_table_id" class="display">
    <thead>
        <tr>
            <th>Role x Stage</th>
            <th>Speed</th>
            <th>Quality</th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td>Author (Stage 1)</td>
            <td data-order='<?php echo $roleXstage11_speed_count < 5 ? "0" : $roleXstage11_speed_average; ?>'>
<?php

if ($roleXstage11_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage11_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage11_speed_average;
    echo " <i data-star='" . $roleXstage11_speed_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage11_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $roleXstage11_quality_count < 5 ? "0" : $roleXstage11_quality_average; ?>'>
<?php

if ($roleXstage11_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage11_quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage11_quality_average;
    echo " <i data-star='" . $roleXstage11_quality_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage11_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

        <tr>
            <td>Author (Stage 2)</td>
            <td data-order='<?php echo $roleXstage12_speed_count < 5 ? "0" : $roleXstage12_speed_average; ?>'>
<?php

if ($roleXstage12_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage12_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage12_speed_average;
    echo " <i data-star='" . $roleXstage12_speed_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage12_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $roleXstage12_quality_count < 5 ? "0" : $roleXstage12_quality_average; ?>'>
<?php

if ($roleXstage12_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage12_quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage12_quality_average;
    echo " <i data-star='" . $roleXstage12_quality_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage12_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

        <tr>
            <td>Reviewer (Stage 1)</td>
            <td data-order='<?php echo $roleXstage21_speed_count < 5 ? "0" : $roleXstage21_speed_average; ?>'>
<?php

if ($roleXstage21_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage21_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage21_speed_average;
    echo " <i data-star='" . $roleXstage21_speed_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage21_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $roleXstage21_quality_count < 5 ? "0" : $roleXstage21_quality_average; ?>'>
<?php

if ($roleXstage21_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage21_quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage21_quality_average;
    echo " <i data-star='" . $roleXstage21_quality_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage21_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

        <tr>
            <td>Reviewer (Stage 2)</td>
            <td data-order='<?php echo $roleXstage22_speed_count < 5 ? "0" : $roleXstage22_speed_average; ?>'>
<?php

if ($roleXstage22_speed_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage22_speed_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage22_speed_average;
    echo " <i data-star='" . $roleXstage22_speed_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage22_speed_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
            <td data-order='<?php echo $roleXstage22_quality_count < 5 ? "0" : $roleXstage22_quality_average; ?>'>
<?php

if ($roleXstage22_quality_count == 0) {

    echo "No data yet <span id='tooltip_no-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

elseif ($roleXstage22_quality_count < 5) {

    echo "Not enough data <span id='tooltip_not-enough-data-cat' class='survey-tooltip' tabindex='-1'>?</span>";
}

else

{
    echo $roleXstage22_quality_average;
    echo " <i data-star='" . $roleXstage22_quality_average . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $roleXstage22_quality_count . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";
}

?>
            </td>
        </tr>

    </tbody>
</table>

</div>

<script>

    $('#stage_table_id').DataTable({searching: false, paging: false, info: false, autoWidth: false, columnDefs: [ {targets: [ -1, -2 ], className: 'dt-center'}]});
    $('#role_table_id').DataTable({searching: false, paging: false, info: false, autoWidth: false, columnDefs: [ {targets: [ -1, -2 ], className: 'dt-center'}]});
    $('#role_x_stage_table_id').DataTable({searching: false, paging: false, info: false, autoWidth: false, columnDefs: [ {targets: [ -1, -2 ], className: 'dt-center'}]});

</script>

<br />
<div align="left"><a href="info.php" rel="modal:open">How are these ratings calculated?</a></div>

<br />

<h3>By question</h3>

<p>This section shows average ratings/distributions for each question in the feedback survey, by role and stage. It may be that you are particularly interested in one aspect of the Registered Reports peer review process at this journal, beyond the ratings above. N.B. Some of these will be more useful than others, but we want to present everything!</p>

<?php echo $per_question_html; ?>

</main>
<script>

$(document).ready( function () {

    $('#per_question_11_table_id').DataTable({searching: false, paging: false, info: false, autoWidth: false, columnDefs: [ {targets: [ -1, -2 ], className: 'dt-center'}]});
    $('#per_question_12_table_id').DataTable({searching: false, paging: false, info: false, autoWidth: false, columnDefs: [ {targets: [ -1, -2 ], className: 'dt-center'}]});

    $('#per_question_21_table_id').DataTable({searching: false, paging: false, info: false, autoWidth: false, columnDefs: [ {targets: [ -1, -2 ], className: 'dt-center'}]});
    $('#per_question_22_table_id').DataTable({searching: false, paging: false, info: false, autoWidth: false, columnDefs: [ {targets: [ -1, -2 ], className: 'dt-center'}]});

} );

$(window).on("load resize", function() {
      $('.progress-fill span').each(function(){
        var percent = $(this).html();
        $(this).parent().css('width', percent);

        if($(this).parent().width() <= 40) {
            $(this).css({
                'color': '#000',
                'margin-left': $(this).parent().width() + 5
            });
        }
        
        if($(this).parent().width() > 40) {
            $(this).css({
                'color': '#fff',
                'margin-left': '' 
            });
        }

      });
});


/* https://codepen.io/alligatorio/pen/OJNpgpb */

let myLabels = document.querySelectorAll('.lbl-toggle'); Array.from(myLabels).forEach(label => { label.addEventListener('keydown', e => { if (e.which === 32 || e.which === 13) { e.preventDefault(); label.click(); }; }); }); 
</script>
<?php

}

function getResponseStructure (&$categories, &$question_ids, &$avg_responses, &$oth_responses, $journal_name) {

    $struct_html = "";
    $previous_cat_id  = -1;
    $current_cat_id   = -1;

    // Iterate through each category

    for ($j = 0; $j < count($categories); $j++) {

        $current_cat_id = $j;

        // Add a header for this category

        $cat_title_displayed = 0;

        if ($cat_title_displayed == 0) {

          $cat_title_displayed = 1;

          // Add a horizontal above each survey category (except the first)

          if ($j != 0) {

            $struct_html = $struct_html . "<hr>";
          }

          // Add category header

          $struct_html = $struct_html . "<div class='wrap-collabsible'><input id='collapsible" . $j . "' class='toggle' type='checkbox'><label for='collapsible" . $j . "' class='lbl-toggle' tabindex='0'>" . $categories[$j]['category_title'] . "</label><div class='collapsible-content'><div class='content-inner'>";
        }

        if (isset($question_ids)) {

            // We will stratify response summaries by role (i.e. Author and Reviewer)
            // so here we'll iterate through:

            // Author: role_id = 1
            // Reviewer: role_id = 2

            // and only show questions and responses if they are match the current role_id

            for ($role_id = 1; $role_id <= 2; $role_id++) {

                $current_role_id = $role_id;
                $cat_average_table_start_displayed = 0;
                $cat_average_table_end_displayed   = 0;

                switch($role_id) {

                    Case '1':

                        $struct_html = $struct_html . "<div class='wrap-collabsible'><input id='collapsible" . $j . $role_id . "' class='toggle-2nd' type='checkbox' checked><label for='collapsible" . $j . $role_id . "' class='lbl-toggle-2nd' tabindex='0'>Author</label><div class='collapsible-content-2nd'><div class='content-inner-2nd'>";

                    break;

                    Case '2':

                        $struct_html = $struct_html . "<div class='wrap-collabsible'><input id='collapsible" . $j . $role_id . "' class='toggle-2nd' type='checkbox' checked><label for='collapsible" . $j . $role_id . "' class='lbl-toggle-2nd' tabindex='0'>Reviewer</label><div class='collapsible-content-2nd'><div class='content-inner-2nd'>";

                    break;
                }

                // variables to track when we change question type
                // this is important for when we go FROM displaying a table
                // of average scores (Q type=1 - star rating) in a datatables.net table TO other
                // Q types (e.g. Q type=2 - dropdown), as we will want to open and close
                // html table tags when we've encountered the last Q type=1

                $previous_type_id = 0;
                $current_type_id  = 0;

                // For all question types (except 1) we want to stratify by stage
                // so that site users are not just shown a large, unstructured list

                for ($stage_counter = 1; $stage_counter <= 2; $stage_counter++) {

                    $q_source_replace = ["{stage_number}", "{journal_name}"];
                    $q_target_replace = [$stage_counter,    $journal_name];

                    $stage_title_displayed = 0;

                    // For each question ID, look through response array
                    // matching response(s)

                    foreach ($question_ids as $qid) {

                        // only process this question if it's in the current category to be displayed AND it's in the current role (i.e. Author or Reviewer: role_id)

                        if ($qid['question_category'] == $categories[$j]['category_id'] && $qid['question_role_type'] == $role_id) {

                            $current_type_id = $qid["question_type"];

                            // If the current question type is 1 (i.e. 1-5 star rating)
                            // then we'll show this (and any subsequent type 1 responses)
                            // in a table

                            if ($cat_average_table_start_displayed == 0 && $current_cat_id != $previous_cat_id && $qid["question_type"] == 1 && $stage_counter == 1) {

                                $struct_html = $struct_html . "<div id='per_question_" . $qid['question_category'] . $role_id . "_table_wrapper' style='width:100%; max-width: 1024px;'><table id='per_question_" . $qid['question_category'] . $role_id . "_table_id' class='display'><thead><tr><th>Question</th><th>Stage 1</th><th>Stage 2</th></tr></thead><tbody>";

                                $cat_average_table_start_displayed = 1;
                            }

                            // If we've been displaying average ratings in a datatable and then switch to a different question type within the same category we need to close the table immediately

                            // N.B. If there is no switch to a different question type before the end of the category, the table is closed at the end of category loop (see below)

                            if ($current_type_id != $previous_type_id && $previous_type_id == 1 && $stage_counter == 1) {

                                $struct_html = $struct_html . "</tbody></table></div>";
                                $cat_average_table_end_displayed = 1;
                            }

                          // Add a stage header (once) for any question types after 1

                          if ($stage_title_displayed == 0 && $current_type_id != 1) {

                            $struct_html = $struct_html . "<div class='wrap-collabsible'><input id='collapsible" . $j . $role_id . $stage_counter . "' class='toggle-3rd' type='checkbox' checked><label for='collapsible" . $j . $role_id . $stage_counter . "' class='lbl-toggle-3rd' tabindex='0'>Stage " . $stage_counter . "</label><div class='collapsible-content-3rd'><div class='content-inner-3rd'>";

                            $stage_title_displayed = 1;
                          }

                          $q_root_displayed = false;

                          // Display each question root only once.
                          // This is important where there are multiple responses
                          // to a single question (e.g. sub-options, checkboxes etc)

                          if ($q_root_displayed == false) {

                            // Only show initial question text - important for
                            // condition questions, where initial and follow-up
                            // questions are embedded in some question field
                            // in database, delimited by @@

                            if ($qid['question_text_summary'] == "") {

                                $temp = ltrim(rtrim($qid['question_text'],'"'), '"');
                            }

                            else

                            {
                                $temp = ltrim(rtrim($qid['question_text_summary'],'"'), '"');
                            }

                            
                            $question_parts_array = explode(' @@ ', $temp);

                            // if we're dealing with numerical average data from Q type = 1 then we'll be displaying it in a table, so first insert the question text as the initial column

                            $q_root_displayed = true;

                            // pass the average ratings recordset to getSurveyResponses, if we're processing a star rating (i.e. Q type = 1)

                            if ($qid["question_type"] == 1 && $stage_counter == 1) {

                                $struct_html = $struct_html . "<tr><td>" . stripslashes(trim(str_replace($q_source_replace, $q_target_replace, $question_parts_array[0]), "\"")) . "</td>";

                                $struct_html = $struct_html . getSurveyResponses($qid, $avg_responses, $categories[$j]['category_id'], "", "", $journal_name) . "</tr>";
                            }

                            // otherwise pass the recordset for all other responses 

                            elseif ($qid["question_type"] != 1) {

                                switch($role_id) {

                                    // Authors

                                    case '1':

                                        if (($stage_counter == 1 && ($qid["question_sub_stage_1"] == '1' || $qid["question_sub_stage_2"] == '1' || $qid["question_sub_stage_3"] == '1' || $qid["question_sub_stage_4"] == '1' || $qid["question_sub_stage_8"] == '1')) || ($stage_counter == 2 && ($qid["question_sub_stage_5"] == '1' || $qid["question_sub_stage_6"] == '1' || $qid["question_sub_stage_7"] == '1'))) {

                                            $struct_html = $struct_html . "<br /><b>" . stripslashes(trim(str_replace($q_source_replace, $q_target_replace, $question_parts_array[0]), "\"")) . "</b><br /><br />";

                                            $struct_html = $struct_html . getSurveyResponses($qid, $oth_responses, $categories[$j]['category_id'], $role_id, $stage_counter, $journal_name);
                                        }

                                    break;

                                    // Reviewers

                                    case '2':

                                        if (($stage_counter == 1 && ($qid["q_stage_1"] == '1')) || ($stage_counter == 2 && $qid["q_stage_2"] == '1')) {

                                            $struct_html = $struct_html . "<br /><b>" . stripslashes(trim(str_replace($q_source_replace, $q_target_replace, $question_parts_array[0]), "\"")) . "</b><br /><br />";

                                            $struct_html = $struct_html . getSurveyResponses($qid, $oth_responses, $categories[$j]['category_id'], $role_id, $stage_counter, $journal_name);
                                        }

                                    break;
                                }
                            }
                          } 

                            $previous_type_id = $current_type_id;
                        }
                    
                    // End of question ID loop
                    }

                    // if we reach the end of a role within a category where we've exclusively displayed average ratings (i.e. only Q type = 1) then close the datatables table before proceeding to the next category

                    if ($current_cat_id != $previous_cat_id && $cat_average_table_end_displayed == 0 && $cat_average_table_start_displayed == 1 && $stage_counter == 1) {

                        $struct_html = $struct_html . "</tbody></table></div>";
                    }

                if ($stage_title_displayed == 1) {

                    $struct_html = $struct_html . "</div></div></div>";
                }

                // End of stage loop
                }

                // End of role loop - close role DIVs

                $struct_html = $struct_html . "</div></div></div>";
            }
        }

    // Close category DIVs

    $previous_cat_id = $current_cat_id;
    $struct_html = $struct_html . "</div></div></div>";

    }

    return $struct_html;
}
?>
<script>
  tippy('#tooltip_no-data-overall', { content: 'There is currently no feedback for this journal.<br /><br />Once a minimum of 5 pieces of feedback has been given by authors or reviewers for this category, an average rating will appear.', allowHTML: true});

  tippy('#tooltip_not-enough-data-overall', { content: 'There is not yet enough data available to calculate an average (mean) rating for this journal\'s category.<br /><br />Once a minimum of 5 pieces of feedback (and at least 5 response ratings) has been completed by authors or reviewers for this category, an average rating will appear.', allowHTML: true});

  tippy('#tooltip_no-data-cat', { content: 'There are currently no question responses for this category.<br /><br />Once a minimum of 5 responses has been given by authors or reviewers for this category, an average rating will appear.', allowHTML: true});

  tippy('#tooltip_not-enough-data-cat', { content: 'There are currently not enough question responses for this category.<br /><br />Once a minimum of 5 responses has been completed by authors or reviewers for this category, an average rating will appear.', allowHTML: true});

  tippy('#tooltip_no-data-item', { content: 'There are currently no responses for this question.<br /><br />Once a minimum of 5 responses has been given by authors or reviewers for this question, an average rating or summary of responses will appear.', allowHTML: true});

  tippy('#tooltip_not-enough-data-item', { content: 'There are currently not enough responses for this question<br /><br />Once a minimum of 5 responses has been completed by authors or reviewers for this question, an average rating or summary of responses will appear.', allowHTML: true});
</script>

<?php
include '../assets/layouts/footer.php';

// End clock time in seconds
$end_time = microtime(true);
  
// Calculate script execution time
$execution_time = ($end_time - $start_time);

echo "<!-- Execution time:".$execution_time." sec // -->";
?>
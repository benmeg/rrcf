<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('TITLE', "Questions");
include '../assets/layouts/header.php';
check_verified();

require 'functions.php';

?>
    <!-- SurveyJS -->
    <script type="text/javascript" src="../assets/js/survey.jquery.min.js"></script>
    <script type="text/javascript" src="../assets/js/jquery.barrating.js"></script>

    <link rel="stylesheet" type="text/css" href="../assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/fontawesome-stars.css">

    <!-- Showdown -->
    <script type="text/javascript" src="../assets/js/showdown.min.js"></script>

    <!-- Themes -->
    <script type="text/javascript" src="../assets/js/surveyjs-widgets.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/survey.min.css" />

    <link rel="stylesheet" type="text/css" href="style.css" /> 

    <style>

		h2.confirm-text {
			color: red;
		}

		.sv_header {
			top: 0; 
			z-index: 999;
			background-color: white;
			width: 95%;
		}

		.sv_header__text {
			width: 778px;
		}

		/* Header text size */
		.sv_main .sv_container .sv_header h3 {
			font-size: 1.4em;
		}

		.sv_body {
			border-top-width: 0px;
		}

		/* Hide top border of main questionnaire body element */
		.sv_main .sv_body {
			border-top-width: 0px;
		}

		/* Category header */
		.sv_progress-buttons__container-center {
			top: 70px;
			z-index: 999;
			background-color: white;
			border-bottom: 2px solid #1ab394;
		}

		/* body {
			padding-top: 80px;
		} */

		.sv_container {
			background-color: white;
			/* padding-top: 80px; */
		}

		
		/* make the first column width sufficiently wide so that text does not line wrap */

		.sv_q_matrix tr td:first-child {
			width: 13em;
		} 


		/* narrower screens */

		@media
			(max-width: 767px) {

		    	.sv_body {
			    	width:95%;
			    }

			    /* Left align matrix questions on mobile view - otherwise they are centered by default and don't look good! */

			    .sv_main .sv_container .sv_body .sv_p_root table.sv_q_matrix td, .sv_main .sv_container .sv_body .sv_p_root table.sv_q_matrix th {
			    	text-align:  left !important;
			    }
		}

		/* make matrix row titles bold if in mobile mode, for better clarity/distinction between row title and first response option */

		@media
			(max-width: 601px) {

			    .sv_main .sv_container .sv_body .sv_p_root table.sv_q_matrix td:first-child {
			    	font-weight: bold;
			    }
		}

		
		/* wider screens */

		 @media
			(min-width: 768px) {

		    	.sv_body {
			    	width: 778px; 
			    	max-width: 95%;
			    }

			    /* override column widths for matrix SurveyJS component to fix issue
			       where column widths are hardcoded as: min-width: 10em in survey.min.css
			       which means that if .sv_body is set to a relatively narrow width (e.g 778)
			       the container will horizontally scroll, hiding columns which are too far
			       to the right. This not only looks poor and will likely effect user interaction,
			       but on some browsers (e.g. Safari) the horizontal scroll bar is not visible */

			    .sv_main .sv_container .sv_body .sv_p_root table.sv_q_matrix td {

			    	min-width: 3rem !important;
			    	padding: 0 1em !important;
			    }
		}

		.sv_main .sv_container .sv_body .sv_p_root table.sv_q_matrix tr:nth-child(odd) {
			color: #000;
			background: #FFF;
		}

		.sv_main .sv_container .sv_body .sv_p_root table.sv_q_matrix tr:nth-child(even) {
			color: #000;
			background: #DDD;
		}

    </style>

  	<?php

if (isset($_GET['submit'])) { 

parse_str($_SERVER['QUERY_STRING'], $params);

	// possible variables passed by querystring:

	// reviewer_role - 1,2
	// journal       	 - integer (corresponds to `journals` table)
	// uuid				 - blank or UUID string in format: 8-4-4-4-12

	// stage         	 - 1,2,3

	// stage1        	 - 1,2,3,4
	// stage2        	 - 5,6,7

	// stage1_both   	 - 3,4
	// stage2_both       - 5,6,7

	// stage1_role1_orphan_id - blank or review id (integer)
	// stage1_role2_orphan_id - blank or review id (integer)
	// stage2_role1_orphan_id - blank or review id (integer)
	// stage2_role2_orphan_id - blank or review id (integer)

	// doi           	 - free text (0-500 characters)

	// s1year_choice 	 - 1,2,3,4
	// s1singleyear  	 - 2012 - current year
	// s1startyear   	 - 2012 - current year
	// s1endyear     	 - 2012 - current year
    // s1share_year_data - blank or 1

    // s2year_choice 	 - 1,2,3,4
    // s2singleyear   	 - 2013 - current year
	// s2startyear   	 - 2013 - current year
	// s2endyear     	 - 2013 - current year
	// s2share_year_data - blank or 1

	// s1_academic_radio - 1,2,3,4
	// s1_academic_role  - 1-16
	// s1_academic_role_text - free text (max 255)

	// s2_academic_radio - 1,2,3,4
	// s2_academic_role  - 1-16
	// s2_academic_role_text - free text (max 255)

	// ref_code      - free text (15-254 characters)



	// define variables

	$user_id 		 	= "";
	$role_id 		 	= $params['reviewer_role'];
	$role_name       	= "";
	$doi              	= "";
	$ref_code        	= $params['ref_code'];
	$journal_id    	  	= $params['journal'];
 
	$stage_id 		 	= $params['stage'];
	$stage_ids 		 	= array();
	$sub_stage_ids  	= array();
	$orphan_id 			= "";
	$decision_letter    = "";
	$questions_array 	= array();
	$question_json   	= "";
	$question_script 	= "";
	$survey_invoke   	= "";
	$start_time_obj		= new DateTime('now');
	$start_time_str     = $start_time_obj->format('Y-m-d H:i:s');

	if (isset($params['uuid'])) {

		$uuid = $params['uuid'];
	}

	else

	{
		$uuid = "";	
	}

	if(isset($params['stage1_both'])) {

		$stage1_sub = $params['stage1_both'];
	}
	else
	{
		$stage1_sub = "";
	}

	if(isset($params['stage2_both'])) {

		$stage2_sub = $params['stage2_both'];
	}
	else
	{
		$stage2_sub = "";
	}


	// Get year of stage(s) and user's sharing selection
	switch ($stage_id) {

		case "1":

			switch ($params['s1year_choice']) {

				// Single year
				case "1":

					$startdate = $params['s1singleyear'];
					$enddate   = $params['s1singleyear'];

				break;


				// Range of years - from 20XX to 20XX
				case "2":

					$startdate = $params['s1startyear'];
					$enddate   = $params['s1endyear'];

				break;


				// Prefer not to answer
				case "3":

					$startdate = "999";
					$enddate   = "999";

				break;


				// Don't know / don't recall
				case "4":

					$startdate = "777";
					$enddate   = "777";

				break;
			}

			// Has the user chosen to share the year data?
			if(isset($params['s1share_year_data'])) {

				$share_year_data = '1';

			}

			else

			{
				$share_year_data = '0';
			}

		break;


		case "2":

			switch ($params['s2year_choice']) {

				// Single year
				case "1":

					$startdate = $params['s2singleyear'];
					$enddate   = $params['s2singleyear'];

				break;


				// Range of years - from 20XX to 20XX
				case "2":

					$startdate = $params['s2startyear'];
					$enddate   = $params['s2endyear'];

				break;


				// Prefer not to answer
				case "3":

					$startdate = "999";
					$enddate   = "999";

				break;


				// Don't know / don't recall
				case "4":

					$startdate = "777";
					$enddate   = "777";

				break;
			}

			// Has the user chosen to share the year data?
			if(isset($params['s2share_year_data'])) {

				$share_year_data = '1';

			}

			else

			{
				$share_year_data = '0';
			}

		break;


		case "3":

	    switch ($params['s1year_choice']) {

	        // Single year
	        case "1":

	            $s1startdate = $params['s1singleyear'];
	            $s1enddate   = $params['s1singleyear'];

	        break;


	        // Range of years - from 20XX to 20XX
	        case "2":

	            $s1startdate = $params['s1startyear'];
	            $s1enddate   = $params['s1endyear'];

	        break;


	        // Prefer not to answer
	        case "3":

	            $s1startdate = "999";
	            $s1enddate   = "999";

	        break;


	        // Don't know / don't recall
	        case "4":

	            $s1startdate = "777";
	            $s1enddate   = "777";

	        break;
	    }

	    // Has the user chosen to share the Stage 1 year data?
			if(isset($params['s1share_year_data'])) {

				$s1share_year_data = '1';

			}

			else

			{
				$s1share_year_data = '0';
			}

	    switch ($params['s2year_choice']) {

	        // Single year
	        case "1":

	            $s2startdate = $params['s2singleyear'];
	            $s2enddate   = $params['s2singleyear'];

	        break;


	        // Range of years - from 20XX to 20XX
	        case "2":

	            $s2startdate = $params['s2startyear'];
	            $s2enddate   = $params['s2endyear'];

	        break;


	        // Prefer not to answer
	        case "3":

	            $s2startdate = "999";
	            $s2enddate   = "999";

	        break;


	        // Don't know / don't recall
	        case "4":

	            $s2startdate = "777";
	            $s2enddate   = "777";

	        break;
	    }

	    // Has the user chosen to share the Stage 2 year data?
			if(isset($params['s2share_year_data'])) {

				$s2share_year_data = '1';

			}

			else

			{
				$s2share_year_data = '0';
			}

		break;
	}


	// Get academic role of stage(s)

	switch ($stage_id) {

		case "1":

			switch ($params['s1_academic_radio']) {

				// Selection from dropdown (including 'Other')
				case "1":

					$academic_role = $params['s1_academic_role'];
					
					// If user has chosen 'Other', record their free text entry

					if ($academic_role == 16) {

						$academic_role_text = $params['s1_academic_role_text'];
					}

				break;


				// Prefer not to answer
				case "2":

					$academic_role = "999";

				break;


				// Don't know / don't recall
				case "3":

					$academic_role = "777";

				break;
			}

		break;


		case "2":

			switch ($params['s2_academic_radio']) {

				// Selection from dropdown (including 'Other')
				case "1":

					$academic_role = $params['s2_academic_role'];
					
					// If user has chosen 'Other', record their free text entry

					if ($academic_role == 16) {

						$academic_role_text = $params['s2_academic_role_text'];
					}

				break;


				// Prefer not to answer
				case "2":

					$academic_role = "999";

				break;


				// Don't know / don't recall
				case "3":

					$academic_role = "777";

				break;
			}

		break;


		case "3":

		// Stage 1

			switch ($params['s1_academic_radio']) {

				// Selection from dropdown (including 'Other')
				case "1":

					$academic_role1 = $params['s1_academic_role'];
					
					// If user has chosen 'Other', record their free text entry

					if ($academic_role1 == 16) {

						$academic_role1_text = $params['s1_academic_role_text'];
					}

				break;


				// Prefer not to answer
				case "2":

					$academic_role1 = "999";

				break;


				// Don't know / don't recall
				case "3":

					$academic_role1 = "777";

				break;
			}


		// Stage 2

			switch ($params['s2_academic_radio']) {

				// Selection from dropdown (including 'Other')
				case "1":

					$academic_role2 = $params['s2_academic_role'];
					
					// If user has chosen 'Other', record their free text entry

					if ($academic_role2 == 16) {

						$academic_role2_text = $params['s2_academic_role_text'];
					}

				break;


				// Prefer not to answer
				case "2":

					$academic_role2 = "999";

				break;


				// Don't know / don't recall
				case "3":

					$academic_role2 = "777";

				break;
			}
		break;
	}

	// Get orphan choice (if any)
	
	// N.B. Use opposite stage to one user has chosen
	// i.e. if user has chosen to complete Stage 2 feedback they can pick
	// orphan feedback from Stage 1 (and visa-versa)

	if ($stage_id == "1" && $role_id == "1") {

		if(isset($params['stage2_role1_orphan_id'])) {

			$orphan_id = $params['stage2_role1_orphan_id'];
		}
	}

	elseif ($stage_id == "1" && $role_id == "2") {

		if(isset($params['stage2_role2_orphan_id'])) {

			$orphan_id = $params['stage2_role2_orphan_id'];
		}
	}

	elseif ($stage_id == "2" && $role_id == "1") {

		if(isset($params['stage1_role1_orphan_id'])) {

			$orphan_id = $params['stage1_role1_orphan_id'];
		}
	}

	elseif ($stage_id == "2" && $role_id == "2") {

		if(isset($params['stage1_role2_orphan_id'])) {

			$orphan_id = $params['stage1_role2_orphan_id'];
		}
	}

	
	if(isset($params['decision_letter'])) {

		$decision_letter = $params['decision_letter'];
	}


	if(isset($params['doi'])) {

		$doi = $params['doi'];
	}


	Switch ($role_id) {

		Case "1":

			$role_name = "Author";

		break;

		Case "2":

			$role_name = "Reviewer";

		break;
	}

	// Build SQL statement to retrieve question set for user selection

	// First check if we have a valid Role ID and Stage ID

	Switch ($stage_id) {

		// Stage 1

		Case "1":

			$stage_ids[] = "1";

			if ($role_id == "1") {

				if(isset($params['stage1'])) {

					$sub_stage_ids[] = $params['stage1'];
				}	
			}

		break;

		// Stage 2

		Case "2";

			$stage_ids[] = "2";

			if ($role_id == "1") {

				if(isset($params['stage2'])) {

					$sub_stage_ids[] = $params['stage2'];
				}
			}

		break;

		// Stage 1 and Stage 2

		Case "3";

			array_push($stage_ids, "1", "2");

			// For authors

			if ($role_id == "1") {

				array_push($sub_stage_ids, $stage1_sub, $stage2_sub);
			}

		break;
	}


	$db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

	/* check connection */
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

	/* change character set to utf8mb4 */
	if (!mysqli_set_charset($db_handle, "utf8mb4")) {
	     //printf("Error loading character set utf8mb4: %s\n", mysqli_error($db_handle));
	     exit();

	}

	else

	{
	     //printf("Current character set: %s\n", mysqli_character_set_name($db_handle));
	}

	
	// Get journal name from ID

	$sql = "SELECT journal_name FROM `journals` WHERE `journal_id` = '" . $journal_id . "';";

	if ($result = mysqli_query($db_handle, $sql)) {

		if (mysqli_num_rows($result) > 0) {

			$journal_array = mysqli_fetch_row($result);
			$journal_name = $journal_array[0];
		}
	}


	// Get question categories/IDs

	$sql = "SELECT `category_id`, `category_title`, `category_header_author`, `category_header_reviewer` FROM `feedback_question_categories` ORDER BY `category_id` ASC"; 

	if ($result = mysqli_query($db_handle, $sql)) {

			if (mysqli_num_rows($result) > 0) {

				$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
			}
		}


	// Loop through each stage selected and get all matching questions
	// for the sub stage(s) and role selected

	foreach ($stage_ids as $stage) {

		// Build SQL statement for current stage
		
		// Base query, without selectors

		$sql = "SELECT `question_id`, `question_text`, `question_options`, `question_type`, `question_helptext`, `question_tooltip`, `question_category`, `no_repeat`, `feedback_question_categories`.`category_title` FROM `feedback_questions` INNER JOIN `feedback_question_categories` ON `feedback_questions`.question_category = `feedback_question_categories`.`category_id` WHERE ";

		Switch ($role_id) {

			// Author
			Case "1":

				$sql = $sql . "question_role_type = 1 AND `question_sub_stage_";

				// If the user has chosen both stages, extract the corresponding
				// sub-stage ID for this stage from the $sub_stage_ids array

				if ($stage_id == "3") {

					$sql = $sql . $sub_stage_ids[$stage-1] . "` = 1 ";
				}
				else
				{
					$sql = $sql . $sub_stage_ids[0] . "` = 1 ";
				}

				break;

			// Reviewer
			Case "2":

				$sql = $sql . "question_role_type = 2 AND q_stage_" . $stage . " = 1";

			break;
		}

		// suffix to sort query

		$sql = $sql . " ORDER BY `question_category` ASC, `question_order` ASC";

		if ($result = mysqli_query($db_handle, $sql)) {

			if (mysqli_num_rows($result) > 0) {

				$result_array = mysqli_fetch_all($result, MYSQLI_ASSOC);
			}
		}

		$questions_array[$stage-1] = $result_array;
	}

	mysqli_close($db_handle);

	$stage_keys = array_keys($questions_array);

	// Loop through each stage's questions, to create a SurveyJS-compatible JSON object

	for ($i = 0; $i < count($questions_array); $i++) {

		$stage_number = $stage_keys[$i] + 1;

		// SurveyJS header
		
		$question_json = $question_json . 'var stage' . $stage_number . ' = new Survey.Model({';
		$question_json = $question_json . 'showQuestionNumbers: "off", ';
		$question_json = $question_json . 'clearInvisibleValues: "none", ';
		$question_json = $question_json . 'completedHtml: "Survey completed - saving...", ';

		if ($stage_id == 3) {

			switch($i) {

				Case "0":

					$question_json = $question_json . '"title": "{role_name} feedback (Stage <b>1</b> of 2) at <i>{journal_name}</i> - your REF: {ref_code}", ';

				break;

				Case "1":

					$question_json = $question_json . '"title": "{role_name} feedback (Stage <b>2</b> of 2) at <i>{journal_name}</i> - your REF: {ref_code}", ';

				break;
			}
		}

		else

		{
			$question_json = $question_json . '"title": "{role_name} feedback (Stage {stage_number}) at <i>{journal_name}</i> - your REF: {ref_code}", ';
		}
		$question_json = $question_json . '"showProgressBar": "top", ';
		$question_json = $question_json . '"progressBarType": "buttons", "pages": [ ';

		// Step through each category (i.e. Speed, Quality)
		// so we can split each category's questions by SurveyJS page

		for ($j = 0; $j < count($categories); $j++) {

			// Add a header for this page/category

			$cat_title_displayed = 0;

			if ($cat_title_displayed == 0) {

				$cat_title_displayed = 1;
				$question_json = $question_json . '{ "navigationTitle": "' . $categories[$j]['category_title'] . '", ';
				$question_json = $question_json . '"navigationDescription": "Stage ' . $stage_number . '", ';

				// Add any category header, for the role selected

				Switch($role_id) {

					Case '1':

						if (isset($categories[$j]['category_header_author'])) {

							$question_json = $question_json . '"title": "' . $categories[$j]['category_header_author'] . '", ';
						}

					break;


					Case '2':

						if (isset($categories[$j]['category_header_reviewer'])) {

							$question_json = $question_json . '"title": "' . $categories[$j]['category_header_reviewer'] . '", ';
						}

					break;
				}


				$question_json = $question_json . '"questions": [ ';

				// If the user has chosen to answer both Stage 1 and 2 questions,
				// put a confirmation message after they have finished Stage 1
				// to make it obvious they are no longer answering Stage 1 questions

				if ($j == 0 && $stage_id == "3" && $stage_number == 2) {

					$question_json = $question_json . '{"type": "html", ';
					$question_json = $question_json . '"name": "stage2confirm", ';
					$question_json = $question_json . '"html": "<h2 class=\"confirm-text\">Stage 1 feedback complete</h2><h3>Now please answer the following questions, concerning your Stage 2 ' . strtolower($role_name) . ' experience at <i>{journal_name}</i></h3><h4>N.B. Your Stage 1 feedback will not be saved until you also complete your Stage 2 feedback.</h4>"}, ';
				}
			}

			// Loop through each question in this category

			foreach($questions_array[$stage_keys[$i]] as $stage_items) {

				// For multi-stage (Stage 1 & 2) questionnaires, check that we don't repeat any question
				// in Stage 2 if that question's `feedback_questons`.`no_repeat` == 1

				if ($stage_items['no_repeat'] == '1' && $stage_id == '3' && $stage_number == 2) {

					// Deliberately empty - don't show any questions matching above criteria
				}

				// otherwise show every question as usual

				else

				{

					if ($categories[$j]['category_id'] == $stage_items['question_category']) {

						// For each question, generate appropriate JSON, according to stage/question type/question category

						$question_json = $question_json . getSurveyQuestionLayout($stage_number, $stage_items, $categories[$j]['category_id']) . ', ';
					}
				}
			}

			$question_json = rtrim($question_json, ', ');
			$question_json = $question_json . ']}, ';
		}

		$question_json = rtrim($question_json, ', ');

		// End of this stage's json

		$question_json = $question_json . ']}); ';
	}


	// Create javascript to invoke SurveyJS

	// For a single stage, output just one set of handler code

	if ($stage_id == 1 || $stage_id == 2) {

		$question_script = $question_script . 'stage' . $stage_number . '.onComplete.add(function (result, options) { ';
		$question_script = $question_script . 'result.setValue("stage' . $stage_number . '_end_time", new Date()); ';
		
		$question_script = $question_script . 'setFormSubmitting(); ';
		$question_script = $question_script . 'options.showDataSaving(); ';
	    $question_script = $question_script . 'var xhr = new XMLHttpRequest(); ';
	    $question_script = $question_script . 'xhr.open("POST", "process_survey.php"); ';
	    $question_script = $question_script . 'xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8"); ';
	    $question_script = $question_script . 'xhr.onload = xhr.onerror = function () { ';
	    $question_script = $question_script . 'if (xhr.status == 200 && xhr.responseText == "success") { ';
	    $question_script = $question_script . 'document.location = "../feedback/?survey_success=1"; ';
	    $question_script = $question_script . 'options.showDataSavingSuccess(); ';
	    $question_script = $question_script . ' } else { ';
	    $question_script = $question_script . 'options.showDataSavingError("Sorry, something went wrong - please click the \'Try again\' button - if this problem persists please contact us, quoting code: ' . $_SESSION['id'] . '"); }};';
	    $question_script = $question_script . 'xhr.send(JSON.stringify(result.data)); ';
	    $question_script = $question_script . '}); ';

    
	    // Pass user selections into questionnaire data

	    $question_script = $question_script . 'stage' . $stage_number . '.data = { ';
	    $question_script = $question_script . 'ref_code: "' . $ref_code . '", ';
	    $question_script = $question_script . 'stage_' . $stage_id . '_start_date: "' . $startdate . '", ';
	    $question_script = $question_script . 'stage_' . $stage_id . '_end_date: "' . $enddate . '", ';
	    $question_script = $question_script . 'stage_' . $stage_id . '_share_year_data: "' . $share_year_data . '", ';
	    $question_script = $question_script . 'stage_' . $stage_id . '_academic_role: "' . $academic_role . '", ';

	    if (isset($academic_role_text)) {

	    	$question_script = $question_script . 'stage_' . $stage_id . '_academic_role_text: "' . $academic_role_text . '", ';
	    }
    
	    if($orphan_id != "") {

	    	$question_script = $question_script . 'orphan_id: "' . $orphan_id . '", ';
	    }

	    if($decision_letter != "") {

	    	$question_script = $question_script . 'decision_letter: "1", ';
	    }

	    if($doi != "") {

	    	$question_script = $question_script . 'doi: "' . $doi . '", ';
	    }

	    if($uuid != "") {

	    	$question_script = $question_script . 'uuid: "' . $uuid . '", ';
	    }

	    $question_script = $question_script . 'journal_id: "' . $journal_id . '", ';

	    $question_script = $question_script . 'journal_name: "' . $journal_name . '", ';
		$question_script = $question_script . 'stage_id: "' . $stage_id . '", ';
	    $question_script = $question_script . 'stage_number: "' . $stage_number . '", ';
	    
	    if ($role_id == "1") {

	    	$question_script = $question_script . 'stage_choices: "' . implode(',', $sub_stage_ids) . '", ';
	    }
	    
	    $question_script = $question_script . 'role_id: "' . $role_id . '", ';
	    $question_script = $question_script . 'role_name: "' . $role_name . '"}; ';

	    // Invoke questionnaire object

		$survey_invoke   = '$("#surveyElement").Survey({model: stage' . $stage_number . '}); ';
		$survey_invoke = $survey_invoke . 'stage' . $stage_number . '.setValue("stage' . $stage_number . '_start_time", new Date()); ';
	}


	// For Stage 1 and Stage 2 questionnaires, link pages and pass data
	// from Stage 1 section to Stage 2 section when Stage 1 in completed

	if ($stage_id == 3) {

		$survey_invoke   = '$("#surveyElement").Survey({model: stage1}); ';
		$survey_invoke = $survey_invoke . 'stage1.setValue("stage1_start_time", new Date()); ';
		$question_script = $question_script . 'stage1';
	    $question_script = $question_script . '.onComplete';
	    $question_script = $question_script . '.add(function (result) {';
	    $question_script = $question_script . 'result.setValue("stage1_end_time", new Date()); ';
	    $question_script = $question_script . 'result.setValue("stage_number", "2"); ';

	    // add stage delimiter so we can more easily split the resulting JSON into
	    // Stage 1 and Stage 2 sections for inserting into the database (feedback_reviews)
	    // which records feedback per-stage (due to users being able to give just Stage 1
		  // or Stage 2 feedback at one time)

	    $question_script = $question_script . 'result.setValue("stage_delimiter", "true"); ';
	    $question_script = $question_script . 'stage2.data = result.data; ';
	    $question_script = $question_script . '$("#surveyElement").Survey({model: stage2}); ';
	    $question_script = $question_script . 'stage2.setValue("stage2_start_time", new Date()); ';
	    $question_script = $question_script . '}); ';

		$question_script = $question_script . 'stage2.onComplete.add(function (result, options) { ';
		$question_script = $question_script . 'result.setValue("stage2_end_time", new Date()); ';
		$question_script = $question_script . 'setFormSubmitting(); ';
		$question_script = $question_script . 'options.showDataSaving(); ';
	    $question_script = $question_script . 'var xhr = new XMLHttpRequest(); ';
	    $question_script = $question_script . 'xhr.open("POST", "process_survey.php"); ';
	    $question_script = $question_script . 'xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8"); ';
	    $question_script = $question_script . 'xhr.onload = xhr.onerror = function () { ';
	    $question_script = $question_script . 'if (xhr.status == 200 && xhr.responseText == "success") { ';
	    $question_script = $question_script . 'document.location = "../feedback/?survey_success=1"; ';
	    $question_script = $question_script . 'options.showDataSavingSuccess(); ';
	    $question_script = $question_script . ' } else { ';
	    $question_script = $question_script . 'options.showDataSavingError("Sorry, something went wrong - please click the \'Try again\' button - if this problem persists please contact us, quoting code: ' . $_SESSION['id'] . '"); }};';
	    $question_script = $question_script . 'xhr.send(JSON.stringify(result.data)); ';
	    $question_script = $question_script . '}); ';

	  
		// Pass user selections into questionnaire data

	    $question_script = $question_script . 'stage1.data = { ';
	    $question_script = $question_script . 'ref_code: "' . $ref_code . '", ';

	    $question_script = $question_script . 'stage_1_start_date: "' . $s1startdate . '", ';
	    $question_script = $question_script . 'stage_1_end_date: "' . $s1enddate . '", ';

	    $question_script = $question_script . 'stage_2_start_date: "' . $s2startdate . '", ';
	    $question_script = $question_script . 'stage_2_end_date: "' . $s2enddate . '", ';

	    $question_script = $question_script . 'stage_1_share_year_data: "' . $s1share_year_data . '", ';
	    $question_script = $question_script . 'stage_2_share_year_data: "' . $s2share_year_data . '", ';


		$question_script = $question_script . 'stage_1_academic_role: "' . $academic_role1 . '", ';
		$question_script = $question_script . 'stage_2_academic_role: "' . $academic_role2 . '", ';

	    if (isset($academic_role1_text)) {

	    	$question_script = $question_script . 'stage_1_academic_role_text: "' . $academic_role1_text . '", ';
	    }

	    if (isset($academic_role2_text)) {

	    	$question_script = $question_script . 'stage_2_academic_role_text: "' . $academic_role2_text . '", ';
	    }
		
		if($doi != "") {

	    	$question_script = $question_script . 'doi: "' . $doi . '", ';
	  	}

	    if($uuid != "") {

	    	$question_script = $question_script . 'uuid: "' . $uuid . '", ';
	    }

	    if($decision_letter != "") {

	    	$question_script = $question_script . 'decision_letter: "1", ';
	    }

	    $question_script = $question_script . 'journal_id: "' . $journal_id . '", ';

	    $question_script = $question_script . 'journal_name: "' . $journal_name . '", ';
	    $question_script = $question_script . 'stage_id: "' . $stage_id . '", ';
		$question_script = $question_script . 'stage_number: "1", ';
		
		// For the author role, add sub-stage choices

		if ($role_id == "1") {

			$question_script = $question_script . 'stage_choices: "' . implode(',', $sub_stage_ids) . '", ';
		}

	    $question_script = $question_script . 'role_id: "' . $role_id . '", ';
	    $question_script = $question_script . 'role_name: "' . $role_name . '"}; ';
	}

?>
<div class="headerShield"></div>
<div id="surveyElement" style="display:inline-block; width:100%"></div>
        <div id="surveyResult"></div>
<script type="text/javascript" src="../assets/js/popper.min.js"></script>
<script type="text/javascript" src="../assets/js/tippy-bundle.umd.min.js"></script>
<script>
	
	Survey
	    .StylesManager
	    .applyTheme("default");

	Survey
    	.JsonObject
    	.metaData
    	.addProperty("questionbase", "tooltip");

    Survey
    	.JsonObject
    	.metaData
    	.addProperty("questionbase", "customstarrating");

<?php echo $question_json; ?>


<?php echo $question_script;

// Insert TippyJS tooltips

foreach ($stage_ids as $stage) { ?>


stage<?php echo $stage?>

    .onAfterRenderQuestion
    .add(function (stage<?php echo $stage?>, options) {

        // Return if there is no description to show in popup

        if (!options.question.tooltip) 
            return;
        
        q_name = options.question.name;
        
        var header = options
            .htmlElement
            .querySelector("h5");
        
        var span = document.createElement("span");
        span.innerText = "?";
        span.className = "survey-tooltip";
        
        spanid = q_name + "_tooltip";
        span.id = spanid;

        tooltiptexttmp = options.question.tooltip;
        tooltiptext = tooltiptexttmp.replace(/{journal_name}/g, stage<?php echo $stage?>.getValue("journal_name"));
        tooltiptext = tooltiptexttmp.replace(/{stage_number}/g, '<?php echo $stage?>');

        header.appendChild(span);

        tippy('#' + spanid, {content: tooltiptext, allowHTML: true});
});

// Insert custom star ratings

stage<?php echo $stage?>

    .onAfterRenderQuestion
    .add(function (stage<?php echo $stage?>, options) {

        // Return if this question isn't a star rating

        if (!options.question.customstarrating) 
            return;
        
        q_name = options.question.name;
        
        // define where to inject radio buttons for star rating questions
        
        var clearer = options
            .htmlElement
            .querySelector("div.br-wrapper");
        
        // Define and create 'Prefer not to answer' option
        
        var star_radio1input = document.createElement("input");
            star_radio1input.setAttribute("type", "radio");
            star_radio1input.setAttribute("name", q_name);
            star_radio1input.setAttribute("value", "999");

            star_radio1inputid = options.question.name + '_pnta';

            star_radio1input.setAttribute("id", star_radio1inputid);
            
            star_radio1inputonClick = "stage<?php echo $stage?>.clearValue('" + q_name + "'); stage<?php echo $stage?>.render(); radiobtn = document.getElementById('" + star_radio1inputid + "'); radiobtn.checked = true; stage<?php echo $stage?>.setValue('" + q_name + "', '999');";

            star_radio1input.setAttribute("onClick", value=star_radio1inputonClick);

        var star_radio1label = document.createElement("label");
            star_radio1label.setAttribute("for", star_radio1inputid);
            star_radio1label.innerHTML = 'Prefer not to answer';

        clearer.appendChild(star_radio1input);
        clearer.appendChild(star_radio1label);

        clearer.appendChild(document.createElement("br"));

        // Define and create 'Don't know / don't recall' option

        var star_radio2input = document.createElement("input");
            star_radio2input.setAttribute("type", "radio");
            star_radio2input.setAttribute("name", q_name);
            star_radio2input.setAttribute("value", "777");

            star_radio2inputid = options.question.name + '_dkdr';

            star_radio2input.setAttribute("id", star_radio2inputid);

            star_radio2inputonClick = "stage<?php echo $stage?>.clearValue('" + q_name + "'); stage<?php echo $stage?>.render(); radiobtn = document.getElementById('" + star_radio2inputid + "'); radiobtn.checked = true; stage<?php echo $stage?>.setValue('" + q_name + "', '777');";

            star_radio2input.setAttribute("onClick", value=star_radio2inputonClick);

        var star_radio2label = document.createElement("label");
            star_radio2label.setAttribute("for", star_radio2inputid);
            star_radio2label.innerHTML = 'Don\'t know \/ don\'t recall';
        
        clearer.appendChild(star_radio2input);
        clearer.appendChild(star_radio2label);

        clearer.appendChild(document.createElement("br"));

        // Define and create 'N/A' option

        var star_radio3input = document.createElement("input");
            star_radio3input.setAttribute("type", "radio");
            star_radio3input.setAttribute("name", q_name);
            star_radio3input.setAttribute("value", "888");

            star_radio3inputid = options.question.name + '_na';

            star_radio3input.setAttribute("id", star_radio3inputid);

            star_radio3inputonClick = "stage<?php echo $stage?>.clearValue('" + q_name + "'); stage<?php echo $stage?>.render(); radiobtn = document.getElementById('" + star_radio3inputid + "'); radiobtn.checked = true; stage<?php echo $stage?>.setValue('" + q_name + "', '888');";

            star_radio3input.setAttribute("onClick", value=star_radio3inputonClick);

        var star_radio3label = document.createElement("label");
            star_radio3label.setAttribute("for", star_radio3inputid);
            star_radio3label.innerHTML = 'N/A';
        
        clearer.appendChild(star_radio3input);
        clearer.appendChild(star_radio3label);
});


<?php

}

echo $survey_invoke;

foreach ($stage_ids as $stage) { ?>


stage<?php echo $stage?>

    .onValueChanged
    .add(function(stage<?php echo $stage?>, options) {

        if (options.value == "1" || options.value == "2" || options.value == "3" || options.value == "4" || options.value == "5") {
            
            radiobtn = document.getElementById(options.name + '_na');
            
            if (radiobtn != null) {
            	radiobtn.checked = false;
            }

            radiobtn = document.getElementById(options.name + '_pnta');

            if (radiobtn != null) {
            	radiobtn.checked = false;
            }

            radiobtn = document.getElementById(options.name + '_dkdr');

            if (radiobtn != null) {
            	radiobtn.checked = false;
            }
        }
});

stage<?php echo $stage?>

    .onAfterRenderPage
    .add(function(stage<?php echo $stage?>, options) {

    var arrayAllQuestions = stage<?php echo $stage?>.getAllQuestions();

    for (var i = 0; i < arrayAllQuestions.length; i++) {

        var qItemObj = arrayAllQuestions[i];

        if (qItemObj['customWidgetValue'] != null ) {
                    
	        if ( stage<?php echo $stage?>.getValue(qItemObj['name']) == "777" ) {

	            radiobtn = document.getElementById(qItemObj['name'] + "_dkdr");

	            if (radiobtn != null) {

	            	radiobtn.checked = true;
	            }
	        }

	        if ( stage<?php echo $stage?>.getValue(qItemObj['name']) == "888" ) {

	            radiobtn = document.getElementById(qItemObj['name'] + "_na");

	            if (radiobtn != null) {

	            	radiobtn.checked = true;
	            }
	        }

	        if ( stage<?php echo $stage?>.getValue(qItemObj['name']) == "999" ) {

	            radiobtn = document.getElementById(qItemObj['name'] + "_pnta");
	            
	            if (radiobtn != null) {

	            	radiobtn.checked = true;	
	            }	            
	        }
        }
    }
});

<?php } ?>

// Create showdown markdown converters (allows HTML-like styling of questions)

var converter = new showdown.Converter();

<?php

// Output converters for each stage chosen by user

foreach ($stage_ids as $stage) {
?>

stage<?php echo $stage?>

    .onTextMarkdown
    .add(function (stage<?php echo $stage?>, options) {
        //convert the markdown text to html
        var str = converter.makeHtml(options.text);
        //remove root paragraphs <p></p>
        str = str.substring(3);
        str = str.substring(0, str.length - 4);
        //set html
        options.html = str;
});

<?php
}

foreach ($stage_ids as $stage) {
?>

stage<?php echo $stage?>

    .onCurrentPageChanged
    .add(function (stage<?php echo $stage?>, options) {

    $('html, body').animate({ scrollTop: 0 }, 'fast');
});
<?php
} ?>
</script>
<?php
}
?>
<script type="text/javascript">
/* https://stackoverflow.com/a/7317311 */

var formSubmitting = false;

function setFormSubmitting() {

	formSubmitting = true;
};

  $(window).on('beforeunload', function(){

  	/* console.log('in beforeunload function'); */

if (formSubmitting) {

	/* console.log('submitting form'); */

	return undefined;
}

/* https://www.codespeedy.com/alert-before-leaving-page-javascript/ */

var c=confirm();

if(c){
  return true;
}
else
return false;
}
);


</script>
<?php
include '../assets/layouts/footer.php'
?>
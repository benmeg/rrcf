<?php

// N.B. UUID_TO_BIN and BIN_TO_UUID are needed (MySQL 8+)
// For support on lower versions, add these custom functions:

// https://remarkablemark.org/blog/2020/05/21/mysql-uuid-bin/

// BIN_TO_UUID

// DELIMITER //

// CREATE FUNCTION BIN_TO_UUID(bin BINARY(16))
// RETURNS CHAR(36) DETERMINISTIC
// BEGIN
//   DECLARE hex CHAR(32);
//   SET hex = HEX(bin);
//   RETURN LOWER(CONCAT(LEFT(hex, 8), '-', MID(hex, 9, 4), '-', MID(hex, 13, 4), '-', MID(hex, 17, 4), '-', RIGHT(hex, 12)));
// END; //

// DELIMITER ;


// UUID_TO_BIN

// DELIMITER //

// CREATE FUNCTION UUID_TO_BIN(uuid CHAR(36))
// RETURNS BINARY(16) DETERMINISTIC
// BEGIN
//   RETURN UNHEX(CONCAT(REPLACE(uuid, '-', '')));
// END; //

// DELIMITER ;


// Creates correct SurveyJS JSON markup for individual question item
// based on question type:

// Question types:

// 1: 5 star
// 2: dropdown
// 3: radio
// 4: radio conditional
// 5: radio conditional checkbox
// 6: textbox
// 7: matrix

function getSurveyQuestionLayout ($stage_number, &$question_array, $category_id) {

    $JSONed_question = "{";

    switch($question_array['question_type']) {

    	// 1: 5 star
    	// No input - 1 star = 1 -> 5 stars = 5

    	Case '1':

    		$JSONed_question = $JSONed_question . 'type: "barrating", ';

    		if ($question_array['question_tooltip'] != "") {

    			$JSONed_question = $JSONed_question . 'tooltip: "' . $question_array['question_tooltip'] . '", ';
    		}

    		$JSONed_question = $JSONed_question . 'customstarrating: "true", ';
    		$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '", ';
    		$JSONed_question = $JSONed_question . 'ratingTheme: "fontawesome-stars", ';
    		$JSONed_question = $JSONed_question . 'title: ' . $question_array['question_text'] . ', ';
            $JSONed_question = $JSONed_question . '"isRequired": true, ';

    		if ($question_array['question_helptext'] != "") {

    			$JSONed_question = $JSONed_question . 'description: ' . $question_array['question_helptext'] . ', ';
    		}
    		
    		// N.B. Needs empty first element (i.e. "" to fix bug on Mac devices (Safari and Chrome)
    		// Where 1 star appears highlighted upon load, but is actually not checked re:
    		// data (returns blank on submit)

    		$JSONed_question = $JSONed_question . 'choices: ["", "1", "2", "3", "4", "5"]';

    	break;


    	// 2: dropdown
    	// "1,2,3,4,5,6,7"
    	
    	Case '2':

    		$JSONed_question = $JSONed_question . 'type: "dropdown", ';

    		if ($question_array['question_tooltip'] != "") {

    			$JSONed_question = $JSONed_question . 'tooltip: "' . $question_array['question_tooltip'] . '", ';
    		}

    		$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '", ';
    		$JSONed_question = $JSONed_question . 'title: ' . $question_array['question_text'] . ', ';
            $JSONed_question = $JSONed_question . '"isRequired": true, ';

    		if ($question_array['question_helptext'] != "") {

    			$JSONed_question = $JSONed_question . 'description: ' . $question_array['question_helptext'] . ', ';
    		}

    		$JSONed_question = $JSONed_question . 'choices: [';

    		$temp = ltrim(rtrim($question_array['question_options'],'"'), '"');
    		$temp_array = explode(' ## ', $temp);

    		foreach($temp_array as $items) {

    			$temp_items = explode('::', $items);

    			$JSONed_question = $JSONed_question . '{value:' . $temp_items[0] . ', text:"' . $temp_items[1] . '"}, ';
    		}

 			// Remove final trailing delimiting comma, created from above foreach loop
    		$JSONed_question = rtrim($JSONed_question, ', ');

    		$JSONed_question = $JSONed_question . ']';

    	break;


    	// 3: radio

    	// "1::Yes: LESS likely to submit a RR to this journal ## 2::Yes: MORE likely submit a RR to this journal ## 3::No"

    	Case '3':

    		$JSONed_question = $JSONed_question . 'type: "radiogroup", ';

    		if ($question_array['question_tooltip'] != "") {

    			$JSONed_question = $JSONed_question . 'tooltip: "' . $question_array['question_tooltip'] . '", ';
    		}

    		$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '", ';
    		$JSONed_question = $JSONed_question . 'title: ' . $question_array['question_text'] . ', ';
            $JSONed_question = $JSONed_question . '"isRequired": true, ';

    		if ($question_array['question_helptext'] != "") {

    			$JSONed_question = $JSONed_question . 'description: ' . $question_array['question_helptext'] . ', ';
    		}

    		$JSONed_question = $JSONed_question . 'colCount: 1, ';
    		$JSONed_question = $JSONed_question . 'choices: [';

    		$temp = ltrim(rtrim($question_array['question_options'],'"'), '"');
    		$temp_array = explode(' ## ', $temp);
    		
    		foreach($temp_array as $items) {

    			$temp_items = explode('::', $items);

    			$JSONed_question = $JSONed_question . '{value:' . $temp_items[0] . ', text:"' . $temp_items[1] . '"}, ';
    		}

    		// Add Prefer not to answer / Don't know/don't recall / N/A to all radio choices

    		$JSONed_question = $JSONed_question . '{value: 999, text: "Prefer not to answer"}, ';
    		$JSONed_question = $JSONed_question . '{value: 777, text: "Don\'t know \/ don\'t recall"}, ';
    		$JSONed_question = $JSONed_question . '{value: 888, text: "N\/A"}]';

    	break;


        // 4: radio conditional
        //
        // single (i.e. if 'Yes' is chosen), show additional questions
        //
        // Q: "Have you ever reviewed a regular empirical article (other than a Registered Report) for this journal? @@ Please compare your experience reviewing a Stage 2 Registered Report to this journal with your previous experience(s) at the journal reviewing a regular empirical article. If you have previously reviewed multiple regular articles for the journal, please compare your Registered Report experience with your overall or “average” experience reviewing regular empirical articles at the journal."
        //
        // Options: "-2::Yes ## -1:No @@ -2 !! 1::Registered Report experience much worse ## 2::Registered Report experience slightly worse ## 3::Registered Report experience and regular article experience about the same ## 4::Registered Report experience slightly better ## 5::Registered Report experience much better"
        //
        //
        // Double (i.e. multiple initial responses, e.g. Yes/No lead to different secondary responses)
        //
        // Q: "Do you feel you received sufficient credit or other acknowledgment for your review?"
        //
        // Options: "-2::Yes ## -1::No @@ -2 !! 1::I received some form of credit or acknowledgement and it was sufficient ## 2::I received some form of credit or acknowledgement and it was insufficient ## 3::I received some form of credit or acknowledgement but I believe it was unnecessary or unwarranted ~~ -1 !! 4::I did not receive any credit or acknowledgement and I'm ok with that ## 5::I did not receive any credit or acknowledgement and I'm NOT ok with that"

    	Case '4':

    		$temp = ltrim(rtrim($question_array['question_text'],'"'), '"');
    		$question_parts_array = explode(' @@ ', $temp);

    		// Initial question

    		$JSONed_question = $JSONed_question . 'type: "radiogroup", ';

    		if ($question_array['question_tooltip'] != "") {

    			$JSONed_question = $JSONed_question . 'tooltip: "' . $question_array['question_tooltip'] . '", ';
    		}

    		$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '_main", ';
    		$JSONed_question = $JSONed_question . 'title: "' . $question_parts_array[0] . '", ';
            $JSONed_question = $JSONed_question . '"isRequired": true, ';

    		if ($question_array['question_helptext'] != "") {

    			$JSONed_question = $JSONed_question . 'description: ' . $question_array['question_helptext'] . ', ';
    		}

    		$JSONed_question = $JSONed_question . 'colCount: 1, ';
    		$JSONed_question = $JSONed_question . 'choices: [';

    		$temp = ltrim(rtrim($question_array['question_options'],'"'), '"');
    		$temp_array = explode(' @@ ', $temp);

    		$temp_main_q_array = explode(' ## ', $temp_array[0]);

    		foreach($temp_main_q_array as $items) {

    			$temp_items = explode('::', $items);
    			$JSONed_question = $JSONed_question . '{value:' . $temp_items[0] . ', text:"' . $temp_items[1] . '"}, ';

    		}

            $JSONed_question = rtrim($JSONed_question, ', ');
            $JSONed_question = $JSONed_question . ']}, ';

    		// Secondary question (dependent on initial question above).
            // May contain multiple elements, so we will loop through
            // group options delimited by ~~

            $answers_array = explode(' ~~ ', $temp_array[1]);

            for ($n = 0; $n < sizeof($answers_array); $n++) {

        		$JSONed_question = $JSONed_question . '{type: "radiogroup", ';
        		$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '_sub", ';
        		
                // if there's a sub question to display, show it as the sub question title
                // e.g. Please compare your experience...

                if (sizeof($question_parts_array) > 1 ) {

                    $JSONed_question = $JSONed_question . 'title: "' . $question_parts_array[1] . '", ';
                    $JSONed_question = $JSONed_question . '"isRequired": true, ';
                }

                // Otherwise, use the text values for the top-level selections as the sub question title
                // e.g. Yes, No

                else

                {
                    $option_values_array = explode('::', $temp_main_q_array[$n]);
                    $JSONed_question = $JSONed_question . 'title: "' . $option_values_array[1] . '", ';
                    $JSONed_question = $JSONed_question . '"isRequired": true, ';
                }
                
        		$JSONed_question = $JSONed_question . 'colCount: 1, ';

                $display_array = explode(" !! ", $answers_array[$n]);

        		$JSONed_question = $JSONed_question . 'visibleIf: "{stage' . $stage_number . '_question_' . $question_array['question_id'] . "_main}='" . $display_array[0] . '\'", ';
        		$JSONed_question = $JSONed_question . 'choices: [';

        		$temp_sub_q_array = explode(' ## ', $display_array[1]);

        		foreach($temp_sub_q_array as $items) {

        			$temp_items = explode('::', $items);
        			$JSONed_question = $JSONed_question . '{value:' . $temp_items[0] . ', text:"' . $temp_items[1] . '"}, ';

        		}

                $JSONed_question = rtrim($JSONed_question, ', ');

        		$JSONed_question = $JSONed_question . ']}, ';

            }

            $JSONed_question = rtrim($JSONed_question, '}, ');

    	break;


    	// 5: radio conditional checkbox
    	//
    	// Q: "Were there any aspects of your review(s) that you feel the authors (or editor) either ignored or dismissed inappropriately?"
    	//
    	// Options (old): "-4::Yes and on major issues ## -3::Yes but only on minor issues ## -2::No @@ -4,-3 @@ 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other @@ 11"

        // Options (new): // Options (new): "-4::Yes and on major issues ## -3::Yes but only on minor issues ## -2::No ## 999::Prefer not to answer ## 777::Don't know / don't recall ## 888::N/A @@ -4 !! 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other @@ 11 ~~ -4 !! 12::Consideration of background literature ## 13::Study rationale and/or hypotheses ## 14::Study procedures and/or analysis plans ## 15::Obtained results ## 16::Implementation of preregistered analyses ## 17::Validity and appropriateness of unregistered analyses ## 18::Interpretation of results ## 19::Conclusions of the study ## 20::Research transparency (e.g. data availability, quality of data curation etc.) ## 21::Ethical concerns ## 22::Other @@ 22"

    	Case '5':

			$JSONed_question = $JSONed_question . 'type: "radiogroup", ';

			if ($question_array['question_tooltip'] != "") {

    			$JSONed_question = $JSONed_question . 'tooltip: "' . $question_array['question_tooltip'] . '", ';
    		}

    		$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '_main", ';
    		$JSONed_question = $JSONed_question . 'title: ' . $question_array['question_text'] . ', ';
            $JSONed_question = $JSONed_question . '"isRequired": true, ';

    		$JSONed_question = $JSONed_question . 'colCount: 1, ';
    		$JSONed_question = $JSONed_question . 'choices: [';

            //echo $question_array['question_options'];

            // old: "-4::Yes and on major issues ## -3::Yes but only on minor issues ## -2::No @@ -4,-3 @@ 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other @@ 11"

            // new: "-4::Yes and on major issues ## -3::Yes but only on minor issues ## -2::No ## 999::Prefer not to answer ## 777::Don't know / don't recall ## 888::N/A @@ -4 !! 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other ^^ 11 ~~ -3 !! 12::Consideration of background literature ## 13::Study rationale and/or hypotheses ## 14::Study procedures and/or analysis plans ## 15::Obtained results ## 16::Implementation of preregistered analyses ## 17::Validity and appropriateness of unregistered analyses ## 18::Interpretation of results ## 19::Conclusions of the study ## 20::Research transparency (e.g. data availability, quality of data curation etc.) ## 21::Ethical concerns ## 22::Other ^^ 22"

            $temp = ltrim(rtrim($question_array['question_options'],'"'), '"');
            
    		$temp_array                       = explode(' @@ ', $temp);
    		$temp_main_q_array                = explode(' ## ', $temp_array[0]);
            $checkbox_arrays                  = explode(' ~~ ', $temp_array[1]);

            foreach($temp_main_q_array as $items) {

    			$temp_items = explode('::', $items);
    			$JSONed_question = $JSONed_question . '{value:' . $temp_items[0] . ', text:"' . $temp_items[1] . '"}, ';

    		}

            $JSONed_question = rtrim($JSONed_question, ', ');

            $JSONed_question = $JSONed_question . ']}, ';


            // Secondary question

            // Loop through all checkbox arrays

            $checkbox_ids_for_comment_box = [];

            foreach ($checkbox_arrays as $checkbox_array) {

                $temp_checkbox_array = explode(' !! ', $checkbox_array);

                $JSONed_question = $JSONed_question . '{type: "checkbox", ';
                $JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '_sub", ';
                $JSONed_question = $JSONed_question . 'title: ' . $question_array['question_helptext'] . ', ';
                $JSONed_question = $JSONed_question . '"isRequired": true, ';
                $JSONed_question = $JSONed_question . 'visibleIf: "';

                $JSONed_question = $JSONed_question . '{stage' . $stage_number . '_question_' . $question_array['question_id'] . "_main}='" . $temp_checkbox_array[0] . "'" . '", ';

                // split choices and id to show comment box into array

                $temp_checkbox_choices_array = explode(' ^^ ', $temp_checkbox_array[1]);

                $JSONed_question = $JSONed_question . 'choices: [';

                $temp_sub_q_array = explode(' ## ', $temp_checkbox_choices_array[0]);

                foreach($temp_sub_q_array as $items) {

                    $temp_items = explode('::', $items);
                    $JSONed_question = $JSONed_question . '{value:' . $temp_items[0] . ', text:"' . $temp_items[1] . '"}, ';
                }

                $JSONed_question = rtrim($JSONed_question, ', ');

                $JSONed_question = $JSONed_question . ']}, ';

            }


    		// Tertiary textbox for any checkboxes chosen

            // Get root value (e.g. -4) and corresponding 'Other' value (e.g. 11)
            // and use these to build SurveyJS conditional logic, meaning a single
            // comment box ('Comments (Other)') will be shown be shown for any condition

            for ($n = 0; $n < sizeof($checkbox_arrays); $n++) {

                $temp_checkbox_array_pairs_first = explode(' !! ', $checkbox_arrays[$n]);
                $temp_checkbox_array_pairs_second = explode(' ^^ ', $temp_checkbox_array_pairs_first[1]);

                $JSONed_question = $JSONed_question . '{type: "text", ';
                $JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '_checkbox_' . $temp_checkbox_array_pairs_second[1] . '_text", ';
                $JSONed_question = $JSONed_question . 'visibleIf: "';

                $JSONed_question = $JSONed_question . '{stage' . $stage_number . '_question_' . $question_array['question_id'] . "_main} = '" . $temp_checkbox_array_pairs_first[0] . "' and {stage" . $stage_number . '_question_' . $question_array['question_id'] . "_sub} contains ['" . $temp_checkbox_array_pairs_second[1] . "']" . '", ';
                
                // N.B. Currently the text is brackets (Other) is hardcoded
                // Best practice would be to grab the text from the checkbox choices
                // that corresponds to the ID after ^^ (e.g. 11)

                $JSONed_question = $JSONed_question . 'title: "Comments (Other)", ';
                $JSONed_question = $JSONed_question . '"isRequired": true}, ';
            }

            $JSONed_question = rtrim($JSONed_question, ', ');

    		$JSONed_question = rtrim($JSONed_question, '}, ');

    	break;


    	// 6: textbox

    	Case '6':

    		$JSONed_question = $JSONed_question . 'type: "comment", ';

    		if ($question_array['question_tooltip'] != "") {

    			$JSONed_question = $JSONed_question . 'tooltip: "' . $question_array['question_tooltip'] . '", ';
    		}
    		
    		$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '_comment", ';
    		$JSONed_question = $JSONed_question . 'title: ' . $question_array['question_text'] . ', ';

    		if ($question_array['question_helptext'] != "") {

    			$JSONed_question = $JSONed_question . 'description: ' . $question_array['question_helptext'];
    		}

    	break;


        // 7: matrix

        Case '7':

            $JSONed_question = $JSONed_question . 'type: "matrix", ';

            if ($question_array['question_tooltip'] != "") {

                $JSONed_question = $JSONed_question . 'tooltip: "' . $question_array['question_tooltip'] . '", ';
            }

            $JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '", ';
            $JSONed_question = $JSONed_question . 'title: ' . $question_array['question_text'] . ', ';
            $JSONed_question = $JSONed_question . '"isAllRowRequired": true, ';

            if ($question_array['question_helptext'] != "") {

                $JSONed_question = $JSONed_question . 'description: ' . $question_array['question_helptext'] . ', ';
            }

            $JSONed_question = $JSONed_question . 'horizontalScroll: true, ';

            // Insert 0-4 scoring, with a Prefer not to answer option (value 999)

            $JSONed_question = $JSONed_question . 'columns: [{value: 0, text: "0"}, {value: 1, text: "1"}, {value: 2, text: "2"}, {value: 3, text: "3"}, {value: 4, text: "4"}, {value: 999, text: "Prefer not to answer"}, {value: 777, text: "Don\'t know \/ don\'t recall"}, {value: 888, text: "N\/A", maxWidth: "50px"}], ';

            $JSONed_question = $JSONed_question . 'rows: [';

            $temp = ltrim(rtrim($question_array['question_options'],'"'), '"');
            $temp_array = explode(' ## ', $temp);

            foreach($temp_array as $items) {

                $temp_items = explode('::', $items);

                $JSONed_question = $JSONed_question . '{value:' . $temp_items[0] . ', text:"' . $temp_items[1] . '"}, ';
            }

            $JSONed_question = rtrim($JSONed_question, ', ');

            $JSONed_question = $JSONed_question . ']';

        break;
    }

    $JSONed_question = $JSONed_question . '}';

    // For all question types except comment boxes, add an optional, collapsed, comment box below

    if ($question_array['question_type'] != '6') {

    	$JSONed_question = $JSONed_question . ', {type: "comment", ';
    	$JSONed_question = $JSONed_question . 'name: "stage' . $stage_number . '_question_' . $question_array['question_id'] . '_comment", ';
    	$JSONed_question = $JSONed_question . 'state: "collapsed", ';
    	$JSONed_question = $JSONed_question . 'title: "+optional comment", ';
    	$JSONed_question = $JSONed_question . 'placeHolder: "If you have any notes regarding this question, please enter them here - they won\'t be displayed publicly or published with your feedback, but may be used for research purposes."}';

    }

    return $JSONed_question;
}


// https://www.benmarshall.me/get-ip-address/

function get_ip_address() {

    foreach([

      'HTTP_CLIENT_IP', 
      'HTTP_X_FORWARDED_FOR', 
      'HTTP_X_FORWARDED', 
      'HTTP_X_CLUSTER_CLIENT_IP', 
      'HTTP_FORWARDED_FOR', 
      'HTTP_FORWARDED', 
      'REMOTE_ADDR'

    ] as $key ) {

      if ( array_key_exists( $key, $_SERVER ) === true ) {

        foreach( explode( ',', $_SERVER[ $key ] ) as $ip_address ) {

          $ip_address = trim( $ip_address );

          if ( filter_var( 
            $ip_address, 
            FILTER_VALIDATE_IP, 
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false 
          ) {
            return $ip_address;
          }
        }
      }
    }
  }


// https://gist.github.com/Joel-James/3a6201861f12a7acf4f2#gistcomment-3030120

function isValidUuid( $uuid ) {
    
    if (!is_string($uuid) || (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid) !== 1)) {
        return false;
    }

    return true;
}

// https://stackoverflow.com/a/1519236

// https://stackoverflow.com/a/31375547
// https://www.designcise.com/web/tutorial/how-to-get-the-datetime-difference-in-seconds-between-two-datetime-objects-in-php

// Calculates the difference in seconds between 2 javascript Date() objects

// function receives:

// first date/time
// second date/time

// function returns:

// difference in seconds

function secondsDifference($datetime1, $datetime2) {

    $UTC1 = strtotime($datetime1);
    $UTC2 = strtotime($datetime2);

    $seconds = $UTC2 - $UTC1;

    // $php_datetime1 = DateTime::createFromFormat('D M d Y H:i:s T +', $datetime1);
    // $php_datetime2 = DateTime::createFromFormat('D M d Y H:i:s T +', $datetime2);

    // $diff = $php_datetime1->diff($php_datetime2);

    // $daysInSecs = $diff->format('%r%a') * 24 * 60 * 60;
    // $hoursInSecs = $diff->h * 60 * 60;
    // $minsInSecs = $diff->i * 60;

    // $seconds = $daysInSecs + $hoursInSecs + $minsInSecs + $diff->s;

    return $seconds;
}


// Give per-question breakdowns for a specific journal

// function receives:

// array of current question
// array of responses, dependent on current question type (either all average ratings if Q type = 1, or all other responses)
// category ID of the current category being displayed/processed (N.B. not the current category of the individual Q being passed/processed)
// role ID (optional - required for all Q types != 1)
// stage ID (optional - required for all Q types != 1)
// journal name - used for replacing {journal_name} in some questions to current journal

function getSurveyResponses (&$question_array, &$responses_array, $category_id, $role_id, $stage_id, $journal_name) {

    $JSONed_question  = "";
    $stage_number     = "1";

    // $q_target_replace = [$journal_name];
    // $q_source_replace = ["{journal_name}"];

    $q_source_replace = ["{stage_number}", "{journal_name}"];
    $q_target_replace = [$stage_id,         $journal_name];

    switch($question_array['question_type']) {

        // 1: 5 star        

        Case '1':

            // look for responses for both stage 1 and stage 2

            for ($stage_counter = 1; $stage_counter <= 2; $stage_counter++) {

                // Before looking for a matching response, check to see if a response to this question is possible (for the current stage i.e. stage_counter)
                
                // i.e. If a question is only shown for Stage 1 feedback, and we're returning a Stage 2 response, then no response will ever be shown.

                // there are 2 reasons not to do the following:

                // 1) to save time
                // 2) we want to return a different response for data that should not exist vs. data that does not yet exist

                // i.e. not enough data OR no data yet


                // Author role check

                if ( $question_array["question_role_type"] == '1' && ( ($stage_counter == 1 && $question_array["question_sub_stage_1"] == '0' && $question_array["question_sub_stage_2"] == '0' && $question_array["question_sub_stage_3"] == '0' && $question_array["question_sub_stage_4"] == '0' && $question_array["question_sub_stage_8"] == '0') || ($stage_counter == 2 && $question_array["question_sub_stage_5"] == '0' && $question_array["question_sub_stage_6"] == '0' && $question_array["question_sub_stage_7"] == '0') ) ) {


                    // Add a dash in this table cell, as this response does not apply for this stage of the question

                    $JSONed_question = $JSONed_question . "<td data-order='0'>&#8212;</td>";
                }

                // Reviewer role check

                elseif ( $question_array["question_role_type"] == '2' && ( ($stage_counter == 1 && $question_array["q_stage_1"] == '0' ) || ($stage_counter == 2 && $question_array["q_stage_2"] == '0') ) ) {


                    // Add a dash in this table cell, as this response does not apply for this stage of the question

                    $JSONed_question = $JSONed_question . "<td data-order='0'>&#8212;</td>";
                }

                // This question is asked for this stage (stage_counter)

                else

                {  
                    $found = false;            

                    foreach ($responses_array as $responses_item) {

                        if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["stage_id"] == $stage_counter) {

                            // Only show averages if number of responses is above a certain value

                            if ($responses_item["frequency"] < 5) {

                                $JSONed_question = $JSONed_question . "<td data-order='0'>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
                                $found = true;
                            }

                            else

                            {
                                $mean_avg = round($responses_item["average_rating_mean"], 1);
                                $mean_percentage = round((((($mean_avg - 1) * 80) / (5-1)) + 20));

                                $JSONed_question = $JSONed_question . "<td data-order='" . $mean_avg . "'>";

                                $JSONed_question = $JSONed_question . $mean_avg . " <i data-star='" . $mean_avg . "'></i> <span class='tooltip' style='font-size: 0.7em'>(" . $responses_item["frequency"] . ")<span class='tooltiptext'>Number of question responses this average is based on</span></span>";

                                $found = true;
                            }
                        }
                    }

                    if ($found == false) {

                        $JSONed_question = $JSONed_question . "<td data-order='0'>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span>";
                    }

                    $JSONed_question = $JSONed_question . "</td>";
                }
            }
            
        break;


        // 2: dropdown
        // "1,2,3,4,5,6,7"
        
        Case '2':            

            $temp = ltrim(rtrim($question_array['question_options'],'"'), '"');
            $temp_array = explode(' ## ', $temp);

            $total_frequency_count = 0;

            // loop through all frequencies for this question, to get a total (for later calculating each frequency as a percentage of total responses for this question)

            foreach ($responses_array as $responses_item) {

                if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id) {

                    // get total frequency count

                    $total_frequency_count = $total_frequency_count + $responses_item["frequency"];
                }
            }

            if ($total_frequency_count == 0) {

                $JSONed_question = $JSONed_question . "<ul><li>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            elseif ($total_frequency_count <5) {

                $JSONed_question = $JSONed_question . "<ul><li>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            else 

            {
                $JSONed_question = $JSONed_question . "<div class='graph-container horizontal flat' align='left'>";

                // now loop through all possible question responses, matching any and all response data we have for this question

                foreach($temp_array as $items) {

                    $found = false;
                    $temp_items = explode('::', $items);

                    $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label'>" . stripslashes(str_replace($q_source_replace, $q_target_replace, $temp_items[1])) . "</div>";


                    foreach ($responses_array as $responses_item) {

                        if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["question_response_value"] == $temp_items[0]) {

                            $mean_percentage = round(($responses_item["frequency"] / $total_frequency_count) * (100 / 1), 2);

                            $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>" . $mean_percentage . "%</span></div></div></div>";

                            $found = true;
                        }
                    }

                    if ($found == false) {

                        $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>0%</span></div></div></div>";
                    }

                    $JSONed_question = $JSONed_question . "</div>";
                }

                // Add N=total frequency count below table

                $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label2'>&nbsp;</div>";
                $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><span>N=" . $total_frequency_count . "</span></div></div>";

                $JSONed_question = $JSONed_question . "</div><br />";
            }

        break;


        // 3: radio

        // "1::Yes: LESS likely to submit a RR to this journal ## 2::Yes: MORE likely submit a RR to this journal ## 3::No"

        Case '3':

            $temp = ltrim(rtrim($question_array['question_options'],'"'), '"');

            // Add:
            
            // Prefer not to answer
            // Don't know/don't recall
            // N/A

            // to all radio responses
            
            // N.B. This is because the above 3 are automatically added to all
            // radio questions as responses, without being stored on a per-question
            // basis in the database.
            // This is fine for our purposes, though arguably not best practice - caveat emptor!

            $temp = $temp . " ## 999::Prefer not to answer ## 777::Don't know / don't recall ## 888::N/A";

            $temp_array = explode(' ## ', $temp);

            $total_frequency_count = 0;

            // loop through all frequencies for this question, to get a total (for later calculating each frequency as a percentage of total responses for this question)

            foreach ($responses_array as $responses_item) {

                if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id) {

                    // get total frequency count

                    $total_frequency_count = $total_frequency_count + $responses_item["frequency"];
                }
            }

            if ($total_frequency_count == 0) {

                $JSONed_question = $JSONed_question . "<ul><li>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            elseif ($total_frequency_count <5) {

                $JSONed_question = $JSONed_question . "<ul><li>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            else 

            {

                $JSONed_question = $JSONed_question . "<div class='graph-container horizontal flat' align='left'>";

                // now loop through all possible question responses, matching any and all response data we have for this question

                foreach($temp_array as $items) {

                    $found = false;

                    $temp_items = explode('::', $items);

                    $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label'>" . stripslashes(str_replace($q_source_replace, $q_target_replace, $temp_items[1])) . "</div>";

                    foreach ($responses_array as $responses_item) {

                        if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["question_response_value"] == $temp_items[0]) {

                            $mean_percentage = round(($responses_item["frequency"] / $total_frequency_count) * (100 / 1), 2);

                            $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>" . $mean_percentage . "%</span></div></div></div>";

                            $found = true;
                        }
                    }

                    if ($found == false) {

                        $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>0%</span></div></div></div>";
                    }

                    $JSONed_question = $JSONed_question . "</div>";
                }

                // Add N=total frequency count below table

                $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label2'>&nbsp;</div>";
                $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><span>N=" . $total_frequency_count . "</span></div></div>";

                $JSONed_question = $JSONed_question . "</div><br />";
            }

        break;


        // 4: radio conditional
        //
        // Single (i.e. if 'Yes' is chosen), show additional questions
        //
        // Q: "Have you ever reviewed a regular empirical article (other than a Registered Report) for this journal? @@ Please compare your experience reviewing a Stage 2 Registered Report to this journal with your previous experience(s) at the journal reviewing a regular empirical article. If you have previously reviewed multiple regular articles for the journal, please compare your Registered Report experience with your overall or "average" experience reviewing regular empirical articles at the journal."
        //
        // Options: "-2::Yes ## -1:No @@ -2 !! 1::Registered Report experience much worse ## 2::Registered Report experience slightly worse ## 3::Registered Report experience and regular article experience about the same ## 4::Registered Report experience slightly better ## 5::Registered Report experience much better"
        //
        //
        // Double (i.e. multiple initial responses, e.g. Yes/No lead to different secondary responses)
        //
        // Q: "Do you feel you received sufficient credit or other acknowledgment for your review?"
        //
        // Options: "-2::Yes ## -1::No @@ -2 !! 1::I received some form of credit or acknowledgement and it was sufficient ## 2::I received some form of credit or acknowledgement and it was insufficient ## 3::I received some form of credit or acknowledgement but I believe it was unnecessary or unwarranted ~~ -1 !! 4::I did not receive any credit or acknowledgement and I'm ok with that ## 5::I did not receive any credit or acknowledgement and I'm NOT ok with that"

        Case '4':

            $temp         = ltrim(rtrim($question_array['question_text'],'"'), '"');

            $temp_options = ltrim(rtrim($question_array['question_options'],'"'), '"');

            // Put all question parts into an array for later processing

            $question_parts_array = explode(' @@ ', $temp);
            $option_parts_array   = explode(' @@ ', $temp_options);

            // Get initial answers (i.e. everything before first @@ delimiter)

            $initial_responses = substr($temp_options, 0, strpos($temp_options, " @@"));
            
            $total_frequency_count = 0;

            $initial_responses_array = explode(' ## ', $initial_responses);

            // loop through all frequencies for the initial question, to get a total (for later calculating each frequency as a percentage of total responses for this initial question)

            foreach ($responses_array as $responses_item) {

                // only count root responses, not sub questions - hence looking for NULL sub questions

                if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["sub_question_id"] == NULL) {

                    // get total frequency count

                    $total_frequency_count = $total_frequency_count + $responses_item["frequency"];
                }
            }

            if ($total_frequency_count == 0) {

                $JSONed_question = $JSONed_question . "<ul><li>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            elseif ($total_frequency_count < 5) {

                $JSONed_question = $JSONed_question . "<ul><li>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            else 

            {
                $JSONed_question = $JSONed_question . "<div class='graph-container horizontal flat' align='left'>";

                // now loop through all possible question responses, matching any and all response data we have for this question

                foreach($initial_responses_array as $items) {

                    $found = false;

                    $temp_items = explode('::', $items);

                    $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label'>" . stripslashes($temp_items[1]) . "</div>";

                    foreach ($responses_array as $responses_item) {

                        if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["question_response_value"] == $temp_items[0] && $responses_item["sub_question_id"] == NULL) {

                            $mean_percentage = round(($responses_item["frequency"] / $total_frequency_count) * (100 / 1), 2);

                            $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>" . $mean_percentage . "%</span></div></div></div>";

                            $found = true;
                        }
                    }

                    if ($found == false) {

                        $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>0%</span></div></div></div>";
                    }

                    $JSONed_question = $JSONed_question . "</div>";
                }

                // Add N=total frequency count below table

                $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label2'>&nbsp;</div>";
                $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><span>N=" . $total_frequency_count . "</span></div></div>";

                $JSONed_question = $JSONed_question . "</div><br />";

                
                // Determine if there's a sub question to show or
                // just a series of sub choices

                // sub question

                if (sizeof($question_parts_array) > 1 ) {

                    // get conditional ID (i.e. the option value that shows additional sub questions e.g. -2 from Yes )

                    $option_temp_array = explode(' !! ', $option_parts_array[1]);
                    $conditional_options_id = $option_temp_array[0];

                    $conditional_label = "";

                    // Find the text (e.g. Yes) from the initial options
                    // that corresponds to the conditional ID

                    foreach ($initial_responses_array as $initial_responses_array_item) {

                        $temp_initial_responses_array = explode('::', $initial_responses_array_item);

                        if ($temp_initial_responses_array[0] == $conditional_options_id) {

                            $conditional_label = $temp_initial_responses_array[1];
                        }
                    }

                    $JSONed_question = $JSONed_question . "<ul><li>Further responses for '" . $conditional_label . "':<br /><br />";
                    $JSONed_question = $JSONed_question . "<ul><li>" . stripslashes(str_replace($q_source_replace, $q_target_replace, $question_parts_array[1])) . "</li></ul></li></ul>";

                    // Grab the earlier splitted sub responses, so we just have the value/label pairs
                    
                    $temp_secondary_responses_array = explode(' ## ', $option_temp_array[1]);

                    $total_frequency_count = 0;

                    // loop through all frequencies for the sub question, to get a total (for later calculating each frequency as a percentage of total responses for this sub question)

                    foreach ($responses_array as $responses_item) {

                        // only count sub responses, not root questions - hence looking for sub questions which equal 1 (which is a generic placeholder value
                        // for responses of all types except radio conditional checkbox (type=5)

                        if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["sub_question_id"] == "1") {

                            // get total frequency count

                            $total_frequency_count = $total_frequency_count + $responses_item["frequency"];
                        }
                    }

                    if ($total_frequency_count == 0) {

                        $JSONed_question = $JSONed_question . "<ul><ul><ul><li>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul></ul></ul>";
                    }

                    elseif ($total_frequency_count < 5) {

                        $JSONed_question = $JSONed_question . "<ul><ul><ul><li>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul></ul></ul>";
                    }

                    else 

                    {
                        $JSONed_question = $JSONed_question . "<br /><div class='graph-container horizontal flat' align='left'>";
                        
                        foreach ($temp_secondary_responses_array as $temp_secondary_responses_array_item) {

                            $found = false;

                            $temp_secondary_responses_array_pairs = explode('::', $temp_secondary_responses_array_item);

                            $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label'>" . stripslashes(str_replace($q_source_replace, $q_target_replace, $temp_secondary_responses_array_pairs[1])) . "</div>";

                            foreach ($responses_array as $responses_item) {

                                if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["sub_question_id"] == 1 && $responses_item["question_response_value"] == $temp_secondary_responses_array_pairs[0]) {

                                    $mean_percentage = round(($responses_item["frequency"] / $total_frequency_count) * (100 / 1), 2);

                                    $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>" . $mean_percentage . "%</span></div></div></div>";

                                    $found = true;
                                }
                            }

                            if ($found == false) {

                                $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>0%</span></div></div></div>";
                            }

                            $JSONed_question = $JSONed_question . "</div>";
                        }

                        // Add N=total frequency count below table

                        $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label2'>&nbsp;</div>";
                        $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><span>N=" . $total_frequency_count . "</span></div></div>";

                    $JSONed_question = $JSONed_question . "</div><br />";

                    }
                }

                // no sub question

                else

                {                    
                    $temp_secondary_responses_array = explode(' ~~ ', $option_parts_array[1]);

                    // array will contain this sort of structure:

                    // [0] => -2 !! 1::I received some form of credit or acknowledgement and it was sufficient ## 2::I received some form of credit or acknowledgement and it was insufficient ## 3::I received some form of credit or acknowledgement but I believe it was unnecessary or unwarranted

                    // [1] => -1 !! 4::I did not receive any credit or acknowledgement and I'm ok with that ## 5::I did not receive any credit or acknowledgement and I'm NOT ok with that

                    // Iterate through each conditional set of responses 

                    foreach($temp_secondary_responses_array as $temp_secondary_responses_array_item) {

                        $temp_secondary_responses_array_map = explode(' !! ', $temp_secondary_responses_array_item);

                        $conditional_options_id = $temp_secondary_responses_array_map[0];

                        $conditional_label = "";

                        // Find the text (e.g. Yes) from the initial options
                        // that corresponds to the conditional ID

                        foreach ($initial_responses_array as $initial_responses_array_item) {

                            $temp_initial_responses_array = explode('::', $initial_responses_array_item);

                            if ($temp_initial_responses_array[0] == $conditional_options_id) {

                                $conditional_label = $temp_initial_responses_array[1];
                            }
                        }

                        $JSONed_question = $JSONed_question . "<ul><li>Further responses for '" . $conditional_label . "':</li></ul>";

                        $total_frequency_count = 0;

                        // loop through all frequencies for the sub question, to get a total (for later calculating each frequency as a percentage of total responses for this sub question)

                        // We do this in a different way because currently we store '1' in each sub_question_id (this is done across all sub question responses to denote a response is to a sub question, not a root question).

                        // Perhaps a better way would be to store the root question's response (e.g. -2 = Yes) in the sub_question, allowing easier stratification of sub questions responses.

                        // However, for now, we'll iterate through each set of sub question responses, matching response IDs against the response array - this will be instead of the frequency loop used in other Q types (e.g. 1, 2, 3)

                        $temp_sub_responses_conditional = explode(' ## ', $temp_secondary_responses_array_map[1]);

                        foreach ($responses_array as $responses_item) {

                            // only count sub responses, not root questions - hence looking for sub questions which equal 1 (which is a generic placeholder value
                            // for responses of all types except radio conditional checkbox & matrix (Q types=5 and 7)

                            foreach ($temp_sub_responses_conditional as $temp_sub_responses_conditional_item) {

                                $temp_secondary_conditional_responses_array_pairs = explode('::', $temp_sub_responses_conditional_item);

                                if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["sub_question_id"] == "1" && $temp_secondary_conditional_responses_array_pairs[0] == $responses_item["question_response_value"]) {

                                    // get total frequency count

                                    $total_frequency_count = $total_frequency_count + $responses_item["frequency"];
                                }
                            }
                        }

                        if ($total_frequency_count == 0) {

                            $JSONed_question = $JSONed_question . "<ul><ul><li>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul></ul>";
                        }

                        elseif ($total_frequency_count < 5) {

                            $JSONed_question = $JSONed_question . "<ul><ul><li>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul></ul>";
                        }

                        else 

                        {
                            $JSONed_question = $JSONed_question . "<div class='graph-container horizontal flat' align='left'>";

                            // loop through each response for this conditional sub question

                            foreach($temp_sub_responses_conditional as $temp_sub_responses_conditional_item) {

                                $found = false;

                                $temp_secondary_conditional_responses_array_pairs = explode('::', $temp_sub_responses_conditional_item);

                                $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label'>" . stripslashes($temp_secondary_conditional_responses_array_pairs[1]) . "</div>";

                               foreach ($responses_array as $responses_item) {
                               
                                    if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["sub_question_id"] == "1" && $temp_secondary_conditional_responses_array_pairs[0] == $responses_item["question_response_value"]) {

                                        $mean_percentage = round(($responses_item["frequency"] / $total_frequency_count) * (100 / 1), 2);

                                        $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>" . $mean_percentage . "%</span></div></div></div>";

                                        $found = true;
                                    }
                               }

                               if ($found == false) {

                                    $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>0%</span></div></div></div>";
                                }

                                $JSONed_question = $JSONed_question . "</div>";
                            }

                            // Add N=total frequency count below table

                            $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label2'>&nbsp;</div>";
                            $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><span>N=" . $total_frequency_count . "</span></div></div>";

                            $JSONed_question = $JSONed_question . "</div><br />";
                        }
                    }
                }
            }

            $JSONed_question = rtrim($JSONed_question, '}, ');

        break;


        // 5: radio conditional checkbox
        //
        // Q: "Were there any aspects of your review(s) that you feel the authors (or editor) either ignored or dismissed inappropriately?"
        //
        // Options (old): "-4::Yes and on major issues ## -3::Yes but only on minor issues ## -2::No @@ -4,-3 @@ 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other @@ 11"

        // Options (new): "-4::Yes and on major issues ## -3::Yes but only on minor issues ## -2::No ## 999::Prefer not to answer ## 777::Don't know / don't recall ## 888::N/A @@ -4 !! 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other ^ 11 ~~ -3 !! 12::Consideration of background literature ## 13::Study rationale and/or hypotheses ## 14::Study procedures and/or analysis plans ## 15::Obtained results ## 16::Implementation of preregistered analyses ## 17::Validity and appropriateness of unregistered analyses ## 18::Interpretation of results ## 19::Conclusions of the study ## 20::Research transparency (e.g. data availability, quality of data curation etc.) ## 21::Ethical concerns ## 22::Other ^^ 22"

        Case '5':

            $temp = ltrim(rtrim($question_array['question_options'],'"'), '"');
            
            // temp_array[0] contains root responses
            // temp_array[1] contains sub responses

            $temp_array = explode(' @@ ', $temp);

            // Get initial answers (i.e. everything before first @@ delimiter)

            $total_frequency_count = 0;

            $initial_responses_array = explode(' ## ', $temp_array[0]);
            $sub_responses_array     = explode(' ~~ ', $temp_array[1]);

            // loop through all frequencies for the initial question, to get a total (for later calculating each frequency as a percentage of total responses for this initial question)

            foreach ($responses_array as $responses_item) {

                // only count root responses, not sub questions - hence looking for NULL sub questions

                if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["sub_question_id"] == NULL) {

                    // get total frequency count

                    $total_frequency_count = $total_frequency_count + $responses_item["frequency"];
                }
            }

            if ($total_frequency_count == 0) {

                $JSONed_question = $JSONed_question . "<ul><li>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            elseif ($total_frequency_count < 5) {

                $JSONed_question = $JSONed_question . "<ul><li>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul>";
            }

            else

            {
                $JSONed_question = $JSONed_question . "<div class='graph-container horizontal flat' align='left'>";

                // now loop through all possible question responses, matching any and all response data we have for this question

                // create a variable to allow tracking of which root item we're currently processing, which will allow us to append the frequency count to the initial_responses_array

                $root_response_item_counter = 0;

                foreach($initial_responses_array as $items) {

                    $found = false;

                    $temp_items = explode('::', $items);

                    $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label'>" . stripslashes($temp_items[1]) . "</div>";

                    foreach ($responses_array as $responses_item) {

                        if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["question_response_value"] == $temp_items[0]) {

                            $mean_percentage = round(($responses_item["frequency"] / $total_frequency_count) * (100 / 1), 2);

                            // Add the frequency count of the current root item as found in the responses array

                            $initial_responses_array[$root_response_item_counter] = $initial_responses_array[$root_response_item_counter] . "::" . $responses_item["frequency"];

                            $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>" . $mean_percentage . "%</span></div></div></div>";

                            $found = true;
                        }
                    }

                    if ($found == false) {

                        $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>0%</span></div></div></div>";

                        // Add frequency count of zero to any root item counts not found in the responses array

                        $initial_responses_array[$root_response_item_counter] = $initial_responses_array[$root_response_item_counter] . "::0";
                    }

                    $JSONed_question = $JSONed_question . "</div>";

                    $root_response_item_counter = $root_response_item_counter +1;
                }

                // Add N=total frequency count below table

                $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label2'>&nbsp;</div>";
                $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><span>N=" . $total_frequency_count . "</span></div></div>";

                $JSONed_question = $JSONed_question . "</div><br />";


                // Secondary question (dependent on initial question above)

                // Iterate through each root question that corresponds to sub question arrays that exist, displaying the root question as sub sections e.g:

                // "Further responses for 'Root question a:"

                foreach ($sub_responses_array as $sub_responses_array_item) {

                    $temp_array = explode(' !! ', $sub_responses_array_item);

                    foreach ($initial_responses_array as $initial_responses_array_item) {

                        $temp_initial_response_array = explode('::', $initial_responses_array_item);

                        // Find root question text that matches sub question set

                        if ($temp_array[0] == $temp_initial_response_array[0]) {

                            $JSONed_question = $JSONed_question . "<ul><li>Further responses for '" . $temp_initial_response_array[1] . "':</ul></li>";

                            // (total responses: $temp_initial_response_array[2]

                            if ($temp_initial_response_array[2] == 0) {

                                $JSONed_question = $JSONed_question . "<ul><ul><ul><li>No data yet <span id='tooltip_no-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul></ul></ul>";
                            }

                            elseif ($temp_initial_response_array[2] < 5) {

                                $JSONed_question = $JSONed_question . "<ul><ul><ul><li>Not enough data <span id='tooltip_not-enough-data-item' class='survey-tooltip' tabindex='-1'>?</span></li></ul></ul></ul>";
                            }

                            // There is >1 response to root question that would cause this sub question to be displayed, so we will enumerate all possible responses
                            // N.B. These responses are optional checkboxes, so there may be no responses
                            // Additionally, because users can choose multiple reponses, percentage calculations may add up to >100% - this is by design

                            else

                            {
                                // We just want the indexed pairs of value::label from sub_responses_array_item, so we'll perform a couple of explodes to remove anything before or after them

                                $temp_sub_responses_array_first  = explode(' !! ', $sub_responses_array_item);

                                $temp_sub_responses_array_second = explode(' ^^ ', $temp_sub_responses_array_first[1]);

                                $temp_sub_responses_items = explode(' ## ', $temp_sub_responses_array_second[0]);

                                // Now print all possible sub question checkbox responses

                                // add header

                                $JSONed_question = $JSONed_question . "<br /><div class='graph-container horizontal flat' align='left'>";

                                foreach ($temp_sub_responses_items as $temp_sub_responses_item) {

                                    $found = false;

                                    $temp_secondary_responses_array_pairs = explode('::', $temp_sub_responses_item);

                                    $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label'>" . stripslashes(str_replace($q_source_replace, $q_target_replace, $temp_secondary_responses_array_pairs[1])) . "</div>";

                                    foreach ($responses_array as $responses_item) {

                                        if ($responses_item["question_id"] == $question_array["question_id"] && $responses_item["question_role_type"] == $role_id && $responses_item["stage_id"] == $stage_id && $responses_item["sub_question_id"] == $temp_secondary_responses_array_pairs[0] && $responses_item["question_response_value"] == 1) {

                                            $mean_percentage = round(($responses_item["frequency"] / $temp_initial_response_array[2]) * (100 / 1), 2);

                                            $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>" . $mean_percentage . "%</span></div></div></div>";

                                            $found = true;
                                        }
                                    }

                                    if ($found == false) {

                                        $JSONed_question = $JSONed_question . "<div class='progress-bar horizontal'><div class='progress-track progress-track-right'><div class='progress-fill'><span>0%</span></div></div></div>";
                                    }

                                    $JSONed_question = $JSONed_question . "</div>";
                                }

                                // Add N=total frequency count below table
                                // Add footer to checkbox table explaining that the sum of the percentages may be greater than 100% (as users can select multiple options) 
                                

                                $JSONed_question = $JSONed_question . "<div class='progress-group'><div class='progress-label2'>&nbsp;</div>";
                                $JSONed_question = $JSONed_question . "<div class='progress-label2'><span>N=" . $temp_initial_response_array[2] . " (N.B. Percentages may be greater than 100% as users can select multiple options)</span></div></div>";

                            $JSONed_question = $JSONed_question . "</div><br />";

                            }
                        }
                    }

                    $JSONed_question = $JSONed_question . "<br /><br />";
                }
            }    

        break;
    }

    return $JSONed_question;
}


// Takes Type 3,4,5 responses whose questions have a transformation key, standardises these responses to a 1-5 range, calculates a weighed average (mean), then combines this standardised 3,4,5 average with the Type 1 average, again using a weighted average.

// Receives:

// Journal ID
// Quality mean of Type 1 (1-5 rating) questions
// Quality item count of Type 1 (1-5 rating) questions

// Returns an array of 2 elements:

// element 0: pseudo boolean - 1 if combined item counts >5, otherwise 0
// element 1: single integer, rounded to 2 decimal places - weighted averaged of Type 1 and Type 3,4,5 transform responses

// element 2: single integer of total item count (Type 1 items + Type 3,4,5)

// assumptions - will only be called if the journal_id has >=5 reviews

function getCombinedOverallQualityAverage ($journal_id, $type1_response_item_average, $type1_response_item_count) {

    $db_handle = mysqli_connect($GLOBALS['servername'], $GLOBALS['database_username'], $GLOBALS['database_password'], $GLOBALS['database_name']) or die(mysql_error());

    /* check connection */

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    $response_array = [];

    // By default, return that there were not enough combined items - this will be changed if there are

    $response_array[0] = 0;
    $response_array[1] = 0;
    $response_array[2] = 0;

    // Now look for any non-type 1 (i.e. Type 3, 4, 5) quality responses, with the aim of calculating an overall average and combining it with the average type 1 response

    // Get count of unique responses by stage for this journal ID (but not by role, as a question cannot span multiple roles - we have to have identical Qs if we want to ask the same Q for both author and reviewer)

    $sql = "SELECT SUM(frequency) AS frequency_sum FROM (SELECT `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, `feedback_questions`.question_role_type, `feedback_reviews`.stage_id, `feedback_questions`.`question_id`, `feedback_questions`.question_text, `feedback_responses`.sub_question_id, `feedback_responses`.question_response_value, COUNT(`feedback_responses`.question_response_value) As frequency, `feedback_questions`.question_transform_std FROM `feedback_responses` LEFT JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` LEFT JOIN feedback_reviews ON `feedback_responses`.review_id = `feedback_reviews`.review_id WHERE ((`feedback_questions`.`question_type` IN (3,5) AND `feedback_responses`.sub_question_id IS NULL) OR ((`feedback_questions`.`question_type` = 4 AND `feedback_responses`.sub_question_id IS NOT NULL))) AND `feedback_questions`.question_transform_std <> '' AND `feedback_questions`.`question_category` = 2 AND `feedback_responses`.question_response_value < 777 AND `feedback_reviews`.journal_id = '" . $journal_id . "' GROUP BY `feedback_questions`.question_id, `feedback_reviews`.stage_id, `feedback_responses`.question_response_value) AS frequency_sum";

    if ($result = mysqli_query($db_handle, $sql)) {

        if (mysqli_num_rows($result) > 0) {
            
            $quality_response_item_count = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }

    // If total number of quality items (this count + existing item count) i.e. any quality items is <5, then we will leave response_array[0] = 0 and skip the following if section

    if ($type1_response_item_count + $quality_response_item_count[0]['frequency_sum'] >= 5) {

        $response_array[0] = 1;

        // Otherwise, do a further SQL to get actual responses, for processing and later average weighting (with existing average score/item count)

        $sql =  "SELECT `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, `feedback_questions`.question_role_type, `feedback_reviews`.stage_id, `feedback_questions`.`question_id`, `feedback_questions`.question_text, `feedback_responses`.sub_question_id, `feedback_responses`.question_response_value, COUNT(`feedback_responses`.question_response_value) As frequency, `feedback_questions`.question_transform_std FROM `feedback_responses` LEFT JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` LEFT JOIN feedback_reviews ON `feedback_responses`.review_id = `feedback_reviews`.review_id WHERE ((`feedback_questions`.`question_type` IN (3,5) AND `feedback_responses`.sub_question_id IS NULL) OR ((`feedback_questions`.`question_type` = 4 AND `feedback_responses`.sub_question_id IS NOT NULL))) AND `feedback_questions`.question_transform_std <> '' AND `feedback_questions`.`question_category` = 2 AND `feedback_responses`.question_response_value < 777 AND `feedback_reviews`.journal_id = '" . $journal_id . "' GROUP BY `feedback_questions`.question_id, `feedback_reviews`.stage_id, `feedback_responses`.question_response_value ORDER BY `feedback_questions`.question_role_type ASC, `feedback_reviews`.`stage_id` ASC, `feedback_questions`.`question_id` ASC";

        if ($result = mysqli_query($db_handle, $sql)) {

          if (mysqli_num_rows($result) > 0) {

            $quality_response_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
          }
        }

        // Iterate over each response pair (response value and frequency), transforming the question_response_value to a standardised (1-5 rating) based on corresponding rule in question_transform_std

        // and also adding each item to a string to be evaled to calculate a weighted average

        // e.g. ((set 1 mean x item count in set 1) + (set 2 mean x item count in set 2)) / (item count in set 1 + item count in set 2)

        $weighted_average_string = "(";

        foreach ($quality_response_items as $quality_response_item) {

          $temp_transform_main = explode(' ## ', $quality_response_item['question_transform_std']);

          // iterate through each transformation pair

          foreach ($temp_transform_main as $temp_transform_main_elements) {

            $temp_transform_main_element_pair = explode('::', $temp_transform_main_elements);

            // if the current response value matches the source value in the current tranformation pair, then replace the current response value with the destination value.

            if ($temp_transform_main_element_pair[0] == $quality_response_item['question_response_value']) {

              $quality_response_item['question_response_value'] = $temp_transform_main_element_pair[1];

              // N.B. We need to break out of the foreach loop to prevent situations where a response value is transformed, then later matches another source value in a later transform pair.

              // e.g. response value: 1 - transformation pairs: 1::5 ## 3::3 ## 5::1 - our response value of 1 will correctly be transformed to 5 based on the first transformation pair, but then the 5 would be incorrectly transformed (back) to 1 by the last transformation pair, hence while we break out of the foreach loop once we find a match

              break;
            }
          }

          // Add each transformed response value and item count pair to the weighted average equation

          $weighted_average_string = $weighted_average_string . "(" . $quality_response_item['question_response_value'] . "*" . $quality_response_item['frequency'] . ")+";
        }

        $weighted_average_string = rtrim($weighted_average_string, '+');

        $weighted_average_string = $weighted_average_string . ")/" . $quality_response_item_count[0]['frequency_sum'];

        $non_type_1_average = round(eval("return $weighted_average_string;"), 1);
        
        // Calculate a weighted average of the Type 1 rating mean average/item count and Types 3,4,5 mean average/item count

        $response_array[1] = round((($type1_response_item_average * $type1_response_item_count) + ($non_type_1_average * $quality_response_item_count[0]['frequency_sum'])) / ($type1_response_item_count + $quality_response_item_count[0]['frequency_sum']), 1);

        $response_array[2] = $type1_response_item_count + $quality_response_item_count[0]['frequency_sum'];
        
    }

    return $response_array;
}




// Takes Type 3,4,5 responses (for a specific Role x Stage) whose questions have a transformation key, standardises these responses to a 1-5 range, calculates a weighed average (mean), then combines this standardised 3,4,5 average with the Type 1 average, again using a weighted average.

// Receives:

// Journal ID
// Quality mean of Type 1 (1-5 rating) questions
// Quality item count of Type 1 (1-5 rating) questions
// Role ID (1=Author, 2=Reviewer)
// Stage ID (1 or 2)

// Returns an array of 3 elements:

// element 0: pseudo boolean - 1 if combined item counts >5, otherwise 0
// element 1: single integer, rounded to 2 decimal places - weighted averaged of Type 1 and Type 3,4,5 transform responses

// element 2: single integer of total item count (Type 1 items + Type 3,4,5)

// assumptions - will only be called if the journal_id has >=5 reviews

function getCombinedQualityAverageByRoleXstage ($journal_id, $type1_response_item_average, $type1_response_item_count, $role_id, $stage_id) {

    $db_handle = mysqli_connect($GLOBALS['servername'], $GLOBALS['database_username'], $GLOBALS['database_password'], $GLOBALS['database_name']) or die(mysql_error());

    /* check connection */

    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    $response_array = [];

    // By default, return that there were not enough combined items - this will be changed if there are

    $response_array[0] = 0;
    $response_array[1] = 0;
    $response_array[2] = 0;

    // Now look for any non-type 1 (i.e. Type 3, 4, 5) quality responses, with the aim of calculating an overall average and combining it with the average type 1 response

    // Get count of unique responses by stage for this journal ID by $stage_id and $role_id

    $sql = "SELECT SUM(frequency) AS frequency_sum FROM (SELECT `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, `feedback_questions`.question_role_type, `feedback_reviews`.stage_id, `feedback_questions`.`question_id`, `feedback_questions`.question_text, `feedback_responses`.sub_question_id, `feedback_responses`.question_response_value, COUNT(`feedback_responses`.question_response_value) As frequency, `feedback_questions`.question_transform_std FROM `feedback_responses` LEFT JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` LEFT JOIN feedback_reviews ON `feedback_responses`.review_id = `feedback_reviews`.review_id WHERE ((`feedback_questions`.`question_type` IN (3,5) AND `feedback_responses`.sub_question_id IS NULL) OR ((`feedback_questions`.`question_type` = 4 AND `feedback_responses`.sub_question_id IS NOT NULL))) AND `feedback_questions`.question_transform_std <> '' AND `feedback_questions`.`question_category` = 2 AND `feedback_responses`.question_response_value < 777 AND `feedback_reviews`.journal_id = '" . $journal_id . "' AND `feedback_questions`.question_role_type = '" . $role_id . "' AND `feedback_reviews`.stage_id = '" . $stage_id . "' GROUP BY `feedback_questions`.question_id, `feedback_reviews`.stage_id, `feedback_responses`.question_response_value) AS frequency_sum";

    if ($result = mysqli_query($db_handle, $sql)) {

        if (mysqli_num_rows($result) > 0) {
            
            $quality_response_item_count = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    }

    // If total number of quality items (this count + existing item count) i.e. any quality items is <5, then we will leave response_array[0] = 0 and skip the following if section

    if ($type1_response_item_count + $quality_response_item_count[0]['frequency_sum'] >= 5) {

        $response_array[0] = 1;

        // Otherwise, do a further SQL to get actual responses, for processing and later average weighting (with existing average score/item count)

        $sql =  "SELECT `feedback_questions`.`question_type`, `feedback_questions`.`question_category`, `feedback_questions`.question_role_type, `feedback_reviews`.stage_id, `feedback_questions`.`question_id`, `feedback_questions`.question_text, `feedback_responses`.sub_question_id, `feedback_responses`.question_response_value, COUNT(`feedback_responses`.question_response_value) As frequency, `feedback_questions`.question_transform_std FROM `feedback_responses` LEFT JOIN `feedback_questions` ON `feedback_responses`.`question_id` = `feedback_questions`.`question_id` LEFT JOIN feedback_reviews ON `feedback_responses`.review_id = `feedback_reviews`.review_id WHERE ((`feedback_questions`.`question_type` IN (3,5) AND `feedback_responses`.sub_question_id IS NULL) OR ((`feedback_questions`.`question_type` = 4 AND `feedback_responses`.sub_question_id IS NOT NULL))) AND `feedback_questions`.question_transform_std <> '' AND `feedback_questions`.`question_category` = 2 AND `feedback_responses`.question_response_value < 777 AND `feedback_reviews`.journal_id = '" . $journal_id . "' AND `feedback_questions`.question_role_type = '" . $role_id . "' AND `feedback_reviews`.stage_id = '" . $stage_id . "' GROUP BY `feedback_questions`.question_id, `feedback_reviews`.stage_id, `feedback_responses`.question_response_value ORDER BY `feedback_questions`.question_role_type ASC, `feedback_reviews`.`stage_id` ASC, `feedback_questions`.`question_id` ASC";

        if ($result = mysqli_query($db_handle, $sql)) {

          if (mysqli_num_rows($result) > 0) {

            $quality_response_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
          }
        }

        // Iterate over each response pair (response value and frequency), transforming the question_response_value to a standardised (1-5 rating) based on corresponding rule in question_transform_std

        // and also adding each item to a string to be evaled to calculate a weighted average

        // e.g. ((set 1 mean x item count in set 1) + (set 2 mean x item count in set 2)) / (item count in set 1 + item count in set 2)

        $weighted_average_string = "(";

        foreach ($quality_response_items as $quality_response_item) {

          $temp_transform_main = explode(' ## ', $quality_response_item['question_transform_std']);

          // iterate through each transformation pair

          foreach ($temp_transform_main as $temp_transform_main_elements) {

            $temp_transform_main_element_pair = explode('::', $temp_transform_main_elements);

            // if the current response value matches the source value in the current tranformation pair, then replace the current response value with the destination value.

            if ($temp_transform_main_element_pair[0] == $quality_response_item['question_response_value']) {

              $quality_response_item['question_response_value'] = $temp_transform_main_element_pair[1];

              // N.B. We need to break out of the foreach loop to prevent situations where a response value is transformed, then later matches another source value in a later transform pair.

              // e.g. response value: 1 - transformation pairs: 1::5 ## 3::3 ## 5::1 - our response value of 1 will correctly be transformed to 5 based on the first transformation pair, but then the 5 would be incorrectly transformed (back) to 1 by the last transformation pair, hence while we break out of the foreach loop once we find a match

              break;
            }
          }

          // Add each transformed response value and item count pair to the weighted average equation

          $weighted_average_string = $weighted_average_string . "(" . $quality_response_item['question_response_value'] . "*" . $quality_response_item['frequency'] . ")+";
        }

        $weighted_average_string = rtrim($weighted_average_string, '+');

        $weighted_average_string = $weighted_average_string . ")/" . $quality_response_item_count[0]['frequency_sum'];

        $non_type_1_average = round(eval("return $weighted_average_string;"), 1);

        // Calculate a weighted average of the Type 1 rating mean average/item count and Types 3,4,5 mean average/item count

        $response_array[1] = round((($type1_response_item_average * $type1_response_item_count) + ($non_type_1_average * $quality_response_item_count[0]['frequency_sum'])) / ($type1_response_item_count + $quality_response_item_count[0]['frequency_sum']), 1);

        $response_array[2] = $type1_response_item_count + $quality_response_item_count[0]['frequency_sum'];
    }

    return $response_array;
}
?>
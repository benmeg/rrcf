<?php

header("Content-Type: text/html; charset=utf-8", true);
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

require 'functions.php';
require $vendor_path;

// set default to be not found

if (isset($_GET['doi']) && !empty($_GET['doi'])) {

	$lookup_doi = 'works/' . $_GET['doi'];

	$client = new RenanBr\CrossRefClient();
	$client->setCache(new voku\cache\CachePsr16());

	try {

		$work = $client->request($lookup_doi);

		$strRef = '';

		// Loop through author list, extracting names for each
		//
		// N.B. Some authors may be institutions, hence we need to check if:
		// [family] and [given] exist, or [name]
		//
		// TODO: Need to properly process unicode (UTF-8) characters in 'family' author field

		for ($i = 0; $i < sizeof($work['message']['author']); $i++) {

			if (array_key_exists("given", $work['message']['author'][$i])) {

				// this is an individual

				$strRef .= utf8_decode($work['message']['author'][$i]['family']);

				// convert and concatenate first name(s) into initials
				//
				// TODO: also need to strip fullstops/commas etc from name before processing
				//       otherwise 'J. M G' is converted to 'J. MG', rather than 'JMG'

				//$strRef .= ', ' . preg_replace('/(?<=\w)./', '', utf8_decode($work['message']['author'][$i]['given'])) . '.';

				// from: https://stackoverflow.com/a/17373847

				// split the name on whitespace, this includes Unicode characters
    			// that represent whitespace but are not 0x20 (ASCII space)
    			$elements = preg_split('/\s+/', $work['message']['author'][$i]['given']);

    			// get the initials
				$initials = '';
				foreach($elements as $element) {
				    $initials .= mb_substr($element, 0, 1, 'UTF-8');
				}

				$strRef .= ', ' . $initials . '.';
			}

			else if (array_key_exists("name", $work['message']['author'][$i])) {

				// this is an institution

				$strRef .= utf8_decode($work['message']['author'][$i]['name']);
			}

			
			// add comma or ampersand to list of authors, depending on how many there are
			//
			// TODO: fix for a single author publications

			if (sizeof($work['message']['author']) - $i > 2) {

				$strRef .= ', ';
			}

			else if (sizeof($work['message']['author']) - $i == 2) {

				$strRef .= ' & ';
			}
		}

		// add year of publication

		$strRef .= ' (' . $work['message']['issued']['date-parts'][0][0] . '). ';

		// add article name

		$strRef .= $work['message']['title'][0];

		// add journal name (if applicable - will be blank for pre-prints)

		if (!empty($work['message']['short-container-title'])) {

			$strRef .= '. ' . $work['message']['short-container-title'][0];

		}

		$arr = array('available' => 'true', 'reference' => utf8_encode($strRef));

		echo json_encode($arr);

	}

	catch (exception $e) {

		$arr = array('available' => 'false');
		echo json_encode($arr);
	}
}

?>
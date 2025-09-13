-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 13, 2025 at 01:27 AM
-- Server version: 10.3.39-MariaDB-log
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rr_data`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`rr_user`@`localhost` FUNCTION `BIN_TO_UUID` (`bin` BINARY(16)) RETURNS CHAR(36) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci  BEGIN
  DECLARE hex CHAR(32);
  SET hex = HEX(bin);
  RETURN LOWER(CONCAT(LEFT(hex, 8), '-', MID(hex, 9, 4), '-', MID(hex, 13, 4), '-', MID(hex, 17, 4), '-', RIGHT(hex, 12)));
END$$

CREATE DEFINER=`rr_user`@`localhost` FUNCTION `fn_strip_html_tags` (`html_text` TEXT) RETURNS TEXT CHARSET utf8 COLLATE utf8_unicode_ci NO SQL BEGIN  
     DECLARE start,end INT DEFAULT 1; 
     DECLARE text_without_nbsp TEXT;
     LOOP
        SET start = LOCATE("<", html_text, start);
        IF (!start) THEN RETURN html_text; END IF;
        SET end = LOCATE(">", html_text, start);
        IF (!end) THEN SET end = start; END IF;
        SET text_without_nbsp = REPLACE(html_text, "&nbsp;", " ");
        SET html_text = INSERT(text_without_nbsp, start, end - start + 1, "");
            END LOOP;
END$$

CREATE DEFINER=`rr_user`@`localhost` FUNCTION `UUID_TO_BIN` (`uuid` CHAR(36)) RETURNS BINARY(16)  RETURN UNHEX(CONCAT(REPLACE(uuid, '-', '')))$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `auth_type` varchar(255) NOT NULL,
  `selector` text NOT NULL,
  `token` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `feedback_academic_roles`
--

CREATE TABLE `feedback_academic_roles` (
  `role_id` tinyint(4) NOT NULL,
  `role_text` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback_academic_roles`
--

INSERT INTO `feedback_academic_roles` (`role_id`, `role_text`) VALUES
(1, 'Undergraduate'),
(2, 'Postgraduate/graduate'),
(3, 'PhD student'),
(4, 'Post-doc'),
(5, 'Early career fellow'),
(6, 'Senior research fellow'),
(7, 'Lecturer'),
(8, 'Senior lecturer'),
(9, 'Reader'),
(10, 'Assistant professor'),
(11, 'Associate professor'),
(12, 'Full professor'),
(13, 'Independent researcher (unaffiliated)'),
(14, 'Industry scientist/researcher'),
(15, 'Government researcher'),
(16, 'Other (please specify)'),
(17, 'Non-profit sector');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_invite_log`
--

CREATE TABLE `feedback_invite_log` (
  `invite_uuid` binary(16) NOT NULL,
  `invite_count` mediumint(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `feedback_questions`
--

CREATE TABLE `feedback_questions` (
  `question_id` int(11) NOT NULL,
  `question_order` tinyint(4) NOT NULL,
  `question_role_type` tinyint(4) NOT NULL,
  `question_text` varchar(800) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `question_text_summary` varchar(800) NOT NULL,
  `question_options` varchar(1200) NOT NULL,
  `question_transform_std` varchar(100) NOT NULL,
  `question_type` tinyint(4) NOT NULL,
  `question_helptext` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `question_tooltip` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `question_category` tinyint(4) NOT NULL,
  `no_repeat` tinyint(1) NOT NULL DEFAULT 0,
  `q_stage_1` tinyint(1) NOT NULL DEFAULT 0,
  `q_stage_2` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_1` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_2` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_3` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_4` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_5` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_6` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_7` tinyint(1) NOT NULL DEFAULT 0,
  `question_sub_stage_8` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback_questions`
--

INSERT INTO `feedback_questions` (`question_id`, `question_order`, `question_role_type`, `question_text`, `question_text_summary`, `question_options`, `question_transform_std`, `question_type`, `question_helptext`, `question_tooltip`, `question_category`, `no_repeat`, `q_stage_1`, `q_stage_2`, `question_sub_stage_1`, `question_sub_stage_2`, `question_sub_stage_3`, `question_sub_stage_4`, `question_sub_stage_5`, `question_sub_stage_6`, `question_sub_stage_7`, `question_sub_stage_8`) VALUES
(1, 0, 1, '\"Speed of Stage 1 peer review\"', '', '', '', 1, '', 'Award more stars for FASTER performance.<br /><br />Please taken into account only the time the journal was handling your manuscript, not the time you spent revising it or conducting the research itself.', 1, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 1),
(2, 0, 1, '\"Speed of Stage 2 peer review\"', '', '', '', 1, '', 'Award more stars for FASTER performance.<br /><br />Please taken into account only the time the journal was handling your manuscript, not the time you spent revising it or conducting the research itself.', 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0),
(3, 0, 1, '\"Speed of response to a presubmission enquiry (if applicable)\"', '', '', '', 1, '', 'Award more stars for FASTER performance.<br /><br />Please taken into account only the time the journal was handling your manuscript, not the time you spent revising it or conducting the research itself.', 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 1),
(4, 0, 1, '\"Speed of response to any other author enquiries (if applicable)\"', '', '', '', 1, '', 'Award more stars for FASTER performance.<br /><br />Please taken into account only the time the journal was handling your manuscript, not the time you spent revising it or conducting the research itself.', 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(5, 0, 1, '\"Speed of the editorial decision\"', '\"Speed of the editorial decision (when manuscript was rejected)\"', '', '', 1, '', 'Award more stars for FASTER performance.<br /><br />Please taken into account only the time the journal was handling your manuscript, not the time you spent revising it or conducting the research itself.', 1, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0),
(6, 0, 2, '\"Speed of response by the journal to your enquiries (if any)\"', '', '', '', 1, '', 'Award more stars for FASTER performance.<br /><br />If you didn\'t have any interactions with the journal, choose N/A', 1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(7, 1, 1, '\"Number of peer reviewers at Stage {stage_number}\"', '', '\"0::0 ## 1::1 ## 2::2 ## 3::3 ## 4::4 ## 5::5 ## 6::6 ## 7::7 ## 8::8 ## 9::9 ## 11::10+ ## 999::Prefer not to answer ## 777::Don\'t know/don\'t recall\"', '', 2, '', 'Where the manuscript was considered over more than one round of review, and there were a different number of reviewers between different rounds, please select the MOST number of reviewers that assessed the manuscript in any individual round.<br /><br />For example, if 3 reviewers assessed the first Stage {stage_number} submission, and 2 reviewers returned to assess a revised Stage {stage_number} submission, then you would respond 3.', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(9, 0, 1, '\"Quality of Stage 1 peer reviews\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 1),
(10, 0, 1, '\"Quality of Stage 2 peer reviews\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding<br /><br />If no reviewers were invited at Stage 2, and you only received a review from the action editor, choose N/A here and for further peer review questions.', 2, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0),
(11, 0, 1, '\"Quality of feedback from the editor in rejection letter\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0),
(12, 0, 1, '\"Quality of response from the editor to a presubmission enquiry (if applicable)\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 1),
(13, 0, 1, '\"Quality of editorial input, including clarity of editorial guidance\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(14, 0, 1, '\"Flexibility of editor to unforeseen circumstances, e.g. in granting extensions of submission deadline, necessary deviations from the approved protocol etc.\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1),
(15, 0, 1, '\"Clarity and efficiency of the manuscript handling system\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(16, 0, 1, '\"Clarity and accessibility of the journal\'s RR policy\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(17, 0, 1, '\"Administrative handling of the manuscript, over and above the review process\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding<br /><br />If you didn\'t have any admin contact in this stage, choose N/A', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(18, 0, 1, '\"The extent to which the journal adhered to its stated policy on RRs\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(19, 0, 1, '\"The extent to which the journal adhered to general principles/spirit of RRs\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(20, 0, 2, '\"Clarity and efficiency of the manuscript handling system\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(21, 0, 2, '\"Clarity and accessibility of the journal\'s RR policy and expectations of reviewers\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(22, 0, 2, '\"Flexibility of journal or editor to unforeseen circumstances, e.g. in granting extensions of review deadlines, etc.\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(23, 0, 2, '\"Extent to which the authors responded appropriately and constructively to your review(s) through either revision or rebuttal\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(24, 0, 2, '\"Extent to which the editor took into account your review in their editorial decision(s)\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(25, 0, 2, '\"Extent to which the editor helped authors resolve conflicting recommendations between reviewers\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(26, 0, 2, '\"Quality of comments provided by any other reviewers (to the extent observed)\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(27, 0, 2, '\"Overall quality of editing (to the extent observed)\"', '', '', '', 1, '', '1 star = very poor -> 5 stars = outstanding', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(28, 0, 1, '\"To what extent do you feel that the reviewers/editor were granted too much, too little, or the right amount of power to shape your study design at Stage 1?\"', '', '\"1::Too little power ## 2::About the right amount of power ## 3::Too much power\"', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 1),
(29, 0, 1, '\"To what extent did the editor\'s rejection letter provide useful information about the reasons for the rejection?\"', '', '\"1::Not at all useful - the rejection letter was boilerplate with little or no specific feedback ## 2::Somewhat useful ## 3::Very useful\"', '1::1 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0),
(30, 0, 1, '\"To what extent do you feel you could have feasibly revised the manuscript to satisfy the concerns raised?\"', '\"To what extent do you feel you could have feasibly revised the manuscript to satisfy the concerns raised? (Stage 1 - Manuscript rejected after one or more rounds of specialist peer review)\"', '\"1::Manuscript could have been easily revised to address concerns ## 2::Any revisions were challenging but feasible ## 3::Any revisions were technically feasible but, in any case, would in my view have invalidated the research or deviated too far from the original research question ## 4::Any revisions made in response to the reviews were infeasible or impossible\"', '', 3, '', '', 3, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0),
(31, 0, 1, '\"To what extent did you feel coerced into making <u>invalid or unnecessary</u> changes to the manuscript (i.e. hypotheses, methods, analyses) in order to achieve Stage {stage_number} acceptance? Please consider only <u>invalid or unnecessary</u> changes, not changes you agreed with.\"', '', '\"1::Not at all coerced ## 2::Somewhat coerced ## 3::Heavily coerced\"', '1::5 ## 2::3 ## 3::1', 3, '', 'Do not consider language edits/stylistic/level of detail when answering this question', 2, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0),
(33, 0, 1, '\"To what extent do you believe that the journal (e.g. through editorial action/inaction or policy) bears at least some responsibility for the withdrawal of your submission following Stage 1 acceptance?\"', '', '\"1::Journal was entirely responsible ## 2::Journal was partially responsible ## 3::Journal was not at all responsible\"', '1::1 ## 2::3 ## 3::5', 3, '', '', 2, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(34, 0, 1, '\"At Stage 2, to what extent did the reviewers/editor reevaluate parts of the Stage 1 manuscript (e.g. study rationale, methods, confirmatory analysis plans) that had already received Stage 1 In-Principle Acceptance?\"', '', '\"1::Not at all ## 2::To a minor extent ## 3::To a major extent\"', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0),
(35, 0, 1, '\"At Stage 2, to what extent did the reviewers/editor evaluate the manuscript <u>at least in part</u> based on the <u>obtained results</u>, over and above your interpretation of those results?\"', '', '\"1::Not at all ## 2::Only as necessary to assess whether any prespecified outcome-neutral tests / positive controls / data quality checks succeeded ## 3::To a major extent involving the main outcomes ## 4::To a minor extent involving the main outcomes\"', '1::5 ## 2::5 ## 3::1 ## 4::2', 3, '', '', 2, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0),
(36, 0, 1, '\"At Stage 2, to what extent do you feel that the reviewers/editor pressured (or required) you to perform extra analyses that you believe were <u>invalid or unnecessary</u>?\"', '', '\"1::Not at all ## 2::To a minor extent ## 3::To a major extent\"', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0),
(37, 0, 1, '\"At Stage 2, to what extent do you feel the reviewers/editor pressured (or required) you to <u>inappropriately alter</u> parts of the manuscript that were previously approved at Stage 1?\"', '', '\"1::Not at all ## 2::To a minor extent ## 3::To a major extent\"', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0),
(38, 0, 1, '\"To what extent do you feel that the reviewers/editor were <u>overly inflexible</u> about necessary deviations from the approved Stage 1 manuscript at Stage 2?\"', '', '\"1::Not at all ## 2::To a minor extent ## 3::To a major extent\"', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0),
(39, 0, 1, '\"Do you believe the editor read your Stage {stage_number} manuscript?\"', '', '1::Yes and in detail ## 2::Yes but only superficially ## 3::No', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(40, 0, 1, '\"In retrospect, do you believe the editor made the correct decision in desk-rejecting your Stage {stage_number} manuscript?\"', '', '\"1::Yes ## 2::No\"', '1::5 ## 2::1', 3, '', '', 2, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0),
(41, 0, 1, '\"In retrospect, do you believe the editor made the correct decision in rejecting your Stage {stage_number} manuscript?\"', '', '\"1::Yes ## 2::No\"', '1::5 ## 2::1', 3, '', '', 2, 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0),
(42, 0, 1, '\"In retrospect, do you believe the editor made the correct decision in granting in-principle acceptance to your manuscript?\"', '', '\"1::Yes ## 2::No\"', '1::5 ## 2::1', 3, '', '', 2, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0),
(43, 0, 1, '\"In retrospect, do you believe the editor made the correct decision in accepting your Stage 2 manuscript?\"', '', '\"1::Yes ## 2::No\"', '1::5 ## 2::1', 3, '', '', 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(44, 0, 1, '\"Would you submit a Registered Report to <i>{journal_name}</i> again?\"', '', '\"1::Yes ## 2::No\"', '1::5 ## 2::1', 3, '', '', 2, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(45, 0, 2, '\"Do you believe the editor read the manuscript you reviewed?\"', '', '1::Yes and in detail ## 2::Yes but only superficially ## 3::No', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(46, 0, 2, '\"Were there any aspects of your review(s) that you feel the authors (or editor) either ignored or dismissed inappropriately?\"', '', '\"-4::Yes and on major issues ## -3::Yes but only on minor issues ## -2::No ## 999::Prefer not to answer ## 777::Don\'t know / don\'t recall ## 888::N/A @@ -4 !! 1::Consideration of background literature ## 2::Study rationale and/or hypotheses ## 3::Study procedures and/or analysis plans ## 4::Obtained results ## 5::Implementation of preregistered analyses ## 6::Validity and appropriateness of unregistered analyses ## 7::Interpretation of results ## 8::Conclusions of the study ## 9::Research transparency (e.g. data availability, quality of data curation etc.) ## 10::Ethical concerns ## 11::Other ^^ 11 ~~ -3 !! 12::Consideration of background literature ## 13::Study rationale and/or hypotheses ## 14::Study procedures and/or analysis plans ## 15::Obtained results ## 16::Implementation of preregistered analyses ## 17::Validity and appropriateness of unregistered analyses ## 18::Interpretation of results ## 19::Conclusions of the study ## 20::Research transparency (e.g. data availability, quality of data curation etc.) ## 21::Ethical concerns ## 22::Other ^^ 22\"', '-4::1 ## -3::3 ## -2::5', 5, '\"Select one or more areas of the manuscript to which these issues applied:\"', '', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(47, 0, 2, '\"To what extent do you feel that you were granted too much, too little, or the right amount of power to shape the authors\' proposed study design at Stage 1?\"', '', '\"1::Too little power ## 2::About the right amount of power ## 3::Too much power\"', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(48, 0, 2, '\"Do you believe the editor made the correct editorial decision?\"', '', '\"1::Yes ## 2::No\"', '1::5 ## 2::1', 3, '', '', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(49, 0, 2, '\"Do you feel you received sufficient credit or other acknowledgment for your review?\"', '', '\"-2::Yes ## -1::No ## 999::Prefer not to answer ## 777::Don\'t know / don\'t recall ## 888::N/A @@ -2 !! 0::I did not receive any credit or acknowledgement and I\'m ok with that ## 1::I received some form of credit or acknowledgement and it was sufficient ## 2::I received some form of credit or acknowledgement and it was insufficient ## 3::I received some form of credit or acknowledgement but I believe it was unnecessary or unwarranted ## 9991::Prefer not to answer ## 7771::Don\'t know / don\'t recall ~~ -1 !! 4::I did not receive any credit or acknowledgement and I\'m ok with that ## 5::I did not receive any credit or acknowledgement and I\'m NOT ok with that ## 9992::Prefer not to answer ## 7772::Don\'t know / don\'t recall\"', '0::5 ## 1::5 ## 2::1 ## 3::3 ## 4::5 ## 5::1', 4, '', '', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(50, 0, 2, '\"Roughly how long did you spend preparing your review, including time taken to read the manuscript?\"', '', '\"1::Less than an hour ## 2::About an hour ## 3::Several hours ## 4::About a day ## 5::Several days\"', '', 3, '', '', 3, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(51, 0, 2, '\"Would you review a Registered Report for <i>{journal_name}</i> again?\"', '', '\"1::Yes ## 2::No\"', '1::5 ## 2::1', 3, '', '', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(52, 0, 2, '\"Did your experience reviewing this manuscript change your view about potentially <u>submitting</u> a Registered Report to <i>{journal_name}</i> as an author?\"', '', '\"1::Yes: LESS likely to submit a RR to <i>{journal_name}</i> ## 2::Yes: MORE likely submit a RR to <i>{journal_name}</i> ## 3::No\"', '1::1 ## 2::5 ## 3::3', 3, '', '', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(53, 0, 2, '\"To what extent was the approved Stage 1 manuscript available for comparison with the submitted Stage 2 manuscript?\"', '', '\"1::The approved Stage 1 manuscript was publicly registered and the URL was easy to find in the Stage 2 manuscript ## 2::The approved Stage 1 manuscript did NOT appear to publicly registered but it was sent to me by the editor during the Stage 2 review process ## 3::The approved Stage 1 manuscript was NOT made available to me in any form during Stage 2 review.\"', '1::5 ## 2::3 ## 3::1', 3, '', '', 2, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(58, 0, 1, '\"Any additional general comments about this Stage {stage_number} process\"', '', '', '', 6, '\"N.B. Any comments you provide here won\'t be displayed publicly or published with your review, but may be used for research purposes.\"', '', 5, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(59, 0, 2, '\"Any additional general comments about this Stage {stage_number} process\"', '', '', '', 6, '\"N.B. Any comments you provide here won\'t be displayed publicly or published with your review, but may be used for research purposes.\"', '', 5, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(66, 0, 1, '\"Across all journals, on how many RR submissions have you been an author/co-author?\"', '', '\"1::1 ## 2::2 ## 3::3 ## 4::4 ## 5::5 ## 6::6 ## 7::7 ## 8::8 ## 9::9 ## 11::10+ ## 999::Prefer not to answer ## 777::Don\'t know/don\'t recall ## 888::N/A\"', '', 2, '', 'Registered Reports that have been submitted at Stage 1 <i>and</i> Stage 2 count as one manuscript. Registered Reports that have <i>only</i> been submitted at Stage 1 also count as one manuscript. If you have been involved as an author at either Stage 1 <i>or</i> Stage 2 (but not both), you can count each case as a single manuscript.', 3, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(67, 0, 2, '\"Across all journals, how many RR manuscripts have you reviewed?\"', '', '\"1::1 ## 2::2 ## 3::3 ## 4::4 ## 5::5 ## 6::6 ## 7::7 ## 8::8 ## 9::9 ## 11::10+ ## 999::Prefer not to answer ## 777::Don\'t know/don\'t recall ## 888::N/A\"', '', 2, '', 'Registered Reports that you have reviewed at Stage 1 <i>and</i> Stage 2 count as one manuscript. Registered Reports that you have reviewed <i>only</i> at Stage 1 also count as one manuscript. If you have served as a reviewer at either Stage 1 <i>or</i> Stage 2 (but not both), you can count each case as a single manuscript.', 3, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(69, 0, 1, '\"Have you ever submitted a regular empirical article (other than a Registered Report) to <i>{journal_name}</i>? @@ Please compare your experience submitting a Stage {stage_number} Registered Report to <i>{journal_name}</i> with your previous experience(s) at <i>{journal_name}</i> submitting a regular empirical article. If you have previously submitted multiple regular articles to <i>{journal_name}</i>, please compare your Registered Report experience with your overall or \\\"average\\\" experience with regular empirical articles at <i>{journal_name}</i>.\"', '', '\"-2::Yes ## -1::No ## 999::Prefer not to answer ## 777::Don\'t know / don\'t recall ## 888::N/A @@ -2 !! 1::Registered Report experience much better ## 2::Registered Report experience slightly better ## 3::Registered Report experience and regular article experience about the same ## 4::Registered Report experience slightly worse ## 5::Registered Report experience much worse ## 999::Prefer not to answer ## 777::Don\'t know / don\'t recall\"', '1::5 ## 2::4 ## 3::3 ## 4::2 ## 5::1', 4, '', '', 2, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(70, 0, 2, '\"Have you ever reviewed a regular empirical article (other than a Registered Report) for <i>{journal_name}</i>? @@ Please compare your experience reviewing a Stage {stage_number} Registered Report at <i>{journal_name}</i> with your previous experience(s) at <i>{journal_name}</i> reviewing a regular empirical article. If you have previously reviewed multiple regular articles for <i>{journal_name}</i>, please compare your Registered Report experience with your overall or \\\"average\\\" experience reviewing regular empirical articles at <i>{journal_name}</i>.\"', '', '\"-2::Yes ## -1::No ## 999::Prefer not to answer ## 777::Don\'t know / don\'t recall ## 888::N/A @@ -2 !! 1::Registered Report experience much better ## 2::Registered Report experience slightly better ## 3::Registered Report experience and regular article experience about the same ## 4::Registered Report experience slightly worse ## 5::Registered Report experience much worse ## 999::Prefer not to answer ## 777::Don\'t know / don\'t recall\"', '1::5 ## 2::4 ## 3::3 ## 4::2 ## 5::1', 4, '', '', 2, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(71, 0, 1, '\"Prior to your experience at <i>{journal_name}</i>, what was your opinion of Registered Reports overall?\"', '', '\"1::Positive ## 2::Neutral / no opinion ## 3::Negative\"', '', 3, '', '', 4, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(72, 0, 1, '\"Has your experience at <i>{journal_name}</i> changed your opinion of RRs overall?\"', '', '\"1::Yes, my opinion is now more positive ## 2::Yes, my opinion is now more negative ## 3::No, my opinion is unchanged\"', '1::5 ## 2::1 ## 3::3', 3, '', '', 2, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(73, 0, 2, '\"Prior to your experience at <i>{journal_name}</i>, what was your opinion of Registered Reports overall?\"', '', '\"1::Positive ## 2::Neutral / no opinion ## 3::Negative\"', '', 3, '', '', 4, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(74, 0, 2, '\"Has your experience at <i>{journal_name}</i> changed your opinion of RRs overall?\"', '', '\"1::Yes, my opinion is now more positive ## 2::Yes, my opinion is now more negative ## 3::No, my opinion is unchanged\"', '1::5 ## 2::1 ## 3::3', 3, '', '', 2, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(76, 1, 2, '\"What was your final peer review recommendation for the manuscript?\"', '', '\"1::Grant in-principle acceptance (IPA) ## 2::Revise ## 3::Reject\"', '', 3, '\"Please add details in the \'optional comment\' section below this question to give details of decisions at earlier rounds of peer review.\"', '', 3, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(77, 0, 2, '\"What was the decision for the manuscript (if you know it)?\"', '', '\"1::Granted in-principle acceptance (IPA) ## 2::Revise ## 3::Reject\"', '', 3, '', '', 3, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(78, 1, 2, '\"What was your final peer review recommendation for the manuscript?\"', '', '\"1::Accept ## 2::Revise ## 3::Reject\"', '', 3, '\"Please add details in the \'optional comment\' section below this question to give details of decisions at earlier rounds of peer review.\"', '', 3, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(79, 0, 2, '\"What was the decision for the manuscript (if you know it)?\"', '', '\"1::Accept ## 2::Revise ## 3::Reject\"', '', 3, '', '', 3, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(80, 1, 1, '\"Are you, or have ever been, a member of staff at <i>{journal_name}</i>?\"', '', '\"1::Yes ## 2::No\"', '', 3, '', '', 3, 1, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1),
(81, 1, 2, '\"Are you, or have ever been, a member of staff at <i>{journal_name}</i>?\"', '', '\"1::Yes ## 2::No\"', '', 3, '', '', 3, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(93, 0, 1, '\"What was the primary reason for you withdrawing your Stage 1 manuscript before IPA was given?\"', '', '\"1::One or more rounds of peer review were too slow ## 2::The changes requested by the reviewers or editor were either infeasible or inconsistent with our plans ## 3::We identified a more suitable journal or platform for submission ## 4::Resource limitations or other issues meant we could no longer conduct the research, irrespective of comments from reviewers ## 5::Other (please specify in \'optional comment\' below)\"', '', 3, '', '', 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(94, 0, 1, '\"How many rounds of peer review were there at Stage {stage_number}?\"', '', '\"0::0 ## 1::1 ## 2::2 ## 3::3 ## 4::4 ## 5::5 ## 6::6 ## 7::7 ## 8::8 ## 9::9 ## 10::10+ ## 999::Prefer not to answer ## 777::Don\'t know/don\'t recall\"', '', 2, '', '', 2, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 1),
(95, 0, 2, '\"How many rounds of peer review were there at Stage {stage_number}?\"', '', '\"0::0 ## 1::1 ## 2::2 ## 3::3 ## 4::4 ## 5::5 ## 6::6 ## 7::7 ## 8::8 ## 9::9 ## 10::10+ ## 999::Prefer not to answer ## 777::Don\'t know/don\'t recall\"', '', 2, '', '', 2, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `feedback_question_categories`
--

CREATE TABLE `feedback_question_categories` (
  `category_id` tinyint(4) NOT NULL,
  `category_title` tinytext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `category_header_author` varchar(500) DEFAULT NULL,
  `category_header_reviewer` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback_question_categories`
--

INSERT INTO `feedback_question_categories` (`category_id`, `category_title`, `category_header_author`, `category_header_reviewer`) VALUES
(1, 'Speed', '<b>Please taken into account only the time the journal was handling your manuscript, not the time you spent revising it or conducting the research itself</b>', NULL),
(2, 'Quality', '<b>In reflecting on the quality of your experience of the peer review process, consider e.g. constructiveness, helpfulness, usefulness of reviews, editors input, clarity.<br /><br />N.B. Quality is not necessarily synonymous with quantity.</b>', '<b>In reflecting on the quality of your experience of the peer review process, consider e.g. constructiveness, helpfulness, editors input, clarity.<br /><br />N.B. Quality is not necessarily synonymous with quantity.</b>'),
(3, 'Additional questions', NULL, NULL),
(4, 'Comparison', NULL, NULL),
(5, 'Comments', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback_question_type`
--

CREATE TABLE `feedback_question_type` (
  `question_type_id` tinyint(4) NOT NULL,
  `question_type` tinytext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback_question_type`
--

INSERT INTO `feedback_question_type` (`question_type_id`, `question_type`) VALUES
(1, '5 star'),
(2, 'dropdown'),
(3, 'radio'),
(4, 'radio conditional'),
(5, 'radio checkbox conditional'),
(6, 'textbox'),
(7, 'matrix');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_responses`
--

CREATE TABLE `feedback_responses` (
  `response_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `sub_question_id` tinyint(4) DEFAULT NULL,
  `question_response_value` smallint(4) DEFAULT NULL,
  `question_response_text` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_reviewer_roles`
--

CREATE TABLE `feedback_reviewer_roles` (
  `role_id` tinyint(4) NOT NULL,
  `role_text` tinytext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback_reviewer_roles`
--

INSERT INTO `feedback_reviewer_roles` (`role_id`, `role_text`) VALUES
(1, 'Author'),
(2, 'Reviewer');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_reviews`
--

CREATE TABLE `feedback_reviews` (
  `review_id` int(11) NOT NULL,
  `review_uuid` binary(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_ref` tinytext NOT NULL,
  `stage_id` int(11) NOT NULL,
  `sub_stage_id` int(11) DEFAULT NULL,
  `journal_id` int(11) NOT NULL,
  `start_date` smallint(4) NOT NULL,
  `end_date` smallint(4) NOT NULL,
  `share_date` tinyint(1) NOT NULL,
  `academic_role` tinyint(1) DEFAULT NULL,
  `academic_role_text` varchar(255) DEFAULT NULL,
  `doi` tinytext DEFAULT NULL,
  `review_json` text NOT NULL,
  `role_type` tinyint(4) NOT NULL,
  `review_link_id` int(11) DEFAULT NULL,
  `review_source` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` char(45) DEFAULT NULL,
  `user_agent` text NOT NULL,
  `completed_on` datetime NOT NULL,
  `completion_time` mediumint(9) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_stages`
--

CREATE TABLE `feedback_stages` (
  `sub_stage_id` int(11) NOT NULL,
  `stage_order` tinyint(4) NOT NULL,
  `stage` tinyint(4) NOT NULL,
  `stage_label` char(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `stage_text` tinytext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `stage_helptext` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `stage_tooltip` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `stage_both` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback_stages`
--

INSERT INTO `feedback_stages` (`sub_stage_id`, `stage_order`, `stage`, `stage_label`, `stage_text`, `stage_helptext`, `stage_tooltip`, `stage_both`) VALUES
(1, 1, 1, '1.1', 'Desk rejected prior to peer review', '', '', 0),
(2, 2, 1, '1.2', 'Rejected after one or more rounds of specialist peer review', '', '', 0),
(3, 4, 1, '1.4', 'Granted IPA and proceeded to Stage 2', '', '', 1),
(4, 5, 1, '1.5', 'Granted IPA but then withdrawn by authors prior to Stage 2', '', '', 1),
(5, 6, 2, '2.1', 'Desk rejected prior to peer review', '', '', 1),
(6, 7, 2, '2.2', 'Rejected after one or more rounds of specialist peer review', '', '', 1),
(7, 8, 2, '2.3', 'Accepted', '', '', 1),
(8, 3, 1, '1.3', 'Withdrawn by author before IPA granted', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `journals`
--

CREATE TABLE `journals` (
  `journal_id` int(11) NOT NULL,
  `journal_order` int(11) NOT NULL DEFAULT 0,
  `journal_name` varchar(500) DEFAULT NULL,
  `journal_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Scientific Discipline` varchar(26) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Psychology Area` varchar(118) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Replication Studies` varchar(31) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Analysis of existing datasets` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Meta-Analyses` varchar(13) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Analysis of existing datasets 2` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Meta-Analyses 2` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Qualitative Research` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Post-Study Peer Review` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Publishes Accepted Protocols` varchar(28) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Replication Studies 2` varchar(31) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Structured criteria for editorial decisions` varchar(43) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Unregistered Analyses Allowed` varchar(29) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Unregistered Preliminary Studies` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Word Limits` varchar(114) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `h-index` varchar(7) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Open Access Policy` varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `APCs` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `journals`
--

INSERT INTO `journals` (`journal_id`, `journal_order`, `journal_name`, `journal_url`, `Scientific Discipline`, `Psychology Area`, `Replication Studies`, `Analysis of existing datasets`, `Meta-Analyses`, `Analysis of existing datasets 2`, `Meta-Analyses 2`, `Qualitative Research`, `Post-Study Peer Review`, `Publishes Accepted Protocols`, `Replication Studies 2`, `Structured criteria for editorial decisions`, `Unregistered Analyses Allowed`, `Unregistered Preliminary Studies`, `Word Limits`, `h-index`, `Open Access Policy`, `APCs`) VALUES
(1, 0, 'AAS Open Research', 'https://aasopenresearch.org', 'General', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '920 GBP'),
(2, 0, 'Academia Journal of Stroke', 'https://wrightacademia.org/ajs.php', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', 'Yes', 'null'),
(3, 0, 'Academy of Management Discoveries', 'https://aom.org/research/journals/discoveries', 'Unknown', 'Unknown', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(4, 0, 'Acta Psychologica', 'https://www.journals.elsevier.com/acta-psychologica/', 'Psychology', 'Cognitive; Cognitive; Perception; Cognitive; Memory', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', '88', 'Yes', '1500 USD'),
(5, 0, 'Adaptive Human Behavior and Physiology', 'https://www.springer.com/journal/40750/', 'Psychology', 'Biological', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '1,500 extended abstract for consideration', '13', 'Hybrid', '2780 USD'),
(6, 0, 'Addiction Research & Theory', 'https://www.tandfonline.com/toc/iart20/current', 'Psychology', 'Human Behavior; Social & Personality; Social', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', '45', 'Hybrid', '3500 USD'),
(7, 0, 'Advances in Methods and Practices in Psychological Science', 'https://www.psychologicalscience.org/publications/ampps', 'Psychology', 'Research Methods and Statistics', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', 'Soft word limits (not strictly enforced):  3,000 - 5,000 for RR <1,500 Intro, 500-1,000 general discussion for RRR', 'null', 'Yes', '1000 USD'),
(8, 0, 'AERA Open', 'http://journals.sagepub.com/articles/ero', 'Psychology', 'Developmental; Learning; Developmental', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Yes', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '8,000 words', 'null', 'Yes', '700 USD'),
(9, 0, 'Affective Science', 'https://www.springer.com/psychology/journal/42761', 'Psychology', 'Social & Personality; Emotion; Biological; Biopsychology/Neuroscience', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '2,000 words (Intro and discussion for research articles)', 'null', 'Hybrid', '2780 USD'),
(10, 0, 'Alimentary Pharmacology & Therapeutics', 'https://www.wiley.com/en-us/Alimentary+Pharmacology+%26+Therapeutics-p-9780J', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', '12-20 pages double-spaced', '155', 'Hybrid', '4500 USD'),
(11, 0, 'American Journal of Political Science', 'https://ajps.org', 'Political Science', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '10,000 words', '136', 'Hybrid', '3650 USD'),
(12, 0, 'American Political Science Review', 'http://www.apsanet.org/apsr', 'Political Science', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '12,000 words', '148', 'Hybrid', '3255 USD'),
(13, 0, 'American Politics Research', 'http://apr.sagepub.com', 'Political Science', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', 'Unknown', '41', 'Hybrid', 'null'),
(14, 0, 'AMRC Open Research', 'https://amrcopenresearch.org', 'General', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '680 GBP'),
(15, 0, 'Analyses of Social Issues and Public Policy', 'https://spssi.onlinelibrary.wiley.com/journal/15302415', 'Psychology', 'Social', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(16, 0, 'Animal Behavior and Cognition', 'http://animalbehaviorandcognition.org', 'Psychology', 'Biological; Cognitive', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Ambiguous', 'Unknown', 'null', 'Yes', 'null'),
(17, 0, 'Animals', 'https://www.mdpi.com/journal/animals', 'Zoology', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', '23', 'Yes', '1600 CHF'),
(18, 0, 'Applied Cognitive Psychology', 'https://onlinelibrary.wiley.com/journal/10990720', 'Psychology', 'Cognitive; Developmental; Cognitive; Memory', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', '8,500 words', '81', 'Hybrid', '2500 USD'),
(19, 0, 'Archive for the Psychology of Religion', 'https://uk.sagepub.com/en-gb/eur/archive-for-the-psychology-of-religion/journal203533', 'Psychology', 'Religion', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', '12,000 words', '14', 'Hybrid', 'null'),
(20, 0, 'Assessment', 'https://journals.sagepub.com/home/asm', 'Clinical', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '30 pages', '74', 'Hybrid', 'null'),
(21, 0, 'Attention, Perception & Psychophysics', 'http://www.springer.com/psychology/cognitive+psychology/journal/13414', 'Psychology', 'Biological; Neuroscience; Cognitive', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', '3,000 words', '100', 'Unknown', 'Unknown'),
(22, 0, 'Auditory Perception & Cognition', 'https://www.tandfonline.com/toc/rpac20/current', 'Psychology', 'null', 'Yes', 'Yes, proof of no access to data prior to study required', 'Unknown', 'Yes, proof of no access to data prior to study required', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Yes, separate section', 'Yes', 'No Limit', 'null', 'Hybrid', '800 USD'),
(23, 0, 'Basic and Applied Social Psychology', 'https://www.tandfonline.com/journals/hbas20', 'Psychology', 'Social ', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(24, 0, 'Behavioral Neuroscience', 'http://www.apa.org/pubs/journals/bne/', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '3,250 for brief communications, no limit for full length articles', '125', 'Hybrid', '3000 USD'),
(25, 0, 'Bilingualism: Language and Cognition', 'https://www.cambridge.org/core/journals/bilingualism-language-and-cognition', 'Psychology', 'Linguistics; Biological; Biopsychology/Neuroscience; Cognitive', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '9,00 words', '46', 'Hybrid', '3255 USD'),
(26, 0, 'Biolinguistics', 'https://www.biolinguistics.eu/index.php/biolinguistics', 'Linguistics', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', 'null'),
(27, 0, 'Biological Psychiatry: Global Open Science', 'https://www.journals.elsevier.com/biological-psychiatry-global-open-science/', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '3,000 for stage 1, 4,000 for stage 2', 'null', 'Yes', '3200 USD'),
(28, 0, 'BMC Biology', 'https://bmcbiol.biomedcentral.com', 'Biology', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '84', 'Yes', '1780 GBP'),
(29, 0, 'BMC Ecology', 'https://bmcecol.biomedcentral.com', 'Biology', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '35', 'Yes', '1570 GBP'),
(30, 0, 'BMC Medicine', 'https://bmcmedicine.biomedcentral.com', 'Biology/Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Yes', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '98', 'Yes', '1880 GBP'),
(31, 0, 'BMJ Open Science', 'http://openscience.bmj.com', 'Biology/Medicine', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Unknown', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', 'null', 'Yes', '1500 GBP'),
(32, 0, 'Brain and Behavior', 'https://onlinelibrary.wiley.com/journal/21579032', 'Psychology', 'Biological', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '24', 'Yes', '2700 USD'),
(33, 0, 'Brain and Cognition', 'https://www.journals.elsevier.com/brain-and-cognition', 'Psychology', 'Biological; Biopsychology/Neuroscience; Cognitive', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', '111', 'Hybrid', '2720 USD'),
(34, 0, 'Brain and Neuroscience Advances', 'https://journals.sagepub.com/home/bna', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '600 intro, 1,500 discussion', 'null', 'Yes', '1200 GBP'),
(35, 0, 'Brain Communications', 'https://academic.oup.com/braincomms', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '6,000 words', 'null', 'Yes', '2500 GBP'),
(36, 0, 'British Journal of Clinical Psychology', 'https://onlinelibrary.wiley.com/journal/20448260', 'Psychology', 'Mental & Physical Health', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', '5,000 words', '81', 'Hybrid', '3000 USD'),
(37, 0, 'British Journal of Developmental Psychology', 'https://onlinelibrary.wiley.com/journal/2044835x', 'Psychology', 'Developmental; Developmental; Learning', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '2,000 (brief reports); 5,000 words (full length)', '64', 'Hybrid', '2500 USD'),
(38, 0, 'British Journal of Educational Psychology', 'https://onlinelibrary.wiley.com/journal/20448279', 'Psychology', 'Developmental; Developmental; Learning', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000 for quantitative research, 6,000 for qualitative research', '78', 'Hybrid', '3000 USD'),
(39, 0, 'British Journal of General Practice', 'https://bjgp.org', 'General', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '2,500 words (main text(', '90', 'Hybrid', '2000 GBP'),
(40, 0, 'British Journal of Health Psychology', 'https://onlinelibrary.wiley.com/page/journal/20448287/bjhpregisteredreportsguidelines.htm', 'Psychology', 'Mental & Physical Health; Mental & Physical Health; Therapies; Mental & Physical Health; Stress, Lifestyle, and Health', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000 for quantitative research, 6,000 for qualitative research', '73', 'Hybrid', '3300 USD'),
(41, 0, 'British Journal of Mathematical and Statistical Psychology', 'https://onlinelibrary.wiley.com/journal/20448317', 'Psychology', 'Research Methods and Statistics', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '4,000 for Expert Tutorials; 5,000 words; 6,000 for Review Articles', '42', 'Hybrid', '2500 USD'),
(42, 0, 'British Journal of Psychology', 'https://onlinelibrary.wiley.com/journal/20448295', 'Psychology', 'Cognitive; Mental & Physical Health; Developmental', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '8,000 words', '76', 'Hybrid', '3100 USD'),
(43, 0, 'British Journal of Social Psychology', 'https://onlinelibrary.wiley.com/journal/20448309', 'Psychology', 'Social & Personality; Social', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '7,000 words', '84', 'Hybrid', '3200 USD'),
(44, 0, 'Buildings and Cities', 'https://journal-buildingscities.org', 'Unknown', 'Unknown', 'Yes', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown'),
(45, 0, 'Business & Information Systems Engineering: Human Computer Interaction and Social Computing', 'https://www.springer.com/journal/12599', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown'),
(46, 0, 'Campbell Systematic Reviews', 'https://onlinelibrary.wiley.com/journal/18911803', 'Unknown', 'Unknown', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'No', 'Yes', 'No', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(47, 0, 'Canadian Journal of Experimental Psychology', 'https://www.apa.org/pubs/journals/cep/index.aspx', 'Psychology', 'Cognitive; Cognitive; Perception; Cognitive; Memory', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', 'No mention', '53', 'Hybrid', '3000 USD'),
(48, 0, 'Canadian Journal of School Psychology', 'http://journals.sagepub.com/home/cjs', 'Psychology', 'Developmental; Learning', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '5,000 words', '21', 'Hybrid', '3000 USD'),
(49, 0, 'Cancer Medicine', 'https://onlinelibrary.wiley.com/journal/20457634', 'Biology/Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '27', 'Yes', '2430 USD'),
(50, 0, 'Cancer Reports', 'https://onlinelibrary.wiley.com/journal/25738348', 'Biology/Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'None', 'null', 'Yes', '2300 USD'),
(51, 0, 'Cancers', 'https://www.mdpi.com/journal/cancers', 'Medicine', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', '53', 'Yes', '2200 CHF'),
(52, 0, 'Cardiology Cases and Systematic Reviews', 'https://wrightacademia.org/ccsr.php', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', 'Yes', 'None'),
(53, 0, 'Chemistry and Ecology', 'https://www.tandfonline.com/journals/gche20', 'Chemistry; Ecology', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(54, 0, 'Child Development', 'https://www.srcd.org/research/journals/child-development', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '40 pages', '243', 'Hybrid', '2200 USD'),
(55, 0, 'Clinical Endocrinology', 'https://onlinelibrary.wiley.com/journal/13652265', 'Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', '3,500 words (original articles)', '132', 'Hybrid', '4000 USD'),
(56, 0, 'Clinical Otolaryngology', 'https://onlinelibrary.wiley.com/journal/17494486', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '2,500 words', '65', 'Hybrid', '2800 USD'),
(57, 0, 'Clinical Psychology in Europe', 'https://cpe.psychopen.eu', 'Psychology', 'Mental & Physical Health', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '4,500 words', 'null', 'Yes', 'null'),
(58, 0, 'Clocks and Sleep', 'http://www.mdpi.com/journal/clockssleep', 'Psychology', 'Biological; Biopsychology/Neuroscience; Mental & Physical Health', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Ambiguous', 'None', 'null', 'Yes', '1200 CHF'),
(59, 0, 'Cochrane Reviews', 'http://www.cochranelibrary.com/about/about-cochrane-systematic-reviews.html', 'Medicine', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '10,000 words', '212', 'Hybrid', '5000'),
(60, 0, 'Cogent Psychology', 'https://www.tandfonline.com/journals/oaps20', 'Unknown', 'Unknown', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(61, 0, 'Cognition and Emotion', 'http://www.tandfonline.com/toc/pcem20/current', 'Psychology', 'Social & Personality; Emotion; Cognitive; Social & Personality; Social', 'Yes', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Yes', 'Ambiguous', 'Ambiguous', '8,000 words', '107', 'Hybrid', '3500 USD'),
(62, 0, 'Cognitive Linguistics', 'https://www.degruyter.com/view/journals/cogl/cogl-overview.xml', 'Psychology', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '8,000-10,000 words for Stage 1, 12,000 words for Stage 2', '50', '?', 'null'),
(63, 0, 'Cognitive Research: Principles and Implications', 'http://cognitiveresearchjournal.springeropen.com', 'Psychology', 'Cognitive', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', 'null', 'Yes', '960 GBP'),
(64, 0, 'Collabra: Psychology', 'https://online.ucpress.edu/collabra', 'Unknown', 'Unknown', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(65, 0, 'Communication Research Reports', 'https://www.tandfonline.com/toc/rcrr20/current', 'Unknown', 'Unknown', 'Yes', 'No', 'Yes', 'No', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(66, 0, 'Comparative Political Studies', 'http://cps.sagepub.com', 'Politics', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '12,000 words', '85', '?', 'null'),
(67, 0, 'Comprehensive Results in Social Psychology', 'http://www.tandfonline.com/loi/rrsp20', 'Psychology', 'Social & Personality; Social', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', '40 pages', 'null', 'Hybrid', '2995 USD'),
(68, 0, 'Computational Communication Research', 'https://computationalcommunication.org/index.php/ccr', 'Communication', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '9,000 words', 'null', 'Yes', '1000 USD'),
(69, 0, 'Computational Psychiatry', 'https://cpsyjournal.org', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'Unknown', 'null', 'Unknown', 'Unknown'),
(70, 0, 'Computer Science Education', 'https://www.tandfonline.com/toc/NCSE20/current?utm_source=CPB&utm_medium=cms&utm_campaign=JPG15743', 'Education', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'Yes', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', 'up to 10,000 words', '29', 'Hybrid', '2995 USD'),
(71, 0, 'Consciousness and Cognition', 'https://www.journals.elsevier.com/consciousness-and-cognition', 'Psychology', 'Biological; Consciousness; Cognitive; Develpmental', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '14,000 words', '90', 'Hybrid', '2740 USD'),
(72, 0, 'Conservation Biology', 'https://onlinelibrary.wiley.com/journal/15231739', 'Biology', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '6,000 words', '213', 'Hybrid', '2000 USD'),
(73, 0, 'Cortex', 'http://www.journals.elsevier.com/cortex', 'Psychology', 'Biological; Biopsychology/Neuroscience; Cognitive; Mental & Physical Health', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'No', '93', 'Hybrid', '3150 USD'),
(74, 0, 'Crisis', 'https://us.hogrefe.com/products/journals/crisis', 'Psychology', 'Mental & Physical Health', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'No', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '3,500 words for stage 1 and 4,500 for stage 2', '45', 'Hybrid', '3000 USD'),
(75, 0, 'Developmental Cognitive Neuroscience', 'https://www.journals.elsevier.com/developmental-cognitive-neuroscience/', 'Psychology', 'Biological; Biopsychology/Neuroscience; Developmental; Cognitive', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '6,000 words', '47', 'Yes', '2500 USD'),
(76, 0, 'Developmental Science', 'http://onlinelibrary.wiley.com/journal/10.1111/(ISSN)1467-7687', 'Psychology', 'Developmental; Biological; Biopsychology/Neuroscience', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '8,000 words', '106', 'Hybrid', '3800 USD'),
(77, 0, 'Discourse Processes', 'https://www.tandfonline.com/toc/hdsp20/current', 'Psychology', 'Language; Social & Personality; Social; Developmental; Learning', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '35 double-spaces pages', '50', 'Hybrid', '2995 USD'),
(78, 0, 'Discover Chemical Engineering', 'https://www.springer.com/journal/43938', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(79, 0, 'Discover Energy', 'https://www.springer.com/journal/43937', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(80, 0, 'Discover Internet of Things', 'https://www.springer.com/journal/43926', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(81, 0, 'Discover Materials', 'https://www.springer.com/journal/43939', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(82, 0, 'Discover Oncology', 'https://www.springer.com/journal/12672', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(83, 0, 'Discover Social Science and Health', 'https://www.springer.com/journal/44155', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(84, 0, 'Discover Sustainability', 'https://www.springer.com/journal/43621', 'Sustainability', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No', 'null', 'Yes', '1290 USD'),
(85, 0, 'Discover Water', 'https://www.springer.com/journal/43832', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(86, 0, 'Drug and Alcohol Dependence', 'http://www.journals.elsevier.com/drug-and-alcohol-dependence', 'Medicine', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '4,000 words', '142', 'Hybrid', '3690 USD'),
(87, 0, 'Ecological Solutions and Evidence', 'https://besjournals.onlinelibrary.wiley.com/journal/26888319', 'Ecology', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '7,000 words', 'null', 'Yes', '2010 USD'),
(88, 0, 'Ecology and Evolution', 'https://onlinelibrary.wiley.com/journal/20457758', 'Ecology', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'None', '41', 'Yes', '1950 USD'),
(89, 0, 'eLife', 'https://elifesciences.org', 'Biology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Ambiguous', '5,000 for research articles', '74', 'Yes', '2500 USD'),
(90, 0, 'Emerald Open Research', 'https://emeraldopenresearch.com', 'Sustainability', 'null', 'Ambiguous', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '1200 - 2500 USD'),
(91, 0, 'Emerging Adulthood', 'https://us.sagepub.com/en-us/nam/emerging-adulthood/journal202127', 'Psychology', 'Developmental; Social & Personality; Social', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'No', 'Ambiguous', 'Yes', 'Yes', 'Ambiguous', '6,000 words', '14', 'Hybrid', '3000 USD'),
(92, 0, 'Endocrinology, Diabetes & Metabolism', 'https://onlinelibrary.wiley.com/journal/23989238', 'Biology/Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', 'null', 'Yes', '2000 USD'),
(93, 0, 'eNeuro', 'http://www.eneuro.org', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Intro: 750 words Discussion: 3,000 words', '8', 'Yes', '2925 USD'),
(94, 0, 'Entrepreneurship Theory and Practice', 'https://journals.sagepub.com/home/etp', 'Business', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '20 pages', '140', 'Hybrid', '3000 USD'),
(95, 0, 'Environment International', 'https://www.journals.elsevier.com/environment-international/', 'Environmental Studies', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Yes', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', 'No limit', '157', 'Yes', '3500 USD'),
(96, 0, 'Equine Veterinary Journal', 'https://onlinelibrary.wiley.com/journal/20423306', 'Veterinary', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Yes', 'Yes', '4,000 words', '79', 'Hybrid', '3300 USD'),
(97, 0, 'European Journal of Cancer Care', 'https://onlinelibrary.wiley.com/journal/13652354', 'Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '4,000 words', '59', 'Hybrid', '4300 USD'),
(98, 0, 'European Journal of Neuroscience', 'http://onlinelibrary.wiley.com/journal/10.1111/(ISSN)1460-9568', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '185', 'Hybrid', '3000 USD'),
(99, 0, 'European Journal of Personality', 'http://onlinelibrary.wiley.com/journal/10.1002/(ISSN)1099-0984', 'Psychology', 'Social & Personality; Social & Personality; Social; Social & Personality; Personality', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'None', '68', 'Hybrid', '3000 USD'),
(100, 0, 'European Journal of Psychological Assessment', 'https://us.hogrefe.com/products/journals/european-journal-of-psychological-assessment', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '2,500 words for Brief Reports; 5,000 words for Original Articles; 7,500 words for Multistudy reports', '52', 'Hybrid', '2500 - 3000 USD'),
(101, 0, 'European Journal of Psychotraumatology', 'https://www.tandfonline.com/toc/zept20/current', 'Psychology', 'Mental & Physical Health; Mental & Physical Health; Stress, Lifestyle, and Health; Mental & Physical Health; Therapies', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '6,000 words', '28', 'Yes', '1450 EUR'),
(102, 0, 'European Journal of Social Psychology', 'https://onlinelibrary.wiley.com/journal/10990992', 'Psychology', 'Social & Personality; Social', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', '3,000–10,000 words', '106', 'Hybrid', '3000 USD'),
(103, 0, 'Evolution and Human Behavior', 'https://www.ehbonline.org', 'Psychology', 'Biological; Cognitive', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '8,000 words', '96', 'Hybrid', '3000 USD'),
(104, 0, 'Evolutionary Human Sciences', 'http://www.cambridge.org', 'Biology', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000 words', 'null', 'Yes', '1700 GBP'),
(105, 0, 'Exceptional Children', 'https://us.sagepub.com/en-us/nam/journal/exceptional-children#submission-guidelines', 'Psychology', 'Developmental; Developmental; Learning', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '28–35 pages', '78', '?', 'null'),
(106, 0, 'Experimental Economics', 'https://link.springer.com/journal/10683', 'Economics', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', '51', 'Hybrid', '2780 USD'),
(107, 0, 'Experimental Physiology', 'https://physoc.onlinelibrary.wiley.com/journal/1469445x', 'Physiology', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '6,000 words', '87', 'Hybrid', '3600 USD'),
(108, 0, 'Experimental Psychology', 'https://us.hogrefe.com/products/journals/exppsy', 'Psychology', 'Cognitive', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'Unknown', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000 words for one or two experiments; 10,000 words for more than two experiments', '49', 'Hybrid', '3000 USD'),
(109, 0, 'F1000Research', 'https://f1000research.com', 'Biology', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'None', '21', 'Yes', '1350 USD'),
(110, 0, 'Fertility and Sterility', 'https://www.fertstert.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '3,500 words', '190', 'Hybrid', '3500 USD'),
(111, 0, 'Frontiers in Cognition', 'https://www.frontiersin.org/journals/cognition/', 'Psychology', 'Cognitive', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(112, 0, 'Frontiers in Communication', 'https://www.frontiersin.org/journals/communication', 'Communication', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', 'null', 'Yes', '950 USD'),
(113, 0, 'Frontiers in Communications and Networks', 'https://www.frontiersin.org/journals/communications-and-networks#', 'Communications', 'null', 'Yes', 'Yes', 'Unknown', 'Yes', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Yes, separate section', 'Yes', '3,000 for Stage 1, 12,000 for Stage 2', 'null', 'Yes', '2950 USD'),
(114, 0, 'Frontiers in Computer Science', 'https://www.frontiersin.org/journals/computer-science', 'Computer Science', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', 'null', 'Yes', '1150 USD'),
(115, 0, 'Frontiers in Education', 'https://www.frontiersin.org/journals/education', 'Education', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', 'null', 'Yes', '950 USD'),
(116, 0, 'Frontiers in Genetics', 'https://www.frontiersin.org/journals/genetics#article-types', 'Biology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for Stage 1, 12,000 for Stage 2', '69', 'Yes', '2490 USD'),
(117, 0, 'Frontiers in Neuroscience', 'https://www.frontiersin.org/journals/neuroscience', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', '71', 'Yes', '2950 USD'),
(118, 0, 'Frontiers in Nutrition', 'https://www.frontiersin.org/journals/nutrition', 'Nutrition', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', 'null', 'Yes', '1150 USD'),
(119, 0, 'Frontiers in Plant Science', 'https://www.frontiersin.org/journals/plant-science#', 'Biology', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '12,000 words', '101', 'Yes', '2950 USD'),
(120, 0, 'Frontiers in Psychiatry', 'https://www.frontiersin.org/journals/psychiatry', 'Psychology', 'Mental & Physical Health; Mental & Physical Health; Therapies', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', '52', 'Yes', '1900 USD'),
(121, 0, 'Frontiers in Psychology', 'https://www.frontiersin.org/journals/psychology', 'Psychology', 'Cognitive; Cognitive; Perception', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', '81', 'Yes', '2950 USD'),
(122, 0, 'Frontiers in Sociology', 'https://www.frontiersin.org/journals/sociology', 'Sociology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', 'null', 'Yes', '950 USD'),
(123, 0, 'Frontiers in Sports and Active Living', 'https://www.frontiersin.org/journals/sports-and-active-living', 'Sports', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', 'null', 'Yes', '1900 USD'),
(124, 0, 'Frontiers in Virtual Reality', 'https://www.frontiersin.org/journals/virtual-reality', 'Technology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '3,000 for stage 1; 12,000 for stage 2', 'null', 'Yes', '950 USD'),
(125, 0, 'Gates Open Research', 'https://gatesopenresearch.org', 'General', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '1150 USD'),
(126, 0, 'Gesture', 'https://benjamins.com/#catalog/journals/gest/main', 'Psychology', 'Cognitive; Social & Personality; Developmental', 'Ambiguous', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'Unknown', '10,000 words', '22', 'Hybrid', '1800 EUR'),
(127, 0, 'Gifted Child Quarterly', 'http://journals.sagepub.com/home/gcq', 'Psychology', 'Developmental; Developmental; Learning', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', '36 pages', '37', 'Hybrid', '1000 USD'),
(128, 0, 'Glossa Psycholinguistics', 'https://escholarship.org/uc/glossapsycholinguistics', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown'),
(129, 0, 'Health Psychology Bulletin', 'http://www.healthpsychologybulletin.com/', 'Psychology', 'Mental & Physical Health; Biological', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Yes', 'Yes', 'Ambiguous', 'Ambiguous', '4,000 words', 'null', 'Yes', '500 EUR'),
(130, 0, 'HRB Open Research', 'https://hrbopenresearch.org', 'Medicine', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '859 EUR'),
(131, 0, 'Human Behavior and Emerging Technologies', 'https://www.onlinelibrary.wiley.com/journal/25781863', 'Psychology', 'Biological; Cognitive; Social & Personality; Social', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', 'Hybrid', '2500 USD'),
(132, 0, 'Human Movement Science', 'http://www.journals.elsevier.com/human-movement-science/', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'None', '76', 'Hybrid', '3100 USD'),
(133, 0, 'Human Resource Management Journal', 'https://onlinelibrary.wiley.com/journal/17488583', 'Management', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '4,000 words', '67', 'Hybrid', '3700 USD'),
(134, 0, 'i-Perception', 'https://uk.sagepub.com/en-gb/eur/i-perception/journal202441#description', 'Psychology', 'Cognitive; Perception', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Yes', 'No limit', '22', 'Yes', '530 GBP'),
(135, 0, 'Immunity, Inflammation and Disease', 'https://onlinelibrary.wiley.com/journal/20504527', 'Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '3', 'Yes', '2200 USD'),
(136, 0, 'Infancy', 'http://onlinelibrary.wiley.com/journal/10.1111/(ISSN)1532-7078', 'Psychology', 'Developmental; Mental & Physical Health; Abnormal; Cognitive', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '10,000 words', '56', 'Hybrid', '3000 USD'),
(137, 0, 'Infant and Child Development', 'https://onlinelibrary.wiley.com/journal/15227219', 'Psychology', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Yes', 'No', 'Ambiguous', 'Yes', 'No', '53', 'Hybrid', '1667 USD'),
(138, 0, 'Infant Behavior and Development', 'https://www.journals.elsevier.com/infant-behavior-and-development', 'Psychology', 'Developmental; Cognitive; Social & Personality; Emotion', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '4,000 - 10,000 words (Original Research Article)', '71', 'Hybrid', '2700 USD'),
(139, 0, 'Interactive Journal of Medical Research', 'http://www.i-jmr.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(140, 0, 'Interdisciplinary Perspectives on the Built Environment', 'https://ipbe.innorenew.eu/ipbe', 'Sustainability', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '8,000 words', 'null', 'Yes', 'None'),
(141, 0, 'International Journal of Eating Disorders', 'https://onlinelibrary.wiley.com/journal/1098108x', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '3,000 words', '126', 'Hybrid', '4200 USD'),
(142, 0, 'International Journal of Psychophysiology', 'http://www.journals.elsevier.com/international-journal-of-psychophysiology', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '106', 'Hybrid', '2680 USD'),
(143, 0, 'International Journal of Selection and Assessment', 'https://onlinelibrary.wiley.com/journal/14682389', 'Management', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '15-20 Double-spaced pages', '54', 'Hybrid', '1667 USD'),
(144, 0, 'International Review of Social Psychology', 'https://www.rips-irsp.com', 'Psychology', 'Social & Personality; Social', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '10,000 words', '13', 'Yes', 'null'),
(145, 0, 'Japanese Journal of Political Science', 'https://www.cambridge.org/core/journals/japanese-journal-of-political-science', 'Political Science', 'null', 'Unknown', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'No', 'Unknown', 'No', 'Ambiguous', 'Yes', '4,000 - 8,000 words', '10', 'Hybrid', '3255 USD'),
(146, 0, 'JMIR Aging', 'http://aging.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '1500 USD'),
(147, 0, 'JMIR Bioinformatics and Biotechnology', 'http://bioinform.jmir.org', 'Biology', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', 'None'),
(148, 0, 'JMIR Biomedical Engineering', 'http://biomedeng.jmir.org', 'Biology/Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(149, 0, 'JMIR Cancer', 'http://cancer.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(150, 0, 'JMIR Cardio', 'http://cardio.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '1500 USD'),
(151, 0, 'JMIR Challenges', 'http://challenges.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', 'None'),
(152, 0, 'JMIR Data', 'http://data.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '1500 USD'),
(153, 0, 'JMIR Dermatology', 'http://derma.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', 'None'),
(154, 0, 'JMIR Diabetes', 'http://diabetes.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(155, 0, 'JMIR Formative Research', 'http://formative.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '1900 USD'),
(156, 0, 'JMIR Human Factors', 'http://humanfactors.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(157, 0, 'JMIR Medical Education', 'http://mededu.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(158, 0, 'JMIR Medical Informatics', 'http://medinform.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(159, 0, 'JMIR Mental Health', 'http://mental.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(160, 0, 'JMIR mHealth and uHealth', 'http://mhealth.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1900 USD'),
(161, 0, 'JMIR Nursing', 'https://nursing.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '1500 USD'),
(162, 0, 'JMIR Pediatrics and Parenting', 'http://pediatrics.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '1500 USD'),
(163, 0, 'JMIR Perioperative Medicine', 'http://periop.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', 'None'),
(164, 0, 'JMIR Preprints', 'http://preprints.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', 'None'),
(165, 0, 'JMIR Public Health and Surveillance', 'http://publichealth.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(166, 0, 'JMIR Rehabilitation and Assistive Technologies', 'http://rehab.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(167, 0, 'JMIR Serious Games', 'http://games.jmir.org', 'General', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', 'null', 'Yes', '1500 USD'),
(168, 0, 'Journal for Reproducibility in Neuroscience', 'https://jrn.epistemehealth.com', 'Unknown', 'Unknown', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(169, 0, 'Journal of Accounting Research', 'http://onlinelibrary.wiley.com/journal/10.1111/(ISSN)1475-679X', 'Accounting', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '113', 'Yes', 'null'),
(170, 0, 'Journal of Advanced Academics', 'https://us.sagepub.com/en-us/nam/journal/journal-advanced-academics#submission-guidelines', 'Education', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '10-40 pages double-spaced', 'null', 'Hybrid', '3000 USD'),
(171, 0, 'Journal of Business and Psychology', 'http://link.springer.com/journal/10869', 'Psychology', 'Business; Social & Personality; Social', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '15-25 pages for Stage 1', '58', 'Hybrid', '2780 USD'),
(172, 0, 'Journal of Business Ethics', 'http://www.springer.com/philosophy/ethics+and+moral+philosophy/journal/10551', 'Business/Ethics', 'null', 'Ambiguous', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '12,000 words', '132', 'Hybrid', '3280 USD'),
(173, 0, 'Journal of Child Language', 'https://www.cambridge.org/core/journals/journal-of-child-language', 'Psychology', 'Developmental; Developmental; Learning; Linguistic', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '10,000 words', '62', 'Hybrid', '3255 USD'),
(174, 0, 'Journal of Child Psychology and Psychiatry (JCPP) Advances', 'https://www.acamh.org/category/journal/jcppadvances/', 'Psychology', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '6,000 words', 'null', 'Yes', 'None'),
(175, 0, 'Journal of Clinical Medicine', 'https://www.mdpi.com/journal/jcm', 'Medicine', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No limit', 'null', 'Yes', '2200 CHF'),
(176, 0, 'Journal of Clinical Nursing', 'https://onlinelibrary.wiley.com/journal/13652702', 'Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '8,000 words', '82', 'Hybrid', '2750 USD'),
(177, 0, 'Journal of Cognition', 'http://www.journalofcognition.org', 'Psychology', 'Cognitive; Cognitive; Memory; Cognitive; Perception', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '8,000 words', 'null', 'Yes', '1150 EUR'),
(178, 0, 'Journal of Cognition and Development', 'https://www.tandfonline.com/toc/hjcd20/current', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '40 pages', '53', 'Hybrid', '2995 USD'),
(179, 0, 'Journal of Cognitive Enhancement', 'http://www.springer.com/psychology/cognitive+psychology/journal/41465', 'Psychology', 'Cognitive; Cognitive; Perception; Cognitive; Memory', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Ambiguous', '3,000 words', 'null', 'Hybrid', '2780 USD'),
(180, 0, 'Journal of Cognitive Neuroscience', 'https://www.mitpressjournals.org/loi/jocn', 'Psychology', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No', '206', 'Hybrid', '1600 USD');
INSERT INTO `journals` (`journal_id`, `journal_order`, `journal_name`, `journal_url`, `Scientific Discipline`, `Psychology Area`, `Replication Studies`, `Analysis of existing datasets`, `Meta-Analyses`, `Analysis of existing datasets 2`, `Meta-Analyses 2`, `Qualitative Research`, `Post-Study Peer Review`, `Publishes Accepted Protocols`, `Replication Studies 2`, `Structured criteria for editorial decisions`, `Unregistered Analyses Allowed`, `Unregistered Preliminary Studies`, `Word Limits`, `h-index`, `Open Access Policy`, `APCs`) VALUES
(181, 0, 'Journal of Cognitive Psychology', 'https://www.tandfonline.com/toc/pecp21/current', 'Psychology', 'Cognitive; Cognitive; Perception; Cognitive; Memory', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', '54', 'Hybrid', '2995 USD'),
(182, 0, 'Journal of Computer Assisted Learning', 'http://onlinelibrary.wiley.com/journal/10.1111/(ISSN)1365-2729', 'Computer Science/Education', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '4,000 words for Stage 1; 8,000 words for Stage 2', '76', 'Hybrid', '2133 USD'),
(183, 0, 'Journal of Development Economics', 'https://www.journals.elsevier.com/journal-of-development-economics/', 'Economics', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '60 pages (Stage 1)', '115', 'Hybrid', '3180 USD'),
(184, 0, 'Journal of Economic Psychology', 'https://www.journals.elsevier.com/journal-of-economic-psychology/', 'Psychology', 'Business', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '12,000 words', '77', 'Hybrid', '2200 USD'),
(185, 0, 'Journal of Educational Psychology', 'https://www.apa.org/pubs/journals/edu', 'Psychology', 'null', 'No mention', 'Yes, proof of no access to data prior to study required', 'Unknown', 'Yes, proof of no access to data prior to study required', 'Unknown', 'Unknown', 'null', 'Unknown', 'No mention', 'Unknown', 'Yes, separate section', 'Yes', '12,000 words', '196', 'Hybrid', '3000 USD'),
(186, 0, 'Journal of European Psychology Students', 'http://jeps.efpsa.org', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', '8,000 words', 'null', 'Yes', '250 EUR'),
(187, 0, 'Journal of Evidence-Based Healthcare', 'https://www5.bahiana.edu.br/index.php/evidence', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', 'null'),
(188, 0, 'Journal of Experimental Political Science', 'https://www.cambridge.org/core/journals/journal-of-experimental-political-science', 'Political Science', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Ambiguous', '3,000 words (Replications); 4,000 words (Research Articles)', '6', 'Hybrid', '3069 USD'),
(189, 0, 'Journal of Experimental Psychology: Learning, Memory, and Cognition', 'https://www.apa.org/pubs/journals/xlm/index.aspx', 'Psychology', 'Developmental; Learning; Cognitive; Cognitive; Memory', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', '142', 'Hybrid', '3000 USD'),
(190, 0, 'Journal of Experimental Social Psychology', 'http://www.journals.elsevier.com/journal-of-experimental-social-psychology/', 'Psychology', 'Social & Personality; Social', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Tentative limit of 10,000 words for Full Length Research Articles', '115', 'Hybrid', '2870 USD'),
(191, 0, 'Journal of Illusion', 'https://journalofillusion.net/index.php/joi/index', 'Psychology', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', '12 pages', 'null', 'Yes', 'None'),
(192, 0, 'Journal of Integrated Security and Safety Science', 'https://journals.open.tudelft.nl/jiss/index', 'Security', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', 'Yes', 'None'),
(193, 0, 'Journal of Media Psychology', 'https://us.hogrefe.com/products/journals/jmp', 'Psychology', 'Cognitive; Social & Personality; Emotion', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Yes', 'Yes', '4,000 words for Stage 1; 8,000 words for Stage 2', '20', 'Hybrid', '3000 USD'),
(194, 0, 'Journal of Medical Internet Research (JMIR)', 'http://www.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Yes', '20 pages', '102', 'Yes', '2960 USD'),
(195, 0, 'Journal of Memory and Language', 'https://www.journals.elsevier.com/journal-of-memory-and-language', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', 'No mention', '138', 'Hybrid', '3170 USD'),
(196, 0, 'Journal of Neuropsychology', 'http://onlinelibrary.wiley.com/journal/10.1111/(ISSN)1748-6653', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '6,000 words', '28', 'Hybrid', '1850 USD'),
(197, 0, 'Journal of Neuroscience Research', 'https://onlinelibrary.wiley.com/journal/10974547', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Unknown', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '143', 'Hybrid', '2650 USD'),
(198, 0, 'Journal of Numerical Cognition', 'https://jnc.psychopen.eu/index.php/jnc', 'Psychology', 'Cognitive; Mathematics', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', 'Unknown', 'null', 'Yes', 'null'),
(199, 0, 'Journal of Occupational and Organizational Psychology', 'https://onlinelibrary.wiley.com/journal/20448325', 'Psychology', 'Social & Personality; Social', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '8,000 words', '90', 'Hybrid', '2000 USD'),
(200, 0, 'Journal of Participatory Medicine', 'http://jopm.jmir.org', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '1500 USD'),
(201, 0, 'Journal of Personality', 'https://onlinelibrary.wiley.com/journal/14676494', 'Unknown', 'Unknown', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(202, 0, 'Journal of Personality and Social Psychology', 'http://www.apa.org/pubs/journals/psp/', 'Psychology', 'Social & Personality; Social; Social & Personality; Personality', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', 'Unknown', '311', 'Hybrid', '3000 USD'),
(203, 0, 'Journal of Personnel Psychology', 'https://us.hogrefe.com/products/journals/jpp', 'Psychology', 'Social & Personality; Social; Business', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Ambiguous', '6,000 words (Original Articles) or 2,500 words (Research Notes) at Stage 2; 4,000 words or 1,700 words at Stage 1', '16', 'Hybrid', '3000 USD'),
(204, 0, 'Journal of Physiotherapy Research', 'https://www5.bahiana.edu.br/index.php/fisioterapia', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'null', 'null', 'Yes', 'None'),
(205, 0, 'Journal of Plant Nutrition and Soil Science', 'https://onlinelibrary.wiley.com/journal/15222624', 'Biology', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '15 pages', '71', 'Hybrid', '2350 USD'),
(206, 0, 'Journal of Psychiatric and Mental Health Nursing', 'https://onlinelibrary.wiley.com/journal/13652850', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Yes', 'null', 'Yes', 'Ambiguous', 'No', 'Yes', 'Ambiguous', '3,000 words (Stage 1) 5,000 words (Stage 2)', '53', 'Hybrid', '2400 USD'),
(207, 0, 'Journal of Research in Personality', 'https://www.journals.elsevier.com/journal-of-research-in-personality', 'Psychology', 'Social & Personality; Personality', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'None', '91', 'Hybrid', '2220 USD'),
(208, 0, 'Journal of Research in Reading', 'https://onlinelibrary.wiley.com/journal/14679817', 'Psychology', 'Developmental; Learning; Reading', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000-8,000 words (Empirical Papers)', '42', 'Hybrid', '2000 USD'),
(209, 0, 'Journal of Surgery and Clinical Reports', 'https://wrightacademia.org/jscr.php', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', 'Yes', 'None'),
(210, 0, 'Journal of the American Academy of Child and Adolescent Psychiatry', 'https://www.jaacap.org', 'Psychology', 'Mental & Physical Health', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '3,000 words', '222', 'Hybrid', '3000 USD'),
(211, 0, 'Judgment and Decision Making', 'https://journal.sjdm.org', 'Psychology', 'Social & Personality; Motivation; Cognitive; Thinking', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', 'None', '40', 'Yes', 'null'),
(212, 0, 'Laboratory Phonology', 'https://www.journal-labphon.org', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(213, 0, 'Language and Speech', 'https://journals.sagepub.com/home/las', 'Linguistics', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '20,000 words', '45', 'Hybrid', '3000 USD'),
(214, 0, 'Language Learning', 'https://onlinelibrary.wiley.com/journal/14679922', 'Linguistics', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '75', 'Hybrid', '2200 USD'),
(215, 0, 'Laterality: Asymmetries of Body, Brain, and Cognition', 'https://www.tandfonline.com/toc/plat20/current', 'Psychology', 'Biological', 'Yes', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Ambiguous', 'Ambiguous', 'No Limit', '43', 'Hybrid', '2995 USD'),
(216, 0, 'Law and Human Behavior', 'https://www.apa.org/pubs/journals/lhb/', 'Psychology', 'Human Behavior; Social & Personality; Social', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No', '87', 'Hybrid', '3000 USD'),
(217, 0, 'Learning and Instruction', 'https://www.sciencedirect.com/journal/learning-and-instruction', 'Psychology', 'Developmental; Learning; Developmental', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '8,000 words', '98', 'Hybrid', '3000 USD'),
(218, 0, 'Learning Disability Quarterly', 'https://journals.sagepub.com/home/ldq', 'Psychology', 'Mental & Physical Health; Abnormal', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '37 pages', '47', 'Hybrid', '3000 USD'),
(219, 0, 'Legal and Criminological Psychology', 'https://onlinelibrary.wiley.com/journal/20448333', 'Psychology', 'Law', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000 words', '49', 'Hybrid', '2050 USD'),
(220, 0, 'Legislative Studies Quarterly', 'https://onlinelibrary.wiley.com/journal/19399162', 'Policical Science', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', '10,000 words', '51', 'Hybrid', '1700 USD'),
(221, 0, 'Lifestyle Medicine', 'https://onlinelibrary.wiley.com/journal/26883740', 'Medicine', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '2500 USD'),
(222, 0, 'Linguistics', 'https://www.degruyter.com/view/journals/ling/ling-overview.xml', 'Linguistics', 'null', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', '42', 'Yes', 'null'),
(223, 0, 'Management and Organization Review', 'https://www.cambridge.org/core/journals/management-and-organization-review', 'Management', 'Business', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Yes', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '40 pages', 'null', 'Hybrid', '3255 USD'),
(224, 0, 'Media and Communication', 'https://www.cogitatiopress.com/mediaandcommunication/index', 'Communication', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', 'Stage 1: 3,000 words Stage 2: 6,000 words', '6', 'Yes', '900 EUR'),
(225, 0, 'Media Psychology', 'https://www.tandfonline.com/toc/hmep20/current', 'Psychology', 'Media', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(226, 0, 'Medicine 2.0', 'https://www.medicine20.com', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Unknown', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '5,000 words', 'null', 'Yes', '450 USD'),
(227, 0, 'Memory', 'http://www.tandfonline.com/toc/pmem20/current', 'Psychology', 'Cognitive; Memory', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'None', '76', 'Hybrid', '3500 USD'),
(228, 0, 'Meta-psychology', 'https://open.lnu.se/index.php/metapsychology/index', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', 'null', 'Yes', 'null'),
(229, 0, 'Mind, Brain and Education', 'https://onlinelibrary.wiley.com/journal/1751228x', 'Psychology', 'Biological; Biopsychology/Neuroscience; Developmental; Learning; Developmental', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '4,000 words', '26', 'Hybrid', '2900 USD'),
(230, 0, 'MNI Open Research', 'https://mniopenresearch.org', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '680 GBP'),
(231, 0, 'Music Perception', 'https://mp.ucpress.edu', 'Psychology', 'Music; Cognitive; Perception; Cognitive', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '3,000 words', '56', '?', 'null'),
(232, 0, 'Nature Communications', 'https://www.nature.com/ncomms/', 'Biology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000 words', '298', 'Yes', '3490 GBP'),
(233, 0, 'Nature Human Behaviour', 'http://www.nature.com/nathumbehav/', 'Psychology', 'Biological; Biopsychology/Neuroscience; Cognitive; Perception; Cognitive; Memory', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '6,000 - 7,000 words', '7', 'Hybrid', '9500 EUR'),
(234, 0, 'NC3RS Gateway', 'https://f1000research.com/gateways/nc3rs', 'Animal Research', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'YEs', '300 word abstract', 'null', 'Yes', '1000 USD'),
(235, 0, 'Neurobiology of Language', 'https://www.mitpressjournals.org/nol', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', 'Yes', '1100 USD'),
(236, 0, 'NeuroImage', 'https://www.sciencedirect.com/journal/neuroimage', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No Limit', '307', 'Yes', '3000 USD'),
(237, 0, 'NeuroImage Reports', 'https://www.journals.elsevier.com/neuroimage-reports', 'Unknown', 'Unknown', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'No', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(238, 0, 'Neuropsychopharmacology Reports', 'https://onlinelibrary.wiley.com/journal/2574173x', 'Psychology', 'Biological; Biopsychology/Neuroscience', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '8,000 words', '12', 'Yes', '2280 USD'),
(239, 0, 'Neuroscience of Consciousness', 'https://academic.oup.com/nc', 'Psychology', 'Biological; Biopsychology/Neuroscience; Biological; Consciousness', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '7,000 fpr stage 1; 9,000 for stage 2', '2', 'Yes', '1760 USD'),
(240, 0, 'NFS Journal', 'https://www.journals.elsevier.com/nfs-journal', 'Nutrition', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'None', '6', 'Yes', '1250 USD'),
(241, 0, 'Nicotine & Tobacco Research', 'https://academic.oup.com/ntr', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '4,000 words', '98', 'Yes', '1250 USD'),
(242, 0, 'Nursing Reports', 'https://www.mdpi.com/journal/nursrep', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown'),
(243, 0, 'Open Psychology', 'https://www.degruyter.com/view/j/psych', 'Psychology', 'Cognitive; Social & Personality; Social; Developmental', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', '9', 'Yes', '500 EUR'),
(244, 0, 'Open Research Europe', 'https://open-research-europe.ec.europa.eu', 'General', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '780 EUR'),
(245, 0, 'Pacific-Basin Finance Journal', 'https://www.sciencedirect.com/journal/pacific-basin-finance-journal', 'Finance', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', '52', 'Hybrid', '1980 USD'),
(246, 0, 'Paediatric and Neonatal Pain', 'https://www.onlinelibrary.wiley.com/journal/26373807', 'Medicine', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', '2700 USD'),
(247, 0, 'PCI Ecology', 'https://ecology.peercommunityin.org', 'Biology', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Yes', 'None'),
(248, 1, 'PCI RR (Peer Community In Registered Reports)', 'https://rr.peercommunityin.org', 'Unknown', 'Unknown', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'null', 'Unknown', 'Yes', 'Yes', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(249, 0, 'PeerJ', 'https://peerj.com', 'Biology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'Yes', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '45 pages with additional charge', '35', 'Yes', '1195 USD'),
(250, 0, 'Perception', 'https://uk.sagepub.com/en-gb/eur/perception/journal202440#description', 'Psychology', 'Cognitive; Perception', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Yes', 'No', '87', 'Hybrid', '3000 USD'),
(251, 0, 'Personal Relationships', 'https://onlinelibrary.wiley.com/journal/14756811', 'Psychology', 'Social & Personality; Social & Personality; Social', 'Yes', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Unknown', 'Unknown', 'Unknown', '70', 'Hybrid', '1850 USD'),
(252, 0, 'Personality Science', 'https://ps.psychopen.eu/index.php/ps', 'Psychology', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Yes', 'One study is 5,000 words, multiple studies is 7,000 words', 'null', 'Yes', 'None'),
(253, 0, 'Perspectives on Psychological Science', 'https://journals.sagepub.com/home/pps', 'Psychology', 'null', 'Special category, multilab only', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Special category, multilab only', 'Unknown', 'Yes, separate section', 'Unknown', '<1,500 Intro, 500-1,000 general discussion', '96', 'Hybrid', '3000 USD'),
(254, 0, 'Plant Direct', 'https://www.onlinelibrary.wiley.com/journal/24754455', 'Plant Sciences', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'Yes', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', '5', 'Yes', '2200 USD'),
(255, 0, 'PLOS Biology', 'https://journals.plos.org/plosbiology/', 'Biology', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No Limit', '260', 'Yes', '2900 USD'),
(256, 0, 'PLOS ONE', 'https://journals.plos.org/plosone/', 'Biology', 'null', 'Ambiguous', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Yes', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', 'None', '241', 'Yes', '1595 USD'),
(257, 0, 'Political Analysis', 'http://pan.oxfordjournals.org', 'Politics', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '25-30 pages double-spaced (Research Articles)', '54', 'Hybrid', '3255 USD'),
(258, 0, 'Political Behavior', 'https://www.springer.com/journal/11109', 'Politics', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '25-35 pages double spaced', '53', 'Hybrid', '2780 USD'),
(259, 0, 'Political Science Quarterly', 'http://www.psqonline.org', 'Politics', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '40 pages double spaced', '40', 'Hybrid', '1650 USD'),
(260, 0, 'Political Science Research and Methods', 'http://journals.cambridge.org/action/displayJournal?jid=RAM', 'Politics', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '9,000 words (Normal Length Articles)', 'null', 'Hybrid', '3255 USD'),
(261, 0, 'Politics and the Life Sciences', 'https://www.cambridge.org/core/journals/politics-and-the-life-sciences', 'Politics', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '15,000 words', '18', 'Hybrid', '3162 USD'),
(262, 0, 'Psicológica', 'https://content.sciendo.com/view/journals/psicolj/psicolj-overview.xml', 'Psychology', 'Developmental', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '10,000 words', '12', 'Yes', 'null'),
(263, 0, 'Psycho-Oncology', 'https://onlinelibrary.wiley.com/journal/10991611', 'Psychology', 'Biological; Mental & Physical Health', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '4,000 words', '119', 'Hybrid', '2867 USD'),
(264, 0, 'Psychological Science', 'http://journals.sagepub.com/home/pss', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '1,500 words', '218', 'Hybrid', '3000 USD'),
(265, 0, 'Psychological Test Adaptation and Development', 'https://us.hogrefe.com/products/journals/psychological-test-adaptation-and-development', 'Psychology', 'Research Methods and Statistics', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '5,000 words for 1 study; 7,500 words for 2 studies', 'null', 'Hybrid', '3000 USD'),
(266, 0, 'Psychology, Crime & Law', 'https://www.tandfonline.com/journals/gpcl20', 'Psychology', 'Crime; Law', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(267, 0, 'Psychology & Health', 'https://www.tandfonline.com/toc/gpsh20/current', 'Psychology', 'Biological; Mental & Physical Health', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '30 pages', '75', 'Hybrid', '2995 USD'),
(268, 0, 'Psychology & Sexuality', 'https://www.tandfonline.com/action/journalInformation?show=aimsScope&journalCode=rpse20', 'Psychology', 'Social & Personality; Social', 'Yes', 'Yes', 'Unknown', 'Yes', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Yes, seperate section', 'Yes', '6,000 words', '22', 'Hybrid', '2995 USD'),
(269, 0, 'Psychology and Marketing', 'https://onlinelibrary.wiley.com/journal/15206793', 'Psychology', 'Business', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '90', 'Hybrid', '3000 USD'),
(270, 0, 'Psychology and Psychotherapy: Theory, Research and Practice', 'https://onlinelibrary.wiley.com/journal/20448341', 'Psychology', 'Mental & Physical Health; Mental & Physical Health; Therapies; Social & Personality; Social', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '5,000 words', '46', 'Hybrid', '2200 USD'),
(271, 0, 'Psychology of Addictive Behaviors', 'https://www.apa.org/pubs/journals/adb/', 'Psychology', 'Mental & Physical Health; Abnormal; Mental & Physical Health', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '40 pages', '101', 'Hybrid', '3000 USD'),
(272, 0, 'Psychology of Sport and Exercise', 'http://www.sciencedirect.com/science/journal/14690292', 'Psychology', 'Mental & Physical Health', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', '30 pages', '66', 'Hybrid', '3550 USD'),
(273, 0, 'Psychonomic Bulletin & Review', 'http://www.springer.com/psychology/cognitive+psychology/journal/13423', 'Psychology', 'Cognitive; Cognitive; Perception; Developmental; Learning', 'Yes', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Yes', 'Unknown', '3,000 words', '127', 'Hybrid', '3280 USD'),
(274, 0, 'Psychophysiology', 'https://onlinelibrary.wiley.com/journal/14698986', 'Psychology', 'null', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Yes', '30 pages', '150', 'Hybrid', '2450 USD'),
(275, 0, 'Public Opinion Quarterly', 'http://poq.oxfordjournals.org', 'Politics', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '6,500 words (Poll Reviews)', '87', 'Hybrid', '3460 USD'),
(276, 0, 'Q Open', 'https://academic.oup.com/qopen', 'Environment', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '8,500 words', 'null', 'Yes', '1650 USD'),
(277, 0, 'Quality of Life Research', 'https://www.springer.com/journal/11136/', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '4,000 words', '137', 'Hybrid', '3280 USD'),
(278, 0, 'Quarterly Journal of Experimental Psychology', 'https://us.sagepub.com/en-us/nam/quarterly-journal-of-experimental-psychology/journal203389', 'Psychology', 'Cognitive; Perception; Developmental; Learning', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'None', '59', 'Hybrid', '3000 USD'),
(279, 0, 'Registered Reports in Kinesiology', 'https://www.storkjournals.org/index.php/rrik', 'Kinesiology', 'null', 'Yes', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'No mention', 'null', 'Yes', 'None'),
(280, 0, 'Registered Reports in Psychology', 'https://rrp.psychopen.eu/index.php/rrp/index', 'Psychology', 'Research Methods and Statistics', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', '?', 'null'),
(281, 0, 'Remedial and Special Education', 'https://journals.sagepub.com/home/rse', 'Education', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(282, 0, 'Research & Politics', 'https://uk.sagepub.com/en-gb/eur/journal/research-politics', 'Politics', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '4,000 words', '14', 'Yes', 'null'),
(283, 0, 'Research in Autism Spectrum Disorders', 'https://www.sciencedirect.com/journal/research-in-autism-spectrum-disorders', 'Psychology', 'Autism', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(284, 0, 'Royal Society Open Science', 'http://rsos.royalsocietypublishing.org', 'General', 'null', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '18', 'Yes', '900 GBP'),
(285, 0, 'Science and Medicine in Football', 'https://www.tandfonline.com/toc/rsmf20/current', 'Medicine', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '3,500 words', '9', 'Hybrid', '3300 USD'),
(286, 0, 'Scientific Reports', 'https://www.nature.com/srep/', 'Unknown', 'Unknown', 'Yes', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'Yes', 'Unknown', 'null', 'Unknown', 'Unknown'),
(287, 0, 'Scientific Studies of Reading', 'https://www.tandfonline.com/toc/hssr20/current', 'Reading', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '6,000 words', '58', 'Hybrid', '2800 USD'),
(288, 0, 'Social Influence', 'https://www.tandfonline.com/toc/psif20/current', 'Psychology', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '5,000 words', '24', 'Hybrid', '1950 USD'),
(289, 0, 'Social Psychological Bulletin', 'https://spb.psychopen.eu', 'Psychology', 'Social & Personality; Social', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', '3,500 words for short Research Reports, 8,000 words for long research reports', 'null', 'Yes', 'null'),
(290, 0, 'Social Psychology', 'http://econtent.hogrefe.com/loi/zsp', 'Psychology', 'Social & Personality; Social', 'Yes', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Yes', 'Unknown', 'Ambiguous', 'Unknown', '2,500 words', '26', 'Hybrid', '3000 USD'),
(291, 0, 'Spanish Journal of Psychology', 'https://www.cambridge.org/core/journals/spanish-journal-of-psychology', 'Unknown', 'Unknown', 'Yes', 'No', 'No', 'No', 'No', 'No', 'null', 'No', 'Yes', 'Yes', 'Yes', 'No', 'Unknown', 'null', 'Unknown', 'Unknown'),
(292, 0, 'State Politics and Policy Quarterly', 'http://spa.sagepub.com', 'Politics', 'null', 'No', 'Yes', 'No', 'Yes', 'No', 'No', 'null', 'No', 'No', 'No', 'Ambiguous', 'Ambiguous', '40 pages', '27', 'Hybrid', '3000 USD'),
(293, 0, 'Stress and Health', 'http://onlinelibrary.wiley.com/journal/10.1002/(ISSN)1532-2998', 'Psychology', 'Mental & Physical Health; Mental & Physical Health; Stress, Lifestyle, and Health', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', '6,000 words (Research Articles) and 35 double spaced A4 page', '48', 'Hybrid', '2200 USD'),
(294, 0, 'Studia Psychologica', 'http://www.studiapsychologica.com', 'Psychology', 'Cognitive; Social & Personality', 'Yes', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Yes', 'No', 'Ambiguous', 'Ambiguous', '6,000 words', '18', 'Yes', 'null'),
(295, 0, 'Technology, Mind, and Behavior', 'https://www.apa.org/pubs/journals/tmb', 'Psychology', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '2,000 words', 'null', 'Yes', '1200 USD'),
(296, 0, 'The Australian and New Zealand Journal of Obstetrics and Gynaecology (ANZJOG)', 'https://obgyn.onlinelibrary.wiley.com/journal/1479828X', 'Medicine', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(297, 0, 'The International Journal for the Psychology of Religion', 'https://tandfonline.com/toc/hjpr20/current', 'Psychology', 'Religion', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', '26', 'Hybrid', '2995 USD'),
(298, 0, 'The Leadership Quarterly', 'http://www.journals.elsevier.com/the-leadership-quarterly', 'Psychology', 'Social & Personality; Social; Biological; Business', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', '12,000 - 15,000 words', 'null', 'Hybrid', '4050 USD'),
(299, 0, 'The Review of Corporate Finance Studies', 'https://academic.oup.com/rcfs', 'Finance', 'null', 'Ambiguous', 'Ambiguous', 'Unknown', 'Ambiguous', 'Unknown', 'Unknown', 'null', 'Unknown', 'Ambiguous', 'Unknown', 'Ambiguous', 'Ambiguous', 'No mention', 'null', 'Hybrid', '3460 USD'),
(300, 0, 'The Review of Financial Studies', 'http://rfssfs.org/news/fintech-a-call-for-proposals/', 'Finance', 'null', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'Yes', 'Ambiguous', 'Ambiguous', 'Unknown', 'null', 'Hybrid', '3460 USD'),
(301, 0, 'Wellcome Open Research', 'https://wellcomeopenresearch.org', 'General', 'null', 'Ambiguous', 'Yes', 'Yes', 'Yes', 'Yes', 'No', 'null', 'Yes', 'Ambiguous', 'Yes', 'Ambiguous', 'Yes', 'Unknown', '3', 'Yes', '920 GBP'),
(302, 0, 'Work, Aging and Retirement', 'http://workar.oxfordjournals.org', 'Psychology', 'Developmental; Lifespan Development; Mental & Physical Health; Stress, Lifestyle, and Health', 'Ambiguous', 'Ambiguous', 'No', 'Ambiguous', 'No', 'No', 'null', 'No', 'Ambiguous', 'No', 'Ambiguous', 'Ambiguous', '2,500 words', 'null', 'Hybrid', '3605 USD'),
(303, 0, 'Zeitschrift für Psychologie', 'https://us.hogrefe.com/products/journals/zeitschrift-fuer-psychologie', 'Psychology', 'null', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'null', 'Unknown', 'Unknown', 'Unknown', 'Unknown', 'Unknown', '50,000 characters', '30', 'Hybrid', '3000 USD'),
(304, 0, 'Developmental Psychology', 'https://www.apa.org/pubs/journals/dev', 'Psychology', 'Developmental', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(305, 0, 'Politische Vierteljahresschrift', 'https://www.springer.com/journal/11615', 'Politics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(306, 0, 'Nature Methods', 'https://www.nature.com/nmeth/', 'General', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(307, 0, 'The Journal of Politics', 'https://www.journals.uchicago.edu/toc/jop/current', 'Politics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(308, 0, 'Communications Psychology', 'https://www.nature.com/commspsychol/', 'Psychology', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(309, 0, 'Nature', 'https://www.nature.com', 'General', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(310, 0, 'British Journal of Educational Technology', 'https://bera-journals.onlinelibrary.wiley.com/journal/14678535', 'Education', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(311, 0, 'Journal of Organizational Behavior', 'https://onlinelibrary.wiley.com/journal/10991379', 'Organizational', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(312, 0, 'Ethology', 'https://onlinelibrary.wiley.com/journal/14390310', 'Biology', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(313, 0, 'The Journal of Sex Research', 'https://www.tandfonline.com/journals/hjsr20', 'Psychology', 'Sex; Sexuality; Psychology; Education; Allied Health', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(314, 0, 'Journal of Sports Sciences', 'https://www.tandfonline.com/journals/rjsp20', 'Sports', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(315, 0, 'Journal of Speech, Language, and Hearing Research', 'https://pubs.asha.org/journal/jslhr', 'Language', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(316, 0, 'Oxford Open Economics', 'https://academic.oup.com/ooec', 'Economics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(317, 0, 'Cultural Diversity & Ethnic Minority Psychology', 'https://www.apa.org/pubs/journals/cdp', 'Psychology', 'Culture; Ethnicity; Race', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(318, 0, 'Journal for the Education of the Gifted', 'https://journals.sagepub.com/home/jeg', 'Psychology', 'Developmental; Education', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(319, 0, 'Journal of Individual Differences', 'https://www.hogrefe.com/us/journal/journal-of-individual-differences', 'Psychology', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(320, 0, 'Language and Cognition', 'https://www.cambridge.org/core/journals/language-and-cognition', 'Psychology', 'Language; Cognition', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(321, 0, 'Psychological Assessment', 'https://www.apa.org/pubs/journals/pas', 'Psychology', 'Assessment; Clinical', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(322, 0, 'Second Language Research', 'https://journals.sagepub.com/home/slr', 'Psychology', 'Language', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(323, 0, 'Swiss Psychology Open', 'https://swisspsychologyopen.com', 'Psychology', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(324, 0, 'Zeitschrift für Bildungsforschung', 'https://www.springer.com/journal/35834', 'Education', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(325, 0, 'Nature Ecology & Evolution', 'https://www.nature.com/natecolevol/', 'Ecology', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(326, 0, 'Exercise, Sport, and Movement', 'https://journals.lww.com/acsm-esm', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(327, 0, 'PNAS Nexus', 'https://academic.oup.com/pnasnexus/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(328, 0, 'Scholarship of Teaching and Learning in Psychology', 'https://www.apa.org/pubs/journals/stl', 'Psychology', 'Teaching; Learning', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(329, 0, 'Issues in Accounting Education', 'https://aaahq.org/Research/Journals/Issues-in-Accounting-Education', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(330, 0, 'Acta Sociologica', 'https://journals.sagepub.com/home/asj', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(331, 0, 'Clinical Psychological Science', 'https://journals.sagepub.com/home/cpx', 'Psychology', 'Clinical', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(332, 0, 'International Journal of Intercultural Relations', 'https://www.sciencedirect.com/journal/international-journal-of-intercultural-relations', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(333, 0, 'Personality and Individual Differences', 'https://www.sciencedirect.com/journal/personality-and-individual-differences', 'Psychology', 'Personality', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(334, 0, 'Journal of Behavioral and Experimental Economics', 'https://www.sciencedirect.com/journal/journal-of-behavioral-and-experimental-economics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(335, 0, 'Journal of Environmental Psychology', 'https://www.sciencedirect.com/journal/journal-of-environmental-psychology', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(336, 0, 'Journal of Phonetics', 'https://www.sciencedirect.com/journal/journal-of-phonetics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(337, 0, 'Political Psychology', 'https://onlinelibrary.wiley.com/journal/14679221', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(338, 0, 'Sport, Exercise, and Performance Psychology', 'https://www.apa.org/pubs/journals/spy', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(339, 0, 'Nature Climate Change', 'https://www.nature.com/nclimate/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(340, 0, 'International Journal of Language & Communication Disorders', 'https://onlinelibrary.wiley.com/journal/14606984', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(341, 0, 'Mass Communication and Society', 'https://www.tandfonline.com/journals/hmcs20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(342, 0, 'Journal of Trial & Error', 'https://journal.trialanderror.org', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(343, 0, 'Behavior Modification', 'https://journals.sagepub.com/home/bmo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(344, 0, 'Personality and Social Psychology Bulletin', 'https://journals.sagepub.com/home/PSP', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(345, 0, 'International Journal of Behavioral Development', 'https://uk.sagepub.com/en-gb/eur/journal/international-journal-behavioral-development', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(346, 0, 'Psychology of Religion and Spirituality', 'https://www.apa.org/pubs/journals/rel', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(347, 0, 'Identity', 'https://www.tandfonline.com/journals/hidn20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` int(11) DEFAULT NULL,
  `gender_text` varchar(255) DEFAULT NULL,
  `ethnicity` varchar(255) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `feedback_academic_roles`
--
ALTER TABLE `feedback_academic_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `feedback_invite_log`
--
ALTER TABLE `feedback_invite_log`
  ADD UNIQUE KEY `invite_uuid` (`invite_uuid`);

--
-- Indexes for table `feedback_questions`
--
ALTER TABLE `feedback_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `question_type` (`question_type`),
  ADD KEY `question_review_type` (`question_role_type`),
  ADD KEY `question_category` (`question_category`),
  ADD KEY `question_sub_stage_8` (`question_sub_stage_8`),
  ADD KEY `question_sub_stage_7` (`question_sub_stage_7`),
  ADD KEY `question_sub_stage_6` (`question_sub_stage_6`),
  ADD KEY `question_sub_stage_5` (`question_sub_stage_5`),
  ADD KEY `question_sub_stage_4` (`question_sub_stage_4`),
  ADD KEY `question_sub_stage_3` (`question_sub_stage_3`),
  ADD KEY `question_sub_stage_2` (`question_sub_stage_2`),
  ADD KEY `question_sub_stage_1` (`question_sub_stage_1`),
  ADD KEY `q_stage_2` (`q_stage_2`),
  ADD KEY `q_stage_1` (`q_stage_1`),
  ADD KEY `no_repeat` (`no_repeat`),
  ADD KEY `question_order` (`question_order`);

--
-- Indexes for table `feedback_question_categories`
--
ALTER TABLE `feedback_question_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `feedback_question_type`
--
ALTER TABLE `feedback_question_type`
  ADD PRIMARY KEY (`question_type_id`),
  ADD KEY `question_type_id` (`question_type_id`);

--
-- Indexes for table `feedback_responses`
--
ALTER TABLE `feedback_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `response_id` (`response_id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `question_response_value` (`question_response_value`),
  ADD KEY `sub_question_id` (`sub_question_id`);

--
-- Indexes for table `feedback_reviewer_roles`
--
ALTER TABLE `feedback_reviewer_roles`
  ADD PRIMARY KEY (`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `feedback_reviews`
--
ALTER TABLE `feedback_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `stage_id` (`sub_stage_id`),
  ADD KEY `stage_id_2` (`stage_id`),
  ADD KEY `role_type` (`role_type`),
  ADD KEY `journal_id` (`journal_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `review_link_id` (`review_link_id`),
  ADD KEY `review_uuid` (`review_uuid`);

--
-- Indexes for table `feedback_stages`
--
ALTER TABLE `feedback_stages`
  ADD PRIMARY KEY (`sub_stage_id`),
  ADD KEY `stage_id` (`sub_stage_id`);

--
-- Indexes for table `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`journal_id`),
  ADD KEY `journal_id` (`journal_id`),
  ADD KEY `journal_order` (`journal_order`);


--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `id` (`id`,`email`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=686;

--
-- AUTO_INCREMENT for table `feedback_academic_roles`
--
ALTER TABLE `feedback_academic_roles`
  MODIFY `role_id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `feedback_questions`
--
ALTER TABLE `feedback_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `feedback_question_categories`
--
ALTER TABLE `feedback_question_categories`
  MODIFY `category_id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `feedback_question_type`
--
ALTER TABLE `feedback_question_type`
  MODIFY `question_type_id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedback_responses`
--
ALTER TABLE `feedback_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9547;


--
-- AUTO_INCREMENT for table `feedback_reviewer_roles`
--
ALTER TABLE `feedback_reviewer_roles`
  MODIFY `role_id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback_reviews`
--
ALTER TABLE `feedback_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=394;

--
-- AUTO_INCREMENT for table `feedback_stages`
--
ALTER TABLE `feedback_stages`
  MODIFY `sub_stage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `journals`
--
ALTER TABLE `journals`
  MODIFY `journal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=348;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=337;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

<?php

// TODO: ideally, code that resets cookies should happen up here, before any headers are sent. 

define('TITLE', "Home");
include '../assets/layouts/header.php';

?>
<main>

    <section class="jumbotron text-center py-5">
        <div class="container">
            <h2 class="jumbotron-heading mb-4">Welcome<?php if (isset($_SESSION['email'])){ echo " back";}?></h2>
            <?php
            if (isset($_SESSION['email'])){ ?>

            <p class="text-muted" style="font-size: 1.2em">
                You are logged in as <b><?php echo $_SESSION['email']; ?></b>
            </p>
                
            <?php } ?>

            <p class="text-muted" style="font-size: 1.2em">
                <?php

                if (isset($_SESSION['email'])){

                    // Check if an invite cookie has been set (from an invite link)

                    if (isset($_COOKIE["invite_uuid"]) && isset($_COOKIE["invite_stage"]) && check_verified() == true) {

                        // Before presenting a link to the UUID/stage feedback in the cookie
                        // We first check to make sure the user has not already completed feedback for this UUID/stage.
                        // This is to avoid situations where, for whatever reason (probably browser quirk-related) the cookie was not cleared after
                        // the user completed the feedback, meaning that the user could continue to get reminders on the home page every time they logged on (while the cookie was still valid i.e. for 30 days)

                        // N.B. This does not check if the UUID already exists, only if the currently logged in user has completed a review

                        // Connect to database

                        $db_handle = mysqli_connect($servername, $database_username, $database_password, $database_name) or die(mysql_error());

                        /* check connection */

                        if (mysqli_connect_errno()) {
                            printf("Connect failed: %s\n", mysqli_connect_error());
                            exit();
                        }

                        $sql = "SELECT COUNT(*) AS uuid_completed_count FROM `feedback_reviews` WHERE `review_uuid` = UUID_TO_BIN('" . $_COOKIE["invite_uuid"] . "') AND user_id = " . $_SESSION['id'] . " AND stage_id = " . $_COOKIE["invite_stage"] . ";";

                        if ($result = mysqli_query($db_handle, $sql)) {

                            if (mysqli_num_rows($result) > 0) {

                                $user_feedback_count_by_uuid = mysqli_fetch_all($result, MYSQLI_ASSOC);

                                // no feedback has been given by this user for this UUID/stage - so display a direct link to complete it.

                                if ($user_feedback_count_by_uuid['0']['uuid_completed_count'] == 0) { ?>

                                    &#9888;&#65039;&nbsp;You have a co-author invitation pending - <a href="../feedback/selector.php?uuid=<?php echo $_COOKIE["invite_uuid"] ?>&stage=<?php echo $_COOKIE["invite_stage"] ?>">click here</a> to complete the feedback.&nbsp;<span id="tooltip_pending_invite" class="survey-tooltip" tabindex="-1">?</span><br /><br />

                                <?php
                                }

                                else

                                // feedback already given - clear this cookie

                                {
                                  unset($_COOKIE['invite_uuid']);
                                  unset($_COOKIE['invite_stage']);

                                  setcookie("invite_uuid",  "", time() - 3600, "/");
                                  setcookie("invite_stage", "", time() - 3600, "/");
                                }
                            }
                        }
                    }

                    if(isset($_COOKIE["decisionletter"])) { ?>

                        &#9888;&#65039;&nbsp;You have a decision letter invitation pending - <a href="../feedback/selector.php">click here</a> to complete the feedback.&nbsp;<span id="tooltip_decision_letter" class="survey-tooltip" tabindex="2">?</span><br /><br />
                <?php 
                    }
                }
                
                // Show logged in text

                if (isset($_SESSION['email'])) {

                    if (!isset($_COOKIE["profile_reminder"]) && ($_SESSION['gender'] == "" || $_SESSION['ethnicity'] == "")) { ?>

                &#9888;&#65039;&nbsp;Please <a title="Complete your profile" href="../profile/">complete your profile</a> (<a title="Don't show this reminder for 30 days" href="pause_reminder.php" onclick="return confirm('Are you sure you want to hide this reminder for 30 days?')">x</a>).<br /><br />

                <?php } ?>

                You can <a title="Leave anonymous feedback" href="../feedback/selector.php">leave anonymous feedback</a> about your experience as an author or reviewer of a Registered Report, and you can <a title="View my previous feedback" href="../feedback/my_feedback.php">view your previous feedback</a>. The feedback survey asks about a range of experiences, including the speed and quality of the editorial and review process.<br /><br />

                You can also <a title="View dashboards" href="../dashboards/">view dashboards</a> which show the aggregate ratings of journals across the different feedback categories.

                <?php

                }

                else

                { ?>

                Have you submitted or reviewed a Registered Report?<br /><br />

                <a title="Share your experiences" href="../feedback/selector.php">Share your experiences</a> and help shape the future of this initiative!<br /><br />

                Your feedback:<br />

                <ul>
                    <li>will only take 5 minutes and is anonymous</li>
                    <li>can help maintain high standards at peer-reviewed journals</li>
                    <li>will contribute to community knowledge and experience of the RR-article type</li>
                </ul>
            </p>
            <p class="text-muted" style="font-size: 1.2em">    
                You can also <a title="View dashboards" href="../dashboards/">view dashboards</a> which show the aggregate ratings of journals across the different feedback categories.<br />

                <?php
                }
                ?>
            </p>

        </div>
    </section>
</main>

<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js" integrity="sha512-XLo6bQe08irJObCc86rFEKQdcFYbGGIHVXcfMsxpbvF8ompmd1SNJjqVY5hmjQ01Ts0UmmSQGfqpt3fGjm6pGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/tippy.js/6.3.1/tippy-bundle.umd.min.js" integrity="sha512-xULG5PJghLB+bsq9AuFbarjLtvtxDVjh47LlxXG25U2v3m+YB7OvNjA6m7pyampiwPVvrXv3Wupiv8oSX+5lRw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>

// Create tooltips

tippy('#tooltip_pending_invite', { content: 'You\'re seeing this because you clicked on an invitation link in your email in the last 30 days - for more information check your email, or click the link to complete the feedback.', allowHTML: true});
tippy('#tooltip_decision_letter', { content: 'You\'re seeing this because you clicked on a link from a journal decision letter, asking for feedback on the manuscript that was reviewed.<br /><br />We typically assume that the first piece of feedback you will give after clicking this link will be about this manuscript. If this is not the case, you may untick the corresponding box on the linked page, and complete feedback for another manuscript.<br /><br />N.B. This checkbox and message will continue to appear for 5 days from when you clicked the link in the journal decision letter, after which time it will disappear.', allowHTML: true});
tippy('#tooltip_complete_profile', { content: 'This is optional, but will help our research into how demographic factors influence peoples\' experience of peer review.<br /><br />Any information you provide in this section will be kept confidential.', allowHTML: true});
</script>

<?php

include '../assets/layouts/footer.php'

?>
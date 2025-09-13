<?php

define('TITLE', "Edit Profile");
include '../assets/layouts/header.php';
check_verified();

//XSS filter for session variables
function xss_filter($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
<main>
    <form id="profile" action="includes/profile-edit.inc.php" method="post" enctype="multipart/form-data" autocomplete="off">

        <?php insert_csrf_token(); ?>

        <div class="text-center">
            <sub class="text-danger">
                <?php
                    if (isset($_SESSION['ERRORS']['imageerror']))
                        echo $_SESSION['ERRORS']['imageerror'];
                ?>
            </sub>
        </div>
        <div class="text-center">
            <p style="color: green;" id="confirmDiv">
                <?php
                    if (isset($_SESSION['STATUS']['editstatus']))
                        echo $_SESSION['STATUS']['editstatus'];
                ?>
            </p>
        </div>

        <h2>Your Profile</h2>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="Email address" value="<?php echo xss_filter($_SESSION['email']); ?>">
            <sub class="text-danger">
                <?php
                    if (isset($_SESSION['ERRORS']['emailerror']))
                        echo $_SESSION['ERRORS']['emailerror'];
                ?>
            </sub>
        </div>

        <br />

        <hr>

        <br />

        <span class="h3 mb-3 font-weight-normal text-muted text-center"><b>Optional</b></span>

        <br /><br />Any information you provide in this section will only be used for research purposes, and not shared publicly.<br /><br />
        <img alt="Info icon" title="Info icon" src="../assets/images/info-icon.svg" height="16" width="16">&nbsp;Why would it be helpful to provide this information?
        <ul><li>We are interested if and how demographic factors (gender and ethnicity) influence peoples' experience of peer review.</li></ul>
        
        <br />

        <div class="form-group">
            <fieldset>
            <legend><b>Gender</b></legend>

            <br />

            <div class="custom-control custom-radio custom-control">
                <input type="radio" id="female" name="gender" class="custom-control-input" value="1"<?php if (isset($_SESSION['gender']) && $_SESSION['gender'] == '1') echo ' checked' ?> aria-label="Female">
                <label class="custom-control-label" for="female">Female</label>
            </div>

            <div class="custom-control custom-radio custom-control">
                <input type="radio" id="male" name="gender" class="custom-control-input" value="2"<?php if (isset($_SESSION['gender']) && $_SESSION['gender'] == '2') echo ' checked' ?> aria-label="Male">
                <label class="custom-control-label" for="male">Male</label>
            </div>

            <div class="custom-control custom-radio custom-control">
                <input type="radio" id="pnts" name="gender" class="custom-control-input" value="3"<?php if (isset($_SESSION['gender']) && $_SESSION['gender'] == '3') echo ' checked' ?> aria-label="Prefer not to say">
                <label class="custom-control-label" for="pnts">Prefer not to say</label>
            </div>

            <div class="custom-control custom-radio custom-control">
                <input type="radio" id="ptsd" name="gender" class="custom-control-input" value="4" onClick="$('#gender_text').focus();"<?php if (isset($_SESSION['gender']) && $_SESSION['gender'] == '4') echo ' checked' ?> aria-label="Prefer to self-describe">
                <label class="custom-control-label" for="gender_text">Prefer to self-describe</label>
                <input type="text" id="gender_text" name="gender_text" maxlength="255" placeholder="" onfocus="$('#ptsd').prop('checked', true);" onchange="$('#ptsd').prop('checked', true).trigger('click');" value="<?php if (isset($_SESSION['gender_text'])) echo xss_filter($_SESSION['gender_text']) ?>">
            </div>
            </fieldset>
        </div>

        <br />

        <div class="form-group">

            <div class="custom-control custom-radio custom-control">
                <b>Ethnicity</b>
                <br />
                <br />
                <input type="text" id="ethnicity" name="ethnicity" maxlength="255" placeholder="" value="<?php if (isset($_SESSION['ethnicity'])) echo $_SESSION['ethnicity'] ?>" aria-label="Ethnicity">
            </div>

        </div>

        <br />
        <br />

        <button class="btn btn-lg btn-primary btn-block mb-5" type="submit" name='update-profile'>Confirm Changes</button>

        <br />

        <hr>

        <br />

            <span class="h5 font-weight-normal text-muted mb-4">Change password</span>
            <sub class="text-danger mb-4">
                <?php
                    if (isset($_SESSION['ERRORS']['passworderror']))
                        echo $_SESSION['ERRORS']['passworderror'];

                ?>
            </sub>
            <br /><br />

            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Current Password" autocomplete="new-password" aria-label="Current password">
            </div>

            <br />

            <div class=" form-group">
                <input type="password" id="newpassword" name="newpassword" placeholder="New Password" autocomplete="new-password" aria-label="New password">
            </div>

            <br />

            <div class=" form-group mb-5">
                <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm Password" autocomplete="new-password" aria-label="Confirm password">
            </div>

            <br />
            <br />

            <button class="btn btn-lg btn-primary btn-block mb-5" type="submit" name='update-profile'>Confirm Changes</button>
        
    </form>
</main>

<style>
    .survey-tooltip {
        display: inline-block;
        color: white;
        background-color: #1ab394;
        border-radius: 50%;
        padding: 0 7px;
        cursor: help;
    }

    fieldset {
        border: 0;
        padding-inline-start: 0em;
    }
</style>

<script type="text/javascript" src="../assets/js/popper.min.js"></script>
<script type="text/javascript" src="../assets/js/tippy-bundle.umd.min.js"></script>

<script type="text/javascript">

$('#profile input[name=gender]').on('change', function() {

    if ( $(this).val() != "4") {

        $('#gender_text').val('');
    }
});
</script>

<?php

if (isset($_SESSION['STATUS']['editstatus'])) { ?>

<script>
$(document).ready(()=>{
    $("#confirmDiv").effect( "highlight", {color:"green"}, 2000 );
});
</script>
<?php
}

include '../assets/layouts/footer.php';

?>
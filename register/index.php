<?php

define('TITLE', "Register");
include '../assets/layouts/header.php';
check_logged_out();

?>
<form action="includes/register.inc.php" method="post" enctype="multipart/form-data" id="register">

    <?php insert_csrf_token(); ?>

    <div class="text-center">
        <sub class="text-danger">
            <?php
                if (isset($_SESSION['ERRORS']['imageerror']))
                    echo $_SESSION['ERRORS']['imageerror'];

            ?>
        </sub>
    </div>

    <h2 class="h3 mt-3 mb-3 font-weight-normal text-muted text-center">Create an Account</h2>

    <div class="text-center mb-3">
        <small class="text-success font-weight-bold">
            <?php
                if (isset($_SESSION['STATUS']['signupstatus']))
                    echo $_SESSION['STATUS']['signupstatus'];

            ?>
        </small>
    </div>

    <div class="form-group">
        <label for="email" class="sr-only">Email address</label>
        <input type="email" id="email" name="email" placeholder="Email address" required autofocus>
        <sub class="text-danger">
            <?php
                if (isset($_SESSION['ERRORS']['emailerror']))
                    echo $_SESSION['ERRORS']['emailerror'];
            ?>
        </sub>
    </div>

    <br />

    <div class="form-group">
        <label for="password" class="sr-only">Password</label>
        <input type="password" id="password" name="password" placeholder="Password" required>
    </div>

    <br />

    <div class="form-group mb-4">
        <label for="confirmpassword" class="sr-only">Confirm Password</label>
        <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Confirm Password" required>
        <sub class="text-danger mb-4">
            <?php
                if (isset($_SESSION['ERRORS']['passworderror']))
                    echo $_SESSION['ERRORS']['passworderror'];
            ?>
        </sub>
    </div>

    <br />
<input id="agreeCheckbox" type="checkbox" /><label for="agreeCheckbox">I confirm I have read and agree to the <a href="../consent-form.php" title="Click here to read the consent form" rel="modal:open">consent form</a> and <a href="../data-policy.php" title="Click here to read our data policy" rel="modal:open">data policy</a>.</label>
<p>
<input value="Register" class="btn btn-lg btn-primary btn-block" type="submit" id="submitbutton" name='signupsubmit' disabled>
</p>
</form>
<script>
$(document).ready(function(){

    $('#agreeCheckbox').click(function () {
        $('#submitbutton').prop("disabled", !$("#agreeCheckbox").prop("checked")); 
    });
});
</script>
<?php
include '../assets/layouts/footer.php';
?>

<?php

define('TITLE', "Login");
include '../assets/layouts/header.php';
check_logged_out();
?>

<main>

    <form action="includes/login.inc.php" method="post">

        <?php insert_csrf_token(); ?>

        <?php

        if ( isset($_GET['redirect']) && $_GET['redirect'] != '') {
        ?>
        <input type="hidden" name="redirect" value="<?php echo $_GET['redirect']; ?>">

        <?php
        }
        ?>

        <h2>Login to your Account</h2>

        <p>Thanks for wanting to leave feedback on a Registered Reports manuscript you authored or reviewed.<br /><br />

        <b>Your feedback matters</b> - it can be used to motivate editors and publishers to improve journal practices and policies, and help authors decide where to submit manuscripts.<br /><br />

        We want to hear both your <b>positive and negative feedback</b> - there aren’t many channels that allow this, while fully protecting your anonymity.<br /><br />

        Lastly, your feedback will contribute to the research literature.<br /><br />

        <a href="../register/why_register.php" rel="modal:open">Why do I need to register?</a></p>
        <hr />
        <p>To add feedback, please login or <a href="../register/">register</a>.</p>
        <br />
        <div class="text-center mb-3">
            <small class="text-success font-weight-bold">
                <?php
                    if (isset($_SESSION['STATUS']['loginstatus']))
                        echo $_SESSION['STATUS']['loginstatus'];

                ?>
            </small>
        </div>

        <div class="form-group">
            <label for="email" class="sr-only">Email</label>
            <input type="text" id="email" name="email" placeholder="Email" required autofocus>
            <sub class="text-danger">
                <?php
                    if (isset($_SESSION['ERRORS']['noemail']))
                        echo $_SESSION['ERRORS']['noemail'];
                ?>
            </sub>
        </div>

        <br />

        <div class="form-group">
            <label for="password" class="sr-only">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <sub class="text-danger">
                <?php
                    if (isset($_SESSION['ERRORS']['wrongpassword']))
                        echo $_SESSION['ERRORS']['wrongpassword'];
                ?>
            </sub>
        </div>

        <br />

        <div class="col-auto my-1 mb-4">
            <div class="custom-control custom-checkbox mr-sm-2">
                <input type="checkbox" class="custom-control-input" id="rememberme" name="rememberme">
                <label class="custom-control-label" for="rememberme">Remember me</label>
            </div>
        </div>

        <br />

        <button class="btn btn-lg btn-primary btn-block" type="submit" value="loginsubmit" name="loginsubmit">Login</button>

        <p class="mt-3 text-muted text-center"><a href="../register/">register</a> | <a href="../reset-password/">forgot password?</a></p>
        
    </form>
</main>

<?php

include '../assets/layouts/footer.php'

?>
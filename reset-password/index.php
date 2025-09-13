<?php

define('TITLE', "Login");
include '../assets/layouts/header.php';
check_logged_out();

?>

                <?php if (isset($_GET['selector']) && isset($_GET['validator'])) { ?>

                    <form action="includes/reset.inc.php" method="post">

                        <?php
                            insert_csrf_token();

                            $selector = $_GET['selector'];
                            $validator = $_GET['validator'];
                        ?>

                        <input type="hidden" name="selector" value="<?php echo $selector; ?>">
                        <input type="hidden" name="validator" value="<?php echo $validator; ?>">



                        <h2>Reset password</h2>

                        <div class="text-center mb-3">
                            <small class="text-success font-weight-bold">
                                <?php
                                    if (isset($_SESSION['STATUS']['resetsubmit']))
                                        echo $_SESSION['STATUS']['resetsubmit'];

                                ?>
                            </small>
                        </div>

                        <div class="text-center mb-3">
                            <sub class="text-danger">
                                <?php
                                    if (isset($_SESSION['ERRORS']['passworderror']))
                                        echo $_SESSION['ERRORS']['passworderror'];
                                ?>
                            </sub>
                        </div>

                        <div class=" form-group">
                            <input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="New Password" autocomplete="new-password">
                        </div>

                        <div class=" form-group mb-5">
                            <input type="password" id="confirmpassword" name="confirmpassword" class="form-control" placeholder="Confirm Password" autocomplete="new-password">
                        </div>
                        <br />
                        <button class="btn btn-lg btn-primary btn-block mb-5 mt-4" type="submit" value="resetsubmit" name="resetsubmit">
                            Reset Password
                        </button>

                    </form>

                <?php } else { ?>

                    <form action="includes/sendtoken.inc.php" method="post">

                        <?php insert_csrf_token(); ?>

                        <h2>Reset password</h2>

                        <div class="text-center mb-3">
                                <?php
                                    if (isset($_SESSION['STATUS']['resentsend']))
                                        echo $_SESSION['STATUS']['resentsend'];

                                ?>
                        </div>

                        <div class="form-group">
                            <label for="email" class="sr-only">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Email" required autofocus>
                            <sub class="text-danger">
                                <?php
                                    if (isset($_SESSION['ERRORS']['emailerror']))
                                        echo $_SESSION['ERRORS']['emailerror'];
                                ?>
                            </sub>
                        </div>
                        <br />
                        <button class="btn btn-lg btn-primary btn-block mb-5 mt-4" type="submit" value="resentsend" name="resentsend">
                            Send Password Reset Link
                        </button>

                    </form>

                <?php } ?>
<!--     </div>
</div> -->


<?php

include '../assets/layouts/footer.php'

?>
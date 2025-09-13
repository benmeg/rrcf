<?php

session_start();

require '../../assets/setup/env.php';
require '../../assets/setup/db.inc.php';
require '../../assets/includes/security_functions.php';

if (isset($_GET['selector']) && isset($_GET['validator'])) {

    /*
    * -------------------------------------------------------------------------------
    *   Securing against Header Injection
    * -------------------------------------------------------------------------------
    */

    foreach($_GET as $key => $value){

        $_GET[$key] = _cleaninjections(trim($value));
    }



    $selector = $_GET['selector'];
    $validator = $_GET['validator'];

    if (empty($selector) || empty($validator)) {

        $_SESSION['STATUS']['verify'] = 'invalid token, please use new verification email';
        header("Location: ../");
        exit();
    }

    $sql = "SELECT * FROM auth_tokens WHERE auth_type='account_verify' AND selector=? AND expires_at >= NOW() LIMIT 1;";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {

        $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
        header("Location: ../");
        exit();
    }
    else {

        mysqli_stmt_bind_param($stmt, "s", $selector);
        mysqli_stmt_execute($stmt);
        $results = mysqli_stmt_get_result($stmt);

        if (!($row = mysqli_fetch_assoc($results))) {

            $_SESSION['STATUS']['verify'] = 'The verification link you have used is not valid - is it most likely your account is already verified (the link will only work once).<br /><br />Please try logging in directly.';

            header("Location: ../");
            exit();
        }
        else {

            $tokenBin = hex2bin($validator);
            $tokenCheck = password_verify($tokenBin, $row['token']);

            if ($tokenCheck === false) {

                $_SESSION['STATUS']['verify'] = 'invalid token, please use new verification email';
                header("Location: ../");
                exit();
            }
            else if ($tokenCheck === true) {

                $tokenEmail = $row['user_email'];

                $sql = 'SELECT * FROM users WHERE email=? LIMIT 1;';
                $stmt = mysqli_stmt_init($conn);

                if (!mysqli_stmt_prepare($stmt, $sql)){

                    $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
                    header("Location: ../");
                    exit();
                }
                else {

                    mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                    mysqli_stmt_execute($stmt);
                    $results = mysqli_stmt_get_result($stmt);

                    if (!$row = mysqli_fetch_assoc($results)) {
                        
                        $_SESSION['STATUS']['resentsend'] = 'invalid token, please use new verification email';
                        header("Location: ../");
                        exit();
                    }
                    else {

                        $sql = 'UPDATE users SET verified_at=NOW() WHERE email=?;';
                        $stmt = mysqli_stmt_init($conn);

                        if (!mysqli_stmt_prepare($stmt, $sql))
                        {
                            $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
                            header("Location: ../");
                            exit();
                        }
                        else {

                            mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                            mysqli_stmt_execute($stmt);

                            $sql = "DELETE FROM auth_tokens WHERE user_email=? AND auth_type='account_verify';";
                            $stmt = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($stmt, $sql)){

                                $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
                                header("Location: ../");
                                exit();
                            }
                            else {

                                mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                                mysqli_stmt_execute($stmt);
                                
                                if (isset($_SESSION['auth'])){

                                    $_SESSION['auth'] = 'verified';
                                }

                                // log user into system automatically after they've clicked
                                // the verify link from their email

                                $_SESSION['auth'] = 'verified';

                                $_SESSION['id']                 = $row['id'];
                                $_SESSION['email']              = $row['email'];
                                $_SESSION['academic_role']      = $row['academic_role'];
                                $_SESSION['academic_role_text'] = $row['academic_role_text'];
                                $_SESSION['gender']             = $row['gender'];
                                $_SESSION['gender_text']        = $row['gender_text'];
                                $_SESSION['ethnicity']          = $row['ethnicity'];
                                $_SESSION['verified_at']        = $row['verified_at'];
                                $_SESSION['created_at']         = $row['created_at'];
                                $_SESSION['updated_at']         = $row['updated_at'];
                                $_SESSION['deleted_at']         = $row['deleted_at'];
                                $_SESSION['last_login_at']      = $row['last_login_at'];

                                if (isset($_COOKIE["invite_uuid"]) && isset($_COOKIE["invite_stage"])) {

                                    $url = "../../feedback/selector.php?uuid=" . $_COOKIE["invite_uuid"] . "&stage=" . $_COOKIE["invite_stage"];
                                    header ("Location: " . $url);
                                }

                                else

                                {
                                    header ("Location: ../../home/");
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
else {

    header("Location: ../");
    exit();
}
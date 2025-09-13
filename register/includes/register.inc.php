<?php

session_start();

require '../../assets/includes/auth_functions.php';
require '../../assets/includes/datacheck.php';
require '../../assets/includes/security_functions.php';

check_logged_out();


if (isset($_POST['signupsubmit'])) {

    /*
    * -------------------------------------------------------------------------------
    *   Securing against Header Injection
    * -------------------------------------------------------------------------------
    */

    foreach($_POST as $key => $value){

        $_POST[$key] = _cleaninjections(trim($value));
    }

    /*
    * -------------------------------------------------------------------------------
    *   Verifying CSRF token
    * -------------------------------------------------------------------------------
    */

    if (!verify_csrf_token()){

        $_SESSION['STATUS']['signupstatus'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }

    require '../../assets/setup/db.inc.php';
   
    $email = input_filter($_POST['email']);
    $password = input_filter($_POST['password']);
    $passwordRepeat  = input_filter($_POST['confirmpassword']);

    /*
    * -------------------------------------------------------------------------------
    *   Data Validation
    * -------------------------------------------------------------------------------
    */

    if (empty($email) || empty($password) || empty($passwordRepeat)) {

        $_SESSION['ERRORS']['formerror'] = 'required fields cannot be empty, try again';
        header("Location: ../");
        exit();

    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['ERRORS']['emailerror'] = 'invalid email';
        header("Location: ../");
        exit();

    } else if ($password !== $passwordRepeat) {

        $_SESSION['ERRORS']['passworderror'] = 'passwords do not match';
        header("Location: ../");
        exit();

    } else {

        if (!availableEmail($conn, $email)){

            $_SESSION['ERRORS']['emailerror'] = 'email already taken';
            header("Location: ../");
            exit();
        }

        /*
        * -------------------------------------------------------------------------------
        *   User Creation
        * -------------------------------------------------------------------------------
        */

        $sql = "insert into users(email, password, created_at) values ( ?,?, NOW() )";

        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {

            $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
            header("Location: ../");
            exit();
        } 
        else {

            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

            mysqli_stmt_bind_param($stmt, "ss", $email, $hashedPwd);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            /*
            * -------------------------------------------------------------------------------
            *   Sending Verification Email for Account Activation
            * -------------------------------------------------------------------------------
            */
            
            require 'sendverificationemail.inc.php';

            $_SESSION['STATUS']['loginstatus'] = 'Account created, please check your email for a confirmation link which will log you into automatically.<br />It may take a few minutes to come through - if you have not received it, please check your spam/junk mail<br />';
            header("Location: ../../register/confirm.php");
            exit();
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} 
else {

    header("Location: ../");
    exit();
}

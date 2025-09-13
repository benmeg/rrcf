<?php
session_start();

require '../../assets/includes/security_functions.php';
require '../../assets/includes/auth_functions.php';
check_verified();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['update-profile'])) {

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

        $_SESSION['STATUS']['editstatus'] = 'Request could not be validated';
        header("Location: ../");
        exit();
    }


    require '../../assets/setup/db.inc.php';
    require '../../assets/includes/datacheck.php';

    $email = $_POST['email'];

        if (isset($_POST['gender'])) 

        $gender = input_filter($_POST['gender']);
    else
        $gender = NULL;


    if (isset($_POST['gender_text']) && $gender == "4") {

        $gender_text = input_filter($_POST['gender_text']);
    }

    else

    {
        $gender_text = NULL;
    }


    if (isset($_POST['ethnicity'])) {

        $ethnicity = input_filter($_POST['ethnicity']);
    }

    else

    {
        $ethnicity = NULL;
    }




    $oldPassword = $_POST['password'];
    $newpassword = $_POST['newpassword'];
    $passwordrepeat  = $_POST['confirmpassword'];


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['ERRORS']['emailerror'] = 'invalid email, try again';
        header("Location: ../");
        exit();
    } 
    if ($_SESSION['email'] != $email && !availableEmail($conn, $email)) {

        $_SESSION['ERRORS']['emailerror'] = 'email already taken';
        header("Location: ../");
        exit();
    }

        /*
        * -------------------------------------------------------------------------------
        *   Password Updation
        * -------------------------------------------------------------------------------
        */

        $passwordUpdated = false;

        if( !empty($oldPassword) || !empty($newpassword) || !empty($passwordRepeat)){

            include 'password-edit.inc.php';
        }
        
        if ($passwordUpdated) {

            /*
            * -------------------------------------------------------------------------------
            *   Sending notification email on password update
            * -------------------------------------------------------------------------------
            */

            $to = $_SESSION['email'];
            $subject = 'Password Updated';
            
            /*
            * -------------------------------------------------------------------------------
            *   Using email template
            * -------------------------------------------------------------------------------
            */

            $mail_variables = array();

            $mail_variables['APP_NAME'] = APP_NAME;
            $mail_variables['email'] = $_SESSION['email'];

            $message = file_get_contents("./template_notificationemail.php");

            foreach($mail_variables as $key => $value) {
                
                $message = str_replace('{{ '.$key.' }}', $value, $message);
            }
        
            $mail = new PHPMailer(true);
        
            try {
        
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USERNAME;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPSecure = MAIL_ENCRYPTION;
                $mail->Port = MAIL_PORT;
        
                $mail->setFrom(MAIL_USERNAME, APP_NAME);
                $mail->addAddress($to, APP_NAME);
        
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $message;
        
                $mail->send();
            } 
            catch (Exception $e) {
        
                
            }
        }


        /*
        * -------------------------------------------------------------------------------
        *   User Updation
        * -------------------------------------------------------------------------------
        */

        $sql = "UPDATE users SET email=?, gender=?, gender_text=?, ethnicity=?";

        if ($passwordUpdated){

            $sql .= ", password=? 
                    WHERE id=?;";
        }
        else{

            $sql .= " WHERE id=?;";
        }

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {

            $_SESSION['ERRORS']['scripterror'] = 'SQL ERROR';
            header("Location: ../");
            exit();
        } 
        else {

            if ($passwordUpdated){

                $hashedPwd = password_hash($newpassword, PASSWORD_DEFAULT);


                mysqli_stmt_bind_param($stmt, "ssssss", $email, $gender, $gender_text, $ethnicity, $hashedPwd, $_SESSION['id']
                );
            }
            else{

                mysqli_stmt_bind_param($stmt, "sssss", $email, $gender, $gender_text, $ethnicity, $_SESSION['id']
                );
            }

            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            $_SESSION['email']              = $email;
            $_SESSION['gender']             = $gender;
            $_SESSION['gender_text']        = $gender_text;
            $_SESSION['ethnicity']          = $ethnicity;

            $_SESSION['STATUS']['editstatus'] = '&#9989; Profile successfully updated';
            header("Location: ../");
            exit();
        }
    // }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} 
else {

    header("Location: ../");
    exit();
}

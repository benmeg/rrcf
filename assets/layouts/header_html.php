<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo (isset($page_description)) ? $page_description : APP_DESCRIPTION; ?>">
    <meta name="author" content="<?php echo APP_OWNER;  ?>">

    <title><?php echo TITLE . ' | ' . APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png" />

    <!-- PACE loading bar: https://github.com/CodeByZach/pace // -->
    <script src="../assets/js/pace.min.js" async importance="high"></script>
    <link rel="stylesheet" href="../assets/css/pace-theme-default.min.css" importance="high"> 
 
    <!-- Custom styles -->
    <link rel="stylesheet" type="text/css" href="../assets/css/app.css" />
    <link rel="stylesheet" type="text/css" href="custom.css" />

    <!-- JQuery -->
    <script type="text/javascript" src="../assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="../assets/js/jquery-ui.min.js"></script>

    <!-- Select2 -->
    <script type="text/javascript" src="../assets/js/select2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/select2.min.css" />
    
    <!-- jQuery Modal -->
    <script type="text/javascript" src="../assets/js/jquery.modal.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/css/jquery.modal.min.css" />

    <link rel="stylesheet" href="style.css" />

    <meta property="og:image" content="https://registeredreports.cardiff.ac.uk/feedback/assets/images/rrcf_logo.png"/>

    <meta property="og:title" content="Registered Reports Community Feedback"/>

    <meta property="og:description" content="Collecting feedback to better understand the community's experience of Registered Reports peer review. We welcome Stage 1 and 2 feedback by authors & reviewers."/>

    <meta property="og:image:width" content="447"/>

    <meta property="og:image:height" content="251"/>

</head>

<body>

    <?php require 'navbar.php'; ?>
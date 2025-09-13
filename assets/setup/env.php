<?php

// Set database connection variables depending on environment
switch ($_SERVER['HTTP_HOST']) {

	// dev
	case "localhost":
        $servername                   = 'localhost';
		$database_username;
        $database_username            = '';
        $database_password;
        $database_password            = '';
		$database_name;
        $database_name                = '';

        $vendor_path                  = '';

		if (!defined('MAIL_HOST'))                      define('MAIL_HOST', '');
		if (!defined('MAIL_USERNAME'))                  define('MAIL_USERNAME', '');
		if (!defined('MAIL_PASSWORD'))                  define('MAIL_PASSWORD', '');
		if (!defined('MAIL_ENCRYPTION'))                define('MAIL_ENCRYPTION', '');
		if (!defined('MAIL_PORT'))                      define('MAIL_PORT', );
		if (!defined('SMTP_AUTH'))						define('SMTP_AUTH', );
	break;

	// staging
	case "":
		$servername                   = '';
		$database_username            = '';
		$database_password            = '';
		$database_name                = '';

        $vendor_path                  = '';

        if (!defined('MAIL_HOST'))                      define('MAIL_HOST', '');
		if (!defined('MAIL_USERNAME'))                  define('MAIL_USERNAME', '');
		if (!defined('MAIL_PASSWORD'))                  define('MAIL_PASSWORD', '');
		if (!defined('MAIL_ENCRYPTION'))                define('MAIL_ENCRYPTION', '');
		if (!defined('MAIL_PORT'))                      define('MAIL_PORT', );
		if (!defined('SMTP_AUTH'))						define('SMTP_AUTH', );
	break;

	// live
    case "registeredreports.cardiff.ac.uk":
        $servername                   = '';
        $database_username            = '';
        $database_password            = '';
        $database_name                = '';

        $vendor_path                  = '';

		if (!defined('MAIL_HOST'))                      define('MAIL_HOST', '');
		if (!defined('MAIL_USERNAME'))                  define('MAIL_USERNAME', '');
		if (!defined('MAIL_PASSWORD'))                  define('MAIL_PASSWORD', '');
		if (!defined('MAIL_ENCRYPTION'))                define('MAIL_ENCRYPTION', '');
		if (!defined('MAIL_PORT'))                      define('MAIL_PORT', );
		if (!defined('SMTP_AUTH'))						define('SMTP_AUTH', );
    break;
}

require $vendor_path;

if (!defined('APP_NAME'))                       define('APP_NAME', 'Registered Reports Community Feedback');
if (!defined('APP_ORGANIZATION'))               define('APP_ORGANIZATION', 'Cardiff University');
if (!defined('APP_OWNER'))                      define('APP_OWNER', 'Ben Meghreblian');
if (!defined('APP_DESCRIPTION'))                define('APP_DESCRIPTION', 'Registered Reports Community Feedback');

if (!defined('ALLOWED_INACTIVITY_TIME'))        define('ALLOWED_INACTIVITY_TIME', time()+24*3600);

if (!defined('DB_DATABASE'))                    define('DB_DATABASE', $database_name);
if (!defined('DB_HOST'))                        define('DB_HOST', $servername);
if (!defined('DB_USERNAME'))                    define('DB_USERNAME', $database_username);
if (!defined('DB_PASSWORD'))                    define('DB_PASSWORD' , $database_password);
if (!defined('DB_PORT'))                        define('DB_PORT' ,'');
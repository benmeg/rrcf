<?php

setcookie("profile_reminder",  "1",  time()+30*24*60*60, "/");

header('Location: /feedback/home/');
exit;
?>
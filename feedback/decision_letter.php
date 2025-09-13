<?php

setcookie("decisionletter", "1", time()+5*24*60*60, "/");

header("Location: ../feedback/selector.php");

?>
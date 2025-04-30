<?php

setcookie("token", "", time() - 3600, "/", "", true, true);

header("Location: ../auth/login.php");
exit();
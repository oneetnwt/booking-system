<?php
if (isset($_COOKIE['token'])) {
    header("Location: home/home.php");
    exit();
} else {
    header("Location: auth/login.php");
    exit();
}

<?php

session_start();
require_once 'config.php';
$_SESSION['randomToken'] = bin2hex(random_bytes(32));



// if user is not logged in and action is not login
if (!isset($_SESSION['name']) || !isset($_SESSION['token'])) {
    // show login page
    include './assets/inc/login.inc.php';
    exit;
} else {
    $token = $_SESSION['token'];
    // if user is logged in
    include './assets/inc/chat.inc.php';
    exit;
}
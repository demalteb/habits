<?php

require_once(dirname(__FILE__) . '/local.php');

$_SESSION['user_id'] = null;
unset($_SESSION['user_id']);
session_destroy();

header('Location: login.php');

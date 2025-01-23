<?php
include 'config.php';
include 'functions.php';

session_unset();
session_destroy();
redirect('index.php');
?>
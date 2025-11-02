<?php
include __DIR__ . '/../config/config.php';

session_unset();
session_destroy();

session_start();
regenerateCSRFToken();

redirect('../index.php');
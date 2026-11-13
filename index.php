<?php
require_once __DIR__ . '/auth.php';
header('Location: ' . (empty($_SESSION['user']) ? 'login.php' : 'dashboard.php'));
exit;

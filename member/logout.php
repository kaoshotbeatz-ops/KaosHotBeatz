<?php
require_once __DIR__ . '/../lib.php';
unset($_SESSION['member_id']);
session_regenerate_id(true);
header('Location: /');

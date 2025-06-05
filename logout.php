<?php
require_once 'config.php';
require_once 'includes/functions.php';

$_SESSION = array();

session_destroy();

rediriger('login.php', 'Vous avez été déconnecté avec succès.'); 
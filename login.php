<?php
session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role']; // Pratique pour savoir si c'est un admin !
?>
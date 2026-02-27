<?php
require_once('inc/requires.php');

//create objects
$database = new MySQLDB();
$user = new visitor();

if(!$user->check_session() || !isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_type'] !== 'webmaster'))
{	
	header("location: login.php"); 
	exit();
} else {
	header("location: dashboard.php");
	exit();
}
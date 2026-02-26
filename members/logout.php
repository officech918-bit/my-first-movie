<?php
	require_once('inc/requires.php');
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	
	$user->destroy_session();
	header("location: index.php"); 
	//header("location: ".$_SERVER['HTTP_REFERER']);
	exit();
?>
<?php 
	//get class files
	include('inc/requires.php');
	
	session_destroy();
	header("location: index.php"); 
	exit();
?>
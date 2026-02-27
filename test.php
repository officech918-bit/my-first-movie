<?php
$to = "farooque@satyam raj.com";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: info@evokemediaservices.com" . "\r\n";
//"CC: somebodyelse@example.com";

mail($to,$subject,$txt,$headers);
?>
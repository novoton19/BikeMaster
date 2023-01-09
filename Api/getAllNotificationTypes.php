<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/09/23 11:37pm
		Version: 0.0.5.4
	Updated on
		Version: 0.0.5.4

	Description:
		Returns all of the notification types

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require NotificationTypesDb
	require_once(__DIR__.'/../Resources/Php/Db/notificationTypesDb.php');

	#Create NotificationTypesDb
	$notificationTypes = new NotificationTypesDb();

	#Echo result
	echo json_encode([
		'success' => $notificationTypes->success,
		'notificationTypes' => $notificationTypes->all
	]);
	#Unset unnecessary variables
	unset(
		$notificationTypes
	);
?>
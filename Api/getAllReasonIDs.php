<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 10:13pm
		Version: 0.0.3.2
	Updated on
		Version: 0.0.3.2

	Description:
		Returns all of the reason IDs

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require reasonIDsDb
	require_once(__DIR__.'/../Resources/Php/Db/reasonIDsDb.php');

	#Create reasonIDsDb
	$reasonIDs = new ReasonIDsDb();

	#Echo result
	echo json_encode([
		'Success' => $reasonIDs->success,
		'ReasonIDs' => $reasonIDs->all
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs
	);
?>
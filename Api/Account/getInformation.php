<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 11:11pm
		Version: 0.0.3.2.1
	Updated on
		Version: 0.0.3.2.1

	Description:
		Get account information

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#Require status
	require_once(__DIR__.'/status.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();

	#Default reasonID
	$reasonID = null;
	$reason = null;
	#Other variables
	#$loginStatusResult from status.php
	$loggedIn = false;
	$account = null;
	

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Getting login status
		$loggedIn = $loginStatusResult['LoggedIn'];
		#Getting account
		$account = $loginStatusResult['Account'];
		#Checking if logged in
		if ($loggedIn)
		{
			#Set reason ID
			$reasonID = $reasonIDs->Accepted;
		}
		else
		{
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Not logged in';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (1)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Echo result
	echo json_encode([
		'Account' => $account,
		'ReasonID' => $reasonID,
		'Reason' => $reason,
		'LoginStatusResult' => $loginStatusResult
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$reasonID,
		$reason,
		$loginStatusResult,
		$loggedIn,
		$account
	);
?>
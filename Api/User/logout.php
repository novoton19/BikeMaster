<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 10:20pm
		Version: 0.0.3.2
	Updated on
		Version: 0.0.3.3

	Description:
		Logout

	Changes:
		Version 0.0.3.3 - Correct standards
	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	session_start();
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#Require settings
	require_once(__DIR__.'/../../Resources/Php/Db/settingsDb.php');
	#Require status
	require_once(__DIR__.'/status.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating settingsDb
	$settings = new SettingsDb();

	#Default reasonID
	$reasonID = null;
	$reason = null;
	#Whether logout succeeded
	$loggedOut = false;
	#Other variables
	#$loginStatusResult from status.php
	$loggedIn = false;
	$projectName = null;
	$projectNameSession = null;
	

	#Checking if succeeded to get reasonIDs and settings
	if ($reasonIDs->success and $settings->success)
	{
		#Session project name
		$projectName = $settings->ProjectName;
		#Getting login status
		$loggedIn = $loginStatusResult['loggedIn'];
		#Checking if logged in
		if ($loggedIn)
		{
			#Logging out
			unset($_SESSION[$projectName]['login']);
			#Set reason ID
			$reasonID = $reasonIDs->Accepted;
		}
		else
		{
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Not logged in';
		}
	}
	elseif (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (1)';
	}
	else#if (!$settings->success)
	{
		#Set reasonID
		$reasonID = $reasonIDs->DatabaseError;
		$reason = 'Server experienced an error while processing the request (2)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Echo result
	echo json_encode([
		'success' => $loggedOut,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'loginStatusResult' => $loginStatusResult
	]);
	#Close session
	session_write_close();
	#Unset unnecessary variables
	unset(
		$settings,
		$reasonIDs,
		$reasonID,
		$reason,
		$loggedOut,
		$loginStatusResult,
		$loggedIn,
		$projectName,
		$projectNameSession
	);
?>
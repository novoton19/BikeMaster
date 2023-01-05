<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 08:37pm
		Version: 0.0.4
	Updated on
		Version: 0.0.4

	Description:
		Saves user's journey to the database

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	#header('Content-Type: application/json; charset=utf-8');
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#Require status
	require_once(__DIR__.'/../User/status.php');
	#Require journey validation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/saveJourney.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating JourneyValidation
	$validation = new JourneyValidation();

	#Whether succeeded
	$success = false;
	$reasonID = null;
	$reason = null;
	
	#Track to be submitted
	$track = null;

	#Other variables
	$loggedIn = null;
	$account = null;
	$userID = null;

	#Received inputs
	$inputs = [
		'track' => null
	];
	#Input reasons
	$inputReasons = [
		'track' => null
	];
	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Getting login status
		$loggedIn = $loginStatusResult['loggedIn'];
		#Checking if logged in
		if ($loggedIn)
		{
			#Getting account
			$account = $loginStatusResult['account'];
			$userID = $account['id'];

			#Checking if post exists
			if ($_POST)
			{
				#Getting track
				$track = GeneralFunctions::getValue($_POST, 'track', []);
				#Adding to inputs
				$inputs['track'] = $track;
				
				#Validating track
				$trackValidation = $validation->validateTrack($track);
				#Adding to input reasons
				$inputReasons['track'] = $trackValidation;
				#Checking if valid
				if ($trackValidation['valid'])
				{
					#Valid
					$success = true;
				}
				else#if (!$trackValidation['valid'])
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Track not valid';
				}
			}
			else#if (!$_POST)
			{
				#No post
				$reasonID = $reasonIDs->NoPost;
				$reason = 'No journey submitted';
			}
		}
		else#if (!$loggedIn)
		{
			#Not logged in
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
		'success' => $success,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputReasons' => $inputReasons,
		'inputs' => $inputs,
		'loginStatusResult' => $loginStatusResult
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$success,
		$reasonID,
		$reason
	);
?>
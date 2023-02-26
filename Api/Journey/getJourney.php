<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/24/23 06:19pm
		Version: 0.4.3
	Updated on
		Version: 0.4.3

	Description:
		Loads user's journeys

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
	#Require settings DB
	require_once(__DIR__.'/../../Resources/Php/Db/settingsDb.php');
	#Require JourneysValidation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/journeys.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	#Require journey classes
	require_once(__DIR__.'/../../Resources/Php/journey.php');
	#Require journey loader
	require_once(__DIR__.'/../../Resources/Php/journeyLoader.php');
	#Require login status
	require_once(__DIR__.'/../User/status.php');


	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating SettingsDb
	$settingsDb = new SettingsDb();
	#Creating JourneysValidation
	$journeysValidation = new JourneysValidation();
	#Creating JourneyLoader
	$journeyLoader = new JourneyLoader();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;
	
	#Requested ID
	$id = null;
	$page = null;
	$type = null;
	#Resulting journeys
	$result = [];
	$resultsCount = null;
	
	#Other variables
	$pageSize = null;
	$account = null;
	$accountID = null;
	$latitude = null;
	$longitude = null;
	$home = null;
	$idValidation = null;
	$pageValidation = null;
	$typeValidation = null;
	$querySuccess = null;
	$queryResults = null;
	$queryResult = null;
	$journey = null;
	$segment = null;
	$trackPoint = null;
	$journeyNum = null;
	$segmentNum = null;
	
	#Received inputs
	$inputs = [
		'id' => null
	];
	#Input reasons
	$inputReasons = [
		'id' => null
	];
	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success and $settingsDb->success)
	{
		#Getting page size
		$pageSize = $settingsDb->PageSize;
		#Checking if Get exists
		if ($_GET)
		{
			#Checking if logged in
			if ($loginStatusResult['loggedIn'])
			{
				#Getting accountID
				$account = $loginStatusResult['account'];
				$accountID = $account['id'];

				#Getting journey ID
				$id = GeneralFunctions::getValue($_GET, 'id');

				#Adding received inputs
				$inputs['id'] = $id;

				#Validating inputs
				$idValidation = $journeysValidation->validateRecordID($id);

				#Adding input reasons
				$inputReasons['id'] = $idValidation;

				#Ensure numbers
				$id = intval($id);

				#Checking if inputs are valid
				if ($idValidation['valid'])
				{
					$result = $journeyLoader->loadJourney($id);
				}
				else#if (!inputs valid)
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'One of the inputs is invalid';
				}
			}
			elseif (!$loginStatusResult['success'])
			{
				$reasonID = $reasonIDs->Error;
				$reason = 'Could not verify that user is signed in';
			}
			else#if (!$loginStatusResult['loggedIn'] or !$loginStatusResult['success'])
			{
				$reasonID = $reasonIDs->NotLoggedIn;
				$reason = 'Not logged in';
			}
		}
		else#if (!$_GET)
		{
			$reasonID = $reasonIDs->NoGet;
			$reason = 'Type is required';
		}
	}
	elseif (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (3)';
	}
	else#if (!$settingsDb->success)
	{
		#Cannot get settings
		$reasonID = $reasonIDs->DatabaseError;
		$reason = 'Server experienced an error while processing the request (4)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Echo result
	echo json_encode([
		'success' => $success,
		'result' => $result,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'loginStatusResult' => $loginStatusResult,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$settingsDb,
		$journeysValidation,
		$journeyLoader,
		$success,
		$reasonID,
		$reason,
		$id,
		$page,
		$type,
		$result,
		$resultsCount,
		$pageSize,
		$account,
		$accountID,
		$latitude,
		$longitude,
		$home,
		$idValidation,
		$pageValidation,
		$typeValidation,
		$querySuccess,
		$queryResults,
		$queryResult,
		$journey,
		$segment,
		$trackPoint,
		$journeyNum,
		$segmentNum,
		$inputs,
		$inputReasons,
	);
?>
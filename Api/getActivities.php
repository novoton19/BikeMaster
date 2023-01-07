<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/07/23 09:43pm
		Version: 0.0.4.3
	Updated on
		Version: 0.0.4.3

	Description:
		Returns activities of users

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
	#Require journeysDb
	require_once(__DIR__.'/../Resources/Php/Db/journeysDb.php');
	#Require status
	require_once(__DIR__.'/User/status.php');
	#Require validation
	require_once(__DIR__.'/../Resources/Php/InputValidation/getActivities.php');
	#Require informator
	require_once(__DIR__.'/../Resources/Php/journeyInfo.php');
	#Require general functions
	require_once(__DIR__.'/../Resources/Php/general.php');

	#Create ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Create JourneysDb
	$journeysDb = new JourneysDb();
	#Create GetActivitiesValidation
	$validation = new GetActivitiesValidation();
	#Create JourneyInformator
	$informator = new JourneyInformator();

	#Whether succeeded
	$success = false;
	#Request reasonID
	$reasonID = null;
	$reason = null;
	#Resulting activities
	$activities = [];

	#Other variables
	#$loginStatusResult
	$maxTime = null;
	$maxTimeValidation = null;
	$maxTimeValidationStatus = null;
	$userID = null;
	$querySuccess = null;
	$journeyIDs = null;
	$journeyID = null;

	#Received inputs and their reasons
	$inputs = [
		'maxTime' => null
	];
	$inputReasons = [
		'maxTime' => null
	];

	#Checking if succeeded to load reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if get exists
		if ($_GET)
		{
			#Getting maximum time
			$maxTime = GeneralFunctions::getValue($_GET, 'maxTime');
			#Adding time to inputs
			$inputs['maxTime'] = $maxTime;
		}

		#Validating time
		$maxTimeValidation = $validation->validateTime($maxTime);
		#Getting filtered value
		$maxTime = $maxTimeValidation['value'];
		#Getting status
		$maxTimeValidationStatus = $maxTimeValidation['status'];
		#Adding status to reasons
		$inputReasons['maxTime'] = $maxTimeValidationStatus;

		#Checking if time is valid
		if ($maxTimeValidationStatus['valid'])
		{
			#Checking if loginStatus succeeded in getting information
			if ($loginStatusResult['success'])
			{
				if ($loginStatusResult['loggedIn'])
				{
					#Generate response relevant to user
					#Getting userID
					$userID = $loginStatusResult['account']['id'];
					#Getting journeyIDs
					list($querySuccess, $journeyIDs, ) = $journeysDb->getUserJourneyIDs($userID, $maxTime);

					#Checking if query succeeded
					if ($querySuccess)
					{
						#Adding journey informations
						foreach ($journeyIDs as $key => $journeyID)
						{
							#Adding information
							array_push($activities, $informator->getInformation($journeyID));
						}
					}
					$success = true;
					$reasonID = $reasonIDs->Accepted;
				}
				else#if (!$loginStatusResult['loggedIn'])
				{
					#Generate general response
					$success = true;
					$reasonID = $reasonIDs->Accepted;
				}
			}
			else#if (!$loginStatusResult['success'])
			{
				#Not succeeded to get information
				$reasonID = $reasonIDs->Error;
				$reason = 'Server experienced an error while getting login status';
			}
		}
		else#if (!$maxTimeValidationStatus['valid'])
		{
			#Not valid
			$reasonID = $reasonIDs->InvalidInputs;
			$reason = 'Invalid inputs';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Error while loading reasonIDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (1)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		#Default reasonID
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Echo result
	echo json_encode([
		'success' => $success,
		'activities' => $activities,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$journeysDb,
		$validation,
		$informator,
		$success,
		$reasonID,
		$reason,
		$activities,
		$maxTime,
		$maxTimeValidation,
		$maxTimeValidationStatus,
		$userID,
		$querySuccess,
		$journeyIDs,
		$journeyID,
		$inputs,
		$inputReasons
	);
?>
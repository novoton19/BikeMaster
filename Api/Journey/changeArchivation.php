<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/25/23 08:03am
		Version: 0.4.5
	Updated on
		Version: 0.4.5

	Description:
		Changes archivation

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
	#Require SettingsDb
	require_once(__DIR__.'/../../Resources/Php/Db/settingsDb.php');
	#Require JourneysDb
	require_once(__DIR__.'/../../Resources/Php/Db/journeysDb.php');
	#Require JourneysValidation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/journeys.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	#Require login status
	require_once(__DIR__.'/../User/status.php');


	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating SettingsDb
	$settingsDb = new SettingsDb();
	#Creating JourneysDb
	$journeysDb = new JourneysDb();
	#Creating JourneysValidation
	$journeysValidation = new JourneysValidation();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;
	
	#Requested ID
	$id = null;
	
	#Other variables
	$account = null;
	$accountID = null;
	$idValidation = null;
	$querySuccess = null;
	$journey = null;
	
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
				$idValidation = $journeysValidation->validateRecordID($id, $accountID);

				#Adding input reasons
				$inputReasons['id'] = $idValidation;

				#Ensure number
				$id = intval($id);

				#Checking if inputs are valid
				if ($idValidation['valid'])
				{
					#Getting journey
					list($querySuccess, $journey, ) = $journeysDb->getJourney($id);
					#Checking if success
					if ($querySuccess)
					{
						#Getting parent user ID
						$journeyUserID = intval($journey['UserID']);
						#Checking if user owns this journey
						if ($journeyUserID === $accountID)
						{
							#Changing archivation
							$querySuccess = $journeysDb->changeArchivation($id, $accountID);
							#Checking if success
							if ($querySuccess)
							{
								$success = true;
								$reasonID = $reasonIDs->Accepted;
							}
							else#if (!$querySuccess)
							{
								$reasonID = $reasonIDs->DatabaseError;
								$reason = 'Server experienced an error while processing the request (1)';
							}
						}
						else#if (!$journeyUserID !== $accountID)
						{
							$reasonID = $reasonIDs->NotAllowed;
							$reason = 'Journey doesn\'t belong to the user';
						}
					}
					else#if (!$querySuccess)
					{
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (2)';
					}
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
			else#if (!$loginStatusResult['loggedIn'] and $loginStatusResult['success'])
			{
				$reasonID = $reasonIDs->NotLoggedIn;
				$reason = 'Not logged in';
			}
		}
		else#if (!$_GET)
		{
			$reasonID = $reasonIDs->NoGet;
			$reason = 'ID is required';
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
		$journeysDb,
		$journeysValidation,
		$success,
		$reasonID,
		$reason,
		$id,
		$account,
		$accountID,
		$idValidation,
		$querySuccess,
		$journey,
		$inputs,
		$inputReasons
	);
?>
<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/26/23 10:51pm
		Version: 0.6.1
	Updated on
		Version: 0.6.2

	Description:
		Create competition

	Changes:
		Version 0.6.2 - Unset variables
	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require ReasonIDsDb
	require_once(__DIR__.'/../../../Resources/Php/Db/reasonIDsDb.php');
	#Require CompetitionsDb
	require_once(__DIR__.'/../../../Resources/Php/Db/competitionsDb.php');
	#Require FriendRelationsValidation
	require_once(__DIR__.'/../../../Resources/Php/InputValidation/friendRelations.php');
	#Require CompetitionsValidation
	require_once(__DIR__.'/../../../Resources/Php/InputValidation/competitions.php');
	#Require login status
	require_once(__DIR__.'/../../User/status.php');
	#Require general functions
	require_once(__DIR__.'/../../../Resources/Php/general.php');

	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating CompetitionsDb
	$competitionsDb = new CompetitionsDb();
	#Creating FriendRelationsValidation
	$friendValidation = new FriendRelationsValidation();
	#Creating CompetitionsValidation
	$competitionsValidation = new CompetitionsValidation();

	#Whether success
	$success = false;
	#Reason
	$reasonID = null;
	$reason = null;

	#Received inputs
	$requestedUserID = null;
	$title = null;
	$description = null;
	$distance = null;
	
	#Other variables
	#$loginStatusResult;
	$account = null;
	$accountID = null;
	$querySuccess = false;
	$inputs = [
		'userID' => null,
		'title' => null,
		'description' => null,
		'distance' => null
	];
	$inputReasons = [
		'userID' => null,
		'title' => null,
		'description' => null,
		'distance' => null
	];
	
	#Checking if reasonIDs have loaded
	if ($reasonIDs->success)
	{
		#Checking if logged in
		if ($loginStatusResult['loggedIn'])
		{
			#Getting account
			$account = $loginStatusResult['account'];
			$accountID = $account['id'];

			#Checking if get exists
			if ($_GET)
			{
				#Getting values
				$requestedUserID = GeneralFunctions::getValue($_GET, 'userID');
				$title = GeneralFunctions::getValue($_GET, 'title');
				$description = GeneralFunctions::getValue($_GET, 'description');
				$distance = GeneralFunctions::getValue($_GET, 'distance');
				#Adding inputs
				$inputs['userID'] = $requestedUserID;
				$inputs['title'] = $title;
				$inputs['description'] = $description;
				$inputs['distance'] = $distance;
			}
			#Validating inputs
			$requestedUserIDValidation = $friendValidation->validateUserID($requestedUserID);
			$titleValidation = $competitionsValidation->validateTitle($title);
			$descriptionValidation = $competitionsValidation->validateDescription($description);
			$distanceValidation = $competitionsValidation->validateDistance($distance);
			#Adding input reasons
			$inputReasons['userID'] = $requestedUserIDValidation;
			$inputReasons['title'] = $titleValidation;
			$inputReasons['description'] = $descriptionValidation;
			$inputReasons['distance'] = $distanceValidation;

			#Checking if inputs are valid
			if ($requestedUserIDValidation['valid'] and $titleValidation['valid'] and $descriptionValidation['valid'] and $distanceValidation['valid'])
			{
				#Converting ID and distance to numbers
				$requestedUserID = intval($requestedUserID);
				$distance = floatval($distance);

				#Checking if can create competition
				if ($accountID !== $requestedUserID)
				{
					#Creating competition
					$querySuccess = $competitionsDb->create($accountID, $requestedUserID, $title, $description, $distance);
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
				else#if ($accountID === $requestedUserID)
				{
					#User attempted to create competition with himself
					$reasonID = $reasonIDs->NotAllowed;
					$reason = 'Cannot create competition with yourself (make some friends man, really)';
				}
			}
			else#if (!inputs valid)
			{
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'One or more inputs is invalid';
			}
		}
		elseif (!$loginStatusResult['success'])
		{
			$reasonID = $reasonIDs->DatabaseError;
			$reason = 'Server experienced an error while processing the request (2)';
		}
		else#if (!$loginStatusResult['loggedIn'] and loginStatusResult['success'])
		{
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Must be logged in';
		}
	}
	else#if (!$reasonIDs->success)
	{
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (3)';
	}
	#Checking if reasonID is set
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Printing result
	echo json_encode([
		'success' => $success,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	unset(
		$reasonIDs,
		$competitionsDb,
		$friendValidation,
		$competitionsValidation,
		$success,
		$reasonID,
		$reason,
		$requestedUserID,
		$title,
		$description,
		$distance,
		$account,
		$accountID,
		$querySuccess,
		$inputs,
		$inputReasons
	);
?>
<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/27/23 11:37am
		Version: 0.6.3
	Updated on
		Version: 0.6.3

	Description:
		Respond to competition invitation

	Changes:

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
	#Creating CompetitionsValidation
	$competitionsValidation = new CompetitionsValidation();

	#Whether success
	$success = false;
	#Reason
	$reasonID = null;
	$reason = null;

	#Received inputs
	$responseType = null;
	$competitionID = null;
	
	#Other variables
	#$loginStatusResult;
	$account = null;
	$accountID = null;
	$querySuccess = false;
	$inputs = [
		'responseType' => null,
		'competitionID' => null
	];
	$inputReasons = [
		'responseType' => null,
		'competitionID' => null
	];
	
	#Checking if reasonIDs and settings have loaded
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
				$responseType = GeneralFunctions::getValue($_GET, 'responseType');
				$competitionID = GeneralFunctions::getValue($_GET, 'competitionID');
				#Adding inputs
				$inputs['responseType'] = $responseType;
				$inputs['competitionID'] = is_null($competitionID) ? null : intval($competitionID);
			}
			#Validating inputs
			$responseTypeValidation = $competitionsValidation->validateResponseType($responseType);
			$competitionIDValidation = $competitionsValidation->validateCompetitionID($competitionID);
			#Adding input reasons
			$inputReasons['responseType'] = $responseTypeValidation;
			$inputReasons['competitionID'] = $competitionIDValidation;

			#Checking if inputs are valid
			if ($responseTypeValidation['valid'] and $competitionIDValidation['valid'])
			{
				#Checking response type
				if ($responseType === 'accept')
				{
					#Accept competition
					$querySuccess = $competitionsDb->acceptCompetition($competitionID, $accountID);
				}
				else#if ($responseType === 'decline')
				{
					#Decline competition
					$querySuccess = $competitionsDb->declineCompetition($competitionID, $accountID);
				}
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
		'inputReasons' => $inputReasons,
		'loginStatusResult' => $loginStatusResult
	]);
	unset(
		$reasonIDs,
		$competitionsDb,
		$competitionsValidation,
		$success,
		$reasonID,
		$reason,
		$responseType,
		$competitionID,
		$loginStatusResult,
		$account,
		$accountID,
		$querySuccess,
		$inputs,
		$inputReasons
	);
?>
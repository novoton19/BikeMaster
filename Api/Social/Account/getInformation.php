<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 06:42pm
		Version: 0.0.3.3
	Updated on
		Version: 0.3.6

	Description:
		Get specified account information

	Changes:
		Version 0.0.5.2 - Added friends count
		Version 0.3.6 - Check if logged in, file location changed
	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require reason IDs
	require_once(__DIR__.'/../../../Resources/Php/Db/reasonIDsDb.php');
	#Require UsersDb
	require_once(__DIR__.'/../../../Resources/Php/Db/usersDb.php');
	#Require validation
	require_once(__DIR__.'/../../../Resources/Php/InputValidation/friendRelations.php');
	#Require login status
	require_once(__DIR__.'/../../User/status.php');
	#Require general functions
	require_once(__DIR__.'/../../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating validation
	$validation = new FriendRelationsValidation();
	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;

	#Account ID
	$id = null;
	$account = [];
	
	#Other variables
	$idValidation = null;
	$querySuccess = null;
	$accountExists = null;
	
	#Received inputs
	$inputs = [
		'id' => null
	];
	#Input reasons
	$inputReasons = [
		'id' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if logged in
		if ($loginStatusResult['loggedIn'])
		{
			#Checking if Get exists
			if ($_GET)
			{
				#Getting ID
				$id = GeneralFunctions::getValue($_GET, 'id');
				#Adding ID to inputs
				$inputs['id'] = $id;

				#Validating user ID
				$idValidation = $validation->validateUserID($id);
				#Adding input reason
				$inputReasons['id'] = $idValidation;
				#Checking validity
				if ($idValidation['valid'])
				{
					#Getting account by ID
					list($querySuccess, $account, $accountExists) = $usersDb->getUserByIDSecure($id);
					#Checking if succeeded
					if ($querySuccess and $accountExists)
					{
						$success = true;
						$reasonID = $reasonIDs->Accepted;
					}
					elseif (!$querySuccess)
					{
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (1)';
					}
					else#if (!$accountExists)
					{
						$reasonID = $reasonIDs->UserNotFound;
						$reason = 'Requested user does not exist';
					}
				}
				else#if (!$idValidation['valid'])
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'ID not found';
				}
			}
			else
			{
				$reasonID = $reasonIDs->NoGet;
				$reason = 'No user specified';
			}
		}
		elseif (!$loginStatusResult['success'])
		{
			$reasonID = $reasonIDs->Error;
			$reason = 'Server experienced an error while processing the request (2)';
		}
		else#if (!$loginStatusResult['loggedIn'])
		{
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Login required';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (3)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Echo result
	echo json_encode([
		'success' => $success,
		'account' => [
			'id' => intval($id),
			'username' => GeneralFunctions::getValue($account, 'Username'),
			#'email' => GeneralFunctions::getValue($account, 'Email'),
			'description' => GeneralFunctions::getValue($account, 'Description'),
			'profilePictureUrl' => GeneralFunctions::getValue($account, 'ProfilePictureUrl'),
			'registrationTime' => intval(GeneralFunctions::getValue($account, 'RegistrationTime')),
			'friendsCount' => intval(GeneralFunctions::getValue($account, 'FriendsCount'))
		],
		'inputs' => $inputs,
		'inputReasons' => $inputReasons,
		'reasonID' => $reasonID,
		'reason' => $reason
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$usersDb,
		$validation,
		$loginStatusResult,
		$success,
		$reasonID,
		$reason,
		$id,
		$account,
		$idValidation,
		$querySuccess,
		$accountExists,
		$inputs,
		$inputReasons,
	);
?>
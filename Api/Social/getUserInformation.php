<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 06:42pm
		Version: 0.0.3.3
	Updated on
		Version: 0.0.5.2

	Description:
		Get specified account information

	Changes:
		Version 0.0.5.2 - Added friends count
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
	#Require UsersDb
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;

	#Account ID
	$id = null;
	$account = [];
	
	#Other variables
	$querySuccess = null;
	
	#Received inputs
	$inputs = [
		'id' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if Get exists
		if ($_GET)
		{
			#Getting ID
			$id = intval(GeneralFunctions::getValue($_GET, 'id'));
			#Adding ID to inputs
			$inputs['id'] = $id;
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
		else
		{
			$reasonID = $reasonIDs->NoGet;
			$reason = 'No user specified';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (2)';
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
			'id' => $id,
			'username' => $account['Username'],
			#'email' => $account['Email'],
			'registrationTime' => intval($account['RegistrationTime']),
			'friendsCount' => intval($account['FriendsCount'])
		],
		'inputs' => $inputs,
		'reasonID' => $reasonID,
		'reason' => $reason
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$usersDb,
		$success,
		$reasonID,
		$reason,
		$username,
		$account,
		$querySuccess
	);
?>
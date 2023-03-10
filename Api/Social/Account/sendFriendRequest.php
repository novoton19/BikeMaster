<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/08/23 11:32pm
		Version: 0.0.5
	Updated on
		Version: 0.3.6

	Description:
		Send friend request

	Changes:
		Version 0.0.5.3 - Send notification
		Version 0.3.6 - Change file location
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
	#Require notificationTypesDb
	require_once(__DIR__.'/../../../Resources/Php/Db/notificationTypesDb.php');
	#Require notificationsDb
	require_once(__DIR__.'/../../../Resources/Php/Db/notificationsDb.php');
	#Require FriendRelationsDb
	require_once(__DIR__.'/../../../Resources/Php/Db/friendRelationsDb.php');
	#Require FriendRelationsValidation
	require_once(__DIR__.'/../../../Resources/Php/InputValidation/friendRelations.php');
	#Require general functions
	require_once(__DIR__.'/../../../Resources/Php/general.php');
	#Require login status
	require_once(__DIR__.'/../../User/status.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating NotificationTypesDb
	$notificationTypes = new NotificationTypesDb();
	#Creating NotificationsDb
	$notificationsDb = new NotificationsDb();
	#Creating FriendRelationsDb
	$friendRelationsDb = new FriendRelationsDb();
	#Creating FriendRelationsValidation
	$validation = new FriendRelationsValidation();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;
	#Operation reason
	$operationReason = null;

	#ReceiverID
	$receiverID = null;
	#Account ID
	$id = null;
	$account = [];
	
	#Other variables
	$querySuccess = null;
	
	#Received inputs
	$inputs = [
		'receiverID' => null
	];
	$inputReasons = [
		'receiverID' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success and $notificationTypes->success)
	{
		#Checking if Post exists
		if ($_GET)
		{
			#Getting ReceiverID
			$receiverID = GeneralFunctions::getValue($_GET, 'receiverID');
			#Adding ID to inputs
			$inputs['receiverID'] = $receiverID;

			#Validating ID
			$receiverIDValidation = $validation->validateUserID($receiverID);
			#Adding input reason
			$inputReasons['receiverID'] = $receiverIDValidation;
			#Checking if valid
			if ($receiverIDValidation['valid'])
			{
				#Convert to integer
				$receiverID = intval($receiverID);

				#Checking if logged in
				if ($loginStatusResult['loggedIn'])
				{
					#Getting account
					$account = $loginStatusResult['account'];
					$id = $account['id'];

					#Checking if can create relation
					$createRelationValidation = $validation->canSendRequest($id, $receiverID);
					#Adding operation reason
					$operationReason = $createRelationValidation;
					#Checking if valid
					if ($createRelationValidation['allowed'])
					{
						#Sending a friend request
						list($querySuccess, , ) = $friendRelationsDb->sendRequest($id, $receiverID);
						#Checking if succeeded
						if ($querySuccess)
						{
							#Success
							$success = true;
							$reasonID = $reasonIDs->Accepted;
							#Attempt to send notification
							$notificationsDb->create($notificationTypes->SendRequest, $receiverID, $id);
						}
						else#if (!$querySuccess)
						{
							$reasonID = $reasonIDs->DatabaseError;
							$reason = 'Server experienced an error while processing the request (1)';
						}
					}
					elseif (!$createRelationValidation['success'])
					{
						#Not succeeded
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (2)';
					}
					else#if (!$createRelationValidation['allowed'])
					{
						#Not allowed
						$reasonID = $reasonIDs->NotAllowed;
						$reason = 'Cannot send a friend request';
					}
				}
				elseif (!$loginStatusResult['success'])
				{
					#Not succeeded to get information
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (3)';
				}
				else#if (!$loginStatusResult['loggedIn'])
				{
					$reasonID = $reasonIDs->NotLoggedIn;
					$reason = 'Not logged in';
				}
			}
			elseif (!$receiverIDValidation['success'])
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (4)';
			}
			else#if (!$receiverIDValidation['valid'])
			{
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'Invalid inputs received';
			}
		}
		else#if (!$_GET)
		{
			$reasonID = $reasonIDs->NoGet;
			$reason = 'No user specified';
		}
	}
	elseif (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (5)';
	}
	else#if (!$notificationTypes->success)
	{
		#Cannot get notificationTypes
		$reasonID = $reasonIDs->DatabaseError;
		$reason = 'Server experienced an error while processing the request (6)';
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
		'operationReason' => $operationReason,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$notificationTypes,
		$notificationsDb,
		$usersDb,
		$success,
		$reasonID,
		$reason,
		$username,
		$account,
		$querySuccess
	);
?>
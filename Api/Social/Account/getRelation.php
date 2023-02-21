<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/09/23 07:08pm
		Version: 0.0.5.1
	Updated on
		Version: 0.0.5.1

	Description:
		Get friend relation

	Changes:
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
	#Creating FriendRelationsDb
	$friendRelationsDb = new FriendRelationsDb();
	#Creating FriendRelationsValidation
	$validation = new FriendRelationsValidation();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;

	#Resulting relation
	$relation = [
		'exists' => false,
		'senderID' => null,
		'receiverID' => null,
		'accepted' => null,
		'requestTime' => null,
		'acceptTime' => null
	];

	#Other user ID
	$otherUserID = null;
	#Account ID
	$id = null;
	$account = [];
	
	#Other variables
	$querySuccess = null;
	$queryRelation = null;
	$queryRelationExists = null;
	
	#Received inputs
	$inputs = [
		'id' => null
	];
	$inputReasons = [
		'id' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if Post exists
		if ($_GET)
		{
			#Getting UserID
			$otherUserID = GeneralFunctions::getValue($_GET, 'id');
			#Adding ID to inputs
			$inputs['id'] = $otherUserID;

			#Validating ID
			$otherUserIDValidation = $validation->validateUserID($otherUserID);
			#Adding input reason
			$inputReasons['id'] = $otherUserIDValidation;
			#Checking if valid
			if ($otherUserIDValidation['valid'])
			{
				#Convert to integer
				$otherUserID = intval($otherUserID);

				#Checking if logged in
				if ($loginStatusResult['loggedIn'])
				{
					#Getting account
					$account = $loginStatusResult['account'];
					$id = $account['id'];

					#Getting relation
					list($querySuccess, $queryRelation, $queryRelationExists) = $friendRelationsDb->getRelationByUserIDs($id, $otherUserID);

					#Checking if relation exists
					if ($queryRelationExists)
					{
						#Getting relation
						$success = true;
						$reasonID = $reasonIDs->Accepted;
						$relation = [
							'exists' => true,
							'senderID' => intval($queryRelation['SenderUserID']),
							'receiverID' => intval($queryRelation['ReceiverUserID']),
							'accepted' => boolval($queryRelation['Accepted']),
							'requestTime' => intval($queryRelation['RequestTime']),
							'acceptTime' => $queryRelation['Accepted'] ? intval($queryRelation['AcceptTime']) : null
						];
					}
					elseif ($querySuccess /* and !$queryRelationExists */)
					{
						#Default relation
						$success = true;
						$reasonID = $reasonIDs->Accepted;
						$relation = [
							'exists' => false,
							'senderID' => null,
							'receiverID' => null,
							'accepted' => null,
							'requestTime' => null,
							'acceptTime' => null
						];
					}
					else#if (!$querySuccess)
					{
						#Query did not succeed
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (1)';
					}
				}
				elseif (!$loginStatusResult['success'])
				{
					#Not succeeded to get information
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (2)';
				}
				else#if (!$loginStatusResult['loggedIn'])
				{
					$reasonID = $reasonIDs->NotLoggedIn;
					$reason = 'Not logged in';
				}
			}
			elseif (!$otherUserIDValidation['success'])
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (3)';
			}
			else#if (!$otherUserIDValidation['valid'])
			{
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'Invalid inputs received';
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
		'inputs' => $inputs,
		'inputReasons' => $inputReasons,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'relation' => $relation
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$friendRelationsDb,
		$validation,
		$success,
		$reasonID,
		$reason,
		$relation,
		$otherUserID,
		$id,
		$account,
		$querySuccess,
		$queryRelation,
		$queryRelationExists,
		$inputs,
		$inputReasons,
	);
?>
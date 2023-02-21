<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/21/23 07:51am
		Version: 0.0.5.1
	Updated on
		Version: 0.3.5.1

	Description:
		Accept/decline friend request, remake of original file

	Changes:
		Version 0.0.5.3 - Send notification
		Version 0.3.5.1 - Change file location
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

	#Other user ID
	$otherUserID = null;
	$responseType = null;
	#Account ID
	$id = null;
	$account = [];
	
	#Other variables
	$querySuccess = null;
	$queryRelation = null;
	$queryRelationExists = null;
	$recordID = null;
	$endRelationValidation = null;
	
	#Received inputs
	$inputs = [
		'id' => null,
		'responseType' => null
	];
	$inputReasons = [
		'id' => null,
		'responseType' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success and $notificationTypes->success)
	{
		#Checking if Post exists
		if ($_GET)
		{
			#Getting inputs
			$otherUserID = GeneralFunctions::getValue($_GET, 'id');
			$responseType = GeneralFunctions::getValue($_GET, 'responseType');
			
			#Adding inputs
			$inputs['id'] = $otherUserID;
			$inputs['responseType'] = $responseType;

			#Validating inputs
			$otherUserIDValidation = $validation->validateUserID($otherUserID);
			$responseTypeValidation = $validation->validateResponseType($responseType);

			#Adding input reasons
			$inputReasons['id'] = $otherUserIDValidation;
			$inputReasons['responseType'] = $responseTypeValidation;

			#Checking if valid
			if ($otherUserIDValidation['valid'] and $responseTypeValidation['valid'])
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
						#Getting recordID
						$recordID = $queryRelation['ID'];
						#Validating operation
						$respondValidation = $validation->canRespondToRequest($recordID, $id);
						#Adding operation reason
						$operationReason = $respondValidation;

						$inputReasons['operation'] = $operationReason;

						#Checking if valid
						if ($respondValidation['allowed'])
						{
							#Checking response type
							if ($responseType === 'accept')
							{
								#Accept request
								list($querySuccess, , ) = $friendRelationsDb->acceptRequest($recordID, $id);
							}
							else
							{
								#Decline request
								list($querySuccess, , ) = $friendRelationsDb->declineRequest($recordID, $id);
							}
							#Checking if success
							if ($querySuccess)
							{
								#Responded
								$success = true;
								$reasonID = $reasonIDs->Accepted;
								#Checking if request accepted
								if ($responseType === 'accept')
								{
									#Send notification
									$notificationsDb->create($notificationTypes->AcceptRequest, $otherUserID, $id);
								}
							}
							else#if (!$querySuccess)
							{
								$reasonID = $reasonIDs->DatabaseError;
								$reason = 'Server experienced an error while processing the request (1)';
							}
						}
						elseif ($respondValidation['success'])
						{
							#Operation not allowed
							$reasonID = $reasonIDs->NotAllowed;
							$reason = 'Operation not allowed';
						}
						else#if (!$respondValidation['success'])
						{
							#Query did not succeed
							$reasonID = $reasonIDs->DatabaseError;
							$reason = 'Server experienced an error while processing the request (2)';
						}
					}
					elseif ($querySuccess /* and !$queryRelationExists */)
					{
						$reasonID = $reasonIDs->NotFound;
						$reason = 'Relation doesn\'t exist';
					}
					else#if (!$querySuccess)
					{
						#Query did not succeed
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (3)';
					}
				}
				elseif (!$loginStatusResult['success'])
				{
					#Not succeeded to get information
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (4)';
				}
				else#if (!$loginStatusResult['loggedIn'])
				{
					$reasonID = $reasonIDs->NotLoggedIn;
					$reason = 'Not logged in';
				}
			}
			elseif ($otherUserIDValidation['success'] and $responseTypeValidation['success'])
			{
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'Invalid inputs received';
			}
			else#if (!($otherUserIDValidation['success'] and $responseTypeValidation['success']))
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (5)';
			}
		}
		else
		{
			$reasonID = $reasonIDs->NoGet;
			$reason = 'Target user ID and responseType required';
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
		'loginStatusResult' => $loginStatusResult,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$notificationTypes,
		$notificationsDb,
		$friendRelationsDb,
		$validation,
		$success,
		$reasonID,
		$reason,
		$operationReason,
		$otherUserID,
		$id,
		$account,
		$querySuccess,
		$queryRelation,
		$queryRelationExists,
		$recordID,
		$endRelationValidation,
		$inputs,
		$inputReasons
	);
?>
<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/09/23 08:11pm
		Version: 0.0.5.1
	Updated on
		Version: 0.0.5.1

	Description:
		Delete friend relation

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
	#Require FriendRelationsDb
	require_once(__DIR__.'/../../Resources/Php/Db/friendRelationsDb.php');
	#Require FriendRelationsValidation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/friendRelations.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	#Require login status
	require_once(__DIR__.'/../User/status.php');
	
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
	#Operation reason
	$operationReason = null;

	#Other user ID
	$otherUserID = null;
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
		'id' => null
	];
	$inputReasons = [
		'id' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if Post exists
		if ($_POST)
		{
			#Getting UserID
			$otherUserID = GeneralFunctions::getValue($_POST, 'id');
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
					list($querySuccess, $queryRelation, $queryRelationExists) = $friendRelationsDb->getRelationByUsers($id, $otherUserID);
					#Checking if relation exists
					if ($queryRelationExists)
					{
						#Getting recordID
						$recordID = $queryRelation['ID'];
						#Validating operation
						$endRelationValidation = $validation->canEndRelation($recordID, $id);
						#Adding operation reason
						$operationReason = $endRelationValidation;

						#Checking if valid
						if ($endRelationValidation['allowed'])
						{
							#Ending relation
							list($querySuccess, , ) = $friendRelationsDb->endRelation($recordID, $id);
							#Checking if success
							if ($querySuccess)
							{
								#Deleted
								$success = true;
								$reasonID = $reasonIDs->Accepted;
							}
							else#if (!$querySuccess)
							{
								$reasonID = $reasonIDs->DatabaseError;
								$reason = 'Server experienced an error while processing the request (1)';
							}
						}
						elseif ($endRelationValidation['success'])
						{
							#Operation not allowed
							$reasonID = $reasonIDs->NotAllowed;
							$reason = 'Operation not allowed';
						}
						else#if (!$endRelationValidation['success'])
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
			elseif (!$receiverIDValidation['success'])
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (5)';
			}
			else#if (!$receiverIDValidation['valid'])
			{
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'Invalid inputs received';
			}
		}
		else
		{
			$reasonID = $reasonIDs->NoPost;
			$reason = 'No user specified';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
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
		'inputs' => $inputs,
		'inputReasons' => $inputReasons,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'operationReason' => $operationReason
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
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
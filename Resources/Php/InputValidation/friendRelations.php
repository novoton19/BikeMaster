<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/08/23 10:50pm
		Version: 0.0.5
	Updated on
		Version: 0.3.5

	Description:
		Validation of inputs for any friend relation requests

	Changes:
		Version 0.0.5.1 - validateResponseType, Bug fixes
		Version 0.3.5 - Validate page and viewing type
	*/
	#Making sure that this script is running independently
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require reason IDs
	require_once(__DIR__.'/../Db/reasonIDsDb.php');
	#Require UsersDb
	require_once(__DIR__.'/../Db/usersDb.php');
	#Require FriendRelationsDb
	require_once(__DIR__.'/../Db/friendRelationsDb.php');
	#Validation class
	class FriendRelationsValidation
	{
		#Reason IDs
		private $reasonIDs;
		#UsersDb
		private $usersDb;
		#FriendRelationsDb
		private $friendRelationsDb;
		#Constructor
		public function __construct()
		{
			#Creating ReasonIDsDb
			$this->reasonIDs = new ReasonIDsDb();
			#Creating UsersDb
			$this->usersDb = new UsersDb();
			#Creating FriendRelationsDb
			$this->friendRelationsDb = new FriendRelationsDb();
		}
		#Validates userID
		public function validateUserID($userID)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Status
			$success = false;
			#Whether valid
			$valid = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Checking if is number
				if (is_numeric($userID))
				{
					#Converting to number
					$userID = intval($userID);
					#Getting user
					list($querySuccess, $user, $userExists) = $this->usersDb->getUserByIDSecure($userID);
					
					#Checking if query succeeded
					if ($querySuccess and $userExists)
					{
						#Valid
						$success = true;
						$valid = true;
						$reasonID = $reasonIDs->Accepted;
					}
					elseif (!$querySuccess)
					{
						#Error
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (1)';
					}
					else#if (!$userExists)
					{
						$success = true;
						$reasonID = $reasonIDs->UserNotFound;
						$reason = 'User doesn\'t exist';
					}
				}
				elseif (is_null($userID))
				{
					#Not set
					$success = true;
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Not specified';
				}
				else#if (!is_numeric($userID))
				{
					#Not number
					$success = true;
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Not a number';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (2)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates page
		public function validatePage($page)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Checking if is number
				if (is_numeric($page))
				{
					#Converting to number
					$page = intval($page);
					#Checking if >= 0
					if ($page >= 0)
					{
						$valid = true;
						$reasonID = $reasonIDs->Accepted;
					}
					else#if ($page < 0)
					{
						$reasonID = $reasonIDs->TooSmall;
						$reason = 'Cannot load page lower than 0';
					}
				}
				elseif (is_null($page))
				{
					#Not set
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Not specified';
				}
				else#if (!is_numeric($page))
				{
					#Not number
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Not a number';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates viewing type
		public function validateViewingType($type)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Checking type
				if ($type === 'current' or $type === 'requests')
				{
					$valid = true;
					$reasonID = $reasonIDs->Accepted;
				}
				elseif (is_null($type))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Not specified';
				}
				else#if ($type !== 'current' and $type !== 'requests')
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Not valid';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates recordID
		public function validateRecordID($recordID)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Status
			$success = false;
			#Whether valid
			$valid = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Checking if is number
				if (is_numeric($recordID))
				{
					#Converting to number
					$recordID = intval($recordID);
					#Getting relation
					list($querySuccess, $relation, $relationExists) = $this->friendRelationsDb->getRelationByID($recordID);
					
					#Checking if query succeeded
					if ($querySuccess and $relationExists)
					{
						#Valid
						$success = true;
						$valid = true;
						$reasonID = $reasonIDs->Accepted;
					}
					elseif (!$querySuccess)
					{
						#Error
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (1)';
					}
					else#if (!$relationExists)
					{
						$success = true;
						$reasonID = $reasonIDs->NotFound;
						$reason = 'Relation doesn\'t exist';
					}
				}
				elseif (is_null($recordID))
				{
					#Not set
					$success = true;
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Not specified';
				}
				else#if (!is_numeric($recordID))
				{
					#Not number
					$success = true;
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Not a number';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (2)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates response type
		public function validateResponseType($responseType)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Status
			$success = false;
			#Whether valid
			$valid = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking responseType
				if ($responseType === 'accept' or $responseType === 'decline')
				{
					$valid = true;
					$reasonID = $reasonIDs->Accepted;
				}
				elseif (is_null($responseType))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Not specified';
				}
				else#if ($responseType !== 'accept' and $responseType !== 'decline')
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Not valid';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Determines whether relation can be created between two users
		public function canSendRequest($senderID, $receiverID)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Status
			$success = false;
			#Whether operation allowed
			$allowed = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Checking IDs
				if ($senderID !== $receiverID)
				{
					#Getting relation
					list($querySuccess, $relation, $relationExists) = $this->friendRelationsDb->getRelationByUserIDs($senderID, $receiverID);
					#Checking if success
					if ($querySuccess and !$relationExists)
					{
						#Can send request
						$success = true;
						$allowed = true;
						$reasonID = $reasonIDs->Accepted;
					}
					elseif (!$querySuccess)
					{
						#Error
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (1)';
					}
					else#if ($relationExists)
					{
						#Relation already exists
						$success = true;
						$reasonID = $reasonIDs->AlreadyExists;
						$reason = 'Relation already exists';
					}
				}
				else#if ($senderID === $receiverID)
				{
					#Cannot send to themselves
					$success = true;
					$reasonID = $reasonIDs->NotAllowed;
					$reason = 'Cannot send friend request to yourself';
				}
			}
			else#if (!$reasonIDs->success)
			{
				#ReasonIDs not loaded 
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (2)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Result
			return [
				'success' => $success,
				'allowed' => $allowed,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Whether request can be accepted or declined
		public function canRespondToRequest($recordID, $receiverID)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Status
			$success = false;
			#Whether operation allowed
			$allowed = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Getting relation
				list($querySuccess, $relation, $relationExists) = $this->friendRelationsDb->getRelationByID($recordID);
				#Checking if success
				if ($querySuccess and $relationExists)
				{
					#Getting receiverID
					$recordReceiverID = intval($relation['ReceiverUserID']);
					#Checking ID
					if ($recordReceiverID === $receiverID)
					{
						#Getting status
						$accepted = boolval($relation['Accepted']);
						#Checking if accepted
						if (!$accepted)
						{
							#Can send request
							$success = true;
							$allowed = true;
							$reasonID = $reasonIDs->Accepted;
						}
						else#if ($accepted)
						{
							$success = true;
							$reasonID = $reasonIDs->AlreadyExists;
							$reason = 'Request has already been accepted';
						}
					}
					else#if ($recordReceiverID !== $receiverID)
					{
						$success = true;
						$reasonID = $reasonIDs->DoNotMatch;
						$reason = 'Request is not meant for this user';
					}
				}
				elseif (!$querySuccess)
				{
					#Error
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (1)';
				}
				else#if (!$relationExists)
				{
					#Relation already exists
					$success = true;
					$reasonID = $reasonIDs->NotFound;
					$reason = 'Relation doesn\'t exist';
				}
			}
			else#if (!$reasonIDs->success)
			{
				#ReasonIDs not loaded 
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (2)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Result
			return [
				'success' => $success,
				'allowed' => $allowed,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		public function canEndRelation($recordID, $userID)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Status
			$success = false;
			#Whether operation allowed
			$allowed = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Getting relation
				list($querySuccess, $relation, $relationExists) = $this->friendRelationsDb->getRelationByID($recordID);
				#Checking if success
				if ($querySuccess and $relationExists)
				{
					#Getting senderID
					$recordSenderID = intval($relation['SenderUserID']);
					#Getting receiverID
					$recordReceiverID = intval($relation['ReceiverUserID']);
					#Checking ID
					if ($recordSenderID === $userID or $recordReceiverID === $userID)
					{
						$success = true;
						$allowed = true;
						$reasonID = $reasonIDs->Accepted;
					}
					else#if ($recordSenderID !== $userID and $recordReceiverID !== $userID)
					{
						$success = true;
						$reasonID = $reasonIDs->DoNotMatch;
						$reason = 'User is not partner of this relation';
					}
				}
				elseif (!$querySuccess)
				{
					#Error
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (1)';
				}
				else#if (!$relationExists)
				{
					#Relation already exists
					$success = true;
					$reasonID = $reasonIDs->NotFound;
					$reason = 'Relation doesn\'t exist';
				}
			}
			else#if (!$reasonIDs->success)
			{
				#ReasonIDs not loaded 
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (2)';
			}
			#Checking if reasonID is set
			if (is_null($reasonID))
			{
				#Default
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Result
			return [
				'success' => $success,
				'allowed' => $allowed,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
	}
?>
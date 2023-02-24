<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/08/23 10:50pm
		Version: 0.4.4
	Updated on
		Version: 0.4.4

	Description:
		Validation of inputs for journeys

	Changes:
	
	*/
	#Making sure that this script is running independently
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require reason IDs
	require_once(__DIR__.'/../Db/reasonIDsDb.php');
	
	#Validation class
	class JourneysValidation
	{
		#Reason IDs
		private $reasonIDs;
		#Constructor
		public function __construct()
		{
			#Creating ReasonIDsDb
			$this->reasonIDs = new ReasonIDsDb();
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
				if ($type === 'current' or $type === 'archive')
				{
					$valid = true;
					$reasonID = $reasonIDs->Accepted;
				}
				elseif (is_null($type))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Not specified';
				}
				else#if ($type !== 'current' and $type !== 'archive')
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
		/*public function validateRecordID($recordID)
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
		}*/
	}
?>
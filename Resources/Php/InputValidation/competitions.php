<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/26/23 11:08pm
		Version: 0.6.1
	Updated on
		Version: 0.6.1

	Description:
		Validation of inputs for creating and getting competitions

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Require reason IDs
	require_once(__DIR__.'/../Db/reasonIDsDb.php');
	#Require CompetitionsDb
	require_once(__DIR__.'/../Db/competitionsDb.php');

	#Validation class
	class CompetitionsValidation
	{
		#Valid viewing types
		static $validViewingTypes = ['actual', 'invitations', 'archive'];
		#Valid response types
		static $validResponseTypes = ['accept', 'decline'];
		#Reason IDs
		private $reasonIDs;
		private $competitionsDb;

		#Constructor
		public function __construct()
		{
			#Creating ReasonIDsDb
			$this->reasonIDs = new ReasonIDsDb();
			$this->competitionsDb = new CompetitionsDb();
		}
		#Validates competition ID
		public function validateCompetitionID($id)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$success = false;
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking if numeric
				if (is_numeric($id))
				{
					#Converting to integer
					$id = intval($id);
					#Checking value
					if ($id > 0)
					{
						#Getting competition
						list($querySuccess, , $resultExists) = $this->competitionsDb->getCompetitionByID($id);
						#Checking if exists
						if ($resultExists)
						{
							$valid = true;
							$reasonID = $reasonIDs->Accepted;
						}
						elseif (!$querySuccess)
						{
							$success = false;
							$reasonID = $reasonIDs->DatabaseError;
							$reason = 'Server experienced an error while processing the request (1)';
						}
						else#if (!$resultExists)
						{
							$reasonID = $reasonIDs->NotFound;
							$reason = 'Invalid ID';
						}
					}
					else#if ($id <= 0)
					{
						$reasonID = $reasonIDs->NotAllowed;
						$reason = 'Invalid ID';
					}
				}
				elseif (is_null($id))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'ID not set';
				}
				else
				{
					#Not a number
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'ID not integer';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (2)';
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates title
		public function validateTitle($title)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$success = false;
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking type
				if (gettype($title) === 'string')
				{
					#Getting length
					$length = strlen($title);
					#Checking length
					if ($length <= 32)
					{
						$isValid = true;
						$reasonID = $reasonIDs->Accepted;
					}
					else#if ($length > 32)
					{
						$reasonID = $reasonIDs->TooLong;
						$reason = 'title must not be longer than 32 characters';
					}
				}
				elseif (is_null($title))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Title not set';
				}
				else#if (gettype($title) !== 'string' and !is_null($title))
				{
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Title is not a string';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			return [
				'success' => $success,
				'valid' => $isValid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates description
		public function validateDescription($description)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$success = false;
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking type
				if (gettype($description) === 'string')
				{
					#Getting length
					$length = strlen($description);
					#Checking length
					if ($length <= 512)
					{
						$isValid = true;
						$reasonID = $reasonIDs->Accepted;
					}
					else#if ($length > 512)
					{
						$reasonID = $reasonIDs->TooLong;
						$reason = 'Description must not be longer than 512 characters';
					}
				}
				elseif (is_null($description))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Description not set';
				}
				else#if (gettype($description) !== 'string' and !is_null($description))
				{
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Description is not a string';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			return [
				'success' => $success,
				'valid' => $isValid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates distance
		public function validateDistance($distance)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$success = false;
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking if numeric
				if (is_numeric($distance))
				{
					#Converting to int
					$distance = floatval($distance);
					#Checking value
					if ($distance >= 5 and $distance <= 100)
					{
						#Checking if step is 0.5
						if (($distance * 2) % 1 === 0)
						{
							$valid = true;
							$reasonID = $reasonIDs->Accepted;
						}
						else#if ($distance % 0.5 !== 0)
						{
							$reasonID = $reasonIDs->NotAllowed;
							$reason = '0.5km step only';
						}
					}
					elseif ($distance < 5)
					{
						$reasonID = $reasonIDs->TooSmall;
						$reason = 'Distance cannot be smaller than 5km';
					}
					else#if ($distance > 100)
					{
						$reasonID = $reasonIDs->TooBig;
						$reason = 'Distance cannot be bigger than 100km';
					}
				}
				elseif (is_null($distance))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Distance not set';
				}
				else
				{
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Distance is not a number';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates viewing type
		public function validateViewingType($type)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$success = false;
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking type
				if (gettype($type) === 'string')
				{
					#Checking if type if valid
					if (in_array($type, CompetitionsValidation::$validViewingTypes))
					{
						$valid = true;
						$reasonID = $reasonIDs->Accepted;
					}
					else#if (!in_array($type, CompetitionsValidation::$validViewingTypes))
					{
						$reasonID = $reasonIDs->NotAllowed;
						$reason = 'Viewing type doesn\'t exist'; 
					}
				}
				elseif (is_null($type))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Viewing type not set';
				}
				else#if (gettype($type) !-- 'string' and !is_null($type))
				{
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Viewing type is not a string';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates response type
		public function validateResponseType($type)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$success = false;
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking type
				if (gettype($type) === 'string')
				{
					#Checking if type if valid
					if (in_array($type, CompetitionsValidation::$validResponseTypes))
					{
						$valid = true;
						$reasonID = $reasonIDs->Accepted;
					}
					else#if (!in_array($type, CompetitionsValidation::$valiResponseTypes))
					{
						$reasonID = $reasonIDs->NotAllowed;
						$reason = 'Response type doesn\'t exist'; 
					}
				}
				elseif (is_null($type))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Response type not set';
				}
				else#if (gettype($type) !-- 'string' and !is_null($type))
				{
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Response type is not a string';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
	}
?>
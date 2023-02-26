<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/26/23 02:20pm
		Version: 0.5
	Updated on
		Version: 0.5

	Description:
		Search validation

	Changes:

	*/
	#Require ReasonIDsDb
	require_once(__DIR__.'/../Db/reasonIDsDb.php');

	class SearchValidation
	{
		#Reason IDs
		private $reasonIDs;
		private static $validSectors = [
			'users',
			'journeys',
			'competitions',
			'achievements'
		];
		#Constructor
		public function __construct()
		{
			$this->reasonIDs = new ReasonIDsDb(); 
		}
		#Validates search term
		public function validateTerm($term)
		{
			#Getting reason IDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$success = false;
			$valid = false;
			$reasonID = null;
			$reason = null;
			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking term type
				if (gettype($term) === 'string')
				{
					if (strlen($term) > 0)
					{
						$reasonID = $reasonIDs->Accepted;
						$valid = true;
					}
					else#if (strlen($term) <= 0)
					{
						$reasonID = $reasonIDs->TooShort;
						$reason = 'Must have at least 1 character';
					}
				}
				elseif (is_null($term))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'term is required';
				}
				else#if (gettype($term) !== 'string' and !is_null($term))
				{
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Not a string';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			#Checking if reasonID exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates search sector
		public function validateSector($sector)
		{
			#Getting reason IDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$success = false;
			$valid = false;
			$reasonID = null;
			$reason = null;
			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
				#Checking sector type
				if (gettype($sector) === 'string')
				{
					#Checking if sector exists
					if (in_array($sector, SearchValidation::$validSectors))
					{
						$reasonID = $reasonIDs->Accepted;
						$valid = true;
					}
					else#if (!in_array($sector))
					{
						$reasonID = $reasonIDs->NotFound;
						$reason = 'Sector doesn\'t exist';
					}
				}
				elseif (is_null($sector))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Sector is required';
				}
				else#if (gettype($sector) !== 'string' and !is_null($sector))
				{
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Not a string';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			#Checking if reasonID exists
			if (is_null($reasonID))
			{
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
			$success = false;
			$valid = false;
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				$success = true;
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
				'success' => $success,
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
	}
?>
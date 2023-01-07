<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/07/23 09:56pm
		Version: 0.0.4.3
	Updated on
		Version: 0.0.4.3

	Description:
		Validation of inputs for getting activities

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Require reason IDs
	require_once(__DIR__.'/../Db/reasonIDsDb.php');

	#Validation class
	class GetActivitiesValidation
	{
		#Reason IDs
		private $reasonIDs;
		#Constructor
		public function __construct()
		{
			#Creating ReasonIDsDb
			$this->reasonIDs = new ReasonIDsDb();
		}
		#Validates time
		public function validateTime($time)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Converted time
			$timeValue = null;
			#Whether valid
			$valid = false;
			#Reason
			$reasonID = null;
			$reason = null;

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Checking type
				if (gettype($time) === 'integer')
				{
					#Convert to integer
					$valid = true;
					$time = intval($time);
					$reasonID = $reasonIDs->Accepted;
				}
				elseif (is_null($time))
				{
					#Default value
					$valid = true;
					$time = time() * 1000;
					$reasonID = $reasonIDs->SetDefault;
				}
				else#if (gettype($time) !== 'integer')
				{
					#Not valid
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Not an integer';
				}
			}
			else#if (!$reasonIDs->success)
			{
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				#Default reason
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'value' => $timeValue,
				'status' => [
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
			];
		}
	}
?>
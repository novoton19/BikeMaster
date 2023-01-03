<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/02/23 10:24pm
		Version: 0.0.3
	Updated on
		Version: 0.0.3

	Description:
		Validation of login form inputs

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}

	#Validation class
	class LoginValidation
	{
		#Database
		private $usersDb;
		#Reason IDs for username validation
		public static $usernameReasonIDs = [
			'IsNull' => 0,
			'InvalidType' => 1,
			'UsernameNotTaken' => 2,
			'DatabaseError' => 3
		];
		#Reason IDs for email validation
		public static $emailReasonIDs = [
			'IsNull' => 0,
			'InvalidType' => 1,
			'InvalidEmail' => 2,
			'EmailNotTaken' => 3,
			'DatabaseError' => 4
		];
		#Constructor
		public function __construct()
		{
			#Create database
			$this->usersDb = new UsersDb();
		}

		public function validateUsername($username)
		{
			#Reasons
			$reasonIDs = self::$usernameReasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($username))
			{
				$reasonID = $reasonIDs['IsNull'];
				$reason = 'Username is required';
			}
			elseif (gettype($username) !== 'string')
			{
				$reasonID = $reasonIDs['InvalidType'];
				$reason = 'Username is not a string';
			}
			else
			{
				#Trying to get user by username
				list($success, , $resultExists) = $this->usersDb->getUserByUsername($username);
				#Checking if success
				if (!$success)
				{
					$reasonID = $reasonIDs['DatabaseError'];
					$reason = 'The availability of username could not be verified';
				}
				elseif (!$resultExists)
				{
					$reasonID = $reasonIDs['UsernameNotTaken'];
					$reason = 'Account with this username doesn\'t exist';
				}
				else
				{
					#Valid
					$isValid = true;
				}
			}
			return [
				'valid' => $isValid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		public function validateEmail($email)
		{
			#Reasons
			$reasonIDs = self::$emailReasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($email))
			{
				$reasonID = $reasonIDs['IsNull'];
				$reason = 'E-mail is required';
			}
			elseif (gettype($email) !== 'string')
			{
				$reasonID = $reasonIDs['InvalidType'];
				$reason = 'E-mail is not a string';
			}
			else
			{
				#Checking format
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$reasonID = $reasonIDs['InvalidEmail'];
					$reason = 'E-mail not valid';
				}
				else
				{
					#Trying to get user by email
					list($success, , $resultExists) = $this->usersDb->getUserByEmail($email);
					#Checking if success
					if (!$success)
					{
						$reasonID = $reasonIDs['DatabaseError'];
						$reason = 'The availability of e-mail could not be verified';
					}
					elseif (!$resultExists)
					{
						$reasonID = $reasonIDs['EmailNotTaken'];
						$reason = 'E-mail isn\'t registered';
					}
					else
					{
						#Valid
						$isValid = true;
					}
				}
			}
			return [
				'valid' => $isValid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
	}
?>
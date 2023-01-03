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
		Validation of registration form inputs

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}

	#Validation class
	class RegistrationValidation
	{
		#Database
		private $usersDb;
		#Username pattern
		public static $usernamePattern = '/^[a-zA-Z0-9_]{3,24}$/';
		#Reason IDs for username validation
		public static $usernameReasonIDs = [
			'IsNull' => 0,
			'InvalidType' => 1,
			'TooShort' => 2,
			'TooLong' => 3,
			'InvalidCharacters' => 4,
			'TooManyUnderscores' => 5,
			'UsernameTaken' => 6,
			'DatabaseError' => 7
		];
		#Reason IDs for email validation
		public static $emailReasonIDs = [
			'IsNull' => 0,
			'InvalidType' => 1,
			'InvalidEmail' => 2,
			'EmailTaken' => 3,
			'DatabaseError' => 4
		];
		#Password reason IDs
		public static $passwordReasonIDs = [
			'IsNull' => 0,
			'InvalidType' => 1,
			'TooShort' => 2,
			'TooWeak' => 3
		];
		#Password confirmation reason IDs
		public static $passwordConfirmationReasonIDs = [
			'IsNull' => 0,
			'DoNotMatch' => 1
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
				#Getting length
				$length = strlen($username);
				#Checking length
				if ($length < 3)
				{
					#Too short
					$reasonID = $reasonIDs['TooShort'];
					$reason = 'Username must have at least 3 characters';
				}
				elseif ($length > 24)
				{
					#Too long
					$reasonID = $reasonIDs['TooLong'];
					$reason = 'Username must not have more than 24 characters';
				}
				elseif (preg_match(self::$usernamePattern, $username) !== 1)
				{
					#Invalid characters
					$reasonID = $reasonIDs['InvalidCharacters'];
					$reason = 'Username can contain only english alphabet characters, numbers and one underscore (_)';
				}
				elseif (substr_count($username, '_') > 1)
				{
					#Too many underscores
					$reasonID = $reasonIDs['TooManyUnderscores'];
					$reason = 'Username can contain one underscore at maximum';
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
					elseif ($resultExists)
					{
						$reasonID = $reasonIDs['UsernameTaken'];
						$reason = 'That username is already taken';
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
					elseif ($resultExists)
					{
						$reasonID = $reasonIDs['EmailTaken'];
						$reason = 'That e-mail is already registered';
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
		public static function validatePassword($password)
		{
			#Reasons
			$reasonIDs = self::$passwordReasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($password))
			{
				$reasonID = $reasonIDs['IsNull'];
				$reason = 'Password is required';
			}
			elseif (gettype($password) !== 'string')
			{
				$reasonID = $reasonIDs['InvalidType'];
				$reason = 'Password is not a string';
			}
			else
			{
				#Getting length
				$length = strlen($password);
				#Checking length
				if ($length < 8)
				{
					$reasonID = $reasonIDs['TooShort'];
					$reason = 'Password must have at least 8 characters';
				}
				elseif (
					!preg_match('/[a-z]/', $password) or
					!preg_match('/[A-Z]/', $password) or
					!preg_match('/[0-9]/', $password) or
					!preg_match('/['.preg_quote('[]{}()<>#£$%&@*!?+-~/\\\\|"\\\':;.,=_¬`', '/').']/', $password))
				{
					#https://stackoverflow.com/questions/3937569/preg-match-special-characters
					#Special chars []{}()<>#£$%&@*!?+-~/\\|"\':;.,=_¬`
					$reasonID = $reasonIDs['TooWeak'];
					$reason = 'Password must contain at least one uppercase and lowercase character, number and a special character';	
				}
				else
				{
					$isValid = true;
				}
			}
			return [
				'valid' => $isValid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		public static function validatePasswordConfirmation($password, $confirmation)
		{
			#Reasons
			$reasonIDs = self::$passwordConfirmationReasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($confirmation))
			{
				$reasonID = $reasonIDs['IsNull'];
				$reason = 'Password confirmation is required';
			}
			elseif ($password !== $confirmation)
			{
				$reasonID = $reasonIDs['DoNotMatch'];
				$reason = 'Passwords do not match';
			}
			elseif (gettype($password) !== 'string')
			{
				$reasonID = $reasonIDs['InvalidType'];
				$reason = 'Password is not a string';
			}
			else
			{
				$isValid = true;
			}
			return [
				'valid' => $isValid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
	}
?>
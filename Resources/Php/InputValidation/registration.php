<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/02/23 10:24pm
		Version: 0.0.3
	Updated on
		Version: 0.3.3

	Description:
		Validation of registration form inputs

	Changes:
		Version 0.0.3.1.1 - Use ReasonIDs from database
		Version 0.3.3 - Added ProfilePicture, Description verification
	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Require reason IDs
	require_once(__DIR__.'/../Db/reasonIDsDb.php');
	#Require settings
	require_once(__DIR__.'/../Db/settingsDb.php');
	#Require general functions
	require_once(__DIR__.'/../general.php');

	#Validation class
	class RegistrationValidation
	{
		#Database
		private $usersDb;
		#ReasonIDs
		private $reasonIDs;
		#SettingsDb
		private $settings;
		#Username pattern
		public static $usernamePattern = '/^[a-zA-Z0-9_]{3,24}$/';
		#Constructor
		public function __construct()
		{
			#Create database
			$this->usersDb = new UsersDb();
			#Create ReasonIDsDb
			$this->reasonIDs = new ReasonIDsDb();
			#Create SettingsDb
			$this->settings = new SettingsDb();
		}

		public function validateUsername($username)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($username))
			{
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Username is required';
			}
			elseif (gettype($username) !== 'string')
			{
				$reasonID = $reasonIDs->InvalidType;
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
					$reasonID = $reasonIDs->TooShort;
					$reason = 'Username must have at least 3 characters';
				}
				elseif ($length > 24)
				{
					#Too long
					$reasonID = $reasonIDs->TooLong;
					$reason = 'Username must not have more than 24 characters';
				}
				elseif (preg_match(self::$usernamePattern, $username) !== 1)
				{
					#Invalid characters
					$reasonID = $reasonIDs->InvalidCharacters;
					$reason = 'Username can contain only english alphabet characters, numbers and one underscore (_)';
				}
				elseif (substr_count($username, '_') > 1)
				{
					#Too many underscores
					$reasonID = $reasonIDs->TooManyUnderscores;
					$reason = 'Username can contain one underscore at maximum';
				}
				else
				{
					#Trying to get user by username
					list($success, , $resultExists) = $this->usersDb->getUserByUsername($username);
					#Checking if success
					if (!$success)
					{
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'The availability of username could not be verified';
					}
					elseif ($resultExists)
					{
						$reasonID = $reasonIDs->UsernameTaken;
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
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($email))
			{
				$reasonID = $reasonIDs->IsNull;
				$reason = 'E-mail is required';
			}
			elseif (gettype($email) !== 'string')
			{
				$reasonID = $reasonIDs->InvalidType;
				$reason = 'E-mail is not a string';
			}
			else
			{
				#Checking format
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$reasonID = $reasonIDs->InvalidEmail;
					$reason = 'E-mail not valid';
				}
				else
				{
					#Trying to get user by email
					list($success, , $resultExists) = $this->usersDb->getUserByEmail($email);
					#Checking if success
					if (!$success)
					{
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'The availability of e-mail could not be verified';
					}
					elseif ($resultExists)
					{
						$reasonID = $reasonIDs->EmailTaken;
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
		public function validatePassword($password)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($password))
			{
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Password is required';
			}
			elseif (gettype($password) !== 'string')
			{
				$reasonID = $reasonIDs->InvalidType;
				$reason = 'Password is not a string';
			}
			else
			{
				#Getting length
				$length = strlen($password);
				#Checking length
				if ($length < 8)
				{
					$reasonID = $reasonIDs->TooShort;
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
					$reasonID = $reasonIDs->TooWeak;
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
		public function validatePasswordConfirmation($password, $confirmation)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($confirmation))
			{
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Password confirmation is required';
			}
			elseif ($password !== $confirmation)
			{
				$reasonID = $reasonIDs->DoNotMatch;
				$reason = 'Passwords do not match';
			}
			elseif (gettype($password) !== 'string')
			{
				$reasonID = $reasonIDs->InvalidType;
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
		public function validateProfilePicture($image)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Settings
			$settings = $this->settings;
			
			#Minimum dimensions
			$minWidth = intval($settings->MinimumProfilePictureWidth);
			$minHeight = intval($settings->MinimumProfilePictureHeight);
			#Maximum dimensions
			$maxWidth = intval($settings->MaximumProfilePictureWidth);
			$maxHeight = intval($settings->MaximumProfilePictureHeight);
			#Valid extensions
			$validExtensions = explode(',', $settings->ValidProfilePictureExtensions);
			
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if exists
			if (is_null($image))
			{
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Image not set';
			}
			else#if (!is_null($image))
			{
	
				#Getting fileName
				$fileName = GeneralFunctions::getValue($image, 'name', null);
				$fileLocation = GeneralFunctions::getValue($image, 'tmp_name', null);
				#Checking fileName
				if (is_null($fileName) or is_null($fileLocation) or empty($fileName) or empty($fileLocation))
				{
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Image not found';
				}
				else#if (!(is_null($fileName) or is_null($fileLocation) or empty($fileName) or empty($fileLocation)))
				{
					$fileName = basename($fileName);
					#Getting extension
					$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
					#Getting size
					list($width, $height) = getimagesize($fileLocation);
		
					#Checking image
					if (!in_array($ext, $validExtensions))
					{
						$reasonID = $reasonIDs->InvalidExtension;
						$reason = 'Invalid extension ('.join(',', $validExtensions).' only)';
					}
					elseif ($width < $minWidth or $height < $minHeight)
					{
						$reasonID = $reasonIDs->TooSmall;
						$reason = 'Minimum image size is '.$minWidth.'x'.$minHeight.'px';
					}
					elseif ($width > $maxWidth or $height > $maxHeight)
					{
						$reasonID = $reasonIDs->TooBig;
						$reason = 'Maximum image size is '.$maxWidth.'x'.$maxHeight.'px';
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
		public function validateDescription($description)
		{
			#Reasons
			$reasonIDs = $this->reasonIDs;
			#Whether is valid
			$isValid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Checking if exists
			if (is_null($description))
			{
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Description not set';
			}
			elseif (gettype($description) !== 'string')
			{
				$reasonID = $reasonIDs->InvalidType;
				$reason = 'Description is not a string';
			}
			else
			{
				#Getting length
				$length = strlen($description);
				#Checking length
				if ($length > 511)
				{
					$reasonID = $reasonIDs->TooLong;
					$reason = 'Description must not be longer than 511 characters';
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
		#Validates latitude
		public function validateLatitude($latitude)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#Filtered result
			$latitudeValue = null;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$latitude = doubleval($latitude);

			#Checking range
			if ($latitude >= -90 and $latitude <= 90)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
				$latitudeValue = round($latitude, 7);
			}
			else#if ($latitude < -90 or $latitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not latitude';
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates longitude
		public function validateLongitude($longitude)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#Filtered result
			$longitudeValue = null;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$longitude = doubleval($longitude);

			#Checking range
			if ($longitude >= -180 and $longitude <= 180)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
				$longitudeValue = round($longitude, 7);
			}
			else#if ($longitude < -90 or $longitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not longitude';
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
	}
?>
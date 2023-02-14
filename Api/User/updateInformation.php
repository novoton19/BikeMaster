<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/12/23
		Version: 0.3.3
	Updated on
		Version: 0.3.3

	Description:
		Update account information

	Changes:
		
	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	session_start();
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require settings
	require_once(__DIR__.'/../../Resources/Php/Db/settingsDb.php');
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#Require database
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require input validation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/registration.php');
	#Require login status
	require_once(__DIR__.'/status.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');


	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating SettingsDb
	$settingsDb = new SettingsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating RegistrationValidation
	$registrationValidation = new RegistrationValidation();


	#Whether success
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;

	#Account information
	$account = null;
	$passwordHash = null;
	$username = null;
	$email = null;
	$newPassword = null;
	$profilePicture = null;
	$description = null;
	$latitude = null;
	$longitude = null;

	#Account information validation
	$usernameValidation = null;
	$emailValidation = null;
	$newPasswordValidation = null;
	$profilePictureValidation = null;
	$descriptionValidation = null;
	$latitudeValidation = null;
	$longitudeValidation = null;

	#Other variables
	$profilePicturesLocation = null;
	$imagesFolder = __DIR__.'/../..';
	$imagesUrl = (GeneralFunctions::getValue($_SERVER, 'HTTPS') === 'on' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/Pwa/BikeMaster';
	$pictureName = null;
	$fileName = null;
	$fileLocation = null;
	$ext = null;
	$image = null;
	$imageDir = null;
	$imageUrl = null;
	$originalImageUrl = null;
	$originalImageInformation = null;
	$originalImageName = null;
	$originalImageExtension = null;
	$originalImageDir = null;
	$inputsValid = false;
	$querySuccess = null;

	#List of inputs received
	$inputs = [];
	#List of columns to update
	$columns = [];
	#List of input reasons
	$inputReasons = [];

	#Checking if succeeded to get reasonIDs and settings
	if ($reasonIDs->success and $settingsDb->success)
	{
		#Session project name
		$projectName = $settingsDb->ProjectName;
		#Getting profile pictures location
		$profilePicturesLocation = $settingsDb->ProfilePicturesLocation;
		#Images folder
		$imagesFolder .= $profilePicturesLocation;
		#Images url
		$imagesUrl .= $profilePicturesLocation;
		
		#Getting login status
		$loggedIn = $loginStatusResult['loggedIn'];
		#Checking if logged in
		if ($loggedIn)
		{
			#Getting account
			$account = $loginStatusResult['account'];
			#Getting inputs
			$username = GeneralFunctions::getValue($_POST, 'username');
			$email = GeneralFunctions::getValue($_POST, 'email');
			$newPassword = GeneralFunctions::getValue($_POST, 'newPassword');
			$profilePicture = GeneralFunctions::getValue($_FILES, 'profilePicture');
			$description = GeneralFunctions::getValue($_POST, 'description');
			$latitude = GeneralFunctions::getValue($_POST, 'latitude');
			$longitude = GeneralFunctions::getValue($_POST, 'longitude');
			
			#Inputs valid by default
			$inputsValid = true;
			#Checking if username exists
			if (!is_null($username))
			{
				#Validating
				$usernameValidation = $registrationValidation->validateUsername($username);
				#Adding to inputs
				$inputs['username'] = $username;
				$columns['Username'] = $username;
				$inputReasons['username'] = $usernameValidation;
				#Checking validation
				$inputsValid = ($inputsValid and $usernameValidation['valid']);
			}
			#Checking if email exists
			if (!is_null($email))
			{
				#Validating
				$emailValidation = $registrationValidation->validateEmail($email);
				#Adding to inputs
				$inputs['email'] = $email;
				$columns['Email'] = $email;
				$inputReasons['email'] = $emailValidation;
				#Checking validation
				$inputsValid = ($inputsValid and $emailValidation['valid']);
			}
			#Checking if new password exists
			if (!is_null($newPassword))
			{
				#Validating
				$newPasswordValidation = $registrationValidation->validatePassword($newPassword);
				#Adding to inputs
				$inputs['newPassword'] = $newPassword;
				$columns['Password'] = password_hash($newPassword);
				$inputReasons['newPassword'] = $newPasswordValidation;
				#Checking validation
				$inputsValid = ($inputsValid and $newPasswordValidation['valid']);
			}
			#Checking if profile picture exists
			if (!is_null($profilePicture))
			{
				#Validating
				$profilePictureValidation = $registrationValidation->validateProfilePicture($profilePicture);
				#Adding to inputs
				$inputs['profilePicture'] = $profilePicture;
				$inputReasons['profilePicture'] = $profilePictureValidation;
				#Checking validation
				$inputsValid = ($inputsValid and $profilePictureValidation['valid']);
			}
			#Checking if description exists
			if (!is_null($description))
			{
				#Validating
				$descriptionValidation = $registrationValidation->validateDescription($description);
				#Adding to inputs
				$inputs['description'] = $description;
				$columns['Description'] = $description;
				$inputReasons['description'] = $descriptionValidation;
				#Checking validation
				$inputsValid = ($inputsValid and $descriptionValidation['valid']);
			}
			#Checking if latitude exists
			if (!is_null($latitude))
			{
				#Validating
				$latitudeValidation = $registrationValidation->validateLatitude($latitude);
				#Adding to inputs
				$inputs['latitude'] = $latitude;
				$columns['Latitude'] = $latitude;
				$inputReasons['latitude'] = $latitudeValidation;
				#Checking validation
				$inputsValid = ($inputsValid and $latitudeValidation['valid']);
			}
			#Checking if longitude exists
			if (!is_null($longitude))
			{
				#Validating
				$longitudeValidation = $registrationValidation->validateLongitude($longitude);
				#Adding to inputs
				$inputs['longitude'] = $longitude;
				$columns['Longitude'] = $longitude;
				$inputReasons['longitude'] = $longitudeValidation;
				#Checking validation
				$inputsValid = ($inputsValid and $longitudeValidation['valid']);
			}
			#Checking if inputs valid
			if ($inputsValid)
			{
				#Checking if profile picture requested
				if (!is_null($profilePicture))
				{
					#Picture name will be the same as username, need to check if username is also being updated
					$pictureName = (is_null($username) ? $account['username'] : $username);	
					#Getting fileName
					$fileName = GeneralFunctions::getValue($profilePicture, 'name', null);
					$fileLocation = GeneralFunctions::getValue($profilePicture, 'tmp_name', null);
					#Getting extension
					$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
					#Getting file location
					$imageDir = $imagesFolder.'/'.$pictureName.'.'.'png';
					$imageUrl = $imagesUrl.'/'.$pictureName.'.'.'png';
					#Getting size
					list($width, $height) = getimagesize($fileLocation);
					#Checking extension
					if ($ext === 'png')
					{
						$image = imagecreatefrompng($fileLocation);
					}
					elseif ($ext === 'gif')
					{
						$image = imagecreatefromgif($fileLocation);
					}
					else
					{
						$image = imagecreatefromjpeg($fileLocation);
					}
					#Cropping to make a square
					$image = imagecrop($image, [
						'x' => 0,
						'y' => 0,
						'width' => $width < $height ? $width : $height,
						'height' => $width < $height ? $width : $height
					]);
					#Resize to 128x128 pixels
					$image = imagescale($image, 128, 128);

					#Checking if image exists
					if (file_exists($imageDir))
					{
						#Deleting file
						unlink($imageDir);
					}
					#Creating image
					if (!imagepng($image, $imageDir))
					{
						$reasonID = $reasonIDs->ErrorUploadingImage;
						$reason = 'Error while uploading image';
					}
					else
					{
						#Adding picture to columns
						$columns['ProfilePictureUrl'] = $imageUrl;
					}
				}
				elseif (!is_null($username))
				{
					#Username changed, update picture url
					#Getting original image url
					$originalImageUrl = $account['profilePictureUrl'];
					#Checking if original image exists
					if (!is_null($originalImageUrl))
					{
						#Image information
						$originalImageInformation = pathinfo($originalImageUrl);
						#Getting original image name
						$originalImageName = $originalImageInformation['basename'];
						#Getting original image extension
						$originalImageExtension = GetValue($originalImageInformation, 'extension', '');
						#Getting original image dir
						$originalImageDir = $imagesFolder.'/'.$originalImageName;
						#Checking if original image exists
						if (file_exists($originalImageDir))
						{
							#Image dir
							$imageDir = $imagesFolder.'/'.$username.'.'.$originalImageExtension;
							#Renaming image
							if(!rename($originalImageDir, $imageDir))
							{
								$reasonID = $reasonIDs->ErrorUpdatingImage;
								$reason = 'Error updating profile picture name';
							}
							else
							{
								#Adding profile picture url to columns
								$columns['ProfilePictureUrl'] = $imagesUrl.'/'.$username.'.'.$originalImageExtension;
							}
						}
						else
						{
							#Adding profile picture url to columns
							#Just to make sure, what if previous request did not update database?
							$columns['ProfilePictureUrl'] = $imagesUrl.'/'.$username.'.'.$originalImageExtension;
						}
					}
				}
				#Checking if success
				if (is_null($reasonID) and count($columns) > 0)
				{
					#Updating information
					$querySuccess = $usersDb->updateInformation($account['id'], $columns);
					#Checking if query succeeded
					if ($querySuccess)
					{
						$success = true;
					}
					else
					{
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (1)';
					}
				}
				elseif (count($columns) <= 0)
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'There is nothing to update';
				}
				else//if (!is_null($reasonID))
				{
					$reasonID = $reasonIDs->Error;
					$reason = 'Error updating profile picture information';
				}
			}
			else#if (!inputsValid)
			{
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'Invalid inputs';
			}
		}
		else#if (!loginStatusResult['loggedIn'])
		{
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Not logged in';
		}
	}
	elseif (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (2)';
	}
	else#if (!$settings->success)
	{
		#Set reasonID
		$reasonID = $reasonIDs->DatabaseError;
		$reason = 'Server experienced an error while processing the request (3)';
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
		'inputs' => $inputs,
		'inputReasons' => $inputReasons,
		'loginStatusResult' => $loginStatusResult
	]);
	#Unset unnecessary variables
	unset(
		$settingsDb,
		$reasonIDs,
		$usersDb,
		$registrationValidation,
		$success,
		$reasonID,
		$reason,
		$loginStatusResult,
		$account,
		$passwordHash,
		$username,
		$email,
		$password,
		$profilePicture,
		$description,
		$latitude,
		$longitude,
		$usernameValidation,
		$emailValidation,
		$passwordValidation,
		$profilePictureValidation,
		$descriptionValidation,
		$latitudeValidation,
		$longitudeValidation,
		$profilePicturesLocation,
		$imagesFolder,
		$imagesUrl,
		$pictureName,
		$fileName,
		$fileLocation,
		$ext,
		$image,
		$imageDir,
		$imageUrl,
		$originalImageUrl,
		$originalImageInformation,
		$originalImageName,
		$originalImageExtension,
		$originalImageDir,
		$inputsValid,
		$querySuccess,
		$inputs,
		$columns,
		$inputReasons
	);
	#Close session
	session_write_close();
?>
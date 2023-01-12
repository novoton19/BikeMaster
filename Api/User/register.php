<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/02/23
		Version: 0.0.3
	Updated on
		Version: 0.0.3.3

	Description:
		Registration

	Changes:
		Version 0.0.3.1.1 - Use ReasonIDs from database
		Version 0.0.3.3 - Correct standards
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
	#Require database
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require input validation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/registration.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating RegistrationValidation
	$validation = new RegistrationValidation();

	#Whether succeeded
	$success = false;
	#Request reason ID
	$reasonID = null;
	$reason = null;

	#Account information
	$username = null;
	$email = null;
	$password = null;
	$passwordConfirmation = null;
	$passwordHash = null;
	#Account information validation
	$usernameValidation = null;
	$emailValidation = null;
	$passwordValidation = null;
	$passwordConfirmationValidation = null;
	
	#Other variables
	$inputsValid = false;
	$querySuccess = null;

	#List of inputs received
	$inputs = [
		'username' => null,
		'email' => null
	];
	#List of input reasons
	$inputReasons = [
		'username' => $validation->validateUsername(null),
		'email' => $validation->validateEmail(null),
		'password' => $validation->validatePassword(null),
		'passwordConfirmation' => $validation->validatePasswordConfirmation(null, null)
	];

	#Checking if reasonIDs loaded
	if ($reasonIDs->success)
	{
		#Checking if post exists
		if ($_POST)
		{
			#Getting inputs
			$username = GeneralFunctions::getValue($_POST, 'username');
			$email = GeneralFunctions::getValue($_POST, 'email');
			$password = GeneralFunctions::getValue($_POST, 'password');
			$passwordConfirmation = GeneralFunctions::getValue($_POST, 'passwordConfirmation');
			
			#Validating inputs
			$usernameValidation = $validation->validateUsername($username);
			$emailValidation = $validation->validateEmail($email);
			$passwordValidation = $validation->validatePassword($password);
			$passwordConfirmationValidation = $validation->validatePasswordConfirmation($password, $passwordConfirmation);
			
			#Putting inputs in list
			$inputs['username'] = $username;
			$inputs['email'] = $email;
			#Putting validation in list
			$inputReasons['username'] = $usernameValidation;
			$inputReasons['email'] = $emailValidation;
			$inputReasons['password'] = $passwordValidation;
			$inputReasons['passwordConfirmation'] = $passwordConfirmationValidation;
			#Whether inputs are valid
			$inputsValid = (
				$usernameValidation['valid'] and
				$emailValidation['valid'] and
				$passwordValidation['valid'] and
				$passwordConfirmationValidation['valid']
			);

			#Checking if valid
			if ($inputsValid)
			{
				#Getting hash of password
				$passwordHash = password_hash($password, PASSWORD_DEFAULT);
				#Attempt to create account
				$querySuccess = $usersDb->createUser($username, $email, $passwordHash);
				#Checking if success
				if ($querySuccess)
				{
					#Set success
					$success = true;
					$reasonID = $reasonIDs->Accepted;
				}
				else
				{
					#Database error
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (1)';
				}
			}
			else
			{
				#Invalid inputs
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'Invalid information given';
			}
		}
		else
		{
			$reasonID = $reasonIDs->NoPost;
			$reason = 'Missing information';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reasonIDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (2)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		#Default reasonID
		$reasonID = $reasonIDs->NoReasonAvailable;
	}


	#Echo result
	echo json_encode([
		'success' => $success,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unsetting unnecessary variables
	unset(
		$reasonIDs,
		$usersDb,
		$validation,
		$success,
		$reasonID,
		$reason,
		$username,
		$email,
		$password,
		$passwordConfirmation,
		$passwordHash,
		$usernameValidation,
		$emailValidation,
		$passwordValidation,
		$passwordConfirmationValidation,
		$inputsValid,
		$querySuccess,
		$inputs,
		$inputReasons
	);
?>
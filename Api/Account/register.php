<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/02/23
		Version: 0.0.3
	Updated on
		Version: 0.0.3.1.1

	Description:
		Registration

	Changes:
		Version 0.0.3.1.1 - Use ReasonIDs from database
	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require database
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#Require input validation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/registration.php');
	
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating RegistrationValidation
	$validation = new RegistrationValidation();

	#Whether succeeded
	$success = false;
	#Request reason ID
	$reasonID = null;
	#Request reason
	$reason = null;
	#List of inputs received
	$inputs = [];
	#List of input reasons
	$inputReasons = [];

	#Checking if post exists
	if ($_POST)
	{
		#Getting inputs
		$username = GeneralFunctions::getValue($_POST, 'Username');
		$email = GeneralFunctions::getValue($_POST, 'Email');
		$password = GeneralFunctions::getValue($_POST, 'Password');
		$passwordConfirmation = GeneralFunctions::getValue($_POST, 'PasswordConfirmation');
		#Putting inputs in list
		$inputs = [
			'Username' => $username,
			'Email' => $email
		];
		
		#Validating inputs
		$usernameValidation = $validation->validateUsername($username);
		$emailValidation = $validation->validateEmail($email);
		$passwordValidation = $validation->validatePassword($password);
		$passwordConfirmationValidation = $validation->validatePasswordConfirmation($password, $passwordConfirmation);
		#Putting validation in list
		$inputReasons = [
			'Username' => $usernameValidation,
			'Email' => $emailValidation,
			'Password' => $passwordValidation,
			'PasswordConfirmation' => $passwordConfirmationValidation
		];

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
			$success = $usersDb->createUser($username, $email, $passwordHash);
			#Checking if error
			if (!$success)
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while creating an account';
			}
		}
		else
		{
			$reasonID = $reasonIDs->InvalidInputs;
		}
	}
	else
	{
		$reasonID = $reasonIDs->NoPost;
		$reason = 'Missing information';
		$inputs = [
			'Username' => null,
			'Email' => null
		];
		$inputReasons = [
			'Username' => $validation->validateUsername(null),
			'Email' => $validation->validateEmail(null),
			'Password' => $validation->validatePassword(null),
			'PasswordConfirmation' => $validation->validatePasswordConfirmation(null, null)
		];
	}
	echo json_encode([
		'success' => $success,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unsetting sensitive variables
	unset(
		$usersDb,
		$validation,
		$username,
		$email,
		$password,
		$passwordConfirmation,
		$passwordHash,
		$inputs
	);
?>
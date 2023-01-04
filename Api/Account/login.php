<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/03/23 10:14pm
		Version: 0.0.3
	Updated on
		Version: 0.0.3.1

	Description:
		Login

	Changes:
		Version 0.0.3.1 - Set login timeout
	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	session_start();
	#Return json
	header('Content-Type: application/json; charset=utf-8');
	#Require database
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require input validation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/login.php');
	
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating LoginValidation
	$validation = new LoginValidation();

	#Session project name
	$projectName = 'BikeMaster';

	#List or reason IDs
	$reasonIDs = [
		'NoPost' => 0,
		'InvalidCredentials' => 1,
		'DatabaseError' => 2
	];
	#Whether succeeded
	$success = false;
	$passwordsMatch = false;
	$accountID = null;
	#Request reason ID
	$reasonID = null;
	#Request reason
	$reason = null;
	#List of inputs received
	$inputs = [];

	#Checking if post exists
	if ($_POST)
	{
		#Getting inputs
		$usernameOrEmail = GeneralFunctions::getValue($_POST, 'UsernameOrEmail');
		$password = GeneralFunctions::getValue($_POST, 'Password');
		#Putting inputs in list
		$inputs = [
			'UsernameOrEmail' => $usernameOrEmail
		];
		
		#Validating inputs
		$usernameValidation = $validation->validateUsername($usernameOrEmail);
		$emailValidation = $validation->validateEmail($usernameOrEmail);
	
		#User account
		$account = null;
		#Whether account exists
		$accountExists = false;
		#Whether succeeded to get account from database
		$queried = false;
		$querySuccess = false;

		#Checking if username is valid
		if ($usernameValidation['valid'])
		{
			#Getting account by username
			list($querySuccess, $account, $accountExists) = $usersDb->getUserByUsername($usernameOrEmail);
			$queried = true;
		}
		elseif ($emailValidation['valid'])
		{
			#Getting account by e-mail
			list($querySuccess, $account, $accountExists) = $usersDb->getUserByEmail($usernameOrEmail);
			$queried = true;
		}
		#Checking if success
		if ($querySuccess and $accountExists)
		{
			#Getting password hash
			$passwordHash = GeneralFunctions::getValue($account, 'Password', '');
			#Whether passwords match
			$passwordsMatch = password_verify($password, $passwordHash);
			#Checking if match
			if (!$passwordsMatch)
			{
				$reasonID = $reasonIDs['InvalidCredentials'];
				$reason = 'Invalid credentials';
			}
			else
			{
				#Getting account ID
				$accountID = GeneralFunctions::getValue($account, 'ID');
			}
		}
		elseif ($queried and !$querySuccess)
		{
			$reasonID = $reasonIDs['DatabaseError'];
			$reason = 'Server experienced an error while getting user account';
		}
		else
		{
			$reasonID = $reasonIDs['InvalidCredentials'];
			$reason = 'Invalid credentials';
		}
	}
	else
	{
		$reasonID = $reasonIDs['NoPost'];
		$reason = 'Missing information';
		$inputs = [
			'UsernameOrEmail' => null
		];
	}
	#Checking if passwords match
	if ($passwordsMatch)
	{
		#Checking if session exists
		if (!isset($_SESSION[$projectName]))
		{
			#Create project
			$_SESSION[$projectName] = [];
		}
		#Adding login information
		$_SESSION[$projectName]['Login'] = [
			'Status' => true,
			'UserID' => intval($accountID),
			'Time' => time(),
			'Timeout' => time() + 3600
		];
		#Set success
		$success = true;
	}
	echo json_encode([
		'success' => $success,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs,
	]);
	#Unsetting sensitive variables
	unset(
		$usersDb,
		$validation,
		$usernameOrEmail,
		$password,
		$passwordHash,
		$account,
		$passwordsMatch,
		$inputs
	);
	session_write_close();
?>
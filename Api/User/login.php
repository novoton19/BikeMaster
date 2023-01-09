<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/03/23 10:14pm
		Version: 0.0.3
	Updated on
		Version: 0.0.5.1

	Description:
		Login

	Changes:
		Version 0.0.3.1 - Set login timeout
		Version 0.0.3.1.1 - Use ReasonIDs from database
		Version 0.0.3.3 - Support SettingsDb, correct standards
		Version 0.0.5.1 - Extend login duration to 12h
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
	require_once(__DIR__.'/../../Resources/Php/InputValidation/login.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating SettingsDb
	$settingsDb = new SettingsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating LoginValidation
	$validation = new LoginValidation();

	#Session project name
	$projectName = null;

	#Whether succeeded
	$success = false;
	#Request reason ID
	$reasonID = null;
	$reason = null;

	#UsernameOrEmail
	$usernameOrEmail = null;
	$usernameValidation = null;
	$emailValidation = null;
	$account = [];
	$accountID = null;
	$accountExists = null;
	$password = null;
	$passwordHash = null;
	$passwordsMatch = null;

	#Other variables
	$queried = false;
	$querySuccess = null;

	#List of inputs received
	$inputs = [
		'usernameOrEmail' => null
	];

	#Checking if reasonIDs and settings loaded
	if ($reasonIDs->success and $settingsDb->success)
	{
		#Getting projectName
		$projectName = $settingsDb->ProjectName;
		
		#Checking if post exists
		if ($_POST)
		{
			#Getting inputs
			$usernameOrEmail = GeneralFunctions::getValue($_POST, 'usernameOrEmail');
			$password = GeneralFunctions::getValue($_POST, 'password');
			#Putting inputs in list
			$inputs['usernameOrEmail'] = $usernameOrEmail;
			
			#Validating inputs
			$usernameValidation = $validation->validateUsername($usernameOrEmail);
			$emailValidation = $validation->validateEmail($usernameOrEmail);
		
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
				if ($passwordsMatch)
				{
					#Getting account ID
					$accountID = GeneralFunctions::getValue($account, 'ID');
					#Setting reasonID
					$reasonID = $reasonIDs->Accepted;
				}
				else
				{
					$reasonID = $reasonIDs->InvalidCredentials;
					$reason = 'Invalid credentials';
				}
			}
			elseif ($queried and !$querySuccess)
			{
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			else
			{
				$reasonID = $reasonIDs->InvalidCredentials;
				$reason = 'Invalid credentials';
			}
		}
		else
		{
			$reasonID = $reasonIDs->NoPost;
			$reason = 'Missing information';
			$inputs = [
				'UsernameOrEmail' => null
			];
		}
	}
	elseif (!$reasonIDs->success)
	{
		#Cannot get reasonIDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (2)';
	}
	else#if (!$settingsDb->success)
	{
		#Cannot get settings
		$reasonID = $reasonIDs->DatabaseError;
		$reason = 'Server experienced an error while processing the request (3)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		#Default reasonID
		$reasonID = $reasonIDs->NoReasonAvailable;
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
		$_SESSION[$projectName]['login'] = [
			'status' => true,
			'userID' => intval($accountID),
			'time' => time(),
			'timeout' => time() + 43200
		];
		#Set success
		$success = true;
	}
	echo json_encode([
		'success' => $success,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs
	]);
	#Unsetting unnecessary variables
	unset(
		$reasonIDs,
		$settingsDb,
		$usersDb,
		$validation,
		$projectName,
		$success,
		$reasonID,
		$reason,
		$usernameOrEmail,
		$usernameValidation,
		$emailValidation,
		$account,
		$accountID,
		$accountExists,
		$password,
		$passwordHash,
		$passwordsMatch,
		$queried,
		$querySuccess,
		$inputs
	);
	session_write_close();
?>
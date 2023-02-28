<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 10:00am
		Version: 0.0.3.1
	Updated on
		Version: 1

	Description:
		Verifies login and returns information about login

	Changes:
		Version 0.0.3.1.1 - Use ReasonIDs from database
		Version 0.0.3.2 - Bug fix, support settingsDb, error prevention
		Version 0.0.3.3 - Correct standards
		Version 0.0.4.0.1 - Send account information on session timeout
		Version 0.0.4.3 - Returns success status
		Version 0.1 - Tiny bug fix
	*/
	#Whether is being included
	$isIncluded = count(debug_backtrace());
	#Whether session active on include
	$sessionActiveAtStart = (session_status() === PHP_SESSION_ACTIVE);
	#Checking if session started
	if (!$sessionActiveAtStart)
	{
		#Start session
		session_start();
	}
	#Checking if being included
	if (!$isIncluded)
	{
		#Return json
		header('Content-Type: application/json; charset=utf-8');
	}
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#Require settings
	require_once(__DIR__.'/../../Resources/Php/Db/settingsDb.php');
	#Users database
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#General functions
	require_once(__DIR__.'/../../Resources/Php/general.php');

	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#SettingsDb
	$settings = new SettingsDb();
	#Creating users database
	$usersDb = new UsersDb();

	#Current time
	$time = time();
	
	#Whether succeeded to get information
	$success = false;
	#Whether logged in
	$loggedIn = false;
	#ReasonID
	$reasonID = null;
	#Reason
	$reason = null;
	
	#Getting project
	$project = null;
	#Getting login information
	$login = null;
	#Getting login status
	$status = null;
	#Getting login ID
	$userID = null;
	#Getting login time
	$loginTime = null;
	#Getting timeout
	#Default to time, which triggers timeout
	$timeout = null;
	
	#Other variables
	#Session project name
	$projectName = null;
	$querySuccess = null;
	$account = null;
	$accountExists = null;
	
	#Checking if succeeded to get reasonIDs and settings
	if ($reasonIDs->success and $settings->success)
	{
		#Session project name
		$projectName = $settings->ProjectName;
		#Getting login information
		$project = GeneralFunctions::getValue($_SESSION, $projectName, []);
		$login = GeneralFunctions::getValue($project, 'login', []);
		$status = GeneralFunctions::getValue($login, 'status', false);
		$userID = GeneralFunctions::getValue($login, 'userID');
		$loginTime = GeneralFunctions::getValue($login, 'time', $time);
		$timeout = GeneralFunctions::getValue($login, 'timeout', $time);
		#Checking status
		if ($status === true)
		{
			#Checking login time and timeout
			if ($loginTime <= $time and $time < $timeout)
			{
				#Trying to get user by ID
				list($querySuccess, $account, $accountExists) = $usersDb->getUserByID($userID);
				#Checking if success
				if ($querySuccess and $accountExists)
				{
					#Valid login
					$success = true;
					$loggedIn = true;
					$reasonID = $reasonIDs->Accepted;
				}
				elseif (!$querySuccess)
				{
					#Database error
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (1)';
				}
				else#if (!$accountExists)
				{
					#Invalid login
					$reasonID = $reasonIDs->InvalidLogin;
					$reason = 'Invalid login information (1)';
				}
			}
			elseif ($loginTime > $time)
			{
				#Invalid login
				$reasonID = $reasonIDs->InvalidLogin;
				$reason = 'Invalid login information (2)';
			}
			else#if ($time >= timeout)
			{
				#Timed out
				$success = true;
				$reasonID = $reasonIDs->TimedOut;
				$reason = 'Session timed out';
				#Trying to get user by ID
				list($querySuccess, $account, $accountExists) = $usersDb->getUserByIDSecure($userID);
			}
		}
		else#if (!$status)
		{
			$success = true;
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
	#Result
	$result = [
		'success' => $success,
		'loggedIn' => $loggedIn,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'timeout' => (($loggedIn or $reasonID === $reasonIDs->TimedOut) ? $timeout : null),
		'account' => [
			'id' => intval(GeneralFunctions::getValue($account, 'ID')),
			'username' => GeneralFunctions::getvalue($account, 'Username'),
			'email' => GeneralFunctions::getValue($account, 'Email'),
			'profilePictureUrl' => GeneralFunctions::getValue($account, 'ProfilePictureUrl'),
			'description' => GeneralFunctions::getValue($account, 'Description'),
			'latitude' => GeneralFunctions::getValue($account, 'Latitude'),
			'longitude' => GeneralFunctions::getValue($account, 'Longitude'),
			'registrationTime' => intval(GeneralFunctions::getValue($account, 'RegistrationTime')),
		]
	];
	#Checking if included
	if ($isIncluded)
	{
		#Create result
		$loginStatusResult = $result;
	}
	else
	{
		#Echo result
		echo json_encode($result);
	}
	#Checking if session was active on start
	if (!$sessionActiveAtStart)
	{
		#Close session
		session_write_close();
	}
	#Unset unnecessary variables
	unset(
		$isIncluded,
		$sessionActiveAtStart,
		$usersDb,
		$settings,
		$projectName,
		$time,
		$reasonIDs,
		$success,
		$loggedIn,
		$reasonID,
		$reason,
		$project,
		$login,
		$status,
		$userID,
		$loginTime,
		$timeout,
		$querySuccess,
		$account,
		$accountExists,
		$result
	);
?>
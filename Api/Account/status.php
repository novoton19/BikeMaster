<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 10:00am
		Version: 0.0.3.1
	Updated on
		Version: 0.0.3.1.1

	Description:
		Verifies login and returns information about login

	Changes:
		Version 0.0.3.1.1 - Use ReasonIDs from database
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
	#Users database
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#General functions
	require_once(__DIR__.'/../../Resources/Php/general.php');

	#Creating users database
	$usersDb = new UsersDb();
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();

	#Session project name
	$projectName = 'BikeMaster';
	#Current time
	$time = time();

	#Whether logged in
	$loggedIn = false;
	#ReasonID
	$reasonID = null;
	#Reason
	$reason = null;

	#Getting project
	$project = GeneralFunctions::getValue($_SESSION, $projectName, []);
	#Getting login information
	$login = GeneralFunctions::getValue($project, 'Login', []);
	#Getting login status
	$status = GeneralFunctions::getValue($login, 'Status', false);
	#Getting login ID
	$userID = GeneralFunctions::getValue($login, 'UserID');
	#Getting login time
	$loginTime = GeneralFunctions::getValue($login, 'Time', $time);
	#Getting timeout
	#Default to time, which triggers timeout
	$timeout = GeneralFunctions::getValue($login, 'Timeout', $time);
	
	#Other variables
	$success = null;
	$account = null;
	$accountExists = null;
	
	#Checking status
	if ($status === true)
	{
		#Checking login time and timeout
		if ($loginTime < $time and $time < $timeout)
		{
			#Trying to get user by ID
			list($success, $account, $accountExists) = $usersDb->getUserByIDSecure($userID);
			#Checking if success
			if ($success and $accountExists)
			{
				#Valid login
				$loggedIn = true;
			}
			elseif (!$success)
			{
				#Database error
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Error while verifying login';
			}
			else
			{
				#Invalid login
				$reasonID = $reasonIDs->InvalidLogin;
				$reason = 'Invalid login information';
			}
		}
		elseif ($loginTime >= $time)
		{
			#Invalid login
			$reasonID = $reasonIDs->InvalidLogin;
			$reason = 'Invalid login information';
		}
		else
		{
			#Timed out
			$reasonID = $reasonIDs->TimedOut;
			$reason = 'Session timed out';
		}
	}
	else
	{
		$reasonID = $reasonIDs->NotLoggedIn;
		$reason = 'Not logged in';
	}
	#Result
	$result = [
		'LoggedIn' => $loggedIn,
		'ReasonID' => $reasonID,
		'Reason' => $reason,
		'Timeout' => (($loggedIn or $reasonID === $reasonIDs['TimedOut']) ? $timeout : null),
		'Account' => [
			'ID' => intval(GeneralFunctions::getValue($account, 'ID')),
			'Username' => GeneralFunctions::getvalue($account, 'Username'),
			'Email' => GeneralFunctions::getValue($account, 'Email'),
			'RegistrationTime' => intval(GeneralFunctions::getValue($account, 'RegistrationTime'))
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
		$projectName,
		$time,
		$reasonIDs,
		$loggedIn,
		$reasonID,
		$reason,
		$project,
		$login,
		$status,
		$userID,
		$loginTime,
		$timeout,
		$success,
		$account,
		$accountExists,
		$result
	);
?>
<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 10:00am
		Version: 0.0.3.1
	Updated on
		Version: 0.0.3.2.1

	Description:
		Verifies login and returns information about login

	Changes:
		Version 0.0.3.1.1 - Use ReasonIDs from database
		Version 0.0.3.2 - Bug fix, support settingsDb, error prevention
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
	#Require settings
	require_once(__DIR__.'/../../Resources/Php/Db/settingsDb.php');
	#Require reason IDs
	require_once(__DIR__.'/../../Resources/Php/Db/reasonIDsDb.php');
	#Users database
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#General functions
	require_once(__DIR__.'/../../Resources/Php/general.php');

	#SettingsDb
	$settings = new SettingsDb();
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating users database
	$usersDb = new UsersDb();

	#Current time
	$time = time();
	
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
	$success = null;
	$account = null;
	$accountExists = null;
	
	#Checking if succeeded to get reasonIDs and settings
	if ($reasonIDs->success and $settings->success)
	{
		#Session project name
		$projectName = $settings->ProjectName;
		#Getting login information
		$project = GeneralFunctions::getValue($_SESSION, $projectName, []);
		$login = GeneralFunctions::getValue($project, 'Login', []);
		$status = GeneralFunctions::getValue($login, 'Status', false);
		$userID = GeneralFunctions::getValue($login, 'UserID');
		$loginTime = GeneralFunctions::getValue($login, 'Time', $time);
		$timeout = GeneralFunctions::getValue($login, 'Timeout', $time);
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
					$reasonID = $reasonIDs->Accepted;
				}
				elseif (!$success)
				{
					#Database error
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (1)';
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
		'LoggedIn' => $loggedIn,
		'ReasonID' => $reasonID,
		'Reason' => $reason,
		'Timeout' => (($loggedIn or $reasonID === $reasonIDs->TimedOut) ? $timeout : null),
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
		$settings,
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
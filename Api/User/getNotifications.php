<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/09/23 11:50pm
		Version: 0.0.5.4
	Updated on
		Version: 0.0.5.4

	Description:
		Loads notifications of user

	Changes:

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
	#Require NotificationsDb
	require_once(__DIR__.'/../../Resources/Php/Db/notificationsDb.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	#Require status
	require_once(__DIR__.'/status.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating NotificationsDb
	$notificationsDb = new NotificationsDb();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;

	#Requested page
	$page = null;
	#Resulting notifications
	$notificationsCount = null;
	$notifications = [];
	
	#Other variables
	#$loginStatusResult
	$account = null;
	$id = null;
	$querySuccess = null;
	$queryResults = null;
	$queryResult = null;
	$key = null;
	
	#Received inputs
	$inputs = [
		'page' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if Get exists
		if ($_GET)
		{
			#Getting page
			$page = intval(GeneralFunctions::getvalue($_GET, 'page'));
			#Checking value
			if ($page < 0)
			{
				$page = 0;
			}
		}
		else
		{
			$page = 0;
		}
		$inputs['page'] = $page;

		#Checking if logged in
		if ($loginStatusResult['loggedIn'])
		{
			#Getting account
			$account = $loginStatusResult['account'];
			$id = $account['id'];

			#Getting amount of notifications
			list($querySuccess, $notificationsCount) = $notificationsDb->getNotificationsCount($id);
			#Checking if success
			if ($querySuccess)
			{
				#Getting notifications
				list($querySuccess, $queryResults, ) = $notificationsDb->getNotifications($id, $page);
				#Checking if success
				if ($querySuccess)
				{
					foreach ($queryResults as $key => $queryResult)
					{
						#Adding result
						array_push($notifications, [
							'id' => intval($queryResult['ID']),
							'type' => intval($queryResult['TypeID']),
							'targetID' => intval($queryResult['TargetUserID']),
							'triggerID' => intval($queryResult['TriggerUserID']),
							'viewed' => boolval($queryResult['Viewed']),
							'triggerTime' => intval($queryResult['TriggerTime']),
							'viewTime' => intval($queryResult['ViewTime'])
						]);
					}
					$success = true;
					$reasonID = $reasonIDs->Accepted;
				}
				else#if (!$querySuccess)
				{
					#Error
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (1)';
				}
			}
			else#if (!$querySuccess)
			{
				#Error
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (2)';
			}
		}
		elseif ($loginStatusResult['success'])
		{
			#Not logged in
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Not logged in';
		}
		else#if (!$loginStatusResult['success'])
		{
			#Error
			$reasonID = $reasonIDs->DatabaseError;
			$reason = 'Server experienced an error while processing the request (3)';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (4)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Echo result
	echo json_encode([
		'success' => $success,
		'notifications' => $notifications,
		'notificationsCount' => $notificationsCount,
		'totalPages' => ceil($notificationsCount / 5),
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$notificationsDb,
		$success,
		$reasonID,
		$reason,
		$page,
		$notificationsCount,
		$notifications,
		$loginStatusResult,
		$account,
		$id,
		$querySuccess,
		$queryResults,
		$queryResult,
		$key,
		$inputs
	);
?>
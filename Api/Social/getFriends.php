<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/09/23 09:41pm
		Version: 0.0.5.2
	Updated on
		Version: 0.0.5.2

	Description:
		Load friends of user

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
	#Require UsersDb
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require FriendRelationsDb
	require_once(__DIR__.'/../../Resources/Php/Db/friendRelationsDb.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating FriendRelationsDb
	$friendRelationsDb = new FriendRelationsDb();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;

	#Requested ID
	$id = null;
	$page = null;
	#Resulting users
	$friends = [];
	$friendsCount = null;
	
	#Other variables
	$querySuccess = null;
	$queryResults = null;
	$queryResult = null;
	$key = null;
	
	#Received inputs
	$inputs = [
		'id' => null,
		'page' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if Get exists
		if ($_GET)
		{
			#Getting id term
			$id = GeneralFunctions::getValue($_GET, 'id');
			#Getting page
			$page = intval(GeneralFunctions::getvalue($_GET, 'page'));
			#Checking value
			if ($page < 0)
			{
				$page = 0;
			}
			#Adding inputs
			$inputs['id'] = $id;
			$inputs['page'] = $page;

			#Getting amount of friends
			list($querySuccess, $friendsCount) = $friendRelationsDb->getFriendsCount($id);
			#Checking if success
			if ($querySuccess)
			{
				#Getting friend IDs
				list($querySuccess, $queryResults, ) = $friendRelationsDb->getFriends($id, $page);
				
				#Checking if success
				if ($querySuccess)
				{
					#Success
					#Adding friends
					foreach ($queryResults as $key => $queryResult)
					{
						#Getting account
						list($querySuccess, $queryResult, ) = $usersDb->getUserByIDSecure($queryResult['ID']);
						#Checking if query succeeded
						if (!$querySuccess)
						{
							continue;
						}
						#Adding friend
						array_push($friends, [
							'id' => intval($queryResult['ID']),
							'username' => $queryResult['Username'],
							'registrationTime' => intval($queryResult['RegistrationTime']),
							'friendsCount' => intval($queryResult['FriendsCount'])
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
		else
		{
			$reasonID = $reasonIDs->NoGet;
			$reason = 'No user specified';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
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
		'friends' => $friends,
		'friendsCount' => $friendsCount,
		'totalPages' => ceil($friendsCount / 3),
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$usersDb,
		$friendRelationsDb,
		$success,
		$reasonID,
		$reason,
		$id,
		$page,
		$friends,
		$friendsCount,
		$querySuccess,
		$queryResults,
		$queryResult,
		$key,
		$inputs
	);
?>
<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/08/23 05:40pm
		Version: 0.0.4.4
	Updated on
		Version: 0.0.4.4

	Description:
		Search for user by username

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
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;

	#Search term
	$search = null;
	$page = null;
	#Resulting users
	$searchResults = [];
	$resultsAmount = null;
	
	#Other variables
	$querySuccess = null;
	$queryResults = null;
	$queryResult = null;
	$key = null;
	
	#Received inputs
	$inputs = [
		'search' => null,
		'page' => null
	];

	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Checking if Get exists
		if ($_GET)
		{
			#Getting search term
			$search = GeneralFunctions::getValue($_GET, 'search');
			#Getting page
			$page = intval(GeneralFunctions::getvalue($_GET, 'page'));
			#Checking value
			if ($page < 0)
			{
				$page = 0;
			}
			#Adding search term to inputs
			$inputs['search'] = $search;
			$inputs['page'] = $page;

			#Getting amount of results
			list($querySuccess, $resultsAmount) = $usersDb->getSearchResultsCount($search);
			#Checking if success
			if ($querySuccess)
			{
				#Getting users
				list($querySuccess, $queryResults, ) = $usersDb->search($search, $page);
				
				#Checking if success
				if ($querySuccess)
				{
					#Success
					#Adding search results
					foreach ($queryResults as $key => $queryResult)
					{
						#Adding account
						array_push($searchResults, [
							'id' => intval($queryResult['ID']),
							'username' => $queryResult['Username'],
							'email' => $queryResult['Email'],
							'registrationTime' => intval($queryResult['RegistrationTime'])
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
		'searchResults' => $searchResults,
		'totalResults' => $resultsAmount,
		'totalPages' => ceil($resultsAmount / 3),
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$usersDb,
		$success,
		$reasonID,
		$reason,
		$search,
		$page,
		$searchResults,
		$querySuccess,
		$queryResults,
		$queryResult,
		$key,
		$inputs
	);
?>
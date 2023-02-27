<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/09/23 11:03pm
		Version: 0.1.2
	Updated on
		Version: 0.6.2

	Description:
		Returns user's competitions

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');

	#Require ReasonIDsDb
	require_once(__DIR__.'/../../../Resources/Php/Db/reasonIDsDb.php');
	#Require SettingsDb
	require_once(__DIR__.'/../../../Resources/Php/Db/settingsDb.php');
	#Require UsersDb
	require_once(__DIR__.'/../../../Resources/Php/Db/usersDb.php');
	#Require CompetitionsDb
	require_once(__DIR__.'/../../../Resources/Php/Db/competitionsDb.php');
	#Require FriendRelationsValidation
	require_once(__DIR__.'/../../../Resources/Php/InputValidation/friendRelations.php');
	#Require CompetitionsValidation
	require_once(__DIR__.'/../../../Resources/Php/InputValidation/competitions.php');
	#Require login status
	require_once(__DIR__.'/../../User/status.php');
	#Require general functions
	require_once(__DIR__.'/../../../Resources/Php/general.php');

	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating SettingsDb
	$settings = new SettingsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating CompetitionsDb
	$competitionsDb = new CompetitionsDb();
	#Creating FriendRelationsValidation
	$friendValidation = new FriendRelationsValidation();
	#Creating CompetitionsValidation
	$competitionsValidation = new CompetitionsValidation();

	#Whether success
	$success = false;
	#Reason
	$reasonID = null;
	$reason = null;
	$results = [];
	$resultsCount = null;

	#Received inputs
	$viewingType = null;
	$page = null;
	
	#Other variables
	#$loginStatusResult;
	$pageSize = null;
	$account = null;
	$accountID = null;
	$querySuccess = false;
	$queryResults = null;
	$queryResultNum = null;
	$queryResult = null;
	$sender = null;
	$receiver = null;
	$inputs = [
		'viewingType' => null,
		'page' => null
	];
	$inputReasons = [
		'viewingType' => null,
		'page' => null
	];
	
	#Checking if reasonIDs and settings have loaded
	if ($reasonIDs->success and $settings->success)
	{
		$pageSize = intval($settings->PageSize);
		#Checking if logged in
		if ($loginStatusResult['loggedIn'])
		{
			#Getting account
			$account = $loginStatusResult['account'];
			$accountID = $account['id'];

			#Checking if get exists
			if ($_GET)
			{
				#Getting values
				$viewingType = GeneralFunctions::getValue($_GET, 'viewingType');
				$page = GeneralFunctions::getValue($_GET, 'page');
				#Adding inputs
				$inputs['viewingType'] = $viewingType;
				$inputs['page'] = is_null($page) ? null : intval($page);
			}
			#Validating inputs
			$viewingTypeValidation = $competitionsValidation->validateViewingType($viewingType);
			$pageValidation = $friendValidation->validatePage($page);
			#Adding input reasons
			$inputReasons['viewingType'] = $viewingTypeValidation;
			$inputReasons['page'] = $pageValidation;

			#Checking if inputs are valid
			if ($viewingTypeValidation['valid'] and $pageValidation['valid'])
			{
				#Ensuring number
				$page = intval($page);
				#Getting results count
				list($querySuccess, $resultsCount) = $competitionsDb->getCompetitionsCount($accountID, $viewingType);
				#Checking if success
				if ($querySuccess)
				{
					list($querySuccess, $queryResults, ) = $competitionsDb->getCompetitions($accountID, $viewingType, $page, $pageSize);
					#Checking if success
					if ($querySuccess)
					{
						$success = true;
						$reasonID = $reasonIDs->Accepted;
						#Getting results
						foreach ($queryResults as $queryResultNum => $queryResult)
						{
							#Getting sender
							list($querySuccess, $sender, ) = $usersDb->getUserByIDSecure($queryResult['SenderUserID']);
							#Checking if success
							if (!$querySuccess)
							{
								continue;
							}
							#Getting receiver
							list($querySuccess, $receiver, ) = $usersDb->getUserByIDSecure($queryResult['ReceiverUserID']);
							#Checking if success
							if (!$querySuccess)
							{
								return;
							}
							array_push($results, [
								'id' => intval($queryResult['ID']),
								'sender' => [
									'id' => intval($sender['ID']),
									'username' => $sender['Username'],
									'description' => $sender['Description'],
									'profilePictureUrl' => $sender['ProfilePictureUrl']
								],
								'receiver' => [
									'id' => intval($receiver['ID']),
									'username' => $receiver['Username'],
									'description' => $receiver['Description'],
									'profilePictureUrl' => $receiver['ProfilePictureUrl']
								],
								'title' => $queryResult['Title'],
								'description' => $queryResult['Description'],
								'distance' => floatval($queryResult['Distance']),
								'senderDistanceDriven' => floatval($queryResult['SenderDistanceDriven']),
								'receiverDistanceDriven' => floatval($queryResult['ReceiverDistanceDriven']),
								'accepted' => $queryResult['Accepted'] === '1',
								'finished' => $queryResult['Finished'] === '1',
								'requestTime' => intval($queryResult['RequestTime']),
								'acceptTime' => (!is_null($queryResult['AcceptTime'])) ? intval($queryResult['AcceptTime']) : null,
								'finishTime' => (!is_null($queryResult['FinishTime'])) ? intval($queryResult['FinishTime']) : null,
								'winnerID' => (!is_null($queryResult['WinnerID'])) ? intval($queryResult['WinnerID']) : null
							]);
						}
					}
					else
					{
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (1)';
					}
				}
				else#if (!$querySuccess)
				{
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (2)';
				}
			}
			else#if (!inputs valid)
			{
				$reasonID = $reasonIDs->InvalidInputs;
				$reason = 'One or more inputs is invalid';
			}
		}
		elseif (!$loginStatusResult['success'])
		{
			$reasonID = $reasonIDs->DatabaseError;
			$reason = 'Server experienced an error while processing the request (3)';
		}
		else#if (!$loginStatusResult['loggedIn'] and loginStatusResult['success'])
		{
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Must be logged in';
		}
	}
	elseif (!$reasonIDs->success)
	{
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (4)';
	}
	else#if (!$settings->success)
	{
		$reasonID = $reasonIDs->DatabaseError;
		$reason = 'Server experienced an error while processing the request (5)';
	}
	#Checking if reasonID is set
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Printing result
	echo json_encode([
		'success' => $success,
		'results' => $results,
		'resultsCount' => $resultsCount,
		'totalPages' => (is_null($pageSize) or is_null($resultsCount)) ? null : ceil($resultsCount / $pageSize),
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	unset(
		$reasonIDs,
		$competitionsDb,
		$friendValidation,
		$competitionsValidation,
		$success,
		$reasonID,
		$reason,
		$requestedUserID,
		$title,
		$description,
		$distance,
		$account,
		$accountID,
		$querySuccess,
		$inputs,
		$inputReasons
	);
?>
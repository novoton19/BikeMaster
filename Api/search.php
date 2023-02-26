<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/26/23 02:17pm
		Version: 0.5
	Updated on
		Version: 0.5

	Description:
		Returns search searchR

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
	require_once(__DIR__.'/../Resources/Php/Db/reasonIDsDb.php');
	#Require SettingsDb
	require_once(__DIR__.'/../Resources/Php/Db/settingsDb.php');
	#Require UsersDb
	require_once(__DIR__.'/../Resources/Php/Db/usersDb.php');
	#Require JourneysDb
	require_once(__DIR__.'/../Resources/Php/Db/journeysDb.php');
	#Require status
	require_once(__DIR__.'/User/status.php');
	#Require validation
	require_once(__DIR__.'/../Resources/Php/InputValidation/search.php');
	#Require general functions
	require_once(__DIR__.'/../Resources/Php/general.php');

	#Create ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Create settingsDb
	$settingsDb = new SettingsDb();
	#Create UsersDb
	$usersDb = new UsersDb();
	#Create JourneysDb
	$journeysDb = new JourneysDb();
	#Create SearchValidation
	$validation = new SearchValidation();

	#Whether succeeded
	$success = false;
	#Request reasonID
	$reasonID = null;
	$reason = null;
	#searchR
	$term = null;
	$sector = null;
	$page = null;
	$searchResults = [];
	$searchResultsCount = null;

	#Other variables
	#$loginStatusResult
	$pageSize = null;
	$termValidation = null;
	$sectorValidation = null;
	$pageValidation = null;
	$account = null;
	$accountID = null;
	$queryResults = null;
	$queryResultNum = null;
	$queryResult = null;
	$querySuccess = null;

	#Received inputs and their reasons
	$inputs = [
		'term' => null,
		'sector' => null,
		'page' => null
	];
	$inputReasons = [
		'term' => null, 
		'sector' => null,
		'page' => null
	];

	#Checking if succeeded to load reasonIDs and settings
	if ($reasonIDs->success and $settingsDb->success)
	{
		#Getting page size
		$pageSize = $settingsDb->PageSize;
		#Checking if get exists
		if ($_GET)
		{
			#Getting search term and sector
			$term = GeneralFunctions::getValue($_GET, 'term');
			$sector = GeneralFunctions::getValue($_GET, 'sector');
			$page = GeneralFunctions::getValue($_GET, 'page');
			#Adding inputs
			$inputs['term'] = $term;
			$inputs['sector'] = $sector;
			$inputs['page'] = is_null($page) ? null : intval($page);
		}
		#Validating term and sector
		$termValidation = $validation->validateTerm($term);
		$sectorValidation = $validation->validateSector($sector);
		$pageValidation = $validation->validatePage($page);
		#Adding validation
		$inputReasons['term'] = $termValidation;
		$inputReasons['sectorValidation'] = $sectorValidation;
		$inputReasons['page'] = $pageValidation;

		#Checking if inputs valid
		if ($termValidation['valid'] and $sectorValidation['valid'] and $pageValidation['valid'])
		{
			$page = intval($page);
			#Checking if logged in
			if ($loginStatusResult['loggedIn'])
			{
				#Getting account
				$account = $loginStatusResult['account'];
				$accountID = $account['id'];
				#Checking sector
				if ($sector === 'users')
				{
					#Getting resultsCount
					list($querySuccess, $searchResultsCount) = $usersDb->getSearchResultsCount($term);
					#Checking if query success
					if ($querySuccess)
					{
						#Getting results
						list($querySuccess, $queryResults, ) = $usersDb->search($term, $page, ($page + 1) * $pageSize);
						#Checking if success
						if ($querySuccess)
						{
							$success = true;
							$reasonID = $reasonIDs->Accepted;
							#Getting results
							foreach ($queryResults as $queryResultNum => $queryResult)
							{
								array_push($searchResults, [
									'id' => intval($queryResult['ID']),
									'username' => str_replace($term, '<b>'.$term.'</b>', $queryResult['Username']),
									'description' => str_replace($term, '<b>'.$term.'</b>', $queryResult['Description']),
									'profilePictureUrl' => $queryResult['ProfilePictureUrl'],
									'registrationTime' => intval($queryResult['RegistrationTime'])
								]);
							}
						}
						else#if (!$querySuccess)
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
				elseif ($sector === 'journeys')
				{
	
				}
				elseif ($sector === 'competitions')
				{
	
				}
				else#if ($sector === 'achievements')
				{
	
				}
			}
			elseif (!$loginStatusResult['success'])
			{
				$reasonID = $reasonIDs->Error;
				$reason = 'Server experienced an error while processing the request (3)';
			}
			else#if (!$loginStatusResult['loggedIn'] and $loginStatusResult['success'])
			{
				$reasonID = $reasonIDs->NotLoggedIn;
				$reason = 'Must be logged in';
			}
		}
		else#if (!$termValidation['valid'] or !$sectorValidation['valid'] or !$pageValidation['valid'])
		{
			$reasonID = $reasonIDs->InvalidInputs;
			$reason = 'One of the inputs is invalid';
		}
	}
	elseif (!$reasonIDs->success)
	{
		#Error while loading reasonIDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (4)';
	}
	else#if (!$settingsDb->success)
	{
		$reasonID = $reasonIDs->DatabaseError;
		$reason = 'Server experienced an error while processing the request (5)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		#Default reasonID
		$reasonID = $reasonIDs->NoReasonAvailable;
	}
	#Echo result
	echo json_encode([
		'success' => $success,
		'results' => $searchResults,
		'resultsCount' => $searchResultsCount,
		'totalPages' => (is_null($searchResultsCount) or is_null($pageSize)) ? null : floor($searchResultsCount / $pageSize),
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$settingsDb,
		$usersDb,
		$journeysDb,
		$validation,
		$success,
		$reasonID,
		$reason,
		$term,
		$sector,
		$page,
		$searchResults,
		$searchResultsCount,
		$pageSize,
		$termValidation,
		$sectorValidation,
		$pageValidation,
		$account,
		$accountID,
		$queryResults,
		$queryResultNum,
		$queryResult,
		$querySuccess,
		$inputs,
		$inputReasons
	);
?>
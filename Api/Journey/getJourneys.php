<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/24/23 06:19pm
		Version: 0.4.3
	Updated on
		Version: 0.4.3

	Description:
		Loads user's journeys

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
	#Require settings DB
	require_once(__DIR__.'/../../Resources/Php/Db/settingsDb.php');
	#Require UsersDb
	require_once(__DIR__.'/../../Resources/Php/Db/usersDb.php');
	#Require JourneysDb
	require_once(__DIR__.'/../../Resources/Php/Db/journeysDb.php');
	#Require SegmentsDb
	require_once(__DIR__.'/../../Resources/Php/Db/segmentsDb.php');
	#Require TrackPointsDb
	require_once(__DIR__.'/../../Resources/Php/Db/trackPointsDb.php');
	#Require FriendRelationsValidation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/friendRelations.php');
	#Require JourneysValidation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/journeys.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	#Require journey classes
	require_once(__DIR__.'/../../Resources/Php/journey.php');
	#Require login status
	require_once(__DIR__.'/../User/status.php');


	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating SettingsDb
	$settingsDb = new SettingsDb();
	#Creating UsersDb
	$usersDb = new UsersDb();
	#Creating JourneysDb
	$journeysDb = new JourneysDb();
	#Creating SegmentsDb
	$segmentsDb = new SegmentsDb();
	#Creating TrackPointsDb
	$trackPointsDb = new TrackPointsDb();
	#Creating FriendRelationsValidation
	$friendRelationsValidation = new FriendRelationsValidation();
	#Creating JourneysValidation
	$journeysValidation = new JourneysValidation();

	#Whether request succeeded
	$success = false;
	#Default reasonID
	$reasonID = null;
	$reason = null;
	
	#Requested ID
	$id = null;
	$page = null;
	$type = null;
	#Resulting journeys
	$result = [];
	$resultsCount = null;
	
	#Other variables
	$pageSize = null;
	$account = null;
	$accountID = null;
	$latitude = null;
	$longitude = null;
	$home = null;
	$idValidation = null;
	$pageValidation = null;
	$typeValidation = null;
	$querySuccess = null;
	$queryResults = null;
	$queryResult = null;
	$journey = null;
	$segment = null;
	$trackPoint = null;
	$journeyNum = null;
	$segmentNum = null;
	
	#Received inputs
	$inputs = [
		'id' => null,
		'page' => null,
		'type' => null
	];
	#Input reasons
	$inputReasons = [
		'id' => null,
		'page' => null,
		'type' => null
	];
	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success and $settingsDb->success)
	{
		#Getting page size
		$pageSize = $settingsDb->PageSize;
		#Checking if Get exists
		if ($_GET)
		{
			#Checking if logged in
			if ($loginStatusResult['loggedIn'])
			{
				#Getting accountID
				$account = $loginStatusResult['account'];
				$accountID = $account['id'];
				$latitude = $account['latitude'];
				$longitude = $account['longitude'];
				#Checking if latitude and longitude are set
				if (!is_null($latitude) and !is_null($longitude))
				{
					$home = new TrackPoint($latitude, $longitude, 0, time() * 1000);
				}

				#Getting user ID
				$id = GeneralFunctions::getValue($_GET, 'id', $accountID, false);
				#Getting page
				$page = intval(GeneralFunctions::getValue($_GET, 'page', 0, false));
				#Getting type
				$type = GeneralFunctions::getValue($_GET, 'type');

				#Adding received inputs
				$inputs['id'] = $id;
				$inputs['page'] = $page;
				$inputs['type'] = $type;

				#Validating inputs
				$idValidation = $friendRelationsValidation->validateUserID($id);
				$pageValidation = $friendRelationsValidation->validatePage($page);
				$typeValidation = $journeysValidation->validateViewingType($type);

				#Adding input reasons
				$inputReasons['id'] = $idValidation;
				$inputReasons['page'] = $pageValidation;
				$inputReasons['type'] = $typeValidation;

				#Ensure numbers
				$id = intval($id);
				$page = intval($page);

				#Checking if inputs are valid
				if ($idValidation['valid'] and $pageValidation['valid'] and $typeValidation['valid'])
				{
					#Check requested user ID if should return archive (user cannot see archive of someone else)
					if (($type !== 'archive') || ($type === 'archive' and $id === $accountID))
					{
						#Checking if should get archive
						if ($type === 'current')
						{
							#Getting friends count
							list($querySuccess, $resultsCount) = $journeysDb->getJourneysCount($id);
							#Checking if query succeeded
							if ($querySuccess)
							{
								#Get friend list
								list($querySuccess, $queryResults, ) = $journeysDb->getJourneys($id, $page, $pageSize);
							}
						}
						else#if ($type !== 'current') || ($type === 'archive')
						{
							#Getting friend requests count
							list($querySuccess, $resultsCount) = $friendRelationsDb->getArchivedJourneysCount($id);
							#Checking if query succeeded
							if ($querySuccess)
							{
								#Get friend requests
								list($querySuccess, $queryResults, ) = $friendRelationsDb->getArchive($id, $page, $pageSize);
							}
						}
						#Checking if query success
						if ($querySuccess)
						{
							#Success
							#Adding journeys
							foreach ($queryResults as $journeyNum => $journeyInfo)
							{
								#Getting ID
								$journeyID = $journeyInfo['ID'];
								#Resulting segments
								$segments = [];
								#Getting segments by journeyID
								list($querySuccess, $segmentInfos, ) = $segmentsDb->getSegments($journeyID);
								#Checking if success
								if (!$querySuccess)
								{
									continue;
								}
								#Adding segments
								foreach ($segmentInfos as $segmentNum => $segmentInfo)
								{
									#Getting ID
									$segmentID = $segmentInfo['ID'];
									#Resulting points
									$points = [];
									#Getting points by segmentID
									list($querySuccess, $pointInfos, ) = $trackPointsDb->getPoints($segmentID);
									#Checking if success
									if (!$querySuccess)
									{
										continue;
									}
									#Adding points
									foreach ($pointInfos as $pointNum => $pointInfo)
									{
										#Converting to trackPoint
										$trackPoint = new TrackPoint(
											doubleval($pointInfo['Latitude']),
											doubleval($pointInfo['Longitude']),
											doubleval($pointInfo['Accuracy']),
											intval($pointInfo['Timestamp'])
										);
										#Checking if user has home set
										if (!is_null($home))
										{
											#Checking distance
											if ($home->getDistanceTo($trackPoint) < 0.5)
											{
												#Point is in 500m radius from home, skip
												continue; 
											}
										}
										#Adding point
										array_push($points, [
											'latitude' => doubleval($pointInfo['Latitude']),
											'longitude' => doubleval($pointInfo['Longitude']),
											'accuracy' => doubleval($pointInfo['Accuracy']),
											'timestamp' => intval($pointInfo['Timestamp'])
										]);
									}
									#Adding segment
									array_push($segments, [
										'points' => $points
									]);
								}
								#Adding journey
								array_push($result, [
									'segments' => $segments,
									'id' => $journeyInfo['ID'],
									'title' => $journeyInfo['Title'],
									'description' => $journeyInfo['Description'],
									'length' => doubleval($journeyInfo['Length']),
									'startTime' => intval($journeyInfo['StartTime']),
									'endTime' => intval($journeyInfo['EndTime']),
									'creationTime' => intval($journeyInfo['CreationTime']),
									'archived' => $journeyInfo['Archived'] === '1'
								]);
							}
							$success = true;
							$reasonID = $reasonIDs->Accepted;
						}
						else#if (!$querySuccess)
						{
							#Error
							$reasonID = $reasonIDs->DatabaseError;
							$reason = 'Server experienced an error while processing the request (2)';
						}
					}
					else#if ($id !=== $accountID)
					{
						#Not authorized
						$reasonID = $reasonIDs->NotAuthorized;
						$reason = 'Not allowed to view archive';
					}
				}
				else#if (!inputs valid)
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'One of the inputs is invalid';
				}
			}
			elseif (!$loginStatusResult['success'])
			{
				$reasonID = $reasonIDs->Error;
				$reason = 'Could not verify that user is signed in';
			}
			else#if (!$loginStatusResult['loggedIn'] or !$loginStatusResult['success'])
			{
				$reasonID = $reasonIDs->NotLoggedIn;
				$reason = 'Not logged in';
			}
		}
		else#if (!$_GET)
		{
			$reasonID = $reasonIDs->NoGet;
			$reason = 'Type is required';
		}
	}
	elseif (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (3)';
	}
	else#if (!$settingsDb->success)
	{
		#Cannot get settings
		$reasonID = $reasonIDs->DatabaseError;
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
		'result' => $result,
		'resultsCount' => $resultsCount,
		'totalPages' => ceil($resultsCount / 3),
		'reasonID' => $reasonID,
		'reason' => $reason,
		'loginStatusResult' => $loginStatusResult,
		'inputs' => $inputs,
		'inputReasons' => $inputReasons
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$settingsDb,
		$usersDb,
		$journeysDb,
		$segmentsDb,
		$trackPointsDb,
		$friendRelationsValidation,
		$journeysValidation,
		$success,
		$reasonID,
		$reason,
		$id,
		$page,
		$type,
		$result,
		$resultsCount,
		$pageSize,
		$account,
		$accountID,
		$latitude,
		$longitude,
		$home,
		$idValidation,
		$pageValidation,
		$typeValidation,
		$querySuccess,
		$queryResults,
		$queryResult,
		$journey,
		$segment,
		$trackPoint,
		$journeyNum,
		$segmentNum,
		$inputs,
		$inputReasons,
	);
?>
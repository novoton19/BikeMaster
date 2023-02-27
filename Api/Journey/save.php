<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 08:37pm
		Version: 0.0.4
	Updated on
		Version: 0.4.3

	Description:
		Saves user's journey to the database

	Changes:
		Version 0.0.4.1 - Save journey into database
		Version 0.4.3 - Add title and description
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
	#Require JourneysDb
	require_once(__DIR__.'/../../Resources/Php/Db/journeysDb.php');
	#Require SegmentsDb
	require_once(__DIR__.'/../../Resources/Php/Db/segmentsDb.php');
	#Require TrackPointsDb
	require_once(__DIR__.'/../../Resources/Php/Db/trackPointsDb.php');
	#Require CompetitionsDb
	require_once(__DIR__.'/../../Resources/Php/Db/competitionsDb.php');
	#Require status
	require_once(__DIR__.'/../User/status.php');
	#Require journey validation
	require_once(__DIR__.'/../../Resources/Php/InputValidation/saveJourney.php');
	#Require general functions
	require_once(__DIR__.'/../../Resources/Php/general.php');
	
	#Creating ReasonIDsDb
	$reasonIDs = new ReasonIDsDb();
	#Creating JourneysDb 
	$journeysDb = new JourneysDb();
	$segmentsDb = new SegmentsDb();
	$trackPointsDb = new TrackPointsDb();
	#Creating CompetitionsDb
	$competitionsDb = new CompetitionsDb();
	#Creating JourneyValidation
	$validation = new JourneyValidation();

	#Whether track is valid
	$valid = false;
	#Whether succeeded
	$success = false;
	$reasonID = null;
	$reason = null;
	
	#Track to be submitted
	$title = null;
	$description = null;
	$track = null;
	$trackObj = null;
	$segments = null;
	$points = null;
	#Journey
	$journeyID = null;
	$segmentID = null;
	
	#Other variables
	$titleValidation = null;
	$descriptionValidation = null;
	$trackValidation = null;
	$loggedIn = null;
	$account = null;
	$userID = null;
	$querySuccess = null;
	$segmentNum = null;
	$segment = null;
	$pointNum = null;
	$point = null;
	$startTime = null;
	$endTime = null;

	#Received inputs
	$inputs = [
		'title' => null,
		'description' => null,
		'track' => null
	];
	#Input reasons
	$inputReasons = [
		'title' => null,
		'description' => null,
		'track' => null
	];
	#Checking if succeeded to get reasonIDs
	if ($reasonIDs->success)
	{
		#Getting login status
		$loggedIn = $loginStatusResult['loggedIn'];
		#Checking if logged in
		if ($loggedIn)
		{
			#Getting account
			$account = $loginStatusResult['account'];
			$userID = $account['id'];
			#Checking if post exists
			if ($_POST)
			{
				#Getting title
				$title = GeneralFunctions::getValue($_POST, 'title', '');
				#Getting description
				$description = GeneralFunctions::getValue($_POST, 'description', '');
				#Getting track
				$track = GeneralFunctions::getValue($_POST, 'track', []);

				#Adding inputs
				$inputs['title'] = $title;
				$inputs['description'] = $description;
				$inputs['track'] = $track;
				
				#Validating title
				$titleValidation = $validation->validateTitle($title);
				#Validating description
				$descriptionValidation = $validation->validateDescription($description);
				#Validating track
				$trackValidation = $validation->validateTrack($track);
			
				#Adding input reasons
				$inputReasons['title'] = $titleValidation;
				$inputReasons['description'] = $descriptionValidation;
				$inputReasons['track'] = $trackValidation;
				#Checking if valid
				if ($titleValidation['valid'] and $descriptionValidation['valid'] and $trackValidation['valid'])
				{
					#Valid
					$valid = true;
					$reasonID = $reasonIDs->Accepted;
				}
				else#if (!$titleValidation['valid'] or !$descriptionValidation['valid'] or !$trackValidation['valid'])
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'One of the inputs is invalid';
				}
			}
			else#if (!$_POST)
			{
				#No post
				$reasonID = $reasonIDs->NoPost;
				$reason = 'No journey submitted';
			}
		}
		else#if (!$loggedIn)
		{
			#Not logged in
			$reasonID = $reasonIDs->NotLoggedIn;
			$reason = 'Not logged in';
		}
	}
	else#if (!$reasonIDs->success)
	{
		#Cannot get reason IDs
		$reasonID = -1;
		$reason = 'Server experienced an error while processing the request (1)';
	}
	#Checking if reasonID exists
	if (is_null($reasonID))
	{
		$reasonID = $reasonIDs->NoReasonAvailable;
	}


	#Checking if is valid
	if ($valid)
	{
		#Getting segments
		$trackObj = Track::fromArray($track);
		$segments = $trackObj->segments;
		#Getting startTime and endTime
		$startTime = intval($segments[0]->points[0]->timestamp / 1000);
		$endTime = intval(end(end($segments)->points)->timestamp / 1000);
		#Creating a new journey
		list($querySuccess, $journeyID) = $journeysDb->createNewJourney(
			$userID,
			$title,
			$description,
			$trackObj->length,
			$startTime,
			$endTime
		);
		#Checking if success
		if ($querySuccess)
		{
			#Success
			$success = true;
			#Updating distance in compeititions
			$competitionsDb->updateDistance($userID, $trackObj->length, $startTime);
			#Creating segments
			foreach ($segments as $segmentNum => $segment)
			{
				#Getting points
				$points = $segment->points;
				#Creating segment
				list($querySuccess, $segmentID) = $segmentsDb->createNewSegment($journeyID);
				#Checking if success
				if ($querySuccess)
				{
					#Creating points
					foreach ($points as $pointNum => $point)
					{
						#Creating point
						list($success) = $trackPointsDb->createNewTrackPoint(
							$segmentID,
							$point->latitude,
							$point->longitude,
							$point->accuracy,
							$point->timestamp
						);
						#Checking if success
						if ($success)
						{
							#All good
						}
						else#if (!$success)
						{
							$reasonID = $reasonIDs->DatabaseError;
							$reason = 'Server experienced an error while processing the request (2)';
						}
					}
				}
				else#if (!$querySuccess)
				{
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (3)';
				}
			}
		}
		else#if (!$querySuccess)
		{
			$reasonID = $reasonIDs->DatabaseError;
			$reason = 'Server experienced an error while processing the request (4)';
		}
	}


	#Echo result
	echo json_encode([
		'success' => $success,
		'journeyID' => $journeyID,
		'reasonID' => $reasonID,
		'reason' => $reason,
		'inputReasons' => $inputReasons,
		'inputs' => $inputs,
		'loginStatusResult' => $loginStatusResult
	]);
	#Unset unnecessary variables
	unset(
		$reasonIDs,
		$journeysDb,
		$segmentsDb,
		$trackPointsDb,
		$validation,
		$loginStatusResult,
		$valid,
		$success,
		$reasonID,
		$reason,
		$title,
		$description,
		$track,
		$trackObj,
		$segments,
		$points,
		$journeyID,
		$segmentID,
		$titleValidation,
		$descriptionValidation,
		$trackValidation,
		$loggedIn,
		$account,
		$userID,
		$querySuccess,
		$segmentNum,
		$segment,
		$pointNum,
		$point,
		$startTime,
		$endTime,
		$inputs,
		$inputReasons
	);
?>
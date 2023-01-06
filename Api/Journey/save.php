<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 08:37pm
		Version: 0.0.4
	Updated on
		Version: 0.0.4.1

	Description:
		Saves user's journey to the database

	Changes:
		Version 0.0.4.1 - Save journey into database
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
	#Creating JourneyValidation
	$validation = new JourneyValidation();

	#Whether track is valid
	$valid = false;
	#Whether succeeded
	$success = false;
	$reasonID = null;
	$reason = null;
	
	#Track to be submitted
	$track = null;
	$trackObject = null;
	$segments = null;
	$points = null;
	#Journey
	$journeyID = null;
	$segmentID = null;
	
	#Other variables
	$loggedIn = null;
	$account = null;
	$userID = null;
	$querySuccess = null;
	$segmentNum = null;
	$segment = null;
	$pointNum = null;
	$point = null;

	#Received inputs
	$inputs = [
		'track' => null
	];
	#Input reasons
	$inputReasons = [
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
				#Getting track
				$track = GeneralFunctions::getValue($_POST, 'track', []);
				#Adding to inputs
				$inputs['track'] = $track;
				
				#Validating track
				$trackValidation = $validation->validateTrack($track);
				#Adding to input reasons
				$inputReasons['track'] = $trackValidation['status'];
				#Checking if valid
				if ($trackValidation['status']['valid'])
				{
					#Valid
					$valid = true;
					$trackObject = $trackValidation['object'];
					$reasonID = $reasonIDs->Accepted;
				}
				else#if (!$trackValidation['valid'])
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'Track not valid';
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
		$segments = $trackObject->segments;
		#Getting startTime and endTime
		$startTime = intval($segments[0]->points[0]->timestamp / 1000);
		$endTime = intval(end(end($segments)->points)->timestamp / 1000);
		#Creating a new journey
		list($querySuccess, $journeyID) = $journeysDb->createNewJourney($userID, $startTime, $endTime);
		#Checking if success
		if ($querySuccess)
		{
			#Success
			$success = true;
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
							$point->timestamp,
							$point->altitude,
							$point->altitudeAccuracy
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
		$valid,
		$success,
		$reasonID,
		$reason,
		$track,
		$trackObject,
		$segments,
		$points,
		$journeyID,
		$segmentID,
		$loggedIn,
		$account,
		$userID,
		$querySuccess,
		$segmentNum,
		$segment,
		$pointNum,
		$point,
		$inputs,
		$inputReasons,
	);
?>
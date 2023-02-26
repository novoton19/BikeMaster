<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/07/23 11:09pm
		Version: 0.0.4.3
	Updated on
		Version: 0.4.4

	Description:
		Returns journey information

	Changes:

	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require usersDb
	require_once(__DIR__.'/Db/usersDb.php');
	#Require journeysDb
	require_once(__DIR__.'/Db/journeysDb.php');
	#Require segmentsDb
	require_once(__DIR__.'/Db/segmentsDb.php');
	#Require trackPointsDb
	require_once(__DIR__.'/Db/trackPointsDb.php');
	#Require journey classes
	require_once(__DIR__.'/journey.php');

	class JourneyLoader
	{
		private $usersDb;
		private $journeysDb;
		private $segmentsDb;
		private $trackPointsDb;

		public function __construct()
		{
			$this->usersDb = new UsersDb();
			$this->journeysDb = new JourneysDb();
			$this->segmentsDb = new SegmentsDb();
			$this->trackPointsDb = new TrackPointsDb();
		} 
		public function loadJourney($journeyID)
		{
			#Getting journeyInfo
			list($querySuccess, $journeyInfo, ) = $this->journeysDb->getJourney($journeyID);
			#Checking if success
			if (!$querySuccess)
			{
				return;
			}
			#Getting userInfo
			list($querySuccess, $accountInfo, ) = $this->usersDb->getUserByID($journeyInfo['UserID']);
			#Checking if success
			if (!$querySuccess)
			{
				return;
			}

			$home = null;
			$latitude = $accountInfo['Latitude'];
			$longitude = $accountInfo['Longitude'];
			#Checking if latitude and longitude are set
			if (!is_null($latitude) and !is_null($longitude))
			{
				$home = new TrackPoint(doubleval($latitude), doubleval($longitude), 0, time() * 1000);
			}

			#Resulting segments
			$segments = [];
			#Getting segments by journeyID
			list($querySuccess, $segmentInfos, ) = $this->segmentsDb->getSegments($journeyID);
			#Checking if success
			if (!$querySuccess)
			{
				return;
			}
			#Adding segments
			foreach ($segmentInfos as $segmentNum => $segmentInfo)
			{
				#Getting ID
				$segmentID = $segmentInfo['ID'];
				#Resulting points
				$points = [];
				#Getting points by segmentID
				list($querySuccess, $pointInfos, ) = $this->trackPointsDb->getPoints($segmentID);
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
			#Return journey
			return [
				'segments' => $segments,
				'id' => $journeyInfo['ID'],
				'title' => $journeyInfo['Title'],
				'description' => $journeyInfo['Description'],
				'length' => doubleval($journeyInfo['Length']),
				'startTime' => intval($journeyInfo['StartTime']),
				'endTime' => intval($journeyInfo['EndTime']),
				'creationTime' => intval($journeyInfo['CreationTime']),
				'archived' => $journeyInfo['Archived'] === '1',
				'owner' => [
					'id' => intval($accountInfo['ID']),
					'username' => $accountInfo['Username'],
					'profilePictureUrl' => $accountInfo['ProfilePictureUrl'],
					'description' => $accountInfo['Description'],
					'registrationTime' => intval($accountInfo['RegistrationTime']),
				]
			];
		}
	}
?>
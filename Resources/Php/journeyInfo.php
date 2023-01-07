<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/07/23 11:09pm
		Version: 0.0.4.3
	Updated on
		Version: 0.0.4.3

	Description:
		Returns journey information

	Changes:

	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require reasonIDsDb
	require_once(__DIR__.'/Db/reasonIDsDb.php');
	#Require journeysDb
	require_once(__DIR__.'/Db/journeysDb.php');
	#Require segmentsDb
	require_once(__DIR__.'/Db/segmentsDb.php');
	#Require trackPointsDb
	require_once(__DIR__.'/Db/trackPointsDb.php');

	#JourneyInformator
	class JourneyInformator
	{
		#Attributed
		private $reasonIDs;
		private $journeysDb;
		private $segmentsDb;
		private $trackPointsDb;

		#Constructor
		public function __construct()
		{
			#Creating databases
			$this->reasonIDs = new ReasonIDsDb();
			$this->journeysDb = new JourneysDb();
			$this->segmentsDb = new SegmentsDb();
			$this->trackPointsDb = new TrackPointsDb();
		}
		#Returns journey information
		public function getInformation($journeyID)
		{
			#Getting databases
			$reasonIDs = $this->reasonIDs;
			$journeysDb = $this->journeysDb;
			$segmentsDb = $this->segmentsDb;
			$trackPointsDb = $this->trackPointsDb;

			#Whether success
			$success = false;
			$reasonID = null;
			$reason = null;
			#Result
			$journeyInfo = [
				'ID' => $journeyID
			];

			#Checking if reasonIDs have loaded
			if ($reasonIDs->success)
			{
				#Getting journey information
				list($querySuccess, $journeyInfo, $journeyInfoExists) = $journeysDb->getJourney($journeyID);
				
				#Checking if query succeeded and journey found
				if ($querySuccess and $journeyInfoExists)
				{
					#Getting journey segments
					list($querySuccess, $segmentInfos, ) = $segmentsDb->getSegments($journeyID);
					#Checking if query succeeded
					if ($querySuccess)
					{
						#Creating segments array
						$journeyInfo['Segments'] = [];
						#Set success to true by default
						$success = true;
						#Loading segments
						foreach ($segmentInfos as $key => $segmentInfo)
						{
							#List of points
							$points = [];
							#Getting ID
							$segmentID = $segmentInfo['ID'];
							#Getting points
							list($querySuccess, $pointInfos, ) = $trackPointsDb->getPoints($segmentID);
							#Checking if query succeeded
							if ($querySuccess)
							{
								#Creating points array
								$segmentInfo['Points'] = $pointInfos;
								#Adding segment
								array_push($journeyInfo['Segments'], $segmentInfo);
							}
							else#if (!$querySuccess)
							{
								$success = false;
								$reasonID = $reasonIDs->DatabaseError;
								$reason = 'Server experienced an error while processing the request (1)';
								break;
							}
						}
						#Checking if succeeded
						if ($success)
						{
							$reasonID = $reasonIDs->Accepted;
						}
					}
					else#if (!$querySuccess)
					{
						$reasonID = $reasonIDs->DatabaseError;
						$reason = 'Server experienced an error while processing the request (2)';
					}
				}
				elseif (!$journeyInfoExists)
				{
					#Not found
					$reasonID = $reasonIDs->NotFound;
					$reason = 'Not found';
				}
				else#if (!querySuccess)
				{
					#Query did not succeed
					$reasonID = $reasonIDs->DatabaseError;
					$reason = 'Server experienced an error while processing the request (3)';
				}
			}
			else#if (!$reasonIDs->success)
			{
				#Reasons didn't load
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (4)';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				#Default reason
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'information' => $journeyInfo,
				'status' => [
					'success' => $success,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
			];
		}
	}
?>
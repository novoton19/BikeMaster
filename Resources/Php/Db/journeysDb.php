<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/06/23 12:10pm
		Version: 0.0.4.1
	Updated on
		Version: 0.0.4.3

	Description:
		Manages the Journeys database

	Changes:
		Version 0.0.4.3 - Get recent journeys from user
	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require database
	require_once(__DIR__.'/db.php');
	#Require general functions
	require_once(__DIR__.'/../general.php');
	

	class JourneysDb
	{
		#Database
		private $db;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$this->db = new Db();
		}
		#Create new Journey
		public function createNewJourney($userID, $startTime, $endTime)
		{
			#Insert
			list($success, ,) = $this->db->getData(
				'Insert Into Journeys
					(UserID, StartTime, EndTime) Values
					(:UserID, :StartTime, :EndTime)',
				[
					':UserID' => $userID,
					':StartTime' => $startTime,
					':EndTime' => $endTime
				]
			);
			#Last inserted ID
			$lastInsertedID = null;
			#Checking if success
			if ($success)
			{
				$lastInsertedID = intval($this->db->getLastInsertedID());
			}
			return [
				$success,
				$lastInsertedID
			];
		}
		#Returns journey information
		public function getJourney($journeyID)
		{
			#Result
			return $this->db->getData(
				'Select * From Journeys Where ID = :ID Limit 1',
				[
					':ID' => $journeyID
				],
				true
			);
		}
		#Returns journeyIDs of user
		public function getUserJourneyIDs($userID, $maxTime = null)
		{
			#Resulting journeyIDs
			$journeyIDs = [];

			#Checking if maxTime defined
			if (is_null($maxTime))
			{
				$maxTime = time() * 1000;
			}
			#Get journeys
			list($querySuccess, $queryResult, ) = $this->db->getData(
				'Select ID From Journeys Where UserID = :UserID And CreationTime < :MaxTime Order By CreationTime Desc Limit 3',
				[
					':UserID' => $userID,
					':MaxTime' => $maxTime
				]
			);
			#Checking if query succeeded
			if ($querySuccess)
			{
				#Get journeyIDs
				foreach ($queryResult as $key => $data)
				{
					#Adding ID
					array_push($journeyIDs, intval($data['ID']));
				}
			}
			return [
				$querySuccess,
				$journeyIDs,
				!empty($journeyIDs)
			];
		}
	}
?>
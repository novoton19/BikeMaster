<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/06/23 12:10pm
		Version: 0.0.4.1
	Updated on
		Version: 0.5.1

	Description:
		Manages the Journeys database

	Changes:
		Version 0.0.4.3 - Get recent journeys from user
		Version 0.4.5 - Change archivation
		Version 0.5.1 - Journey search
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
		public function createNewJourney($userID, $title, $description, $length, $startTime, $endTime)
		{
			#Insert
			list($success, ,) = $this->db->getData(
				'Insert Into Journeys
					(UserID, Title, Description, Length, StartTime, EndTime) Values
					(:UserID, :Title, :Description, :Length, :StartTime, :EndTime)',
				[
					':UserID' => $userID,
					':Title' => $title,
					':Description' => $description,
					':Length' => $length,
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
		#Changes archivation
		public function changeArchivation($journeyID, $userID)
		{
			#Update
			list($success, , ) = $this->db->getData(
				'Update Journeys Set Archived = Not Archived Where ID = :JourneyID And UserID = :UserID Limit 1',
				[
					':JourneyID' => $journeyID,
					':UserID' => $userID
				]
			);
			return $success;
		}
		#Returns journeys
		public function getJourneys($userID, $page = 0, $limit = 3)
		{
			#Return result
			return $this->db->getData(
				sprintf('SELECT * From Journeys Where UserID = :UserID And Not Archived Order By CreationTime Desc Limit %d Offset %d', $limit, $page * $limit),
				[
					':UserID' => $userID
				]
			);
		}
		#Returns archive
		public function getArchive($userID, $page = 0, $limit = 3)
		{
			#Return result
			return $this->db->getData(
				sprintf('SELECT * From Journeys Where UserID = :UserID And Archived Order By CreationTime Desc Limit %d Offset %d', $limit, $page * $limit),
				[
					':UserID' => $userID
				]
			);
		}
		#Returns amount of journeys
		public function getJourneysCount($userID)
		{
			#Amount of journeys
			$amount = null;
			#Getting journeys amount
			list($querySuccess, $queryResult, ) =  $this->db->getData(
				'SELECT Count(ID) As Result From Journeys Where UserID = :UserID And Not Archived',
				[
					':UserID' => $userID
				],
				true
			);
			#Checking if success
			if ($querySuccess)
			{
				$amount = intval($queryResult['Result']);
			}
			#Return result
			return [
				$querySuccess,
				$amount
			];
		}
		#Returns amount of items in archive
		public function getArchivedJourneysCount($userID)
		{
			#Amount of journeys
			$amount = null;
			#Getting journeys amount
			list($querySuccess, $queryResult, ) =  $this->db->getData(
				'SELECT Count(ID) As Result From Journeys Where UserID = :UserID And Archived',
				[
					':UserID' => $userID
				],
				true
			);
			#Checking if success
			if ($querySuccess)
			{
				$amount = intval($queryResult['Result']);
			}
			#Return result
			return [
				$querySuccess,
				$amount
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
		#Search journeys
		public function search($term, $userID, $page = 0, $pageSize = 3)
		{
			#Return result
			return $this->db->getData(
				sprintf('SELECT ID From Journeys Where (UserID = :UserID Or Not Archived) And (Title Like :Term Or Description Like :Term) Order By Title Like :Term Desc, CreationTime Desc Limit %d Offset %d', $pageSize, $page * $pageSize),
				[
					':Term' => '%'.$term.'%',
					':UserID' => $userID
				]
			);
		}
		#Returns amount of search results
		public function getSearchResultsCount($term, $userID)
		{
			#Resulting amout
			$amount = null;
			#Geting result
			list($querySuccess, $queryResult, ) = $this->db->getData(
				'SELECT Count(ID) As Result From Journeys Where (UserID = :UserID Or Not Archived) And (Title Like :Term Or Description Like :Term)',
				[
					':Term' => '%'.$term.'%',
					':UserID' => $userID
				],
				true
			);
			#Checking if query succeeded
			if ($querySuccess)
			{
				$amount = intval($queryResult['Result']);
			}
			return [
				$querySuccess,
				$amount
			];
		}
	}
?>
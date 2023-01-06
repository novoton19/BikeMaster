<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/06/23 12:10pm
		Version: 0.0.4.1
	Updated on
		Version: 0.0.4.1

	Description:
		Manages the Journeys database

	Changes:
		
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
	}
?>
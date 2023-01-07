<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/06/23 12:40pm
		Version: 0.0.4.1
	Updated on
		Version: 0.0.4.3

	Description:
		Manages the Segments database

	Changes:
		Version 0.0.4.3 - Return segments from journeyID
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
	

	class SegmentsDb
	{
		#Database
		private $db;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$this->db = new Db();
		}
		#Create new Segment
		public function createNewSegment($journeyID)
		{
			#Insert
			list($success, ,) = $this->db->getData(
				'Insert Into TrackSegments (JourneyID) Values (:JourneyID)',
				[
					':JourneyID' => $journeyID
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
		#Returns segments from journeyID
		public function getSegments($journeyID)
		{
			#Return result
			return $this->db->getData(
				'Select * From TrackSegments Where JourneyID = :JourneyID',
				[
					':JourneyID' => $journeyID
				]
			);
		}
	}
?>
<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/06/23 12:41pm
		Version: 0.0.4.1
	Updated on
		Version: 0.0.4.3

	Description:
		Manages the TrackPoints database

	Changes:
		Version 0.0.4.3 - Return points of segment
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
	

	class TrackPointsDb
	{
		#Database
		private $db;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$this->db = new Db();
		}
		#Create new TrackPoint
		public function createNewTrackPoint($segmentID, $latitude, $longitude, $accuracy, $timestamp, $altitude = null, $altitudeAccuracy = null)
		{
			#Insert
			list($success, ,) = $this->db->getData(
				'Insert Into TrackPoints
					(SegmentID, Latitude, Longitude, Accuracy, Timestamp) Values
					(:SegmentID, :Latitude, :Longitude, :Accuracy, :Timestamp)',
				[
					':SegmentID' => $segmentID,
					':Latitude' => $latitude,
					':Longitude' => $longitude,
					':Accuracy' => $accuracy,
					':Timestamp' => $timestamp
				]
			);
			return [
				$success
			];
		}
		#Returns points with associated segmentID
		public function getPoints($segmentID)
		{
			#Return result
			return $this->db->getData(
				'Select * From TrackPoints Where SegmentID = :SegmentID',
				[
					':SegmentID' => $segmentID
				]
			);
		}
	}
?>
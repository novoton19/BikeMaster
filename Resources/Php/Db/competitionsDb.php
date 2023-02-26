<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/26/23 11:39pm
		Version: 0.6.1
	Updated on
		Version: 0.6.1

	Description:
		Functions for competitions

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
	

	class CompetitionsDb
	{
		#Database
		private $db;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$this->db = new Db();
		}
		#Create competition
		public function create($senderID, $receiverID, $title, $description, $distance)
		{
			#Inserting information
			list($querySuccess, , ) = $this->db->getData(
				'INSERT Into Competitions (SenderUserID, ReceiverUserID, Title, Description, Distance) Values (:SenderID, :ReceiverID, :Title, :Description, :Distance)',
				[
					':SenderID' => $senderID,
					':ReceiverID' => $receiverID,
					':Title' => $title,
					':Description' => $description,
					':Distance' => $distance
				]
			);
			return $querySuccess;
		}
	}
?>
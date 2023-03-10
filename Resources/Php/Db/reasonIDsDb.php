<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 12:33pm
		Version: 0.0.3.1.1
	Updated on
		Version: 0.0.3.2

	Description:
		Class containing all reasonIDs

	Changes:
		Version 0.0.3.2 - Add list of all reasonIDs to object
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
	

	class ReasonIDsDb
	{
		#Whether succeeded to get reasonIDs
		public $success = false;
		#All of the reasonIDs
		public $all = [];
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$db = new Db();
			#Getting all reason IDs
			list($success, $result,) = $db->getData(
				'SELECT ID, NameID From ReasonIDs'
			);
			#Set success
			$this->success = $success;
			#All of the reasonIDs
			$all = [];
			#Checking if success
			if ($success)
			{
				#Loading reasons
				foreach ($result as $key => $reason) {
					#Getting ID and NameID
					$id = intval(GeneralFunctions::GetValue(
						$reason,
						'ID'
					));
					$nameID = GeneralFunctions::GetValue(
						$reason,
						'NameID'
					);
					$all[$nameID] = $id;
					$this->$nameID = $id;
				}
			}
			$this->all = $all;
		}
	}
?>
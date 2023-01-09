<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/09/23 10:23pm
		Version: 0.0.5.3
	Updated on
		Version: 0.0.5.3

	Description:
		Class containing all notification types

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
	

	class NotificationTypesDb
	{
		#Whether succeeded to get notification types
		public $success = false;
		#All of the notification types
		public $all = [];
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$db = new Db();
			#Getting all notification types
			list($success, $result,) = $db->getData(
				'SELECT ID, NameID From NotificationTypes'
			);
			#Set success
			$this->success = $success;
			#All of the notification types
			$all = [];
			#Checking if success
			if ($success)
			{
				#Loading notification types
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
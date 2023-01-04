<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/04/23 10:27pm
		Version: 0.0.3.2
	Updated on
		Version: 0.0.3.2.1

	Description:
		Class containing all settings

	Changes:
		Version 0.0.3.2.1 - Settings row ID was set as value instead of Value
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
	

	class SettingsDb
	{
		#Whether succeeded to get settings
		public $success = false;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$db = new Db();
			#Getting all settings
			list($success, $result,) = $db->getData(
				'SELECT Value, NameID From Settings'
			);
			#Set success
			$this->success = $success;
			#Checking if success
			if ($success)
			{
				#Loading settings
				foreach ($result as $key => $reason) {
					#Getting Value and NameID
					$value = GeneralFunctions::GetValue(
						$reason,
						'Value'
					);
					$nameID = GeneralFunctions::GetValue(
						$reason,
						'NameID'
					);
					$this->$nameID = $value;
				}
			}
		}
	}
?>
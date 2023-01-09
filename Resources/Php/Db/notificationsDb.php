<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/09/23 10:25pm
		Version: 0.0.5.3
	Updated on
		Version: 0.0.5.3

	Description:
		Class containing all notification functions

	Changes:

	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require database
	require_once(__DIR__.'/db.php');
	#Require reasonIDs
	require_once(__DIR__.'/reasonIDsDb.php');
	#Require general functions
	require_once(__DIR__.'/../general.php');
	

	class NotificationsDb
	{
		#Database
		private $db;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$db = new Db();
			$reasonIDs = new ReasonIDsDb();

			#Adding database
			$this->db = $db;
		}
		#Create notification
		public function create($type, $targetID, $triggerID = null)
		{
			#Return result
			return $this->db->getData(
				'INSERT Into Notifications (TypeID, TargetUserID, TriggerUserID) Values (:TypeID, :TargetUserID, :TriggerUserID)',
				[
					':TypeID' => $type,
					':TargetUserID' => $targetID,
					':TriggerUserID' => $triggerID
				]
			);
		}
		#Delete most recent notification if exists and not read
		public function delete($type, $targetID, $triggerID = null)
		{
			#Return result
			return $this->db->getData(
				'DELETE From Notifications Where TypeID = :TypeID And TargetUserID = :TargetUserID And TriggerUserID = :TriggerUserID And Not Viewed Order By TriggerTime Desc Limit 1',
				[
					':TypeID' => $type,
					':TargetUserID' => $targetID,
					':TriggerUserID' => $triggerID
				]
			);
		}
		#Mark notification as read
		public function view($notificationID, $targetID)
		{
			#Return result
			return $this->db->getData(
				'UPDATE Notifications Set Viewed = :Viewed, ViewTime = Unix_Timestamp() Where ID = :ID And TargetUserID = :TargetUserID Limit 1',
				[
					':Viewed' => true,
					':ID' => $notificationID,
					':TargetUserID' => $targetID
				]
			);
		}
		#Get notifications count
		public function getNotificationsCount($targetID)
		{
			#Amount
			$amount = null;
			#Get amount
			list($querySuccess, $queryResult, ) = $this->db->getData(
				'SELECT Count(ID) As Result From Notifications Where TargetUserID = :TargetUserID',
				[
					':TargetUserID' => $targetID
				],
				true
			);
			#Checking if success
			if ($querySuccess)
			{
				$amount = intval($queryResult['Result']);
			}
			return [
				$querySuccess,
				$amount
			];
		}
		#Get notifications
		public function getNotifications($targetID, $page = 0, $limit = 5)
		{
			#Return result
			return $this->db->getData(
				sprintf('SELECT * From Notifications Where TargetUserID = :TargetUserID Limit %d Offset %d', $limit, $page * $limit),
				[
					':TargetUserID' => $targetID
				]
			);
		}
	}
?>
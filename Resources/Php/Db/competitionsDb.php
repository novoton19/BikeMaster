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
		#Adds distance
		public function updateDistance($userID, $distanceDriven, $startTime)
		{
			#Updating information
			list($querySuccess, , ) = $this->db->getData(
				'UPDATE Competitions Set
					SenderDistanceDriven = SenderDistanceDriven + If(SenderUserID = :UserID, :Distance, 0),
					ReceiverDistanceDriven = ReceiverDistanceDriven + If(ReceiverUserID = :UserID, :Distance, 0),
					Finished = (If(SenderUserID = :UserID, SenderDistanceDriven, ReceiverDistanceDriven) + :Distance >= Distance),
					FinishTime = If(If(SenderUserID = :UserID, SenderDistanceDriven, ReceiverDistanceDriven) + :Distance >= Distance, Unix_Timestamp(), Null)
					Where Accepted And AcceptTime < :StartTime And Not Finished',
				[
					':UserID' => $userID,
					':Distance' => $distanceDriven,
					':StartTime' => $startTime
				]
			);
			return $querySuccess;
		}
		#Get competition by ID
		public function getCompetitionByID($id)
		{
			return $this->db->getData(
				'SELECT * From Competitions Where ID = :ID Limit 1',
				[
					':ID' => $id
				],
				true
			);
		}
		#Accept competition
		public function acceptCompetition($id, $userID)
		{
			list($querySuccess, , ) = $this->db->getData(
				'UPDATE Competitions Set Accepted = :Accepted, AcceptTime = Unix_Timestamp() Where ID = :ID And ReceiverUserID = :UserID And Not Finished',
				[
					':Accepted' => true,
					':ID' => $id,
					':UserID' => $userID
				]
			);
			return $querySuccess;
		}
		#Decline competition
		public function declineCompetition($id, $userID)
		{
			list($querySuccess, , ) = $this->db->getData(
				'UPDATE Competitions Set Finished = :Finished, FinishTime = Unix_Timestamp() Where ID = :ID And (ReceiverUserID = :UserID Or SenderUserID = :UserID) And Not Accepted',
				[
					':Finished' => true,
					':ID' => $id,
					':UserID' => $userID
				]
			);
			return $querySuccess;
		}
		#Get competitions
		public function getCompetitions($userID, $viewingType, $page = 0, $pageSize = 3)
		{
			#Resulting sql
			$sql = 'SELECT * From Competitions Where (SenderUserID = :UserID Or ReceiverUserID = :UserID) ';
			#Checking viewing type
			if ($viewingType === 'actual')
			{
				$sql .= 'And Accepted And Not Finished Order By AcceptTime Desc';
			}
			elseif ($viewingType === 'invitations')
			{
				$sql .= 'And Not Accepted And Not Finished Order By RequestTime Desc';
			}
			else#if ($viewingType === 'archive')
			{
				$sql .= 'And Accepted And Finished Order By FinishTime Desc';
			}
			$sql .= ' Limit %d Offset %d';
			#Getting search results
			return $this->db->getData(
				sprintf($sql, $pageSize, $page * $pageSize),
				[
					':UserID' => $userID
				]
			);
		}
		#Get results count
		public function getCompetitionsCount($userID, $viewingType)
		{
			#Result
			$result = null;
			#Resulting sql
			$sql = 'SELECT Count(ID) As Result From Competitions Where (SenderUserID = :UserID Or ReceiverUserID = :UserID) ';
			#Checking viewing type
			if ($viewingType === 'actual')
			{
				$sql .= 'And Accepted And Not Finished';
			}
			elseif ($viewingType === 'invitations')
			{
				$sql .= 'And Not Accepted And Not Finished';
			}
			else#if ($viewingType === 'archive')
			{
				$sql .= 'And Accepted And Finished';
			}
			#Getting search results
			list($querySuccess, $queryResult, ) = $this->db->getData(
				$sql,
				[
					':UserID' => $userID
				],
				true
			);
			#Checking if success
			if ($querySuccess)
			{
				$result = intval($queryResult['Result']);
			}
			return [
				$querySuccess,
				$result
			];
		}
	}
?>
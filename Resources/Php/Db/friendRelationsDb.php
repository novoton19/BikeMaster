<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/08/23 10:32pm
		Version: 0.0.5
	Updated on
		Version: 0.3.5

	Description:
		Functions managing friend relations

	Changes:
		Version 0.0.5.2 - Get IDs of friends
		Version 0.3.5 - Get friend requests, get friend requests count
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
	

	class FriendRelationsDb
	{
		#Database
		private $db;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$this->db = new Db();
		}
		#Returns friend relation
		public function getRelationByUsers($userIDA, $userIDB)
		{
			#Return result
			return $this->db->getData(
				'SELECT * From FriendRelations Where (SenderUserID = :UserIDA And ReceiverUserID = :UserIDB) Or (SenderUserID = :UserIDB And ReceiverUserID = :UserIDA) Limit 1',
				[
					':UserIDA' => $userIDA,
					':UserIDB' => $userIDB
				],
				true
			);
		}
		#Returns friend relation from record ID
		public function getRelationByID($id)
		{
			#Return result
			return $this->db->getData(
				'SELECT * From FriendRelations Where ID = :ID Limit 1',
				[
					':ID' => $id
				],
				true
			);
		}
		#Returns amount of friends user has
		public function getFriendsCount($userID)
		{
			#Amount of friends
			$amount = null;
			#Getting friends amount
			list($querySuccess, $queryResult, ) =  $this->db->getData(
				'SELECT Count(ID) As Result From FriendRelations Where SenderUserID = :UserID Or ReceiverUserID = :UserID And Accepted',
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
		#Returns amount of friend requests for user
		public function getFriendRequestsCount($userID)
		{
			#Amount of friend requests
			$amount = null;
			#Getting friends amount
			list($querySuccess, $queryResult, ) =  $this->db->getData(
				'SELECT Count(ID) As Result From FriendRelations Where SenderUserID = :UserID Or ReceiverUserID = :UserID And Not Accepted',
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
		#Returns friend accounts
		public function getFriends($userID, $page = 0, $limit = 3)
		{
			#Return result
			return $this->db->getData(
				sprintf('SELECT If(SenderUserID = :UserID, ReceiverUserID, SenderUserID) As ID From FriendRelations Where (SenderUserID = :UserID Or ReceiverUserID = :UserID) And Accepted Order By AcceptTime Limit %d Offset %d', $limit, $page * $limit),
				[
					':UserID' => $userID
				]
			);
		}
		#Returns friend accounts
		public function getFriendRequests($userID, $page = 0, $limit = 3)
		{
			#Return result
			return $this->db->getData(
				sprintf('SELECT If(SenderUserID = :UserID, ReceiverUserID, SenderUserID) As ID From FriendRelations Where (SenderUserID = :UserID Or ReceiverUserID = :UserID) And Not Accepted Order By RequestTime Limit %d Offset %d', $limit, $page * $limit),
				[
					':UserID' => $userID
				]
			);
		}
		#Creates a new friend relation request
		public function sendRequest($senderID, $receiverID)
		{
			#Return result
			return $this->db->getData(
				'INSERT Into FriendRelations (SenderUserID, ReceiverUserID) Values (:SenderUserID, :ReceiverUserID)',
				[
					':SenderUserID' => $senderID,
					':ReceiverUserID' => $receiverID
				]
			);
		}
		#Accepts the request
		public function acceptRequest($recordID, $userID)
		{
			#Return result
			return $this->db->getData(
				'UPDATE FriendRelations Set Accepted = :Accepted, AcceptTime = Unix_Timestamp() Where ID = :ID And ReceiverUserID = :UserID Limit 1',
				[
					':Accepted' => true,
					':ID' => $recordID,
					':UserID' => $userID
				]
			);
		}
		#Declines the request by deleting the record
		public function declineRequest($recordID, $userID)
		{
			#Return result
			return $this->db->getData(
				'DELETE From FriendRelations Where ID = :ID And ReceiverUserID = :UserID Limit 1',
				[
					':ID' => $recordID,
					':UserID' => $userID
				]
			);
		}
		#Ends the friend relation
		public function endRelation($recordID, $userID)
		{
			#Return result
			return $this->db->getData(
				'DELETE From FriendRelations Where ID = :ID And (SenderUserID = :UserID Or ReceiverUserID = :UserID) Limit 1',
				[
					':ID' => $recordID,
					':UserID' => $userID
				]
			);
		}
	}
?>
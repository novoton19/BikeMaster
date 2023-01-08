<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/01/23 02:48pm
		Version: 0.0.3
	Updated on
		Version: 0.0.4.4

	Description:
		Class containing functions which execute sql queries on database

	Changes:
		Version 0.0.3.1 - Add getUserByIDSecure
		Version 0.0.4.4 - Added function for searching for users and for getting amount of results
	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require database
	require_once(__DIR__.'/db.php');
	#Require general functions
	#require_once(__DIR__.'/../general.php');
	

	class UsersDb
	{
		#Database
		private $db;
		#Constructor
		public function __construct()
		{
			#Creating a new database connection
			$this->db = new Db();
		}
		#Creates a new user
		public function createUser($username = '', $email = '', $passwordHash = '')
		{
			#Insert a new record
			list($success, , ) = $this->db->getData(
				'INSERT Into Users (Username, Email, Password) Values (:Username, :Email, :Password)',
				[
					':Username' => $username,
					':Email' => $email,
					':Password' => $passwordHash
				]
			);
			#Return result
			return $success;
		}
		#Get user by ID
		public function getUserByIDSecure($id)
		{
			#Return result
			return $this->db->getData(
				'SELECT ID, Username, Email, RegistrationTime From Users Where ID = :ID Limit 1',
				[
					':ID' => $id
				],
				true
			);
		}
		#Get user by username
		public function getUserByUsername($username)
		{
			#Return result
			return $this->db->getData(
				'SELECT * From Users Where Username = :Username Limit 1',
				[
					':Username' => $username
				],
				true
			);
		}
		#Get user by e-mail
		public function getUserByEmail($email)
		{
			#Return result
			return $this->db->getData(
				'SELECT * From Users Where Email = :Email Limit 1',
				[
					':Email' => $email
				],
				true
			);
		}
		#Get user by username or email
		public function getUserByUsernameOrEmail($username, $email)
		{
			#Return result
			return $this->db->getData(
				'SELECT * From Users Where Username = :Username Or Email = :Email Limit 1',
				[
					':Username' => $username,
					':Email' => $email
				],
				true
			);
		}
		#Searches for users
		public function search($username, $page = 0, $limit = 3)
		{
			#Return result
			return $this->db->getData(
				sprintf('SELECT ID, Username, Email, RegistrationTime From Users Where Username Like :Username Limit %d Offset %d', $limit, $page * $limit),
				[
					':Username' => '%'.$username.'%'
				]
			);
		}
		#Returns amount of search results
		public function getSearchResultsCount($username)
		{
			#Resulting amout
			$amount = null;
			#Geting result
			list($querySuccess, $queryResult, ) = $this->db->getData(
				'SELECT Count(ID) As Result From Users Where Username Like :Username',
				[
					':Username' => '%'.$username.'%'
				],
				true
			);
			#Checking if query succeeded
			if ($querySuccess)
			{
				$amount = intval($queryResult['Result']);
			}
			return [
				$querySuccess,
				$amount
			];
		}
	}
?>
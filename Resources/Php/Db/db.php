<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		? Taken from another project
		Version: 0.0.3
	Updated on
		Version: 0.0.4.1

	Description:
		Database connection, Sql executor

	Changes:
		Version 0.0.4.1 - Function getLastInsertID
	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Including general functions
	require_once(__DIR__.'/../general.php');
	

	#Database login information
	define('DB_NAME', 'BikeMaster');
	define('DB_USER', 'User');
	define('DB_PASSWORD', 'RUQN4xe(m(Qa8c8N');
	define('DB_HOST', 'localhost');

	class Db
	{
		private $db;
		public function __construct()
		{
			#Database
			$this->db = new PDO(
				'mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD,
				[
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
				]
			);
		}
		#Executes sql query and returns status and result
		public function getData($sql, $data = [], $returnSingleResult = false)
		{
			#Preparing sql
			$toExecute = $this->db -> prepare($sql);
			#Executing sql
			$success = $toExecute -> execute($data);
			#Getting error status
			$error = !$success;

			#Getting result
			$result = $toExecute -> fetchAll(PDO::FETCH_ASSOC);

			#Getting result info
			$resultExists = !empty($result);
			$isSingleResult = count($result) == 1;

			#If result exists and there is exactly one result
			if ($success and $resultExists and $returnSingleResult) {
				#Getting single result
				$result = GeneralFunctions::getValue(
					$result,
					0,
					[]
				);
			}
			#Returning result information
			return [
				$success,
				$result,
				$resultExists
			];
		}
		#Executes sql query and returns result if exists, otherwise default value
		public function getResult($sql, $data = [], $defaultValue = null)
		{
			list($success, $result, $resultExists) = $this->getData($sql, $data, true);

			#Checking if result exists
			if ($success and $resultExists)
			{
				#Getting resulting value
				return GeneralFunctions::getValue($result, 'Result', $defaultValue, false);
			}
			#Default value
			return $defaultValue;
		}
		#Returns last inserted ID
		public function getLastInsertedID()
		{
			return $this->db->lastInsertId();
		}
	}
?>
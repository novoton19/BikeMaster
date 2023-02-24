<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 08:48pm
		Version: 0.0.4
	Updated on
		Version: 0.4.3

	Description:
		Validation of journey

	Changes:
		Version 0.0.4.1 - Better way of returning result, more clear validation procedure
	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Require reason IDs
	require_once(__DIR__.'/../Db/reasonIDsDb.php');
	#Require SettingsDb
	require_once(__DIR__.'/../Db/settingsDb.php');
	#Require journey classes
	require_once(__DIR__.'/../journey.php');
	#Require general functions
	require_once(__DIR__.'/../general.php');
	
	#Validation class
	class JourneyValidation
	{
		#ReasonIDs
		private $reasonIDs;
		private $settingsDb;
		#Constructor
		public function __construct()
		{
			#Create ReasonIDsDb
			$this->reasonIDs = new ReasonIDsDb();
			$this->settingsDb = new SettingsDb();
		}
		#Validates title
		public function validateTitle($title)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking type
			if (gettype($title) === 'string')
			{
				#Checking length
				if (strlen($title) <= 32)
				{
					$valid = true;
					$reasonID = $reasonIDs->Accepted;
				}
				else
				{
					$reasonID = $reasonIDs->TooLong;
					$reason = 'Title must not be longer than 32 characters';
				}
			}
			else
			{
				$reasonID = $reasonIDs->InvalidType;
				$reason = 'Not a string';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates description
		public function validateDescription($description)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking type
			if (gettype($description) === 'string')
			{
				#Checking length
				if (strlen($description) <= 32)
				{
					$valid = true;
					$reasonID = $reasonIDs->Accepted;
				}
				else
				{
					$reasonID = $reasonIDs->TooLong;
					$reason = 'Description must not be longer than 512 characters';
				}
			}
			else
			{
				$reasonID = $reasonIDs->InvalidType;
				$reason = 'Not a string';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates latitude
		private function validateLatitude($latitude)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$latitude = doubleval($latitude);

			#Checking range
			if ($latitude >= -90 and $latitude <= 90)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
			}
			else#if ($latitude < -90 or $latitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not latitude';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'valid' => $valid, 
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates longitude
		private function validateLongitude($longitude)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$longitude = doubleval($longitude);

			#Checking range
			if ($longitude >= -180 and $longitude <= 180)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
			}
			else#if ($longitude < -90 or $longitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not longitude';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates accuracy
		private function validateAccuracy($accuracy)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Getting settingsDb
			$settingsDb = $this->settingsDb;

			#Getting maximum accuracy
			$maximumAccuracy = intval($settingsDb->MaximumAccuracy);

			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$accuracy = doubleval($accuracy);
				
			#Checking range
			if ($accuracy >= 0 and $accuracy <= $maximumAccuracy)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
			}
			elseif ($accuracy > $maximumAccuracy)
			{
				#Not necessarily marked as invalid, rather capped
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Out of range, capped';
				$valid = true;
			}
			else#if ($accuracy < 0)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Invalid';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates time
		private function validateTime($time)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Getting settingsDb
			$settingsDb = $this->settingsDb;

			#Getting minimum time
			$minimumTime = intval($settingsDb->MinimumTime);

			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$time = intval($time);
			#Checking range
			#Maximum time: Time right now
			if ($time >= $minimumTime and $time <= time() * 1000)
			{
				#Valid
				$valid = true;
				$reasonID = $reasonIDs->Accepted;
			}
			else#if ($time < $minimumTime or $time > time() * 1000)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Invalid';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason
			];
		}
		#Validates track point
		private function validatePoint($point)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;

			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Attribute reasons
			$attributeReasons = [];

			#Checking type
			if (gettype($point) === 'array')
			{
				#Getting attributes
				$latitude = GeneralFunctions::getValue($point, 'latitude');
				$longitude = GeneralFunctions::getValue($point, 'longitude');
				$accuracy = GeneralFunctions::getValue($point, 'accuracy');
				$timestamp = GeneralFunctions::getValue($point, 'timestamp');

				#Validating attributes
				$latitudeValidation = $this->validateLatitude($latitude);
				$longitudeValidation = $this->validateLongitude($longitude);
				$accuracyValidation = $this->validateAccuracy($accuracy);
				$timestampValidation = $this->validateTime($timestamp);

				#Adding attribute reasons
				$attributeReasons = [
					'latitude' => $latitudeValidation,
					'longitude' => $longitudeValidation,
					'accuracy' => $accuracyValidation,
					'timestamp' => $timestampValidation
				];
				#Whether valid
				$valid = (
					$latitudeValidation['valid'] and
					$longitudeValidation['valid'] and
					$accuracyValidation['valid'] and
					$timestampValidation['valid']
				);
				#Checking if valid
				if ($valid)
				{
					$reasonID = $reasonIDs->Accepted;
				}
				else#if (!$valid)
				{
					$reasonID = $reasonIDs->InvalidInputs;
					$reason = 'One or more attributes is invalid';
				}
			}
			elseif (is_null($point))
			{
				#Not set
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Not specified';
			}
			else#if (gettype($point) !== 'array')
			{
				#Not an array
				$reasonID = $reasonIDs->InvalidType;
				$reason = 'Not an array';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'inputReasons' => $attributeReasons
			];
		}
		#Validates segment
		private function validateSegment($segment)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;

			#Whether is valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Point reasons
			$pointReasons = [];
			
			#Checking type
			if (gettype($segment) === 'array')
			{
				#Getting points
				$points = GeneralFunctions::getValue($segment, 'points');
				#Checking type
				if (gettype($points) === 'array')
				{
					#Getting pointsCount
					$pointsCount = count($points);
					#Whether points are valid (default true)
					$pointsValid = true;
					#Validating points
					foreach ($points as $pointNum => $point)
					{
						#Validating point
						$pointValidation = $this->validatePoint($point);
						#Getting status
						$pointValid = $pointValidation['valid'];

						#Adding point to pointReasons
						array_push($pointReasons, $pointValidation);
						#Checking if point valid
						if (!$pointValid)
						{
							#Points are not valid
							$pointsValid = false;
						}
					}
					#Checking if points are valid
					if ($pointsValid)
					{
						$valid = true;
						$reason = $reasonIDs->Accepted;
					}
					else#if (!$pointsValid)
					{
						#Invalid points
						$reasonID = $reasonIDs->InvalidInputs;
						$reason = 'One or more points is invalid';
					}
				}
				elseif (is_null($points))
				{
					#Not set
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Points not specified';
				}
				else#if (gettype($points) !== 'array')
				{
					#Not an array
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Points not an array';
				}
			}
			elseif (is_null($segment))
			{
				#Not set
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Not specified';
			}
			else#if (gettype($segment) !== 'array')
			{
				#Not an array
				$reasonID = $reasonIDs->InvalidType;
				$reason = 'Not an array';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'inputReasons' =>
				[
					'points' => $pointReasons
				]
			];
		}
		#Validates track
		public function validateTrack($track)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Getting settingsDb
			$settingsDb = $this->settingsDb;
			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Segment reasons
			$segmentReasons = [];

			#Checking if succeeded to get reasonIDs and settings
			if ($reasonIDs->success and $settingsDb->success)
			{
				#Getting maximum speed
				$maximumSpeed = doubleval($settingsDb->MaximumSpeed);
				#Checking type
				if (gettype($track) === 'array')
				{
					#Getting segments
					$segments = GeneralFunctions::getvalue($track, 'segments');
					#Checking type
					if (gettype($segments) === 'array')
					{
						#Getting segments count
						$segmentsCount = count($segments);
						#Whether segments are valid
						$segmentsValid = true;
						#Validating segments
						foreach ($segments as $segmentNum => $segment)
						{
							#Getting segment validation
							$segmentValidation = $this->validateSegment($segment);
							#Getting status
							$segmentValid = $segmentValidation['valid'];
							
							#Adding segment to segmentReasons
							array_push($segmentReasons, $segmentValidation);
							#Checking if segment is valid
							if (!$segmentValid)
							{
								#Segments are not valid
								$segmentsValid = false;
							}
						}
						#Checking if segments are valid
						if ($segmentsValid)
						{
							#Checking amount of segments
							if ($segmentsCount > 0)
							{
								#List of all points
								$points = [];
								$chronological = true;
								$teleportation = false;
								#Adding points
								foreach ($segments as $segmentNum => $segment)
								{
									foreach ($segment['points'] as $pointNum => $point)
									{
										#Adding point
										array_push($points, $point);
									}
								}
								#Getting amount of points
								$pointsCount = count($points);
								#Checking amount of points
								if ($pointsCount > 1)
								{
									$pointNum = 1;
									#Validating points
									for ($pointNum = 1; $pointNum < $pointsCount; $pointNum++)
									{
										#Getting points
										$prevPoint = $points[$pointNum - 1];
										$thisPoint = $points[$pointNum];
										#Getitng timestamps
										$prevTimestamp = intval($prevPoint['timestamp']);
										$thisTimestamp = intval($thisPoint['timestamp']);
										#Getting coordinates
										$prevLat = round(floatval($prevPoint['latitude']), 4);
										$prevLon = round(floatval($prevPoint['longitude']), 4);
										$thisLat = round(floatval($thisPoint['latitude']), 4);
										$thisLon = round(floatval($thisPoint['longitude']), 4);
										#Checking time
										if ($thisTimestamp === $prevTimestamp and !($prevLat === $thisLat and $prevLon === $thisLon))
										{
											$teleportation = true;
											break;
										}
										elseif ($prevTimestamp > $thisTimestamp)
										{
											$chronological = false;
											break;
										}
									}
									if ($chronological and !$teleportation)
									{
										#Creating track
										$trackObj = Track::fromArray($track);
										#Checking length and time
										if ($trackObj->length > 0 and $trackObj->time > 0)
										{
											//Checking speed
											if ($trackObj->speed <= $maximumSpeed)
											{
												#Valid
												$valid = true;
												$reasonID = $reasonIDs->Accepted;
											}
											else
											{
												$reasonID = $reasonIDs->TooHighSpeed;
												$reason = 'Too high speed';
											}
										}
										elseif ($trackObj->length <= 0)
										{
											#No distance
											$reasonID = $reasonIDs->TooShort;
											$reason = 'Track does not have any distance';
										}
										else#if ($trackObj->time <= 0)
										{
											#No time
											$reasonID = $reasonIDs->TooShort;
											$reason = 'Track has no time';
										}
									}
									elseif (!$chronological)
									{
										#Time travel
										$reasonID = $reasonIDs->TimeTravel;
										$reason = 'Points are not in chronological order (at point index '.($pointNum - 1).')';
									}
									else#if ($teleportation)
									{
										#Teleportation
										$reasonID = $reasonIDs->Teleportation;
										$reason = 'Different coordinates at the same time (at point index '.($pointNum - 1).')';
									}
								}
								else
								{
									$reasonID = $reasonIDs->TooShort;
									$reason = 'Too few points';
								}
							}
							else
							{
								#Invalid
								$reasonID = $reasonIDs->Empty;
								$reason = 'Does not have any segments';
							}
						}
						else#if (!$segmentsValid)
						{
							#Invalid
							$reasonID = $reasonIDs->InvalidInputs;
							$reason = 'One or more segments is invalid';
						}
					}
					elseif (is_null($segments))
					{
						#Not set
						$reasonID = $reasonIDs->IsNull;
						$reason = 'Segments not specified';
					}
					else#if (gettype($segments) !== 'array')
					{
						#Not an array
						$reasonID = $reasonIDs->InvalidType;
						$reason = 'Segments not an array';
					}
				}
				elseif (is_null($track))
				{
					#Not set
					$reasonID = $reasonIDs->IsNull;
					$reason = 'Not specified';
				}
				else#if (gettype($track) !== 'array')
				{
					#Not an array
					$reasonID = $reasonIDs->InvalidType;
					$reason = 'Not an array';
				}
			}
			elseif (!$reasonIDs->success)
			{
				#Cannot get reason IDs
				$reasonID = -1;
				$reason = 'Server experienced an error while processing the request (1)';
			}
			else#if (!$this->settingsDb->success)
			{
				#Cannot get settings
				$reasonID = $reasonIDs->DatabaseError;
				$reason = 'Server experienced an error while processing the request (2)';
			}
			#Checking if reason exists
			if (is_null($reasonID))
			{
				$reasonID = $reasonIDs->NoReasonAvailable;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'inputReasons' =>
				[
					'segments' => $segmentReasons
				]
			];
		}
	}
?>
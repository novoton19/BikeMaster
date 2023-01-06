<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 08:48pm
		Version: 0.0.4
	Updated on
		Version: 0.0.4.1

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
		#Validates latitude
		private function validateLatitude($latitude)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#Filtered result
			$latitudeValue = null;
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
				$latitudeValue = round($latitude, 7);
			}
			else#if ($latitude < -90 or $latitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not latitude';
			}
			#Return result
			return [
				'value' => $latitudeValue,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
			];
		}
		#Validates longitude
		private function validateLongitude($longitude)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#Filtered result
			$longitudeValue = null;
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
				$longitudeValue = round($longitude, 7);
			}
			else#if ($longitude < -90 or $longitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not longitude';
			}
			#Return result
			return [
				'value' => $longitudeValue,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
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
			#Filtered result
			$accuracyValue = null;
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
				$accuracyValue = round($accuracy, 3);
			}
			elseif ($accuracy > $maximumAccuracy)
			{
				#Not necessarily marked as invalid, rather capped
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Out of range, capped';
				$valid = true;
				$accuracyValue = $maximumAccuracy;
			}
			else#if ($accuracy < 0)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Invalid';
			}
			#Return result
			return [
				'value' => $accuracyValue,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
			];
		}
		#Validates altitude
		private function validateAltitude($altitude)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Getting settingsDb
			$settingsDb = $this->settingsDb;

			#Getting minimum altitude
			$minimumAltitude = intval($settingsDb->MinimumAltitude);
			#Getting maximum altitude
			$maximumAltitude = intval($settingsDb->MaximumAltitude);

			#Whether valid
			$valid = false;
			#Filtered result
			$altitudeValue = null;
			#ReasonID
			$reasonID = null;
			$reason = null;

			#Checking if set
			if ((!is_null($altitude)) and !empty($altitude))
			{
				#Convert to double
				$altitude = doubleval($altitude);
				#Checking range
				if ($altitude >= $minimumAltitude and $altitude <= $maximumAltitude)
				{
					#Valid
					$reasonID = $reasonIDs->Accepted;
					$valid = true;
					$altitudeValue = round($altitude, 3);
				}
				elseif ($altitude > $maximumAltitude)
				{
					#Not necessarily marked as invalid, rather capped
					$reasonID = $reasonIDs->OutOfRange;
					$reason = 'Out of range, capped';
					$valid = true;
					$altitudeValue = $maximumAltitude;
				}
				else#if ($altitude < $minimumAltitude)
				{
					#Not necessarily marked as invalid, rather capped
					$reasonID = $reasonIDs->OutOfRange;
					$reason = 'Out of range, capped';
					$valid = true;
					$altitudeValue = $minimumAltitude;
				}
			}
			else#if (is_null($altitude) or empty($altitude))
			{
				#Can be null
				$valid = true;
				$altitudeValue = null;
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Not set';
			}
			#Return result
			return [
				'value' => $altitudeValue,
				'status' => 
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
			];
		}
		#Validates altitude
		private function validateAltitudeAccuracy($accuracy)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Getting settingsDb
			$settingsDb = $this->settingsDb;

			#Getting minimum altitude accuracy
			$minimumAccuracy = intval($settingsDb->MinimumAltitudeAccuracy);
			#Getting maximum altitude
			$maximumAccuracy = intval($settingsDb->MaximumAltitudeAccuracy);

			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Filtered result
			$accuracyValue = null;

			#Checking if set
			if ((!is_null($accuracy)) and !empty($accuracy))
			{
				#Convert to double
				$accuracy = doubleval($accuracy);
				#Checking range
				if ($accuracy >= $minimumAccuracy and $accuracy <= $maximumAccuracy)
				{
					#Valid
					$valid = true;
					$accuracyValue = round($accuracy, 3);
					$reasonID = $reasonIDs->Accepted;
				}
				elseif ($accuracy > $maximumAccuracy)
				{
					#Not necessarily marked as invalid, rather capped
					$valid = true;
					$accuracyValue = $maximumAccuracy;
					$reasonID = $reasonIDs->OutOfRange;
					$reason = 'Out of range, capped';
				}
				else#if ($accuracy < $minimumAccuracy)
				{
					#Not necessarily marked as invalid, rather capped
					$valid = true;
					$accuracyValue = $minimumAccuracy;
					$reasonID = $reasonIDs->OutOfRange;
					$reason = 'Out of range, capped';
				}
			}
			else#if (is_null($accuracy) or empty($accuracy))
			{
				#Can be null
				$valid = true;
				$accuracyValue = null;
				$reasonID = $reasonIDs->IsNull;
				$reason = 'Not set';
			}

			#Return result
			return [
				'value' => $accuracyValue,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
			];
		}
		#Validates time
		private function validateTime($time, $timeFrom)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Getting settingsDb
			$settingsDb = $this->settingsDb;

			#Getting minimum time
			$minimumTime = intval($settingsDb->MinimumTime);
			#Checking if timeFrom exists
			if (!is_null($timeFrom))
			{
				#Comparing times
				if ($timeFrom > $minimumTime)
				{
					$minimumTime = $timeFrom;
				}
			}

			#Whether valid
			$valid = false;
			#Filtered result
			$timeValue = null;
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
				$timeValue = $time;
				$reasonID = $reasonIDs->Accepted;
			}
			else#if ($time < $minimumTime or $time > time() * 1000)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Invalid';
			}
			#Return result
			return [
				'value' => $timeValue,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason
				]
			];
		}
		#Validates track point
		private function validatePoint($point, $timeFrom)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;

			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Result pointObject
			$pointObject = null;

			#Attribute reasons
			$attributeReasons = [];

			#Checking type
			if (gettype($point) === 'array')
			{
				#Getting attributes
				$latitude = GeneralFunctions::getValue($point, 'latitude');
				$longitude = GeneralFunctions::getValue($point, 'longitude');
				$accuracy = GeneralFunctions::getValue($point, 'accuracy');
				$altitude = GeneralFunctions::getValue($point, 'altitude');
				$altitudeAccuracy = GeneralFunctions::getValue($point, 'altitudeAccuracy');
				$timestamp = GeneralFunctions::getValue($point, 'timestamp');

				#Validating attributes
				$latitudeValidation = $this->validateLatitude($latitude);
				$longitudeValidation = $this->validateLongitude($longitude);
				$accuracyValidation = $this->validateAccuracy($accuracy);
				$altitudeValidation = $this->validateAltitude($altitude);
				$altitudeAccuracyValidation = $this->validateAltitudeAccuracy($altitudeAccuracy);
				$timestampValidation = $this->validateTime($timestamp, $timeFrom);

				#Getting statuses
				$latitudeStatus = $latitudeValidation['status'];
				$longitudeStatus = $longitudeValidation['status'];
				$accuracyStatus = $accuracyValidation['status'];
				$altitudeStatus = $altitudeValidation['status'];
				$altitudeAccuracyStatus = $altitudeAccuracyValidation['status'];
				$timestampStatus = $timestampValidation['status'];

				#Adding attribute reasons
				$attributeReasons = [
					'latitude' => $latitudeStatus,
					'longitude' => $longitudeStatus,
					'accuracy' => $accuracyStatus,
					'altitude' => $altitudeStatus,
					'altitudeAccuracy' => $altitudeAccuracyStatus,
					'timestamp' => $timestampStatus
				];
				#Whether valid
				$valid = (
					$latitudeStatus['valid'] and
					$longitudeStatus['valid'] and
					$accuracyStatus['valid'] and
					$altitudeStatus['valid'] and
					$altitudeAccuracyStatus['valid'] and
					$timestampStatus['valid']
				);
				#Checking if valid
				if ($valid)
				{
					#Creating point
					$pointObject = new TrackPoint(
						$latitudeValidation['value'],
						$longitudeValidation['value'],
						$accuracyValidation['value'],
						$timestampValidation['value'],
						$altitudeValidation['value'],
						$altitudeAccuracyValidation['value']
					);
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
				'object' => $pointObject,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason,
					'inputReasons' => $attributeReasons
				]
			];
		}
		#Validates segment
		private function validateSegment($segment, $timeFrom)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Getting settingsDb
			$settingsDb = $this->settingsDb;

			#Getting maximum speed
			$maximumSpeed = doubleval($settingsDb->MaximumSpeed);
			
			#Whether is valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Resulting segments
			$segmentObjects = [];
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
					#Whether points are valid (default true)
					$pointsValid = true;
					#Points objects
					$pointObjects = [];
					#Getting pointObjects
					foreach ($points as $pointNum => $point)
					{
						#Validating point
						$pointValidation = $this->validatePoint($point, $timeFrom);
						#Getting pointObject
						$pointObject = $pointValidation['object'];
						#Getting status
						$pointStatus = $pointValidation['status'];
						$pointValid = $pointStatus['valid'];

						#Adding point to pointReasons
						array_push($pointReasons, $pointStatus);
						#Checking if point valid
						if ($pointValid)
						{
							#Adding point to pointObjects
							array_push($pointObjects, $pointObject);
							#Getting object time
							$timeFrom = $pointObject->timestamp;
						}
						else#if (!$pointValid)
						{
							#Points are not valid
							$pointsValid = false;
						}
					}
					#Checking if points are valid
					if ($pointsValid)
					{
						#Current points to be added to the next segment
						$segmentPointObjects = [
							$pointObjects[0]
						];
						#Getting pointsCount
						$pointObjectsCount = count($pointObjects);
						#Creating segmentObjects
						for ($pointNum = 1; $pointNum < $pointObjectsCount; $pointNum++)
						{
							#Getting points
							$prevPointObject = $pointObjects[$pointNum - 1];
							$thisPointObject = $pointObjects[$pointNum];

							#Getting distance (meters)
							$distance = $prevPointObject->getDistanceTo($thisPointObject);
							#Taking into account accuracy difference (worst case scenario)
							$distance -= $prevPointObject->accuracy + $thisPointObject->accuracy;
							#Getting time difference (seconds)
							$timeDifference = ($thisPointObject->timestamp - $prevPointObject->timestamp) / 1000;

							#Checking if there is any distance between the points
							if ($distance == 0)
							{
								#Points are identical
								if ($timeDifference > 0)
								{
									#Points have different times
									#Adding point to the list
									array_push($segmentPointObjects, $thisPointObject);
								}
								else#if ($timeDifference <= 0)
								{
									#Do not add point, because points are completely identical
								}
							}
							elseif ($distance > 0)
							{
								#Checking time
								if ($timeDifference > 0)
								{
									#Calculating speed (m/s) => (km/h)
									$speed = ($distance / $timeDifference) / 3.6;
									#Checking speed
									if ($speed <= $maximumSpeed)
									{
										#Point is valid, add it to the list
										array_push($segmentPointObjects, $thisPointObject);
									}
									else#if ($speed > $maximumSpeed)
									{
										#Too high speed, split segments
										#Closing segment
										$segmentObject = new Segment($segmentPointObjects);
										#Checking if has any distance
										if ($segmentObject->length > 0)
										{
											#Adding to segments
											array_push($segmentObjects, $segmentObject);
										}
										#Resetting array starting with current point
										$segmentPointObjects = [
											$thisPointObject
										];
									}
								}
								else#if ($timeDifference <= 0)
								{
									#Teleportation, split segments
									#Closing segment
									$segmentObject = new Segment($segmentPointObjects);
									#Checking if has any distance
									if ($segmentObject->length > 0)
									{
										#Adding to segments
										array_push($segmentObjects, $segmentObject);
									}
									#Resetting array starting with current point
									$segmentPointObjects = [
										$thisPointObject
									];
								}
							}
							else#if ($distance < 0)
							{
								#The accuracy is too low, the points can be identical => their real distance can be 0
								#Checking if time difference is not 0
								if ($timeDifference > 0)
								{
									#Adding point to the list
									array_push($segmentPointObjects, $thisPointObject);
								}
								else#if ($timeDifference <= 0)
								{
									#Skip
								}
							}
						}
						#Finish segment
						$segmentObject = new Segment($segmentPointObjects);
						#Checking if has any distance
						if ($segmentObject->length > 0)
						{
							#Adding to segments
							array_push($segmentObjects, $segmentObject);
						}
						#Checking if there are any segments
						if (!empty($segmentObjects))
						{
							#Valid
							$valid = true;
							$reasonID = $reasonIDs->Accepted;
						}
						else#if (empty($segmentObjects))
						{
							#Invalid speed
							$reasonID = $reasonIDs->InvalidInputs;
							$reason = 'There were either identical points, too high speed or teleportation between the points';
						}
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
				'object' => $segmentObjects,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason,
					'inputReasons' =>
					[
						'points' => $pointReasons
					]
				]
			];
		}
		#Validates track
		public function validateTrack($track)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Resulting track
			$trackObject = null;
			#Segment reasons
			$segmentReasons = [];

			#Checking if succeeded to get reasonIDs and settings
			if ($reasonIDs->success and $this->settingsDb->success)
			{
				#Checking type
				if (gettype($track) === 'array')
				{
					#Getting segments
					$segments = GeneralFunctions::getvalue($track, 'segments');
					#Checking type
					if (gettype($segments) === 'array')
					{
						#Resulting segmentObjects
						$segmentObjects = [];
						#Valid segment start time
						$timeFrom = null;
						#Whether segments are valid
						$segmentsValid = true;
						#Validating segments
						foreach ($segments as $segmentNum => $segment)
						{
							#Getting segment validation
							$segmentValidation = $this->validateSegment($segment, $timeFrom);
							#Getting segmentObject
							$segmentValidationObjects = $segmentValidation['object'];
							#Getting status
							$segmentStatus = $segmentValidation['status'];
							$segmentValid = $segmentStatus['valid'];

							#Adding segment to segmentReasons
							array_push($segmentReasons, $segmentStatus);
							#Checking if segment is valid
							if ($segmentValid)
							{
								#Adding objects
								foreach ($segmentValidationObjects as $segmentNum => $segmentObject)
								{
									#Adding segment to segmentObjects
									array_push($segmentObjects, $segmentObject);
								}
								#Getting segment start time
								$timeFrom = end($segmentValidationObjects)->endTime;
							}
							else#if (!$segmentValid)
							{
								#Segments are not valid
								$segmentsValid = false;
							}
						}
						#Checking if segments are valid
						if ($segmentsValid)
						{
							#Creating trackObject
							$trackObject = new Track($segmentObjects);
							#Checking length of track
							if ($trackObject->length > 0)
							{
								#Valid
								$valid = true;
								$reasonID = $reasonIDs->Accepted;
							}
							else#if ($trackObject->length <= 0)
							{
								#Invalid
								$reasonID = $reasonIDs->Empty;
								$reason = 'Does not have any distance';
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
				'object' => $trackObject,
				'status' =>
				[
					'valid' => $valid,
					'reasonID' => $reasonID,
					'reason' => $reason,
					'inputReasons' =>
					[
						'segments' => $segmentReasons
					]
				]
			];
		}
	}
?>
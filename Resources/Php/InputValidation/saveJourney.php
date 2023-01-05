<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 08:48pm
		Version: 0.0.4
	Updated on
		Version: 0.0.4

	Description:
		Validation of journey

	Changes:

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
			$filteredLatitude = null;
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
				$filteredLatitude = round($latitude, 7);
			}
			else#if ($latitude < -90 or $latitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not latitude';
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'filter' => $filteredLatitude
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
			$filteredLongitude = null;
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
				$filteredLongitude = round($longitude, 7);
			}
			else#if ($longitude < -90 or $longitude > 90)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Not longitude';
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'filter' => $filteredLongitude
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
			$filteredAccuracy = null;
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
				$filteredAccuracy = round($accuracy, 3);
			}
			elseif ($accuracy > $maximumAccuracy)
			{
				#Not necessarily marked as invalid, rather capped
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Out of range, capped';
				$valid = true;
				$filteredAccuracy = $maximumAccuracy;
			}
			else#if ($accuracy < 0)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Invalid';
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'filter' => $filteredAccuracy
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
			$filteredAltitude = null;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$altitude = doubleval($altitude);
				
			#Checking range
			if ($altitude >= $minimumAltitude and $altitude <= $maximumAltitude)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
				$filteredAltitude = round($altitude, 3);
			}
			elseif ($altitude > $maximumAltitude)
			{
				#Not necessarily marked as invalid, rather capped
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Out of range, capped';
				$valid = true;
				$filteredAltitude = $maximumAltitude;
			}
			else#if ($altitude < $minimumAltitude)
			{
				#Not necessarily marked as invalid, rather capped
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Out of range, capped';
				$valid = true;
				$filteredAltitude = $minimumAltitude;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'filter' => $filteredAltitude
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
			#Filtered result
			$filteredAccuracy = null;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$accuracy = doubleval($accuracy);
			
			#Checking range
			if ($accuracy >= $minimumAccuracy and $accuracy <= $maximumAccuracy)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
				$filteredAccuracy = round($accuracy, 3);
			}
			elseif ($accuracy > $maximumAccuracy)
			{
				#Not necessarily marked as invalid, rather capped
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Out of range, capped';
				$valid = true;
				$filteredAccuracy = $maximumAccuracy;
			}
			else#if ($accuracy < $minimumAccuracy)
			{
				#Not necessarily marked as invalid, rather capped
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Out of range, capped';
				$valid = true;
				$filteredAccuracy = $minimumAccuracy;
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'filter' => $filteredAccuracy
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
			#Filtered result
			$filteredTime = null;
			#ReasonID
			$reasonID = null;
			$reason = null;

			$time = intval($time);
			
			#Checking range
			#Maximum time: Time right now
			if ($time >= $minimumTime and $time <= time() * 1000)
			{
				#Valid
				$reasonID = $reasonIDs->Accepted;
				$valid = true;
				$filteredTime = $time;
			}
			else#if ($time < $minimumTime or $time > time() * 1000)
			{
				#Invalid
				$reasonID = $reasonIDs->OutOfRange;
				$reason = 'Invalid';
			}
			#Return result
			return [
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'filter' => $filteredTime
			];
		}
		#Validates track point
		private function validatePoint($point)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#Filtered result
			$filteredResult = [];
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
				$altitude = GeneralFunctions::getValue($point, 'altitude');
				$altitudeAccuracy = GeneralFunctions::getValue($point, 'altitudeAccuracy');
				$timestamp = GeneralFunctions::getValue($point, 'timestamp');

				#Validating attributes
				$latitudeValidation = $this->validateLatitude($latitude);
				$longitudeValidation = $this->validateLongitude($longitude);
				$accuracyValidation = $this->validateAccuracy($accuracy);
				$altitudeValidation = $this->validateAltitude($altitude);
				$altitudeAccuracyValidation = $this->validateAltitudeAccuracy($altitudeAccuracy);
				$timestampValidation = $this->validateTime($timestamp);

				#Adding attribute reasons
				$attributeReasons = [
					'latitude' => $latitudeValidation,
					'longitude' => $longitudeValidation,
					'accuracy' => $accuracyValidation,
					'altitude' => $altitudeValidation,
					'altitudeAccuracy' => $altitudeAccuracyValidation,
					'timestamp' => $timestampValidation
				];
				#Whether valid
				$valid = (
					$latitudeValidation['valid'] and
					$longitudeValidation['valid'] and
					$accuracyValidation['valid'] and
					$altitudeValidation['valid'] and
					$altitudeAccuracyValidation['valid'] and
					$timestampValidation['valid']
				);
				#Checking if valid
				if ($valid)
				{
					#Creating point
					$filteredResult = new TrackPoint(
						$latitudeValidation['filter'],
						$longitudeValidation['filter'],
						$accuracyValidation['filter'],
						$timestampValidation['filter'],
						$altitudeValidation['filter'],
						$altitudeAccuracyValidation['filter']
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
				'valid' => $valid,
				'reasonID' => $reasonID,
				'reason' => $reason,
				'attributeReasons' => $attributeReasons,
				'filter' => $filteredResult
			];
		}
		#Validates segment
		private function validateSegment($segment)
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
			#Point reasons
			$pointReasons = [];
			#Resulting segments
			$filteredSegments = [];

			#Checking type
			if (gettype($segment) === 'array')
			{
				#Getting points
				$pointsInSegment = GeneralFunctions::getValue($segment, 'points', []);
				#Checking if there are any points
				if (!empty($pointsInSegment))
				{
					#Whether points are valid (default true)
					$pointsValid = true;
					#Filtered points
					$filteredPoints = [];
					#Validating points
					foreach ($pointsInSegment as $key => $point)
					{
						#Validating point
						$pointValidation = $this->validatePoint($point);
						#Updating status
						$pointsValid = ($pointsValid and $pointValidation['valid']);
						#Adding point to the list
						array_push($filteredPoints, $pointValidation['filter']);
						#Removing filter from list
						$pointValidation['filter'] = null;
						#Adding point reason
						array_push($pointReasons, $pointValidation);
					}
					#Checking if points are valid
					if ($pointsValid)
					{
						#Points separated into smaller segments on invalid speed
						$segmentedPoints = [];
						#Current segment
						$currentSegment = [
							$filteredPoints[0]
						];
						#Amount of filtered points
						$filteredPointsCount = count($filteredPoints);
						#Grouping points by valid speed
						for ($pointNum = 1; $pointNum < $filteredPointsCount; $pointNum++)
						{
							#Getting points
							$previousPoint = $filteredPoints[$pointNum - 1];
							$thisPoint = $filteredPoints[$pointNum];
							
							#Getting distance
							$distance = $previousPoint->getDistanceTo($thisPoint);
							#Getting time difference
							$timeDifference = $thisPoint->milisecondTime - $previousPoint->milisecondTime;
							#Checking time difference
							if ($timeDifference < 0)
							{
								#Invalid time difference
								$pointsValid = false;
								break;
							}
							#Taking into account accuracy difference (worst case scenario)
							$distance -= $previousPoint->accuracy + $thisPoint->accuracy;
							#Checking if distance is still positive
							if ($distance > 0)
							{
								#Getting speed m/s
								$speed = $distance / $timeDifference;
								#Convert speed to km/h
								$speed /= 3.6;
								#Checking speed
								if ($speed > $maximumSpeed)
								{
									#Create new segment
									array_push($segmentedPoints, $currentSegment);
									$currentSegment = [];
								}
							}
							#Adding this point to the current segment
							array_push($currentSegment, $thisPoint);
							#Checking if this is the last point in segment
							if ($pointNum == $filteredPointsCount - 1)
							{
								#Add last segment
								array_push($segmentedPoints, $currentSegment);
								$currentSegment = [];
							}
						}
						#Checking if points are valid
						if ($pointsValid)
						{
							#Adding filtered segments
							foreach ($segmentedPoints as $key => $points)
							{
								#Creating segment
								$filteredSegment = new Segment($points);
								#Checking length
								if ($filteredSegment->length > 0)
								{
									#Add segment
									array_push($filteredSegments, $filteredSegment);
								}
							}
							$valid = true;
							$reasonID = $reasonIDs->Accepted;
						}
						else#if (!$pointsValid)
						{
							#Not valid time
							$reasonID = $reasonIDs->TimeTravel;
							$reason = 'Time travel isn\'t possible yet';
						}
					}
					else#if (!$pointsValid)
					{
						#Not valid
						$reasonID = $reasonIDs->InvalidInputs;
						$reason = 'One or more points is invalid';
					} 
				}
				else#if (empty($pointsInSegment))
				{
					#No points in segment
					$reasonID = $reasonIDs->Empty;
					$reason = 'Empty';
					$valid = true;
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
				'pointReasons' => $pointReasons,
				'filter' => $filteredSegments
			];
		}
		#Validates track
		public function validateTrack($track)
		{
			#Getting reasonIDs
			$reasonIDs = $this->reasonIDs;
			#Whether valid
			$valid = false;
			#Filtered result
			$filteredTrack = null;
			#ReasonID
			$reasonID = null;
			$reason = null;
			#Segment reasons
			$segmentReasons = [];

			#Checking if succeeded to get reasonIDs and settings
			if ($reasonIDs->success and $this->settingsDb->success)
			{
				#Checking type
				if (gettype($track) === 'array')
				{
					#Getting segments
					$segments = GeneralFunctions::getvalue($track, 'segments', []);
					#Whether segments are valid (default true)
					$segmentsValid = true;
					#Filtered segments
					$filteredSegments = [];
					#Check individual segments
					foreach ($segments as $key => $segment)
					{
						#Validating segment
						$segmentValidation = $this->validateSegment($segment);
						#Whether the segment is valid
						$segmentValid = $segmentValidation['valid'];
						$segmentsValid = ($segmentsValid and $segmentValid);
						
						#Segment validation can generate mode than one segment in case of invalid speed for example
						#Getting filtered segments
						$filteredSegments = $segmentValidation['filter'];
						#Removing filtered segments from segment reasons
						$segmentValidation['filter'] = null;
						#Add segment reason
						array_push($segmentReasons, $segmentValidation);

						#Checking if was this segment valid
						if ($segmentValid)
						{
							#Adding filtered segments to the result
							foreach ($filteredSegments as $key => $filteredSegment)
							{
								#Adding filtered segment to the result
								array_push($filteredSegments, $filteredSegment);
							}
						}
					}
					#Checking if segments are valid
					if ($segmentsValid)
					{
						#Creating track
						$filteredTrack = new Track($filteredSegments);
						#Checking if any segments made it to the track
						if ($filteredTrack->length > 0)
						{
							#Valid track
							$valid = true;
							$reasonID = $reasonIDs->Accepted;
						}
						else#if ($filteredTrack->length <= 0)
						{
							#Does not have segments
							$reasonID = $reasonIDs->Empty;
							$reason = 'Does not have any distance';
						}
					}
					else#if (!$segmentsValid)
					{
						$reasonID = $reasonIDs->InvalidInputs;
						$reason = 'One or more segments is invalid';
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
				'segmentReasons' => $segmentReasons,
				'filter' => $filteredTrack
			];
		}
	}
?>
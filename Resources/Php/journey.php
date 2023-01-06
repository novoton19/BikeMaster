<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 10:45pm
		Version: 0.0.4
	Updated on
		Version: 0.0.4.1

	Description:
		Contains track, segment and trackPoint classes

	Changes:
		Version 0.0.4.1 - Add startTime and endTime, speed
	*/
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}
	#Track point
	class TrackPoint
	{
		#Attributes
		public $latitude;
		public $longitude;
		public $accuracy;
		public $altitude;
		public $altitudeAccuracy;
		public $timestamp;
		#Constructor
		public function __construct(
			$latitude,
			$longitude,
			$accuracy,
			$timestamp,
			$altitude = null,
			$altitudeAccuracy = null,
		)
		{
			$this->latitude = $latitude;
			$this->longitude = $longitude;
			$this->accuracy = $accuracy;
			$this->altitude = $altitude;
			$this->altitudeAccuracy = $altitudeAccuracy;
			$this->timestamp = $timestamp;
		}
		#Returns distance to point in meters
		#https://www.geeksforgeeks.org/program-distance-two-points-earth/
		public function getDistanceTo($point)
		{
			#Radius of the Earth
			$radius = 6378.8;

			#Getting coordinates
			$latA = deg2rad($this->latitude);
			$lonA = deg2rad($this->longitude);
			$latB = deg2rad($point->latitude);
			$lonB = deg2rad($point->longitude);
				
			#Haversine Formula
			$lonDist = $lonB - $lonA;
			$latDist = $latB - $latA;
			
			$val = pow(sin($latDist / 2), 2) + cos($latA) * cos($latB) * pow(sin($lonDist / 2), 2);
			$res = 2 * asin(sqrt($val));
			#Resulting distance (meters)
			return ($res*$radius) * 1000;
		}
	}
	#Segment
	class Segment
	{
		#Attributes
		public $points;
		public $length;
		public $startTime;
		public $endTime;
		public $time;
		public $speed;
		#Constructor
		public function __construct($points)
		{
			#Segment start and end time
			$startTime = null;
			$endTime = null;
			$time = null;
			#Getting amount of points
			$pointsCount = count($points);
			#Checking if has any points
			if ($pointsCount > 0)
			{
				$startTime = $points[0]->timestamp;
				$endTime = end($points)->timestamp;
				$time = $endTime - $startTime;
			}
			
			#Length of the segment
			$length = 0;
			#Calculating length of segment
			for ($pointNum = 1; $pointNum < $pointsCount; $pointNum++)
			{
				#Getting points
				$previousPoint = $points[$pointNum - 1];
				$thisPoint = $points[$pointNum];
				#Adding distance
				$length += $previousPoint->getDistanceTo($thisPoint);
			}

			#Adding values
			$this->points = $points;
			$this->length = $length;
			$this->startTime = $startTime;
			$this->endTime = $endTime;
			$this->time = $time;
		}
	}
	#Track
	class Track
	{
		#Attributes
		public $segments;
		public $length;
		public $startTime;
		public $endTime;
		public $time;
		public $speed;
		#Constructor
		public function __construct($segments = [])
		{
			#Track start and end time
			$startTime = null;
			$endTime = null;
			#Getting amount of segments
			$segmentsCount = count($segments);
			#Checking if has any points
			if ($segmentsCount > 0)
			{
				$startTime = $segments[0]->startTime;
				$endTime = end($segments)->endTime;
				$time = $endTime - $startTime;
			}
			
			#Length of the track
			$length = 0;
			#Total time on track
			$time = 0;
			#Calculating length of track
			foreach ($segments as $key => $segment)
			{
				$length += $segment->length;
				$time += $segment->time;
			}

			#Adding values
			$this->segments = $segments;
			$this->length = $length;
			$this->startTime = $startTime;
			$this->endTime = $endTime;
			$this->time = $time;
			$this->speed = ($time == 0) ? -1 : ($length / $time);
		}
	}
?>
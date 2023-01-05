<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 10:45pm
		Version: 0.0.4
	Updated on
		Version: 0.0.4

	Description:
		Contains track, segment and trackPoint classes

	Changes:

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
		public $milisecondTime;
		#Constructor
		public function __construct(
			$latitude,
			$longitude,
			$accuracy,
			$milisecondTime,
			$altitude = null,
			$altitudeAccuracy = null,
		)
		{
			$this->latitude = $latitude;
			$this->longitude = $longitude;
			$this->accuracy = $accuracy;
			$this->altitude = $altitude;
			$this->altitudeAccuracy = $altitudeAccuracy;
			$this->milisecondTime = $milisecondTime;
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
		#Constructor
		public function __construct($points)
		{
			$this->points = $points;
			#Length of the segment
			$length = 0;
			#Calculating length of segment
			for ($pointNum = 1; $pointNum < count($points); $pointNum++)
			{
				#Getting points
				$previousPoint = $points[$pointNum - 1];
				$thisPoint = $points[$pointNum];
				#Adding distance
				$length += $previousPoint->getDistanceTo($thisPoint);
			}
			#Set length
			$this->length = $length;
		}
	}
	#Track
	class Track
	{
		#Attributes
		public $segments;
		public $length;
		#Constructor
		public function __construct($segments = [])
		{
			$this->segments = $segments;
			#Length of the track
			$length = 0;
			#Calculating length of track
			foreach ($segments as $key => $segment)
			{
				$length += $segment->length;
			}
			#Add length
			$this->length = $length;
		}
	}
?>
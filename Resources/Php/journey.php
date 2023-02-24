<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 01/05/23 10:45pm
		Version: 0.0.4
	Updated on
		Version: 0.4.3

	Description:
		Contains track, segment and trackPoint classes

	Changes:
		Version 0.0.4.1 - Add startTime and endTime, speed
		Version 0.4.3 - Load journey from array, remove altitude
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
		public $timestamp;
		#Constructor
		public function __construct(
			$latitude,
			$longitude,
			$accuracy,
			$timestamp
		)
		{
			$this->latitude = $latitude;
			$this->longitude = $longitude;
			$this->accuracy = $accuracy;
			$this->timestamp = $timestamp;
		}
		#Returns distance to point in meters
		#https:#www.geeksforgeeks.org/program-distance-two-points-earth/
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
			#Resulting distance (kilometers)
			return ($res*$radius);
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
			$this->speed = ($time == 0) ? -1 : ($length / ($time / 1000 / 60 / 60));
		}
		public static function fromArray($array)
		{
			#Getting segments
			$plainSegments = $array['segments'];
			#Resulting segments
			$segments = [];
			#Converting segments
			foreach ($plainSegments as $segmentNum => $plainSegment)
			{
				#Getting points
				$plainPoints = $plainSegment['points'];
				#Resulting points
				$points = [];
				#Converting points
				foreach ($plainPoints as $pointNum => $plainPoint)
				{
					#Creating point
					$point = new TrackPoint(
						round(doubleval($plainPoint['latitude']), 7),
						round(doubleval($plainPoint['longitude']), 7),
						round(doubleval($plainPoint['accuracy']), 3),
						intval($plainPoint['timestamp'])
					);
					#Whether identical
					$identical = false;
					#Checking if there are any points already
					if (count($points) > 0)
					{
						#Getting last point
						$lastPoint = end($points);
						#Getting distance
						$identical = ($point->latitude === $lastPoint->latitude and $point->longitude === $lastPoint->longitude);
					}
					if (!$identical)
					{
						#Adding point
						array_push(
							$points, $point
						);
					}
				}
				#Creating segment
				$segment = new Segment($points);
				#Checking distance
				if ($segment->length > 0)
				{
					#Add to segments
					array_push($segments, $segment);
				}
			}
			#Creating journey
			$track = new Track($segments);
			return $track;
		}
	}
?>
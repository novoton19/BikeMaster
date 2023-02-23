/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/22/23 09:33am
	Version: 0.4
Updated on
	Version: 0.4

Description:
	Javascript for journey page

Changes:

*/
//Waiting for document to load
$(document).ready(() =>
{
	//Creating map manager
	var mapManager = new MapManager('map');
	var map = mapManager.map;
	//Creating journey manager
	var journeyManager = new JourneyManager();
	//Creating position watcher
	var positionWatcher = new PositionWatcher(true);
	
	//Getting map element
	var mapElement = $('#map')[0];
	var recenterButton = $('.recenterButton');
	//Getting statistics
	var statistics = $('#statisticsView');
	var timeElem = statistics.find('.time');
	var distanceElem = statistics.find('.distance');
	var avgSpeedElem = statistics.find('.averageSpeed');
	var crtSpeedElem = statistics.find('.currentSpeed');
	//Getting buttons
	var controlButtons = $('.journeyControls .button');
	var startButton = controlButtons.filter('.startButton');
	var pauseButton = controlButtons.filter('.pauseButton');
	var resumeButton = controlButtons.filter('.resumeButton');
	var endButton = controlButtons.filter('.endButton');
	//Hide unncessary buttons
	recenterButton.hide();
	controlButtons.hide();
	//Adding default controls to the map
	map.addDefaultControls();
	//Changing zoom to something normal
	map.setZoom(16);

	//Creating new geometry layer
	var geometry = new SMap.Layer.Geometry();
	map.addLayer(geometry);
	geometry.enable();
	//Creating new markers layer
	var markers = new SMap.Layer.Marker();
	map.addLayer(markers);
	markers.enable();

	//Creating accuracy circle
	var accuracyCircle = new SMap.Geometry(
		SMap.GEOMETRY_CIRCLE,
		null,
		[
			map.getCenter(),
			100
		],
		{
			color: "#5af",
			opacity: 0.1,
			outlineColor: "#5af",
			outlineOpacity: 0.2,
			outlineWidth: 3
		}
	);
	geometry.addGeometry(accuracyCircle);
	//Creating current position marker
	var marker = new SMap.Marker(map.getCenter(), 'positionMarker');
	markers.addMarker(marker);
	

	//Called on journey status change
	function onJourneyStatusChanged()
	{
		//Hide all buttons by default
		controlButtons.hide();
		//Getting current status
		let status = journeyManager.journey.status;
		if (status === journeyModeStatuses.Idle)
		{
			//Show start button
			startButton.show();
		}
		else if (status === journeyModeStatuses.Running)
		{
			//Show pause button
			pauseButton.show();
		}
		else if (status === journeyModeStatuses.Paused)
		{
			//Show resume and end buttons
			resumeButton.show();
			endButton.show();
		}
		else if (status === journeyModeStatuses.Finished)
		{
			//Do not show anything
		}
	}
	//On journey finished
	function onJourneyFinished()
	{
		//Getting registration
		navigator.serviceWorker.ready.then((registration) => {
			//Sending message to save journey
			registration.active.postMessage({
				type: 'SAVE_JOURNEY',
				journey : Object.fromEntries(Object.entries(journeyManager.journey))
			});
		});
	}
	//Called when current journey changes
	function onJourneyChanged()
	{
		journeyManager.journey.addEventListener('onTrackChanged', onTrackChanged);
		journeyManager.journey.addEventListener('onStatusChanged', onJourneyStatusChanged);
		journeyManager.journey.addEventListener('onFinish', onJourneyFinished);
		onJourneyStatusChanged();
	}
	//Called when the current journey track changes
	function onTrackChanged()
	{
		mapManager.loadGpx(GpxConverter.toGpx(journeyManager.journey));
	}
	//Updates statistics
	function updateStatistics()
	{
		//Getting journey
		let journey = journeyManager.journey;
		//Getting segments
		let segments = journey.segments;
		let segmentsCount = segments.length;

		//Elapsed time and distance
		let time = 0;
		let distance = 0;
		let distanceLastTenSeconds = 0;
		//Calculating values
		segments.forEach((segment) =>
		{
			//Getting points
			let points = segment.points;
			let pointsCount = points.length;
			//Getting current time
			let crtTime = new Date().getTime();
			//Adding time
			time += (segment.endTime || crtTime) - segment.startTime;
			
			//Checking if there are any points
			if (pointsCount)
			{
				//Skip first point to work in point pairs
				for (let pointNum = 1; pointNum < pointsCount; pointNum++)
				{
					let pointA = points[pointNum - 1];
					let pointB = points[pointNum];
					//https://stackoverflow.com/questions/18883601/function-to-calculate-distance-between-two-coordinates
					//Earth's radius in km
					let radius = 6371;
					let latA = pointA.latitude;
					let latB = pointB.latitude;
					let lonA = pointA.longitude;
					let lonB = pointB.longitude;
					//Calculating differences
					let diffLat = (latB - latA) * Math.PI / 180;
					let diffLon = (lonB - lonA) * Math.PI / 180;
					let latARad = latA * Math.PI / 180;
					let latBRad = latB * Math.PI / 180;
					//Calculating angle
					let angle = Math.pow(Math.sin(diffLat / 2), 2) + Math.pow(Math.sin(diffLon / 2), 2) * Math.cos(latARad) * Math.cos(latBRad);
					let c = 2 * Math.atan2(Math.sqrt(angle), Math.sqrt(1 - angle));
					//Adding distance
					distance += radius * c;
					//Checking if points were created in last 10 seconds
					if (crtTime - pointA.timestamp < 10000)
					{
						//Adding distance
						distanceLastTenSeconds += radius * c;
					}
				}
			}
		});
		//Calculating average speed
		let avgSpeed = time > 0 ? distance / (time / 1000 / 60 / 60) : 0;
		let crtSpeed = distanceLastTenSeconds / (10 / 1000 / 60 / 60);
		
		//Getting seconds
		let seconds = Math.floor(time / 1000);
		//Getting minutes
		let minutes = Math.floor(seconds / 60);
		//Getting hours
		let hours = Math.floor(minutes / 60);
		//Updating values
		seconds -= minutes * 60 + hours * 60 * 60;
		minutes -= hours * 60;
		//Formatting values
		seconds = seconds >= 10 ? seconds : '0' + seconds;
		minutes = minutes >= 10 ? minutes : '0' + minutes;
		hours = hours >= 10 ? hours : '0' + hours;
		
		//Rounding values
		distance = Math.round(distance * 100) / 100;
		avgSpeed = Math.round(avgSpeed * 100) / 100;
		crtSpeed = Math.round(crtSpeed * 100) / 100;
		
		//Adding texts
		timeElem.text(`${hours}:${minutes}:${seconds}`);
		distanceElem.text(`${distance} km`);
		avgSpeedElem.text(`${avgSpeed} km/h`);
		crtSpeedElem.text(`${crtSpeed} km/h`);
	}

	//Detecting map location change
	mapManager.positionManager.addEventListener('onPositionUpdated', () =>
	{
		let coords = mapManager.positionManager.mostRecentCoordinates;
		let x = coords.longitude
		let y = coords.latitude;
		//https://en.wikipedia.org/wiki/Latitude
		let angle = coords.accuracy / 110574;
	
		//Creating coords objects
		let markerCoords = new SMap.Coords(x, y);
		let circleEdge = SMap.Coords.fromWGS84(x, y + angle);
		
		//Setting coordinates		
		accuracyCircle.setCoords([markerCoords, circleEdge]);
		marker.setCoords(markerCoords);
	});
	//On touch started
	mapElement.ontouchstart = () =>
	{
		//Disable position watcher so the map doesn't interfere with current action
		mapManager.positionWatcher.update(false);
		recenterButton.show();
	};
	//On mouse down
	mapElement.onmousedown = () =>
	{
		//Disable position watcher so the map doesn't interfere with current action
		mapManager.positionWatcher.update(false);
		recenterButton.show();
	};
	//On mouse wheel
	mapElement.onwheel = () =>
	{
		//Disable position watcher so the map doesn't interfere with current action
		mapManager.positionWatcher.update(false);
		recenterButton.show();
	};
	//On recenter button clicked
	recenterButton.click(() =>
	{
		//Getting current position
		let coords = mapManager.positionManager.mostRecentCoordinates;
		//Recentering the map
		map.setCenter(new SMap.Coords(coords.longitude, coords.latitude));
		//Enabling position watcher
		mapManager.positionWatcher.update(true);
		recenterButton.hide();
	});

	//Adding journey change event listener
	journeyManager.addEventListener('onJourneyChanged', onJourneyChanged);
	onJourneyChanged();
	//Adding functionality to control buttons
	//Starts journey
	startButton.click(() => journeyManager.journey.start());
	//Pauses journey
	pauseButton.click(() => journeyManager.journey.pause());
	//Resumes journey
	resumeButton.click(() => journeyManager.journey.resume());
	//Ends journey
	endButton.click(() => journeyManager.journey.end());
	//Adding interval to update statistics
	setInterval(() =>
	{
		updateStatistics();
	}, 1000);
	updateStatistics();
});
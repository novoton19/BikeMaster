/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22 04:26pm
	Version: 0.0.2
Updated on
	Version: 0.0.2.3

Description:
	Tracks user location during 'Journey Mode'

Changes:
	Version 0.0.2.1 - Ask for location access
	Version 0.0.2.2 - Pause/Resume Journey
	Version 0.0.2.3 - Load map and show current location
*/
$(document).ready(function()
{
	//Location of the user (Prague default)
	let currentLocation = {
		latitude : 50.0755,
		longitude : 14.4378
	};
	//Creating map
	let map = new SMap(
		JAK.gel('Map'),
		undefined,
		14
	);
	//Adding default layer
	map.addDefaultLayer(SMap.DEF_BASE).enable();


	//Getting mentions
	const mention = $('#Mentions');
	const geolocationServiceMention = $(mention).find('#GeolocationServiceMention');
	const locationAccessMention = $(mention).find('#LocationAccessMention');
	//Getting button to grant location access
	const promptLocationAccessButton = $(locationAccessMention).find('#PromptLocationAccessButton');
	//Getting buttons
	const buttons = $('#JourneyModeButtons');
	const startButton = $(buttons).find('#Start');
	const pauseButton = $(buttons).find('#Pause');
	const resumeButton = $(buttons).find('#Resume');
	const endButton = $(buttons).find('#End');
	//Element containing locations
	const locationsElem = $('#Locations');
	
	
	//Removing class from mention and hiding it
	mention.removeClass('Invisible');
	mention.hide();
	//Removing class from buttons and hiding them
	buttons.removeClass('Invisible');
	startButton.hide();
	pauseButton.hide();
	resumeButton.hide();
	endButton.hide();

	
	//Whether this browser supports geolocation
	let supportsGeolocation = 'geolocation' in navigator;
	//Whether location can be obtained
	let canGetLocation = false;
	//Whether location can be requests
	let canRequestLocation = false;

	//Current list of locations
	let locations = [];
	//Whether journey mode activated
	let journeyModeActive = false;
	//Whether journey mode paused
	let journeyModePaused = false;
	//Current function that handles location capturing
	let captureIntervalFunction = undefined;
	

	//Map functions
	function RefreshLocation()
	{
		let center = SMap.Coords.fromWGS84(
			currentLocation.longitude,
			currentLocation.latitude
		);
		map.setCenter(center);
	}
	

	//Permission functions
	//Refreshes the permission state
	function RefreshPermissions(callback = undefined)
	{
		//Cannot get location by default
		canGetLocation = false;
		canRequestLocation = false;
		//Disable start button by default
		startButton.hide();
		pauseButton.hide();
		resumeButton.hide();
		mention.hide();
		geolocationServiceMention.hide();
		locationAccessMention.hide();
		promptLocationAccessButton.hide();

		//Checking if geolocation is supported here
		if (!supportsGeolocation)
		{
			//Geolocation not available
			mention.show();
			geolocationServiceMention.show();
			//Checking if callback exists
			if (callback)
			{
				callback();
			}
			return;
		}
		//Getting permission state
		navigator.permissions.query(
			{
				name : 'geolocation'
			},
		).then(function(result)
		{
			//Getting state
			let state = result.state;
			//Checking state
			if (state !== 'granted')
			{
				//Show mention
				mention.show();
				locationAccessMention.show();
				//Checking if can be prompted
				if (state === 'prompt')
				{
					canRequestLocation = true;
					promptLocationAccessButton.show();
				}
			}
			else
			{
				//Permission granted
				//Everything's fine
				canGetLocation = true;
				//Checking if not in journey mode
				if (!journeyModeActive)
				{
					//Showing button again
					startButton.show();
				}
				else if (journeyModePaused)
				{
					resumeButton.show();
				}
				else if (!journeyModePaused)
				{
					pauseButton.show();
				}
			}
			//Checking if callback exists
			if (callback)
			{
				callback();
			}
		});
	}
	//Adding event on prompt location access button pressed
	promptLocationAccessButton.click(function()
	{
		//Checking if can prompt
		if (!canRequestLocation)
		{
			return;
		}
		//Prompting
		TryGetLocation(undefined, true);
	});
	
	
	//Location functions
	//On location successfully obtained
	function OnLocationObtained(location, callback = undefined)
	{
		//Updating current location
		currentLocation = location.coords;
		RefreshLocation();
		//Checking if in journey mode and not paused
		if (journeyModeActive && !journeyModePaused)
		{
			//Adding location
			locations.push(location);
			//Adding location to the end of the list
			locationsElem.append('timestamp: ' + location.timestamp + ', latitude: ' + location.coords.latitude + ', longitude: ' + location.coords.longitude + ', accuracy: ' + location.coords.accuracy + '<br>');
		}
		//Checking if callback exists
		if (callback)
		{
			callback();
		}
	}
	//On location not obtained successfuly
	function OnLocationNotObtained(error, callback = undefined)
	{
		//Checking if obtain function exists
		/*if (captureIntervalFunction !== undefined)
		{
			//Stopping location capturing
			clearInterval(captureIntervalFunction);
			captureIntervalFunction = undefined;
			journeyModeActive = false;
			journeyModePaused = false;
		
			OnJourneyFinished();
			startButton.show();
			pauseButton.hide();
			resumeButton.hide();
			endButton.hide();
		}*/
		//Checking if callback exists
		if (callback)
		{
			callback();
		}
	}
	//Attempts to get location
	function TryGetLocation(callback = undefined, ignorePrompt = false)
	{
		//Checking if can get location
		if (canGetLocation || (ignorePrompt && canRequestLocation))
		{
			//Getting location
			navigator.geolocation.getCurrentPosition(
				(location) => OnLocationObtained(location, callback),
				(error) => OnLocationNotObtained(error, callback),
				{
					enableHighAccuracy : true,
					maximumAge : 0,
					timeout : 5000
				}
			);
		}
	}


	//Journey functions
	//On start button pressed
	$(startButton).click(function()
	{
		//Checking if can get location
		if (!canGetLocation)
		{
			return;
		}
		//Checking if journey mode active
		if (journeyModeActive)
		{
			//Already running
			return;
		}
		//Starting journey mode
		locations = [];
		locationsElem.text('');

		journeyModeActive = true;
		journeyModePaused = false;

		TryGetLocation();
		//captureIntervalFunction = setInterval(TryGetLocation, 5000);
		startButton.hide();
		pauseButton.show();
		resumeButton.hide();
		endButton.show();
	});
	//On pause button pressed
	$(pauseButton).click(function()
	{
		//Checking if in journey mode
		if (!journeyModeActive)
		{
			return;
		}
		//Checking if not paused
		if (journeyModePaused)
		{
			return;
		}
		//Getting last location
		TryGetLocation(function()
		{
			//Pausing journey mode
			journeyModePaused = true;
		});
		pauseButton.hide();
		resumeButton.show();
	});
	//On resume button pressed
	$(resumeButton).click(function()
	{
		//Checking if in journey mode
		if (!journeyModeActive)
		{
			return;
		}
		//Checking if paused
		if (!journeyModePaused)
		{
			return;
		}
		//Pausing journey mode
		journeyModePaused = false;
		//Getting last location
		TryGetLocation();
		resumeButton.hide();
		pauseButton.show();
	});
	//On end button pressed
	$(endButton).click(function()
	{
		//Checking if journey mode active
		if (!journeyModeActive)
		{
			return;
		}
		//Checking if function exists
		/*if (captureIntervalFunction !== undefined)
		{
			//Stopping location capturing
			clearInterval(captureIntervalFunction);
			captureIntervalFunction = undefined;
		}*/
		//Getting last position
		TryGetLocation(function()
		{
			//Stopping journey mode
			journeyModeActive = false;
			journeyModePaused = false;
			//On journey finished
			OnJourneyFinished();
		});
		startButton.show();
		pauseButton.hide();
		resumeButton.hide();
		endButton.hide();
	});
	//On journey finished
	function OnJourneyFinished()
	{
		//Checking if ended
		if (journeyModeActive)
		{
			return;
		}
	}


	//Loading permissions
	RefreshPermissions(function()
	{
		//Checking if geolocation supported
		if (supportsGeolocation)
		{
			//Listening for permission changes
			navigator.permissions.query(
				{
					name : 'geolocation'
				}
			).then(function(result)
			{
				result.onchange = () => RefreshPermissions();
			});
			//Refreshing center of the map
			RefreshLocation();
			//Trying to get location now
			TryGetLocation();
			//Creating capture function
			captureIntervalFunction = setInterval(TryGetLocation, 5000);

		}
	});
});
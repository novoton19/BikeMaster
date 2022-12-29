/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22 04:26pm
	Version: 0.0.2
Updated on
	Version: 0.0.2

Description:
	Tracks user location during 'Journey Mode'

Changes:
	Version 0.0.2.1 - Ask for location access
*/
$(document).ready(function()
{
	//Getting mentions
	const mention = $('#Mentions');
	const geolocationServiceMention = $(mention).find('#GeolocationServiceMention');
	const locationAccessMention = $(mention).find('#LocationAccessMention');
	//Getting button to grant location access
	const promptLocationAccessButton = $(locationAccessMention).find('#PromptLocationAccessButton');
	//Getting buttons
	const buttons = $('#JourneyModeButtons');
	const startButton = $(buttons).find('#Start');
	const endButton = $(buttons).find('#End');
	//Element containing locations
	const locationsElem = $('#Locations');
	
	
	//Removing class from mention and hiding it
	mention.removeClass('Invisible');
	mention.hide();
	//Removing class from buttons and hiding them
	buttons.removeClass('Invisible');
	startButton.hide();
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
	

	//Permission functions
	//Refreshes the permission state
	function RefreshPermissions(callback = undefined)
	{
		//Cannot get location by default
		canGetLocation = false;
		canRequestLocation = false;
		//Disable start button by default
		startButton.hide();
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
		}
	});
	
	
	//Journey functions
	//On location successfully obtained
	function OnLocationObtained(location, callback = undefined)
	{
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
		if (captureIntervalFunction !== undefined)
		{
			//Stopping location capturing
			clearInterval(captureIntervalFunction);
			captureIntervalFunction = undefined;
			journeyModeActive = false;
			journeyModePaused = false;
		
			OnJourneyFinished();
			endButton.hide();
			startButton.show();
		}
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
		captureIntervalFunction = setInterval(TryGetLocation, 5000);
		startButton.hide();
		endButton.show();
	});
	//On pause button pressed
	$(endButton).click(function()
	{
		//Checking if journey mode active
		if (!journeyModeActive)
		{
			return;
		}
		//Checking if function exists
		if (captureIntervalFunction !== undefined)
		{
			//Stopping location capturing
			clearInterval(captureIntervalFunction);
			captureIntervalFunction = undefined;
		}
		//Getting last position
		TryGetLocation(function()
		{
			//Stopping journey mode
			journeyModeActive = false;
			journeyModePaused = false;
			//On journey finished
			OnJourneyFinished();
		});
		endButton.hide();
		startButton.show();
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
});
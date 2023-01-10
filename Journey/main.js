/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22 04:26pm
	Version: 0.0.2
Updated on
	Version: 0.0.4.2

Description:
	Tracks user location during 'Journey Mode'

Changes:
	Version 0.0.2.1 - Ask for location access
	Version 0.0.2.2 - Pause/Resume Journey
	Version 0.0.2.3 - Load map and show current location
	Version 0.0.2.3.3 - Add promises to functions, location watching optimization
	Version 0.0.2.3.4 - Bug fixes, commenting
	Version 0.0.2.3.5 - Functionality of this file moved to 'locationManager.js' and 'journeyModeManager.js'
	//Version 0.0.2.4 - Track location in the background - turned out to be impossible
	Version 0.0.2.5 - Save and Delete buttons, Support OOP Journey Mode
	Version 0.0.2.6 - Support OOP positionManager
	Version 0.0.2.7 - Added MapManager support
	Version 0.0.4 - Send api request to save journey
	Version 0.0.4.2 - Display track on the map
	Version 0.0.5.4.1 - Unload gpx on track delete
*/
//Api url
const journeyApiUrl = '../Api/Journey/save.php';
//Waiting for document to load
$(document).ready(function()
{
	//Creating new PositionManager
	const positionManager = new PositionManager();
	//Creating new MapManager
	const mapManager = new MapManager('Map');
	//Creating new JourneyModeManager
	const journeyModeManager = new JourneyModeManager();

	//Getting mentions
	const mention = $('#Mentions');
	const geolocationServiceMention = $(mention).find('#GeolocationServiceMention');
	const locationAccessMention = $(mention).find('#LocationAccessMention');
	//Getting button which is used to request location access
	const promptLocationAccessButton = $(locationAccessMention).find('#PromptLocationAccessButton');
	//Getting Journey Mode controls
	const buttons = $('#JourneyModeButtons');
	const startButton = $(buttons).find('#Start');
	const pauseButton = $(buttons).find('#Pause');
	const resumeButton = $(buttons).find('#Resume');
	const endButton = $(buttons).find('#End');
	const saveButton = $(buttons).find('#Save');
	const deleteButton = $(buttons).find('#Delete');

	
	//Removing class from mention and hiding it by default
	mention.removeClass('Invisible');
	mention.hide();
	//Removing class from buttons and hiding them by default
	buttons.removeClass('Invisible');
	startButton.hide();
	pauseButton.hide();
	resumeButton.hide();
	endButton.hide();
	saveButton.hide();
	deleteButton.hide();
	

	//Refreshes the elements
	function refreshElements()
	{
		console.log('Refresh elements');
		//Disable everything by default
		mention.hide();
		geolocationServiceMention.hide();
		locationAccessMention.hide();
		promptLocationAccessButton.hide();
		startButton.hide();
		pauseButton.hide();
		resumeButton.hide();
		endButton.hide();
		saveButton.hide();
		deleteButton.hide();
		
		if (!positionManager.supportsGeolocation)
		{
			//Does not support geolocation
			mention.show();
			geolocationServiceMention.show();
		}
		else if (!positionManager.canGetPosition)
		{
			//Not given permission
			mention.show();
			locationAccessMention.show();
			//Checking if can request location access
			if (positionManager.canRequestPosition)
			{
				promptLocationAccessButton.show();
			}
		}
		//Getting current journey
		let journey = journeyModeManager.journey;
		//Checking if journey exists
		if (journey !== undefined)
		{
			//Getting journey status
			let status = journey.status;
			//Checking if running or paused
			if (status === jmStatuses.Running || status === jmStatuses.Paused)
			{
				//Journey Mode active
				//Showing end button
				endButton.show();
				//Checking if paused
				if (status === jmStatuses.Paused)
				{
					//Checking if can get position
					if (positionManager.canGetPosition)
					{
						//Showing resume button
						resumeButton.show();
					}
				}
				else
				{
					//Journey not paused
					//Showing pause button
					pauseButton.show();
				}
			}
			else if (status === jmStatuses.Idle)
			{
				//Journey not started
				//Checking if can get position
				if (positionManager.canGetPosition)
				{
					//Show start button
					startButton.show();
				}
			}
			else if (status === jmStatuses.Finished)
			{
				//Journey finished
				saveButton.show();
				deleteButton.show();
			}
		}
	}
	function updateJourney()
	{
		//Getting journey
		let journey = journeyModeManager.journey;
		//Checking if journey exists
		if (!journey)
		{
			return;
		}
		//Adding state changes event
		journey.addEventListener('onStatusChanged', refreshElements);
		//Listening for track changes
		journey.addEventListener('onTrackChanged', () =>
		{
			//Creating GPX
			let gpx = GpxConverter.toGpx(journey);
			//Loading track
			mapManager.loadGpx(gpx);
		});
	}
	//Listening for permission updates
	positionManager.addEventListener('onPermissionsUpdated', refreshElements);
	//Listening for journey changes
	journeyModeManager.addEventListener('onJourneyChanged', () =>
	{
		updateJourney();
		//Refreshing elements
		refreshElements();
	});
	updateJourney();
	//Refreshing elements at start
	refreshElements();

	//Adding event to button that requests access to location
	promptLocationAccessButton.click(function()
	{
		//Checking if can request location access
		if (!positionManager.canRequestPosition)
		{
			return;
		}
		//Trying to get location ignoring fact we don't have access to the location yet
		//Prompt will appear during this process
		positionManager.tryGetPosition(true).catch((error, mostRecentPosition) => {});
	});
	

	//Journey controls
	//Require arrow functions to work
	//On start button pressed
	$(startButton).click(() => journeyModeManager.journey.start());
	//On pause button pressed
	$(pauseButton).click(() => journeyModeManager.journey.pause());
	//On resume button pressed
	$(resumeButton).click(() => journeyModeManager.journey.resume());
	//On end button pressed
	$(endButton).click(() => journeyModeManager.journey.end());
	//On save button pressed
	$(saveButton).click(() =>
	{
		//Getting journey
		let journey = journeyModeManager.journey;
		//Converting to json
		let jsonJourney = JSON.stringify(journey);
		//Send api request
		$.post(journeyApiUrl, {track : JSON.parse(jsonJourney)}, function(result)
		{
			console.log(result);
		});
	});
	//On delete button pressed
	$(deleteButton).click(() =>
	{
		journeyModeManager.createNewJourney();
		mapManager.unloadGpx();
	});
});
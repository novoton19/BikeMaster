/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22 04:26pm
	Version: 0.0.2
Updated on
	Version: 0.0.2.3.5

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
*/
//Waiting for document to load
$(document).ready(function()
{
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
	//Getting button which is used to request location access
	const promptLocationAccessButton = $(locationAccessMention).find('#PromptLocationAccessButton');
	//Getting Journey Mode controls
	const buttons = $('#JourneyModeButtons');
	const startButton = $(buttons).find('#Start');
	const pauseButton = $(buttons).find('#Pause');
	const resumeButton = $(buttons).find('#Resume');
	const endButton = $(buttons).find('#End');
	//Element containing locations
	const locationsElem = $('#Locations');

	
	//Removing class from mention and hiding it by default
	mention.removeClass('Invisible');
	mention.hide();
	//Removing class from buttons and hiding them by default
	buttons.removeClass('Invisible');
	startButton.hide();
	pauseButton.hide();
	resumeButton.hide();
	endButton.hide();

	//Map functions
	function refreshLocation()
	{
		let center = SMap.Coords.fromWGS84(
			mostRecentLocation.longitude,
			mostRecentLocation.latitude
		);
		map.setCenter(center);
	}
	
	
	//Refreshes the elements
	function refreshElements()
	{
		//Disable everything by default
		mention.hide();
		geolocationServiceMention.hide();
		locationAccessMention.hide();
		promptLocationAccessButton.hide();
		startButton.hide();
		pauseButton.hide();
		resumeButton.hide();
		endButton.hide();
		
		if (!supportsGeolocation)
		{
			//Does not support geolocation
			mention.show();
			geolocationServiceMention.show();
		}
		else if (!canGetLocation)
		{
			//Not given permission
			mention.show();
			locationAccessMention.show();
			//Checking if can request location access
			if (canRequestLocation)
			{
				promptLocationAccessButton.show();
			}
		}
		if (journeyModeActive)
		{
			//Journey Mode active
			//Showing end button
			endButton.show();
			//Checking if paused
			if (journeyModePaused)
			{
				//Checking if can get location
				if (canGetLocation)
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
		else
		{
			//Checking if can get location
			if (canGetLocation)
			{
				//Show start button
				startButton.show();
			}
		}
	}
	//Listening for permission updates
	document.addEventListener('onPermissionsUpdated', refreshElements);
	document.addEventListener('onJourneyModeStateChanged', refreshElements);
	//Refreshing elements at start
	refreshElements();


	//Adding event to button that requests access to location
	promptLocationAccessButton.click(function()
	{
		//Checking if can request location
		if (!canRequestLocation)
		{
			return;
		}
		//Trying to get location ignoring fact we don't have access to the location yet
		//Prompt will appear during this process
		tryGetLocation(true).catch((error, mostRecentLocation) => {});
	});
	

	//Journey controls
	//On start button pressed
	$(startButton).click(startJourneyMode);
	//On pause button pressed
	$(pauseButton).click(pauseJourneyMode);
	//On resume button pressed
	$(resumeButton).click(resumeJourneyMode);
	//On end button pressed
	$(endButton).click(endJourneyMode);
});
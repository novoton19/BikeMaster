/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22 04:26pm
	Version: 0.0.2
Updated on
	Version: 0.0.2.3.4

Description:
	Tracks user location during 'Journey Mode'

Changes:
	Version 0.0.2.1 - Ask for location access
	Version 0.0.2.2 - Pause/Resume Journey
	Version 0.0.2.3 - Load map and show current location
	Version 0.0.2.3.3 - Add promises to functions, location watching optimization
	Version 0.0.2.3.4 - Bug fixes, commenting
	//Version 0.0.2.4 - Track location in the background - turned out to be impossible
*/
//Waiting for document to load
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

	
	//Whether this browser supports geolocation
	const supportsGeolocation = 'geolocation' in navigator;
	//Whether location can be obtained
	var canGetLocation = false;
	//Whether location can be requests
	var canRequestLocation = false;

	//Current list of locations
	var locations = [];
	//Whether journey mode activated
	var journeyModeActive = false;
	//Whether journey mode paused
	var journeyModePaused = false;
	//Function that watches location of the user
	var locationWatcher = undefined;


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
	function RefreshPermissions()
	{
		//Returns promise
		return new Promise(function(resolve, reject)
		{
			//Disabling elements and then enabling them based on permissions
			//Buttons
			startButton.hide();
			pauseButton.hide();
			resumeButton.hide();
			//Mentions
			mention.hide();
			geolocationServiceMention.hide();
			locationAccessMention.hide();
			promptLocationAccessButton.hide();

			//Checking if geolocation is supported here
			if (!supportsGeolocation)
			{
				//Geolocation not available
				//Cannot get location nor request access
				canGetLocation = false;
				canRequestLocation = false;
				
				//Showing mention
				mention.show();
				geolocationServiceMention.show();
				//Resolved
				resolve();
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
				//Determining privileges
				canGetLocation = state === 'granted';
				canRequestLocation = state === 'prompt';
				//Checking if can get location
				if (canGetLocation)
				{
					//Checking if not in journey mode
					if (!journeyModeActive)
					{
						//Showing start button
						startButton.show();
					}
					else if (journeyModePaused)
					{
						//Showing resume button
						resumeButton.show();
					}
					else if (!journeyModePaused)
					{
						//Showing pause button
						pauseButton.show();
					}
				}
				else
				{
					//Showing mention
					mention.show();
					locationAccessMention.show();
					//Checking if can be prompted
					if (canRequestLocation)
					{
						//Showing button used to request location access
						promptLocationAccessButton.show();
					}
				}
				//Resolved
				resolve();
			});
		});
	}
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
		TryGetLocation(true).catch(error => {});
	});
	
	
	//Location functions
	//On location successfully obtained
	function OnLocationObtained(location)
	{
		//Returns promise
		return new Promise(function(resolve, reject)
		{
			//Updating current location
			currentLocation = location.coords;
			RefreshLocation();
			//Checking if in journey mode and not paused
			if (journeyModeActive && !journeyModePaused)
			{
				//Adding location to the end of the list
				locations.push(location);
				//Display location
				locationsElem.append('timestamp: ' + location.timestamp + ', latitude: ' + location.coords.latitude + ', longitude: ' + location.coords.longitude + ', accuracy: ' + location.coords.accuracy + '<br>');
			}
			//Resolved
			resolve();
		});
	}
	//On location not obtained successfuly
	function OnLocationNotObtained(error)
	{
		//Returns promise
		return new Promise(function(resolve, reject)
		{
			//Eventual resolver
			//Resolve
			resolve();
		});
	}
	//Attempts to get user's location
	function TryGetLocation(allowAttempt = false)
	{
		//Returns promise
		return new Promise(function(resolve, reject)
		{
			//Checking if can get location or if can attempt to get the location 
			if (canGetLocation || (allowAttempt && canRequestLocation))
			{
				//Getting location
				navigator.geolocation.getCurrentPosition(
					(location) => OnLocationObtained(location).then(() => resolve()),
					(error) => OnLocationNotObtained(error).then(() => reject('NotObtained')),
					{
						enableHighAccuracy : true,
						maximumAge : 0,
						timeout : 5000
					}
				);
			}
			else
			{
				//Does not have permission to get the location
				reject('NoPermission');
			}
		})
	}


	//Checks if location watcher can be activated
	function CheckLocationWatcher()
	{
		//Checking if has appropriate permission
		if (canGetLocation)
		{
			//Checking if location watcher exists
			if (locationWatcher === undefined)
			{
				//Creating a new watcher
				locationWatcher = setInterval(
					() =>
					{
						//Trying to get location
						TryGetLocation().catch(function(error)
						{
							//Checking error type
							if (error === 'NoPermission')
							{
								//Re-checking watcher (If the permission has really changed, this interval will get cancelled)
								CheckLocationWatcher();
							}
						});
					}
				);
				console.log('Location watcher activated');
			}
		}
		else
		{
			//Cannot get location
			//Checking if watcher exists
			if (locationWatcher !== undefined)
			{
				//Destroying watcher because has no purpose
				clearInterval(locationWatcher);
				locationWatcher = undefined;
				console.log('Location watcher deactivated');
			}
		}
	}


	//Journey functions
	//On start button pressed
	$(startButton).click(function()
	{
		//Checking if can get location
		if (!canGetLocation)
		{
			//Does not have permission to get the location
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

		//Activating the journey mode
		journeyModeActive = true;
		journeyModePaused = false;

		//Trying to get the immediate location
		TryGetLocation().catch(error => {});
		//Location watcher already running in main thread
		//Changing controls
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
			//Cannot pause anything
			return;
		}
		//Checking if not paused
		if (journeyModePaused)
		{
			//Already paused
			return;
		}
		//Getting immediate location
		TryGetLocation().then(function()
		{
			//Pausing journey mode
			journeyModePaused = true;
		}).catch(
			error => {}
		);
		//Changing controls
		pauseButton.hide();
		resumeButton.show();
	});
	//On resume button pressed
	$(resumeButton).click(function()
	{
		//Checking if in journey mode
		if (!journeyModeActive)
		{
			//Cannot resume because no journey in progress
			return;
		}
		//Checking if paused
		if (!journeyModePaused)
		{
			//Cannot resume journey because already running
			return;
		}
		//Resuming the journey
		journeyModePaused = false;
		//Getting immediate location
		TryGetLocation().catch(error => {});
		//Changing controls
		resumeButton.hide();
		pauseButton.show();
	});
	//On end button pressed
	$(endButton).click(function()
	{
		//Checking if journey mode active
		if (!journeyModeActive)
		{
			//Cannot end journey because not in progress
			return;
		}
		//Location watcher does not need to be destroyed, because it's running in main thread
		//Getting immediate location
		TryGetLocation().then(function()
		{
			//Stopping journey mode
			journeyModeActive = false;
			journeyModePaused = false;
			//Calling on journey finished event
			OnJourneyFinished();
		}).catch(
			error => {}
		);
		//Changing controls
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
			//Journey mode in progress
			return;
		}
	}

	//Loading permissions
	RefreshPermissions().then(function()
	{
		//Checking if geolocation supported
		if (supportsGeolocation)
		{
			//Checking location watcher
			CheckLocationWatcher();
			//Refreshing center of the map
			RefreshLocation();
			//Listening for permission changes
			navigator.permissions.query(
				{
					name : 'geolocation'
				}
			).then(function(result)
			{
				//Refreshing the permissions, because they changed
				result.onchange = () => RefreshPermissions().then(function()
				{
					//Re-checking location watcher
					CheckLocationWatcher();
				});
			});
		}
	});
});
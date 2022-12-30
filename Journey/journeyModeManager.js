/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:09pm
	Version: 0.0.2.3.5
Updated on
	Version: 0.0.2.3.5

Description:
	Responsible for Journey Mode location tracking

Changes:
	
*/
//Current journey track
var track = [];
//Whether Journey Mode active
var journeyModeActive = false;
//Whether Journey Mode paused
var journeyModePaused = false;
//Location watcher for Journey Mode
var journeyModeLocationWatcher = undefined;

//Events
var journeyModeStateChangedEvent = new Event('onJourneyModeStateChanged');


//Checks if location watcher for journey mode is active
function checkJourneyModeLocationWatcher()
{
	//Checking if watcher exists
	if (journeyModeLocationWatcher !== undefined)
	{
		//Watcher exists
		//Checking if has permissions
		if (canGetLocation && journeyModeActive && !journeyModePaused)
		{
			//Watcher can operate normally
		}
		else
		{
			//Stop watcher from grabbing location
			clearInterval(journeyModeLocationWatcher);
			journeyModeLocationWatcher = undefined;
			console.log('Journey Mode location watcher deactivated');
		}
	}
	else
	{
		//Watcher does not exist
		//Checking if has permissions
		if (canGetLocation && journeyModeActive && !journeyModePaused)
		{
			//Can create location watcher
			journeyModeLocationWatcher = setInterval(function()
			{
				tryGetLocation().then(function(location)
				{
					//Adding location to the track
					track.push(location);
				}).catch(function(error, mostRecentLocation)
				{
					//Checking error
					if (error.code == GeolocationPositionError.PERMISSION_DENIED)
					{
						//Re-checking location watcher
						checkJourneyModeLocationWatcher();
					}
				});
			}, 5000);
			console.log('Journey Mode location watcher activated');
		}
		else
		{
			//Cannot create location watcher because either does not have permission or Journey Mode is not active or is paused
		}
	}
}
//Function called on Journey Mode state changed
function onJourneyModeStateChanged()
{
	//Trying to get immediate location
	tryGetLocation().then(function(location)
	{
		//Adding location
		track.push(location);
	}).catch((error, mostRecentLocation) => {});
	//Checking Journey Mode location watcher
	checkJourneyModeLocationWatcher();
	//Dispatching event
	document.dispatchEvent(journeyModeStateChangedEvent);
}


//Starts Journey Mode
function startJourneyMode()
{
	//Checking if can get location
	if (!canGetLocation)
	{
		//Cannot get location to start
		return;
	}
	//Checking if can start Journey Mode
	if (journeyModeActive)
	{
		//Already in Journey Mode
		return;
	}
	//Starting Journey Mode
	journeyModeActive = true;
	journeyModePaused = false;
	track = [];
	//Updating
	onJourneyModeStateChanged();
}
//Pauses Journey Mode
function pauseJourneyMode()
{
	//Checking if Journey Mode active
	if (!journeyModeActive)
	{
		//Not in Journey Mode
		return;
	}
	//Checking if Journey Mode not paused
	if (journeyModePaused)
	{
		//Already paused
		return;
	}
	//Pausing Journey Mode
	journeyModePaused = true;
	//Updating
	onJourneyModeStateChanged();
}
//Resumes Journey Mode
function resumeJourneyMode()
{
	//Checking if can get location
	if (!canGetLocation)
	{
		//Cannot get location to start
		return;
	}
	//Checking if Journey Mode active
	if (!journeyModeActive)
	{
		//Not in Journey Mode
		return;
	}
	//Checking if Journey Mode Paused
	if (!journeyModePaused)
	{
		//Not paused
		return;
	}
	//Resuming Journey Mode
	journeyModePaused = false;
	//Updating
	onJourneyModeStateChanged();
}
//Ends Journey Mode
function endJourneyMode()
{
	//Checking if Journey Mode active
	if (!journeyModeActive)
	{
		//Not in Journey Mode
		return;
	}
	//Finishing Journey Mode
	journeyModeActive = false;
	journeyModePaused = false;
	//Updating
	onJourneyModeStateChanged();
}


//Listening for permission changes and checking location watcher
document.addEventListener('onPermissionsUpdated', checkJourneyModeLocationWatcher);
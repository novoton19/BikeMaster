/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:07pm
	Version: 0.0.2.3.5
Updated on
	Version: 0.0.2.5

Description:
	Responsible for keeping track of permissions and locations

Changes:
	Version 0.0.2.5 - Bug fix (mostRecentLocation undefined by default, reading timestamp)
*/
//Whether this browser supports geolocation
const supportsGeolocation = 'geolocation' in navigator;
//Whether location can be obtained
var canGetLocation = false;
//Whether location can be requests
var canRequestLocation = false;

//Most recent coordinates of the user (Prague by default)
var mostRecentCoordinates = {
	latitude : 50.0755,
	longitude : 14.4378
};
//Most recent location of the user
var mostRecentLocation = undefined;
//Location watcher
var locationWatcher = undefined;


//On permissions updated
var permissionsUpdatedEvent = new Event('onPermissionsUpdated');


//Permission functions
//Refreshes the permission state
function refreshPermissions()
{
	//Checking if geolocation is supported here
	if (!supportsGeolocation)
	{
		//Geolocation not available
		//Cannot get location nor request access
		canGetLocation = false;
		canRequestLocation = false;
		document.dispatchEvent(permissionsUpdatedEvent);
		//Resolve
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
		document.dispatchEvent(permissionsUpdatedEvent);
	});
}


//Location functions
//On location successfully obtained
function onLocationObtained(location)
{
	//Return promise
	return new Promise(function(resolve, reject)
	{
		//Updating current location
		mostRecentLocation = location;
		mostRecentCoordinates = location.coords;
		//Resolve
		resolve();
	});
}
//On location not obtained successfuly
function onLocationNotObtained(error)
{
	return new Promise(function(resolve, reject)
	{
		//Eventual resolver
		//Resolve
		resolve();
	});
}
//Attempts to get user's location
function tryGetLocation(options = { enableHighAccuracy : true, maximumAge : 1000, timeout : 5000 }, allowAttempt = false)
{
	//Return promise
	return new Promise(function(resolve, reject)
	{
		//Checking if most recent location can be used
		if (mostRecentLocation !== undefined && Date.now() - mostRecentLocation.timestamp <= options.maximumAge)
		{
			//Serving older location
			resolve(mostRecentLocation);
			return;
		}
		//Checking if can get location or if can attempt to get the location 
		if (canGetLocation || (allowAttempt && canRequestLocation))
		{
			//Getting location
			navigator.geolocation.getCurrentPosition(
				(location) => onLocationObtained(location).then(() => resolve(location)),
				(error) => onLocationNotObtained(error).then(() => reject(error, mostRecentLocation)),
				options
			);
		}
		else
		{
			let error = new Error('Permission denied');
			error.code = GeolocationPositionError.PERMISSION_DENIED;
			//Does not have permission to get the location
			onLocationNotObtained(error).then(function()
			{
				reject(error, mostRecentLocation);
			});
		}
	});
}


//Checking if geolocation supported
if (supportsGeolocation)
{
	//Loading permissions
	refreshPermissions();

	//Listening for permission changes
	navigator.permissions.query(
		{
			name : 'geolocation'
		}
	).then(function(result)
	{
		//Adding event on change
		result.onchange = refreshPermissions;
	});
}
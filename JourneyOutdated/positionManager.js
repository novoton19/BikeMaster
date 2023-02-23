/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:07pm
	Version: 0.0.2.3.5
Updated on
	Version: 0.0.2.7

Description:
	Responsible for keeping track of permissions and position of this device

Changes:
	Version 0.0.2.5 - Bug fix (mostRecentPosition undefined by default, reading timestamp)
	Version 0.0.2.6 - Convert to OOP, rename file to 'positionManager.js'
	Version 0.0.2.7 - Removed promises, added onPositionUpdated and onPositionNotUpdated events
*/
//Position manager
class PositionManager extends EventTarget
{
	//Whether geolocation is supported by this browser
	supportsGeolocation = false;
	//Whether position can be obtained
	canGetPosition = false;
	//Whether position can be requested
	canRequestPosition = false;

	//Most recent coordinates of the user (Prague by default)
	mostRecentCoordinates = {
		latitude : 50.0755,
		longitude : 14.4378
	};
	//Most recent position of the user
	mostRecentPosition = {
		coords : this.mostRecentCoordinates
	};

	//On permissions updated
	#permissionsUpdatedEvent = new Event('onPermissionsUpdated');
	#positionUpdatedEvent = new Event('onPositionUpdated');
	#positionNotUpdatedEventName = 'onPositionNotUpdated';


	//Constructor
	constructor()
	{
		super();
		//Refreshing permissions
		this.#refreshPermissions();
		//Checking if geolocation is supported
		if (this.supportsGeolocation)
		{
			//Listening for permission changes
			navigator.permissions.query(
				{
					name : 'geolocation'
				}
			).then((result) =>
			{
				//Adding event on change
				result.onchange = () => this.#refreshPermissions();
			});
		}
	}
	//Refreshes the permissions
	#refreshPermissions()
	{
		//Whether supports geolocation
		this.supportsGeolocation = 'geolocation' in navigator;
		//Checking if geolocation is supported in this browser
		if (!this.supportsGeolocation)
		{
			//Geolocation not available
			//Cannot get position nor request access
			this.canGetPosition = false;
			this.canRequestPosition = false;
			//Dispatching event
			this.dispatchEvent(this.#permissionsUpdatedEvent);
			return;
		}
		//Getting permission state
		navigator.permissions.query(
			{
				name : 'geolocation'
			},
		).then((result) =>
		{
			//Getting state
			let state = result.state;
			//Determining privileges
			this.canGetPosition = state === 'granted';
			this.canRequestPosition = state === 'prompt';
			//Dispatching event
			this.dispatchEvent(this.#permissionsUpdatedEvent);
		});
	}
	//On position obtained
	#onPositionObtained(position)
	{
		//Updating current position
		this.mostRecentPosition = position;
		this.mostRecentCoordinates = position.coords;
		//Calling event
		this.dispatchEvent(this.#positionUpdatedEvent);
	}
	//On position not obtained
	#onPositionNotObtained(error)
	{
		//Eventual resolver
		//Dispatching event
		this.dispatchEvent(new CustomEvent(
			this.#positionNotUpdatedEventName,
			{
				detail : {
					error : error
				}
			}
		));
	}
	//Attempts to update position
	tryUpdatePosition(options = { enableHighAccuracy : true, maximumAge : 1000, timeout : 5000 }, allowAttempt = false)
	{
		//Checking if can get position or if can attempt to get the position 
		if (this.canGetPosition || (allowAttempt && this.canRequestPosition))
		{
			//Getting position
			navigator.geolocation.getCurrentPosition(
				(position) => this.#onPositionObtained(position),
				(error) => this.#onPositionNotObtained(error),
				options
			);
		}
		else
		{
			let error = new Error('Permission denied');
			error.code = GeolocationPositionError.PERMISSION_DENIED;
			//Does not have permission to get the position
			this.#onPositionNotObtained(error);
		}
	}
}
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:09pm
	Version: 0.0.2.3.5
Updated on
	Version: 0.0.2.7

Description:
	Responsible for Journey Mode location tracking and backing up

Changes:
	Version 0.0.2.5 - Convert to OOP
	Version 0.0.2.6 - Support OOP positionManager
	Version 0.0.2.7 - Create custom positionManager on new Journey
*/
//Journey Mode statuses
const jmStatuses = {
	Idle : 0,
	Running : 1,
	Paused : 2,
	Finished : 3
};

//Track point class
class TrackPoint
{
	//Point information
	latitude;
	longitude;
	altitude;
	accuracy;
	altitudeAccuracy;
	timestamp;

	//Constructor
	constructor(geolocationPosition)
	{
		//Getting coords
		let coords = geolocationPosition.coords;
		//Adding information
		this.latitude = coords.latitude;
		this.longitude = coords.longitude;
		this.altitude = coords.altitude;
		this.accuracy = coords.accuracy;
		this.altitudeAccuracy = coords.altitudeAccuracy;
		this.timestamp = geolocationPosition.timestamp;
	}
}
//Journey
class Journey extends EventTarget
{
	//List of points along the way
	track;
	//Status of the journey
	status;

	//Private variables
	#positionManager;
	//Current position watcher
	#positionWatcher;
	//On track changed
	#trackChangedEvent = new Event('onTrackChanged');
	//On status changed event
	#statusChangedEvent = new Event('onStatusChanged');
	//On journey finished
	#journeyFinishedEvent = new Event('onFinish');

	//Constructor
	constructor(track = [], status = jmStatuses.Idle)
	{
		super();
		//Creating position manager
		let positionManager = new PositionManager();
		
		//Adding information
		//Creating track
		this.track = track;
		//Adding status
		this.status = status;
		//Adding position manager
		this.#positionManager = positionManager;

		//Listening for permission changes and checking position watcher on event
		positionManager.addEventListener('onPermissionsUpdated', () =>
		{
			//Updating position manager
			this.#updatePositionWatcher();
		});
		//On position updated
		positionManager.addEventListener('onPositionUpdated', () =>
		{
			//Adding point
			this.track.push(new TrackPoint(positionManager.mostRecentPosition));
		})
		//On position not updated
		positionManager.addEventListener('onPositionNotUpdated', (event) =>
		{
			//Getting detail
			let detail = event.detail;
			//Getting error
			let error = detail.error;
			//Checking error code
			if (error == GeolocationPositionError.PERMISSION_DENIED)
			{
				//Cannot get location anymore
				//Update position watcher
				this.#updatePositionWatcher();
			}
			else
			{
				//Print error
				console.error(error);
			}
		});
		//Updating position watcher manually
		this.#updatePositionWatcher();
	}
	//Updates position watcher
	#updatePositionWatcher()
	{
		//Checking status
		if (this.status === jmStatuses.Running && this.#positionManager.canGetPosition)
		{
			//Journey is running and can get position
			//Checking if position watcher not exists
			if (this.#positionWatcher === undefined)
			{
				//Trying to update position now
				this.#positionManager.tryUpdatePosition();
				//Creating a new position watcher
				this.#positionWatcher = setInterval(() =>
				{
					//Trying to update position
					this.#positionManager.tryUpdatePosition();
				}, 5000);
				console.log('Position watcher activated');
			}
		}
		else
		{
			//Journey is not running or cannot get position
			//Checking if position watcher exists
			if (this.#positionWatcher !== undefined)
			{
				//Disabling position watcher
				clearInterval(this.#positionWatcher);
				this.#positionWatcher = undefined;
				console.log('Position watcher deactivated');
			}
		}
	}
	//Called when the status of journey changes
	#onStatusChanged()
	{
		//Checking if Journey running
		if (this.status !== jmStatuses.Idle)
		{
			//Trying to update position now
			this.#positionManager.tryUpdatePosition();
		}
		//Updating position watcher
		this.#updatePositionWatcher();
		//Dispatching event
		this.dispatchEvent(this.#statusChangedEvent);
		//Checking if finished
		if (this.status === jmStatuses.Finished)
		{
			//Dispatching event
			this.dispatchEvent(this.#journeyFinishedEvent);
		}
	}
	//Start journey
	start()
	{
		//Checking if can get position
		if (!this.#positionManager.canGetPosition)
		{
			//Cannot get position to start
			return;
		}
		//Checking status
		if (this.status !== jmStatuses.Idle)
		{
			//Cannot start Journey, because it's already started
			return;
		}
		//Changing status to running
		this.status = jmStatuses.Running;
		//Calling on change event
		this.#onStatusChanged();
	}
	//Pause journey
	pause()
	{
		//Checking if Journey is running
		if (this.status !== jmStatuses.Running)
		{
			//Journey not in progress
			return;
		}
		//Pausing Journey
		this.status = jmStatuses.Paused;
		//Calling on change event
		this.#onStatusChanged();
	}
	//Resume journey
	resume()
	{
		//Checking if can get position
		if (!this.#positionManager.canGetPosition)
		{
			//Cannot get position to start
			return;
		}
		//Checking if Journey Mode is paused
		if (this.status !== jmStatuses.Paused)
		{
			//Journey not paused, nothing to resume
			return;
		}
		//Resuming Journey Mode
		this.status = jmStatuses.Running;
		//Calling on change event
		this.#onStatusChanged();
	}
	//End journey
	end()
	{
		//Checking if Journey running or paused
		if (this.status !== jmStatuses.Running && this.status !== jmStatuses.Paused)
		{
			//Journey not active
			return;
		}
		//Finishing Journey
		this.status = jmStatuses.Finished;
		//Updating
		this.#onStatusChanged();
	}
}
//Journey Mode manager
class JourneyModeManager extends EventTarget
{
	//Current Journey
	journey;

	//Event called when journey changes
	#journeyChangedEvent = new Event('onJourneyChanged');

	//Constructor
	constructor()
	{
		super();
		this.createNewJourney();
	}
	//Creates a new Journey
	createNewJourney() 
	{
		console.log('Created new Journey');
		//Checking if journey exists
		if (this.journey)
		{
			//Deleting previous journey
			delete this.journey;
		}
		//Creating new Journey
		this.journey = new Journey();
		//Dispatching event
		this.dispatchEvent(this.#journeyChangedEvent);
	}
}
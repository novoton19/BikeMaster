/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:09pm
	Version: 0.0.2.3.5
Updated on
	Version: 0.0.2.5

Description:
	Responsible for Journey Mode location tracking and backing up

Changes:
	Version 0.0.2.5 - Convert to OOP
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
	track = [];
	//Status of the journey
	status = jmStatuses.Idle;

	//Private variables
	//Current location watcher
	#locationWatcher = undefined;
	//On track changed
	#trackChangedEvent = new Event('onTrackChanged');
	//On status changed event
	#statusChangedEvent = new Event('onStatusChanged');
	//On journey finished
	#journeyFinishedEvent = new Event('onFinish');

	//Constructor
	constructor()
	{
		super();
		//Listening for permission changes and checking location watcher on event
		//Must be a function becuase if called directly, this would be overwritten
		document.addEventListener('onPermissionsUpdated', () =>
		{
			this.#updateLocationWatcher();
		});
	}
	//Updates location watcher
	#updateLocationWatcher()
	{
		//Checking status
		if (this.status === jmStatuses.Running && canGetLocation)
		{
			//Journey is running and can get location
			//Checking if location watcher not exists
			if (this.#locationWatcher === undefined)
			{
				//Creating a new location watcher
				this.#locationWatcher = setInterval(() =>
				{
					//Trying to get location
					tryGetLocation().then((geolocationPosition) =>
					{
						//Adding point to the track
						this.track.push(
							new TrackPoint(geolocationPosition)
						);
						//Dispatching track changed event
						this.dispatchEvent(this.#trackChangedEvent);
					}).catch((error, mostRecentLocation) =>
					{
						//Checking error
						if (error.code == GeolocationPositionError.PERMISSION_DENIED)
						{
							//Re-checking location watcher
							this.#updateLocationWatcher();
						}
					});
				}, 5000);
				console.log('Location watcher activated');
			}
		}
		else
		{
			//Journey is not running or cannot get location
			//Checking if location watcher exists
			if (this.#locationWatcher !== undefined)
			{
				//Disabling location watcher
				clearInterval(this.#locationWatcher);
				this.#locationWatcher = undefined;
				console.log('Location watcher deactivated');
			}
		}
	}
	//Called when the status of journey changes
	#onStatusChanged()
	{
		//Checking if Journey running
		if (this.status !== jmStatuses.Idle)
		{
			//Trying to get immediate location
			tryGetLocation().then((geolocationPosition) =>
			{
				//Adding location
				this.track.push(
					new TrackPoint(geolocationPosition)
				);
				//Dispatching track changed event
				this.dispatchEvent(this.#trackChangedEvent);
			}).catch((error, mostRecentLocation) => {});
		}
		//Updating location watcher
		this.#updateLocationWatcher();
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
		//Checking if can get location
		if (!canGetLocation)
		{
			//Cannot get location to start
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
		//Checking if can get location
		if (!canGetLocation)
		{
			//Cannot get location to start
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
//Creating a new manager
const jmManager = new JourneyModeManager();
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:09pm
	Version: 0.0.2.3.5
Updated on
	Version: 0.4.1

Description:
	Responsible for Journey Mode location tracking and backing up

Changes:
	Version 0.0.2.5 - Convert to OOP
	Version 0.0.2.6 - Support OOP positionManager
	Version 0.0.2.7 - Create custom positionManager on new Journey
	Version 0.0.4 - Add Segment
	Version 0.0.5.4.1 - Bux fix, added event call on track updated
	Version 0.4 - Support for positionWatcher, remove altitude and altitudeAccuracy
*/
//Journey Mode statuses
const journeyModeStatuses = {
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
	//altitude;
	accuracy;
	//altitudeAccuracy;
	timestamp;

	//Constructor
	constructor(geolocationPosition)
	{
		//Getting coords
		let coords = geolocationPosition.coords;
		//Adding information
		this.latitude = coords.latitude;
		this.longitude = coords.longitude;
		//this.altitude = coords.altitude;
		this.accuracy = coords.accuracy;
		//this.altitudeAccuracy = coords.altitudeAccuracy;
		this.timestamp = geolocationPosition.timestamp;
	}
}
//Segment
class Segment
{
	//List of points
	points;
	startTime;
	endTime;

	//Constructor
	constructor(points = [], startTime = undefined, endTime = undefined)
	{
		this.points = points;
		this.startTime = startTime || new Date().getTime();
		this.endTime = endTime;
	}
	//Adds a point to the end of the list
	addPoint(point)
	{
		//Checking if this is the first points
		if (!this.points.length)
		{
			this.startTime = point.timestamp;
		}
		this.points.push(point);
	}
	//Closes the segment
	close()
	{
		//Checking if not closed already
		if (this.endTime === undefined)
		{
			this.endTime = new Date().getTime();
		}
	}
}
//Journey
class Journey extends EventTarget
{
	//Identifier
	id;
	//List of segments
	segments = [];
	//Status of the journey
	status;

	//Private variables
	positionManager;
	//Current position watcher
	#positionWatcher;
	//On track changed
	#trackChangedEvent = new Event('onTrackChanged');
	//On status changed event
	#statusChangedEvent = new Event('onStatusChanged');
	//On journey finished
	#journeyFinishedEvent = new Event('onFinish');

	//Constructor
	constructor(segments = [], status = journeyModeStatuses.Idle)
	{
		super();
		//Creating position watcher
		let positionWatcher = new PositionWatcher(false);
		//Getting position manager
		let positionManager = positionWatcher.positionManager;
		
		//Adding information
		//Adding ID
		this.id = new Date().getTime();
		//Creating segments
		this.segments = segments;
		//Adding status
		this.status = status;
		//Adding position manager
		this.positionManager = positionManager;
		//Adding position watcher
		this.#positionWatcher = positionWatcher;

		//On position updated
		positionManager.addEventListener('onPositionUpdated', () =>
		{
			//Getting last segment
			let lastSegment = this.segments[this.segments.length - 1];
			//Adding point
			lastSegment.addPoint(new TrackPoint(positionManager.mostRecentPosition));
			//Calling on track changed event
			this.dispatchEvent(this.#trackChangedEvent);
		});
	}
	//Updates position watcher
	#updatePositionWatcher()
	{
		//Checking status
		if (this.status === journeyModeStatuses.Running)
		{
			this.#positionWatcher.update(true);
		}
		else
		{
			this.#positionWatcher.update(false);
		}
	}
	//Called when the status of journey changes
	#onStatusChanged()
	{
		//Checking if Journey running
		if (this.status !== journeyModeStatuses.Idle)
		{
			//Trying to update position now
			this.positionManager.tryUpdatePosition();
		}
		//Updating position watcher
		this.#updatePositionWatcher();
		//Dispatching event
		this.dispatchEvent(this.#statusChangedEvent);
		//Checking if finished
		if (this.status === journeyModeStatuses.Finished)
		{
			//Dispatching event
			this.dispatchEvent(this.#journeyFinishedEvent);
		}
	}
	//Start journey
	start()
	{
		//Checking if can get position
		if (!this.positionManager.canGetPosition)
		{
			//Cannot get position to start
			return;
		}
		//Checking status
		if (this.status !== journeyModeStatuses.Idle)
		{
			//Cannot start Journey, because it's already started
			return;
		}
		//Create new segment
		this.segments.push(new Segment());
		//Start position watcher
		this.#positionWatcher.update(true);
		//Changing status to running
		this.status = journeyModeStatuses.Running;
		//Calling on change event
		this.#onStatusChanged();
	}
	//Pause journey
	pause()
	{
		//Checking if Journey is running
		if (this.status !== journeyModeStatuses.Running)
		{
			//Journey not in progress
			return;
		}
		//Stop position watcher
		this.#positionWatcher.update(false);
		//Close current segment
		this.segments[this.segments.length - 1].close();
		//Pausing Journey
		this.status = journeyModeStatuses.Paused;
		//Calling on change event
		this.#onStatusChanged();
	}
	//Resume journey
	resume()
	{
		//Checking if can get position
		if (!this.positionManager.canGetPosition)
		{
			//Cannot get position to start
			return;
		}
		//Checking if Journey Mode is paused
		if (this.status !== journeyModeStatuses.Paused)
		{
			//Journey not paused, nothing to resume
			return;
		}
		//Create new segment
		this.segments.push(new Segment());
		//Start position watcher
		this.#positionWatcher.update(true);
		//Resuming Journey Mode
		this.status = journeyModeStatuses.Running;
		//Calling on change event
		this.#onStatusChanged();
	}
	//End journey
	end()
	{
		//Checking if Journey running or paused
		if (this.status !== journeyModeStatuses.Running && this.status !== journeyModeStatuses.Paused)
		{
			//Journey not active
			return;
		}
		//Stop position watcher
		this.#positionWatcher.update(false);
		//Close current segment
		this.segments[this.segments.length - 1].close();
		//Finishing Journey
		this.status = journeyModeStatuses.Finished;
		console.log(this);
		//Updating
		this.#onStatusChanged();
	}
}
//Journey Mode manager
class JourneyManager extends EventTarget
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
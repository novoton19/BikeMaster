/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/23/23 04:27pm
	Version: 0.4
Updated on
	Version: 0.4.1

Description:
	Watches position

Changes:
	Version 0.4.1 - Bug fix - kept being deactivated after permission update
*/
class PositionWatcher
{
	//Position manager
	positionManager;
	//Process ID
	#processID;
	//Whether active
	active;
	#requestedState;

	//Constructor
	constructor(activateOnStart = true)
	{
		//Creating position manager
		this.positionManager = new PositionManager();
		this.#requestedState = activateOnStart;

		//Listening for permission changes and checking position watcher on event
		this.positionManager.addEventListener('onPermissionsUpdated', () =>
		{
			this.update(this.#requestedState);
		});
		//Checking if should activate right away
		if (activateOnStart)
		{
			this.#activate();
		}
	}
	//Enable position watcher
	#activate()
	{
		//Checking if disabled
		if (this.#processID === undefined)
		{
			this.active = true;
			//Trying to update position now
			this.positionManager.tryUpdatePosition();
			//Creating a new position watcher
			this.#processID = setInterval(() =>
			{
				//Trying to update position
				this.positionManager.tryUpdatePosition();
			}, 2500);
			console.log('Position watcher activated');
		}
	}
	//Disable position watcher
	#deactivate()
	{
		//Checking if enabled
		if (this.#processID !== undefined)
		{
			this.active = false;
			//Disabling position watcher
			clearInterval(this.#processID);
			this.#processID = undefined;
			console.log('Position watcher deactivated');
		}
	}
	//Update watcher
	update(state)
	{
		this.#requestedState = state;

		if (state && this.positionManager.canGetPosition)
		{
			//Activate watcher
			this.#activate();
		}
		else
		{
			//Deativate watcher
			this.#deactivate();
		}
	}
}
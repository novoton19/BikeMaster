/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:07pm
	Version: 0.0.2.7
Updated on
	Version: 0.0.2.7

Description:
	Loads a map, displays current location on a map

Changes:

*/
//Map manager
class MapManager
{
	//Loaded map
	map;
	
	//Position manager
	#positionManager;
	//Current position watcher
	#positionWatcher;

	//Constructor
	constructor(mapElementID)
	{
		//Creating map
		let map = new SMap(
			JAK.gel('Map'),
			undefined,
			14
		);
		//Adding default layer
		map.addDefaultLayer(SMap.DEF_BASE).enable();
		
		//Creating position manager
		let positionManager = new PositionManager();
		
		//Adding map and position manager
		this.map = map;
		this.#positionManager = positionManager;
		
		//Adding position manager evnet listeners
		//Op permissions updated
		positionManager.addEventListener('onPermissionsUpdated', () =>
		{
			//Update positionWatcher
			this.#updatePositionWatcher();
		});
		//On position updated
		positionManager.addEventListener('onPositionUpdated', () =>
		{
			//Update map position
			this.#updatePosition(positionManager.mostRecentCoordinates);
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
	//Updates the position
	#updatePosition(coords)
	{
		let center = SMap.Coords.fromWGS84(
			coords.longitude,
			coords.latitude
		);
		this.map.setCenter(center);
	}
	//Updates position watcher
	#updatePositionWatcher()
	{
		//Checking if position can be obtained
		if (this.#positionManager.canGetPosition)
		{
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
}
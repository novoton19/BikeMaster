/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:07pm
	Version: 0.0.2.7
Updated on
	Version: 0.3.4

Description:
	Loads a map, displays current location on a map

Changes:
	Version 0.0.4.2 - Show track on a map
	Version 0.0.4.3 - Option not to watch position
	Version 0.0.5.4.1 - Add unload gpx function
	Version 0.3.3 - Enable default controls
	Version 0.3.4 - Disable default controls on default
*/
//Map manager
class MapManager
{
	//Loaded map
	map;
	//Loaded gpx layer
	#gpxLayer;
	
	//Position manager
	#positionManager;
	//Current position watcher
	#positionWatcher;

	//Constructor
	constructor(mapElementID, watchPosition = true)
	{
		//Creating map
		let map = new SMap(
			JAK.gel(mapElementID),
			undefined,
			12
		);
		//Adding default layer
		map.addDefaultLayer(SMap.DEF_TURIST).enable();

		//Adding map
		this.map = map;

		//Checking if allow watching position
		if (watchPosition)
		{
			//Creating position manager
			let positionManager = new PositionManager();
			
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
				this.updatePosition(positionManager.mostRecentCoordinates);
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
	}
	//Creates a GPX layer
	loadGpx(gpx)
	{
		//Checking if gpx layer exists
		if (this.#gpxLayer !== undefined)
		{
			//Removing layer
			this.map.removeLayer(this.#gpxLayer);
			this.#gpxLayer = undefined;
		}
		//Creating xml document
		let xmlDocument = JAK.XML.createDocument(gpx);
		//Creating layer
		let layer = new SMap.Layer.GPX(xmlDocument, null, {maxPoints:500});
		//Adding layer
		this.map.addLayer(layer);
		//Enabling layer
		layer.enable();
		//Setting layer
		this.#gpxLayer = layer;
	}
	//Fits GPX to a map
	fitGpxLayer()
	{
		this.#gpxLayer.fit();
	}
	//Unloads gpx
	unloadGpx()
	{
		//Checking if gpx layer exists
		if (this.#gpxLayer !== undefined)
		{
			//Removing layer
			this.map.removeLayer(this.#gpxLayer);
			this.#gpxLayer = undefined;
		}
	}
	//Updates the position
	updatePosition(coords)
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
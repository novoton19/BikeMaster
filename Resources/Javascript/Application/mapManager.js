/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/30/22 10:07pm
	Version: 0.0.2.7
Updated on
	Version: 0.4

Description:
	Loads a map, displays current location on a map

Changes:
	Version 0.0.4.2 - Show track on a map
	Version 0.0.4.3 - Option not to watch position
	Version 0.0.5.4.1 - Add unload gpx function
	Version 0.3.3 - Enable default controls
	Version 0.3.4 - Disable default controls on default
	Version 0.4 - Add option to disable position watcher, move position watcher to another file
*/
//Map manager
class MapManager
{
	//Loaded map
	map;
	//Loaded gpx layer
	#gpxLayer;
	
	//Position manager
	positionManager;
	//Current position watcher
	#positionWatcher;

	//Constructor
	constructor(mapElementID, watchPositionOnStart = true)
	{
		//Creating map
		let map = new SMap(
			JAK.gel(mapElementID),
			undefined,
			12
		);
		//Creating position watcher
		let positionWatcher = new PositionWatcher(watchPositionOnStart);
		//Getting position manager
		let positionManager = positionWatcher.positionManager;

		
		//Adding map
		this.map = map;
		//Adding managers
		this.positionWatcher = positionWatcher;
		this.positionManager = positionManager;


		//Adding default layer
		map.addDefaultLayer(SMap.DEF_TURIST).enable();
		
		//Adding position manager evnet listeners
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
				positionWatcher.update(true);
			}
			else
			{
				//Print error
				console.error(error);
			}
		});
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
}
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/14/23 03:35pm
	Version: 0.3.3
Updated on
	Version: 0.3.3

Description:
	Javascript specifically designed for location upload

Changes:
	 
*/
//Action url
var action = '../../Api/User/updateInformation.php';
var method = 'post';

//Waiting for document to load
$(document).ready(() =>
{
	//Creating network manager
	const networkManager = new NetworkManager();
	//Creating map manager
	var mapManager = new MapManager('map', false);
	//Creating position manager
	var positionManager = new PositionManager();

	//Getting page
	var page = $('#locationPage');
	var findLocationButton = page.find('.findLocationButton');
	var submitButton = page.find('.submitButton');
	var inputReason = page.find('.inputReason');

	//On position obtained
	function onPositionObtained()
	{
		mapManager.updatePosition(positionManager.mostRecentCoordinates);
		//Set zoom based on accuracy
		mapManager.map.setZoom(17 - Math.round(Math.log(positionManager.mostRecentCoordinates.accuracy / 100) / Math.log(2)));
	}
	//Attempts to get device position
	function getDevicePosition()
	{
		if (positionManager.canRequestPosition || positionManager.canGetPosition)
		{
			positionManager.tryUpdatePosition(undefined, true);
		}
	}
	//Shows/hides find location button
	function updateLocationButton()
	{
		//Checking if location can be obtained
		if (positionManager.canRequestPosition || positionManager.canGetPosition)
		{
			//Showing button
			findLocationButton.show(200);
		}
		else
		{
			//Hiding button
			findLocationButton.hide(200);
		}
	}
	//On location submitted
	function onLocationSubmitted()
	{
		let center = mapManager.map.getCenter();
		let longitude = center.x;
		let latitude = center.y;

		//Remove any input reason text
		inputReason.text('');

		sendRequest(
			action,
			{
				latitude : latitude,
				longitude : longitude
			},
			method,
		).then((response) =>
		{
			//Next page
			onPageFinished();
		}).catch((information) =>
		{
			//Checking if information is structured
			if (information.structured)
			{
				inputReason.text(`${information.response.inputReasons.longitude.status.reason}, ${information.response.inputReasons.latitude.status.reason}`);
			}
			else
			{
				inputReason.text(`Request error: ${information.reason}`);
			}
		});
	}
	//On network changed
	function onNetworkChange()
	{
		//Checking if online
		if (networkManager.online)
		{
			//Enable button
			submitButton.prop('disabled', false);
		}
		else//if (!networkManager.online)
		{
			//Disable button
			submitButton.prop('disabled', true);
		}
	}
	positionManager.addEventListener('onPermissionsUpdated', updateLocationButton);
	updateLocationButton();
	positionManager.addEventListener('onPositionUpdated', onPositionObtained);
	findLocationButton.click(getDevicePosition);
	submitButton.click(onLocationSubmitted);

	//On network changed
	networkManager.addEventListener('onStatusChanged', onNetworkChange);
	onNetworkChange();
});
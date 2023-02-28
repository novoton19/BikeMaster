/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/14/23 04:56pm
	Version: 0.3.4
Updated on
	Version: 0.3.6

Description:
	Loads account information

Changes:
	Version 0.3.5.2 - Sign out
	Version 0.3.6 - Public account url
*/
//Status request url
var statusUrl = '../Api/User/status.php';
var signOutApiUrl = '../Api/User/logout.php';
var signInUrl = 'SignIn/';
var userProfilePicturesUrl = '../Assets/ProfilePictures/Users/';
var defaultProfilePictureUrl = '../Assets/ProfilePictures/Default/default.png';
var viewAccountUrl = 'View/';
var returnUrlFromViewAccount = '../';
//Getting current script name
var mainName = document.currentScript.src.split('/').pop();

//Waiting for page to load
$(document).ready(() =>
{
	//Creating network manager
	var networkManager = new NetworkManager();
	//Creating map manager
	var mapManager = new MapManager('map', false);
	mapManager.map.setZoom(14);
	//Creating layer that will hold marker
	var layer = new SMap.Layer.Marker();
	mapManager.map.addLayer(layer);
	layer.enable();
	//Creating location marker
	var locationMarker = new SMap.Marker(SMap.Coords.fromWGS84(14.41790, 50.12655), 'locationMarker');
	layer.addMarker(locationMarker);
	//Getting page
	var page = $('#account');
	var content = page.find('.content');
	var setupDialog = page.find('.setupDialog');
	var publicProfileButton = page.find('.publicProfileButton');
	var profilePictureElem = page.find('.profilePicture');
	var usernameElem = page.find('.username');
	var emailElem = page.find('.email');
	var registrationElem = page.find('.registrationTime');
	var descriptionWrapper = page.find('.descriptionWrapper');
	var descriptionElem = descriptionWrapper.find('.description');
	var readMoreButton = descriptionWrapper.find('.readMoreDescription');
	var readLessButton = descriptionWrapper.find('.readLessDescription');
	var locationWrapper = page.find('.locationWrapper');
	var signOutButton = page.find('.signOutButton');

	readLessButton.hide();

	function onAccountRequested()
	{
		//Hide content
		content.hide();
		//Return urls to load
		return [{
			url : statusUrl
		}];
	}
	function onAccountLoaded(responses)
	{
		//Getting account
		let status = responses[0];
		let account = status.account;
		let id = account.id;
		let profilePictureUrl = account.profilePictureUrl;
		let username = account.username;
		let email = account.email;
		let registrationTime = `Joined on ${new Date(account.registrationTime * 1000).toLocaleDateString()}`;
		let description = account.description;
		let latitude = account.latitude;
		let longitude = account.longitude;

		//Creating get params for public profile url
		let getParams = new URLSearchParams();
		getParams.set('id', id);
		getParams.set('returnUrl', returnUrlFromViewAccount);
		//Adding public profile url
		publicProfileButton.attr('href', viewAccountUrl + '?' + getParams.toString());

		descriptionElem.text('');
		descriptionElem.addClass('limited');
		readMoreButton.hide();
		readLessButton.hide();

		if (profilePictureUrl)
		{
			profilePictureUrl = userProfilePicturesUrl + profilePictureUrl;
		}
		else
		{
			profilePictureUrl = defaultProfilePictureUrl;
		}
		if (!description)
		{
			descriptionWrapper.hide();
		}
		else
		{
			descriptionWrapper.show();
		}
		if (!latitude || !longitude)
		{
			locationWrapper.hide();
		}
		else
		{
			locationWrapper.show();
		}
		if (!profilePictureUrl || !description || !latitude || !longitude)
		{
			setupDialog.show();
		}
		else
		{
			setupDialog.hide();
		}
		if (descriptionElem.height() < descriptionElem[0].scrollHeight)
		{
			readMoreButton.show();
		}
		//Load information
		profilePictureElem.attr('src', profilePictureUrl);
		usernameElem.text(username);
		emailElem.text(email);
		registrationElem.text(registrationTime);
		descriptionElem.text(description);
		mapManager.updatePosition({
			latitude : latitude,
			longitude : longitude
		});
		locationMarker.setCoords(SMap.Coords.fromWGS84(longitude, latitude));
		//Show content
		content.show();
	}
	
	window.onAccountRequested = onAccountRequested;
	window.onAccountLoaded = onAccountLoaded;
	window.onAccountLoadFailed = (information) => information.reason;

	readMoreButton.click(() =>
	{
		descriptionElem.removeClass('limited');
		readMoreButton.hide();
		readLessButton.show();
	});
	readLessButton.click(() =>
	{
		descriptionElem.addClass('limited');
		readMoreButton.show();
		readLessButton.hide();
	});
	signOutButton.click(() =>
	{
		//Disable sign out button
		signOutButton.prop('disabled', true);
		//Send request to sign out
		sendRequest(
			signOutApiUrl
		).then((response) =>
		{
			window.location.href = signInUrl;
		}).catch(() =>
		{
			signOutButton.prop('disabled', !networkManager.online);
		});
	});
	networkManager.addEventListener('onStatusChanged', () =>
	{
		signOutButton.prop('disabled', !networkManager.online);
	});
});
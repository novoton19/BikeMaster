/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22 04:26pm
	Version: 0.0.2
Updated on
	Version: 0.0.2

Description:
Tracks user location during 'Journey Mode'
*/ 

if ('geolocation' in navigator)
{
	let locations = [];

	function onLocationAdded(pos)
	{
		console.log(pos.coords);
		locations.push(pos.coords);
		
		$('html').append('latitude: ' + pos.coords.latitude + ', longitude: ' + pos.coords.longitude + ', accuracy: ' + pos.coords.accuracy + '<br>');
	}
	function onLocationNotAdded(err)
	{
		console.log(err);
	}
	setInterval(function()
	{
		navigator.geolocation.getCurrentPosition(onLocationAdded, onLocationNotAdded, {timeout: 5000, enableHighAccuracy : true, maximumAge : 0});
	}, 5000);
}
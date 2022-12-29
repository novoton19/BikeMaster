/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22
	Version: 0.0.1
Updated on
	Version: 0.0.1

Description:
Registers service worker
*/ 
//Checking if serviceWorker can be registered
if ('serviceWorker' in navigator)
{
	//Registering serviceWorker
	navigator.serviceWorker.register(
		'/Pwa/BikeMaster/serviceWorker.js',
		{
			'scope' : '/Pwa/BikeMaster/'
		}
	);
}
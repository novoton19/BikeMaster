/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22
	Version: 0.0.1
Updated on
	Version: 0.4.3

Description:
	Registers service worker

Changes:
	Version 0.4.3 - Inform service worker about internet changes
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
	//Getting registration
	navigator.serviceWorker.ready.then((registration) => {
		function informWorker()
		{
			//Sending message to service worker
			registration.active.postMessage({
				type : 'NETWORK_ONLINE'
			});
		}
		//Listening for online status
		window.addEventListener('online', () =>
		{
			informWorker();
		});
		//Checking if online
		if (navigator.onLine)
		{
			informWorker();
		}
	});
}
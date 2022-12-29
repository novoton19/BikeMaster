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
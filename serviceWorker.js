/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22
	Version: 0.0.1
Updated on
	Version: 0.3.1

Description:
	Service worker

Changes:
	Version 0.0.2.3.1 - Optimizations for offline mode
	Version 0.0.2.3.2 - Optimizations for offline mode
	Version 0.0.2.3.5 - Added 'Journey/locationManager.js' and 'Journey/journeyModeManager.js' files
	//Version 0.0.2.4 - Track location in the background - turned out to be impossible
	Version 0.0.2.5.1 - Remove non-existent file list of cached files
	Version 0.0.3 - Added Account files
	Version 0.0.3.2.2 - Temporarily disable cached files list because it's become hard to manage
	Version 0.1 - Different requests may have different fetching strategies
	Version 0.3.1 - Fixed service worker not returning response when neither of the options is available NETWORK-FIRST FIX ONLY
*/
//Cache name
const cacheName = 'pwa-assets';
//Files to be cached
const files = [];
/*const files =
[
	'/favicon.ico',
	'/',
	'index.html',
	'main.css',
	'registerServiceWorker.js',
	'jquery.min.js',
	'app.webmanifest',
	'Icons/',
	'Icons/icon-144.png',
	'Icons/icon-192.png',
	'Icons/icon-512.png',
	'Account/',
	'Account/register.html',
	'Account/register.js',
	'Account/login.html',
	'Account/main.css',
	'Journey/',
	'Journey/index.html',
	'Journey/main.css',
	'Journey/main.js',
	'Journey/positionManager.js',
	'Journey/journeyModeManager.js',
	'Journey/mapManager.js',
	'https://api.mapy.cz/loader.js'
];*/
const networkOnlyRequests = [
	'/Api/User/login.php',
	'/Api/User/logout.php',
	'/Api/User/register.php'
];
const networkFirstRequests = [
	'https://api.mapy.cz',
	'/Api/'
];
const defaultResponse = new Response(
	'Error fetching the resource',
	{
		status: 404,
		statusText: 'Not found'
	}
);

//Intall event
self.addEventListener('install', function(event)
{
	console.log('Service Worker has been installed.');
	//Waiting for files to cache
	event.waitUntil(
		caches.open(cacheName).then(function(cache)
		{
			cache.addAll(files);
		})
	);
});
//Activate event
self.addEventListener('activate', function(event)
{
	console.log('Service Worker has been activated.');
});
//Fetch event
self.addEventListener('fetch', function(event)
{
	//Request
	let request = event.request;
	let url = request.url;

	//Strategies
	let networkOnly = false;
	let networkFirst = false;
	//Determining strategy
	networkOnlyRequests.forEach(networkOnlyRequest =>
	{
		networkOnly = networkOnly || url.includes(networkOnlyRequest);
	});
	networkFirstRequests.forEach(networkFirstRequest =>
	{
		networkFirst = networkFirst || url.includes(networkFirstRequest);
	});
	//Checking strategy
	if (networkOnly)
	{
		console.log(`Network-only request\nUrl: \'${url}\'`);
		//Network only
		event.respondWith(
			fetch(request).then(function(response)
			{
				//Return response
				return response;
			}).catch(function(error)
			{
				//Network failed
				console.log(`Network-only fetch error\nUrl: ${url}\nError: \'${error}\'`);
				return;
			})
		);
	}
	else if (networkFirst)
	{
		console.log(`Network-first request\nUrl: \'${url}\'`);
		//Network first
		event.respondWith(
			fetch(request).then((response) =>
			{
				//Checking if request method is other than post
				if (request.method !== 'POST')
				{
					//Trying to cache response
					caches.open(cacheName).then((cache) =>
					{
						//Update
						return cache.put(request, response.clone());
					});
				}
				return response.clone();
			}).catch((error) =>
			{
				//Network failed, trying cache
				return caches.open(cacheName).then((cache) =>
				{
					//Return result from cache
					return cache.match(request).then((response) =>
					{
						return response || defaultResponse.clone();
					}).catch((error) =>
					{
						return defaultResponse.clone();
					})
				}).catch((error) =>
				{
					//Request failed
					console.log(`Network-first fetch error\nUrl: ${url}\nError: \'${error}\'`);
					return defaultResponse.clone();
				});
			})
		);
	}
	else
	{
		console.log(`Stale while revalidate request\nUrl: \'${url}\'`);
		//Stale While Revalidate
		event.respondWith(
			caches.open(cacheName).then(function(cache)
			{
				//Getting request from cache
				return cache.match(request).then(function(cacheResponse)
				{
					//Fetching
					let fetchResponse = fetch(request).then(function(response)
					{
						//Checking if request method is other than post
						if (request.method !== 'POST')
						{
							//Update
							cache.put(request, response.clone());
						}
						return response.clone();
					}).catch(function(error)
					{
						//Request failed
						console.log(`Stale while revalidate fetch error\nUrl: ${url}\nError: \'${error}\'`);
						return;
					});
					return cacheResponse || fetchResponse;
				});
			})
		);
	}
});
/*
Version: 0.1
*/
//Cache name
const cacheName = 'pwa-assets';
//Files to be cached
const files =
[
	'index.html',
	'main.css',
	'main.js',
	'jquery.min.js',
	'app.webmanifest',
	'Icons/icon-144.png',
	'Icons/icon-192.png',
	'Icons/icon-512.png',
	'/favicon.ico'
];

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
	console.log('Service Worker fetch event\nUrl: \'' + event.request.url + '\'');
	//Request
	let request = event.request;
	let url = request.url;
	//Response
	event.respondWith(
		caches.open(cacheName).then(function(cache)
		{
			//Getting request from cache
			return cache.match(request).then(function(cacheResponse)
			{
				//Fetching
				let fetchResponse = fetch(request).then(function(response)
				{
					//Update
					cache.put(request, response.clone());
				}).catch(function(error)
				{
					//Error
					console.log('Service Worker fetch error:\nUrl: \'' + url + '\'\nError: \'' + error + '\'');
				});
				return cacheResponse || fetchResponse;
			});
		})
	);
});
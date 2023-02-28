/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 12/29/22
	Version: 0.0.1
Updated on
	Version: 1

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
	Version 0.4 - IndexedDb
	Version 0.4.3 - Upload
*/
importScripts('messenger.js');
//Cache name
const cacheName = 'pwa-assets';
//Files to be cached
const files =
[
	'/favicon.ico',
	'/',
	'index.html',
	'main.css',
	'registerServiceWorker.js',
	'jquery.min.js',
	'app.webmanifest',
	'error.css',
	'error.html',
	'error.js',
	'offline.html',
	'offline.js',
	'messenger.js',
	'Assets/',
	'Assets/Fonts/',
	'Assets/Fonts/Roboto/',
	'Assets/Fonts/Roboto/Roboto-Regular.ttf',
	'Assets/Icons/',
	'Assets/Icons/Application/',
	'Assets/Icons/Application/icon-144.png',
	'Assets/Icons/Application/icon-192.png',
	'Assets/Icons/Application/icon-512.png',
	'Assets/ProfilePictures/',
	'Assets/ProfilePictures/Default/default.png',
	'Assets/ProfilePictures/Users',
	'Resources/',
	'Resources/Css/',
	'Resources/Css/Application/',
	'Resources/Css/Application/account.css',
	'Resources/Css/Application/bottombar.css',
	'Resources/Css/Application/buttons.css',
	'Resources/Css/Application/forms.css',
	'Resources/Css/Application/general.css',
	'Resources/Css/Application/icons.css',
	'Resources/Css/Application/loader.css',
	'Resources/Css/Application/records.css',
	'Resources/Css/Application/switchers.css',
	'Resources/Html/',
	'Resources/Html/Application/',
	'Resources/Html/Application/bottombar.html',
	'Resources/Html/Application/competition.html',
	'Resources/Html/Application/friendRequest.html',
	'Resources/Html/Application/journey.html',
	'Resources/Html/Application/loaderContent.html',
	'Resources/Html/Application/offlineAlert.html',
	'Resources/Html/Application/overlays.html',
	'Resources/Html/Application/topbar.html',
	'Resources/Html/Application/user.html',
	'Resources/Javascript/',
	'Resources/Javascript/Application/',
	'Resources/Javascript/Application/bottombarManager.js',
	'Resources/Javascript/Application/contentLoadManager.js',
	'Resources/Javascript/Application/forwarder.js',
	'Resources/Javascript/Application/gpxConverter.js',
	'Resources/Javascript/Application/journeyManager.js',
	'Resources/Javascript/Application/mapManager.js',
	'Resources/Javascript/Application/networkManager.js',
	'Resources/Javascript/Application/positionManager.js',
	'Resources/Javascript/Application/positionWatcher.js',
	'Resources/Javascript/Application/requestHandler.js',
	'Resources/Javascript/Application/requireSignIn.js',
	'Resources/Javascript/Application/responseManager.js',
	'Resources/Javascript/Application/switchManager.js',
	'Account/SignUp/',
	'Account/SignUp/index.html',
	'Account/SignUp/main.js',
	'Account/SignIn/',
	'Account/SignIn/index.html',
	'Account/SignIn/main.js',
	'Account/Setup/',
	'Account/View/',
	'Account/Welcome/',
	'Challenges/',
	'Challenges/Create/',
	'Challenges/Create/SearchOpponent/',
	'Journey/',
	'Journey/index.html',
	'Journey/main.css',
	'Journey/history.js',
	'Journey/archive.js',
	'Journey/New/',
	'Journey/New/index.html',
	'Journey/New/main.css',
	'Journey/New/main.js',
	'Search/',
	'Search/index.html',
	'Search/main.css',
	'Search/main.js',
	'https://api.mapy.cz/loader.js'
];
const networkOnlyRequests = [
	'/Api/User/login.php',
	'/Api/User/logout.php',
	'/Api/User/register.php',
	'/Api/User/updateInformation.php',
	'/Api/Challenges/Competitions/create.php',
	'/Api/Challenges/Competitions/respond.php',
	'/Api/Journey/changeArchivation.php',
	'/Api/Journey/save.php',
	'/Api/Social/Account/respondToFriendRequest.php',
	'/Api/Social/Account/unfriend.php'
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
//Database name
var dbName = 'pwa-db';
var dbVersion = 1;

//Intall event
self.addEventListener('install', (event) =>
{
	//Waiting for files to cache
	event.waitUntil(
		indexedDB.open(dbName, dbVersion).onupgradeneeded = (event) =>
		{
			//Getting db
			let db = event.target.result;
			let objectStore = db.createObjectStore('journeys', {
				keyPath : 'id'
			});
			objectStore.createIndex('title', 'title', { unique : false });
			objectStore.createIndex('description', 'description', { unique : false });
			objectStore.createIndex('segments', 'segments', { unique : false });
			objectStore.createIndex('status', 'status', { unique : false });
			objectStore.createIndex('readyToUpload', 'readyToUpload', { unique : false });
		},
		caches.open(cacheName).then((cache) =>
		{
			cache.addAll(files);
		})
	);
	console.log('Service Worker has been installed.');
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
				return defaultResponse.clone();
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
						//Checking if ends with .html
						if (url.includes('.html') && !url.includes('/Resources/'))
						{
							//Try get offline file
							return cache.match(new Request('offline.html')).catch(() =>
							{
								//Request failed
								console.log(`Stale while revalidate fetch error\nUrl: ${url}\nError: \'${error}\'`);
								return;
							});
						}
						else
						{
							//Request failed
							console.log(`Stale while revalidate fetch error\nUrl: ${url}\nError: \'${error}\'`);
						}
						return defaultResponse.clone();
					});
					return cacheResponse || fetchResponse;
				});
			})
		);
	}
});
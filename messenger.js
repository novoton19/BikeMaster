/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/24/23
	Version: 0.4.3
Updated on
	Version: 0.4.3

Description:
	Service worker extension, accepts messages

Changes:
	
*/
//Database name
var dbName = 'pwa-db';
var dbVersion = 1;
//Journey upload url
const journeyUploadUrl = 'Api/Journey/save.php';

//Display error
function onError(error)
{
	console.log(error);
}
//Saves journey to indexedDb
function saveJourney(journey)
{
	//Opening database
	let dbRequest = indexedDB.open(dbName, dbVersion);
	//On success
	dbRequest.onsuccess = (event) =>
	{
		//Getting databaser
		let db = event.target.result;
		//Getting transaction
		let transaction = db.transaction(['journeys'], 'readwrite');
		
		//On transaction completed
		transaction.oncomplete = () => {};
		transaction.onerror = onError;

		//Getting journeys object store
		let journeys = transaction.objectStore('journeys');
		let getRequest = journeys.get(journey.id);
		//On success
		getRequest.onsuccess = () =>
		{
			//Checking if result exists
			if (getRequest.result)
			{
				//Update
				let putRequest = journeys.put(journey);
				putRequest.onsuccess = () => {};
				putRequest.onerror = onError;
			}
			else
			{
				//Create new record
				let addRequest = journeys.add(journey);
				addRequest.onsuccess = () => {};
				addRequest.onerror = onError;
			}
		};
		getRequest.onerror = onError;
	};
	dbRequest.onerror = onError;
}
//Deletes journey from indexedDb
function deleteJourney(journey)
{
	//Opening database
	let dbRequest = indexedDB.open(dbName, dbVersion);
	//On success
	dbRequest.onsuccess = (event) =>
	{
		//Getting databaser
		let db = event.target.result;
		//Getting transaction
		let transaction = db.transaction(['journeys'], 'readwrite');
		
		//On transaction completed
		transaction.oncomplete = () => {};
		transaction.onerror = onError;

		//Getting journeys object store
		let journeys = transaction.objectStore('journeys');
		let getRequest = journeys.get(journey.id);
		//On success
		getRequest.onsuccess = () =>
		{
			//Checking if result exists
			if (getRequest.result)
			{
				//Delete
				journeys.delete(journey.id);
			}
		};
		getRequest.onerror = onError;
	};
	dbRequest.onerror = onError;
}
//Attempt to upload all available journeys
function uploadAllJourneys()
{
	//Opening database
	let dbRequest = indexedDB.open(dbName, dbVersion);
	//On success
	dbRequest.onsuccess = (event) =>
	{
		//Getting db
		let db = event.target.result;
		let transaction = db.transaction(['journeys'], 'readwrite');
		//On complete
		transaction.oncomplete = () => {};
		transaction.onerror = onError;

		//Getting journeys object store
		let journeys = transaction.objectStore('journeys');
		//Trying to get journeys
		journeys.openCursor().onsuccess = (event) =>
		{
			//Getting cursor
			let cursor = event.target.result;
			//Checking if exists
			if (cursor)
			{
				//Checking if ready to upload
				if (cursor.value.readyToUpload)
				{
					//Attempt to upload journey
					uploadJourney(cursor.value);
				}
			}
		}
	};
	dbRequest.onerror = onError;
}
//Attempts to upload journey
function uploadJourney(journey)
{
	console.log(`Attempting to upload journey #${journey.id}`);
	//Trying to upload right away
	sendUploadRequest(journey).then(
		(response) =>
		{
			//Getting response json
			response.json().then((json) =>
			{
				//Checking if success
				if (json.success)
				{
					//Successfully saved on server, delete local copy
					deleteJourney(journey);
					console.log(`Journey #${journey.id} uploaded successfully`);
				}
				else//if (!json.success)
				{
					//Save to indexedDb
					saveJourney(journey);
				}
			}).catch(() => saveJourney(journey))
		}
	).catch(() => saveJourney(journey));
}
//Attempts to upload journey to the server
function sendUploadRequest(journey)
{
	//Create journey copy
	journey = Object.fromEntries(Object.entries(journey));
	//Getting description and title
	let title = journey.title;
	let description = journey.description;
	//Remove some properties from journey
	delete journey.id;
	delete journey.status;
	delete journey.positionManager;
	delete journey.readyToUpload;
	delete journey.title;
	delete journey.description;
	//https://stackoverflow.com/questions/35325370/how-do-i-post-a-x-www-form-urlencoded-request-using-fetch
	//Changing content type to application/x-www-form-urlencoded
	function encodeObject(obj, prefix = '')
	{
		//Body of encoded object
		let body = '';
		//Encoding
		for (let key in obj)
		{
			//Getting value
			let value = obj[key];
			//Checking if body has length
			if (body.length)
			{
				body += '&';
			}
			//Checking if value is object
			if (typeof(value) === 'object')
			{
				//Encoding
				let encodedKey = encodeURIComponent(key);
				let nextPrefix = prefix;
				//Checking if prefix has length
				if (nextPrefix.length)
				{
					nextPrefix += encodedKey + ']' + '[';
				}
				else
				{
					nextPrefix += encodedKey + '[';
				}
				//Getting object
				let encodedValue = encodeObject(value, nextPrefix);
				//Adding key and value
				body += encodedValue;
			}
			else
			{
				//Encoding
				let encodedKey = prefix + encodeURIComponent(key) + (prefix.length ? ']' : '');
				let encodedValue = encodeURIComponent(value);
				//Adding key and value
				body += encodedKey + '=' + encodedValue;
			}
		}
		return body;
	}
	let body = encodeObject({ track : journey, title : title, description : description });
	//Creating request
	let request = new Request(
		journeyUploadUrl,
		{
			method : 'POST',
			headers : {
				'Content-Type' : 'application/x-www-form-urlencoded;charset=UTF-8'
			},
			body : body
		}
	);
	return fetch(request);
}

self.addEventListener('message', (event) =>
{
	//Getting data
	let data = event.data;
	//Checking if data exists
	if (!data)
	{
		return;
	}
	//Checking message
	if (data.type === 'SAVE_JOURNEY')
	{
		//Getting journey
		let journey = data.journey;
		//Checking if journey exists
		if (!journey)
		{
			return;
		}
		//Checking if journey is ready for upload
		if (journey.readyToUpload)
		{
			//Uploading journey
			uploadJourney(journey);
		}
		else
		{
			//Journey not ready for upload
			saveJourney(journey);
		}
	}
	else if (data.type === 'NETWORK_ONLINE')
	{
		//User is online, attempt to upload
		uploadAllJourneys();
	}
});

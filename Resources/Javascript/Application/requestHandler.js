/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/11/23 05:21pm
	Version: 0.3
Updated on
	Version: 0.3.5

Description:
	Handles requests

Changes:
	Version 0.3.5 - Support 0 requests
*/
//Reason IDs
let requestHandlerReasonIDs = {
	Valid : 0,
	NotSucceeded : 1,
	NotObject : 2,
	TimedOut : 3,
	Aborted : 4,
	Error : 5,
	Offline : 6,
	NotFound : 7,
	OtherStatus : 8
}
//Sends a request and returns result
function sendRequest(url, data = {}, method = 'get', processData = undefined, contentType = undefined, timeout = 5000)
{
	//Return promise
	return new Promise((resolve, reject) =>
	{
		$.ajax({
			url : url,
			data : data,
			method : method,
			processData : processData,
			contentType : contentType,
			timeout : 5000,
			success : (response) =>
			{
				//Whether valid
				let valid = false;
				let structured = false;
				let reasonID = undefined;
				let reason = undefined;
				//Checking if requested url includes .php
				if (url.includes('.php'))
				{
					//Checking type
					if (typeof(response) === 'object')
					{
						//Has a structure
						structured = true;
						//Checking if succeeded
						if (response.success)
						{
							valid = true;
						}
						else//if (!response.success)
						{
							reasonID = requestHandlerReasonIDs.NotSucceeded;
							reason = response.reason;
						}
					}
					else//if (typeof(response) !== 'object')
					{
						reasonID = requestHandlerReasonIDs.NotObject;
						reason = 'Server failed at formatting the request correctly';
					}
				}
				else//if (!url.includes('.php'))
				{
					valid = true;
					reasonID = requestHandlerReasonIDs.Valid;
				}
				//Checking if valid
				if (valid)
				{
					resolve(response);
				}
				else//if (!valid)
				{
					reject({
						response : response,
						structured : structured,
						reasonID : reasonID,
						reason : reason
					});
				}
			},
			error : (response, textStatus) =>
			{
				//Whether valid
				let reasonID = undefined;
				let reason = undefined;
				//Checking status
				if (textStatus === 'timeout')
				{
					reasonID = requestHandlerReasonIDs.TimedOut;
					reason = 'Request took too long';
				}
				else if (textStatus === 'abort')
				{
					reasonID = requestHandlerReasonIDs.Aborted;
					reason = 'Request has been aborted';
				}
				else if (textStatus === 'parsererror')
				{
					reasonID = requestHandlerReasonIDs.Error;
					reason = 'Server experienced an error while processing the request';
				}
				else//if (textStatus === 'error')
				{
					//Checking if offline
					if (!navigator.onLine)
					{
						reasonID = requestHandlerReasonIDs.Offline;
						reason = 'You are offline';
					}					
					else//if (navigation.onLine)
					{
						//Checking status
						if (response.status === 404)
						{
							reasonID = requestHandlerReasonIDs.NotFound;
							reason = 'Unable to load resource';
						}
						else//if (response.status !== 404)
						{
							reasonID = requestHandlerReasonIDs.OtherStatus;
							reason = response.statusText;
						}
					}
				}
				reject({
					response : response,
					structured : false,
					reasonID : reasonID,
					reason : reason
				});
			}
		});
	});
}
//Sends multiple requests
function sendMultipleRequests(requests = [])
{
	//Checking if there are any requests
	if (!requests.length)
	{
		//Resolved
		return new Promise((resolve, reject) => resolve([]));
	}
	return new Promise((resolve, reject) =>
	{
		Promise.all(requests.map((request) =>
		{
			return sendRequest(
				request.url,
				request.data || {},
				request.method || 'get',
				request.timeout || 5000
			);
		})).then((responses) =>
		{
			resolve(responses);
		}).catch((information) =>
		{
			reject(information);
		});
	});
}
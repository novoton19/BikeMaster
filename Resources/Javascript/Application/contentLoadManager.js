/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/09/23 11:04pm
	Version: 0.2
Updated on
	Version: 0.3

Description:
	Content loading manager

Changes:
	Version 0.3 - Added backup treatment option, collaboration with forwarder and requestHandler
*/
//Path to required files
const loaderCssPath = '/Pwa/BikeMaster/Resources/Css/Application/loader.css';
const loaderHtmlPath = '/Pwa/BikeMaster/Resources/Html/Application/loaderContent.html';
//Getting current script name
const contentLoadManagerName = document.currentScript.src.split('/').pop();

//Waiting for document to load
$(document).ready(() =>
{
	//Creating network manager
	let networkManager = new NetworkManager();
	//On html loaded
	function onDataLoaded(responses)
	{
		//Getting html
		let html = responses[0];


		//Adding css
		$('head').append($('<link>').attr('rel', 'stylesheet').attr('href', loaderCssPath));
		

		//Getting loaders
		const loaders = $('[loader]');
		//Setting up loaders
		loaders.each((index, loader) =>
		{
			//Gathering loader information
			let onRequest = $(loader).attr('onRequest');
			let onSuccess = $(loader).attr('onSuccess');
			let onFail = $(loader).attr('onFail');
			//Checking if should have extra treatment for backups
			let backupTreatment = $(loader).attr('backupTreatment') !== undefined;

			//Checking if functions exist
			if (!(onRequest && onSuccess && onFail) || !(window[onRequest] && window[onSuccess] && window[onFail]))
			{
				//One of the functions is not specified
				console.log('Loader is missing one of the following functions: onRequest, onSuccess, onFail', loader);
				return;
			}
			//Adding html
			$(loader).append(html);
			//Getting reminders
			let reminders = $(loader).find('.reminders');
			let rOffline = reminders.find('.reminder.offline');
			let rOfflineDetails = rOffline.find('.details');
			let rBackup = reminders.find('.reminder.backup');
			let rRefresh = reminders.find('.reminder.refresh');
			//Getting overlays
			let overlays = $(loader).find('.overlays');
			let oOffline = overlays.find('.overlay.offline');
			let oFetchFail = overlays.find('.overlay.fetchFail');
			let oFetchFailText = oFetchFail.find('h3');
			let oLoading = overlays.find('.overlay.loading');

			//Buttons
			let backupButtons = $(loader).find('.backupButton');
			let retryButtons = $(loader).find('.retryButton');
			let refreshButtons = $(loader).find('.refreshButton');
			let signInButtons = $(loader).find('.signInButton');

			//Whether backup exists
			let backupExists = false;
			let backup = undefined;
			//Whether SignIn is required in order to access the content
			let mustSignIn = false;
			//Whether result has been loaded
			let resultLoaded = false;
			let resultIsBackup = false;
			//Update button visibility
			function updateButtons()
			{
				if (backupExists && backupTreatment)
				{
					backupButtons.show(200);
				}
				else//if (!backupExists || !backupTreatment)
				{
					backupButtons.hide(200);
				}
				if (mustSignIn)
				{
					signInButtons.show(200);
				}
				else//if (!mustSignIn)
				{
					signInButtons.hide(200);
				}
			}
			//Shows reminder
			function showReminder(reminder)
			{
				//Hide overlays
				overlays.hide(200);
				rOffline.hide(200);
				rBackup.hide(200);
				rRefresh.hide(200);
				reminders.show(200);
				reminder.show(200);
			}
			//Shows overlay
			function showOverlay(overlay)
			{
				reminders.hide(200);
				oOffline.hide(200);
				oFetchFail.hide(200);
				oLoading.hide(200);
				overlays.show(200);
				overlay.show(200);
			}
			//Hides everything
			function hideEverything()
			{
				reminders.hide(200);
				overlays.hide(200);
			}
			//Loads content
			function loadContent()
			{
				//Show loading overlay
				showOverlay(oLoading);
				//Result not loaded
				resultLoaded = false;
				resultIsBackup = false;

				//Getting requests
				let requests = window[onRequest]();
				//Checking if urls exist
				if (!requests)
				{
					//Successfully loaded since there is nothing to load
					window[onSuccess]();
					//Showing refresh reminder
					showReminder(rRefresh);
					return;
				}
				sendMultipleRequests(requests).then((responses) =>
				{
					//Checking if online or doesn't have backup treatment
					if (networkManager.online || !backupTreatment)
					{
						resultLoaded = true;
						//Send responses
						window[onSuccess](responses);
						//Checking if online
						if (networkManager.online)
						{
							//Show refresh reminder
							showReminder(rRefresh);
						}
						else//if (!networkManager.online)
						{
							//Show offline reminder
							showReminder(rOffline);
						}
						return;
					}
					else//if (!networkManager.online || backupTreatment)
					{
						//Backup exists
						backupExists = true;
						backup = responses;
						//Update buttons
						updateButtons();
						showOverlay(oOffline);
						return;
					}
				}).catch((information) =>
				{
					//Backup doesn't exist or is invalid
					backupExists = false;
					backup = undefined;

					//Checking if online
					if (!networkManager.online)
					{
						//Show offline overlay
						showOverlay(oOffline);
						return;
					}
					else//if (networkManager.online)
					{
						//Text to show
						let text;
						//Checking if fail function exists
						if (window[onFail])
						{
							//Checking information reason ID
							if (information.reasonID === requestHandlerReasonIDs.NotSucceeded && information.response.reasonID === 5)
							{
								text = 'You must sign in in order to access this content';
								mustSignIn = true;
							}
							else//if (information.reasonID !== requestHandlerReasonIDs.NotSucceeded || information.response.reasonID !== 5)
							{
								text = window[onFail](information);
								mustSignIn = false;
							}
						}
						if (!text)
						{
							text = 'Request did not succeed';
						}
						updateButtons();
						//Add text
						oFetchFailText.text(text);
						showOverlay(oFetchFail);
						return;
					}
				});
			}


			//Update buttons on default
			updateButtons();
			loadContent();
			//Click event listeners
			retryButtons.click(loadContent);
			refreshButtons.click(loadContent);
			backupButtons.click(() =>
			{
				//Change state
				resultIsBackup = true;
				resultLoaded = true;
				window[onSuccess](backup);
				//Show reminder
				showReminder(rBackup);
			});
			//Listening for network changes
			networkManager.addEventListener('onStatusChanged', () =>
			{
				//Checking if result loaded
				if (!resultLoaded)
				{
					return;
				}
				//Checking if online
				if (networkManager.online)
				{
					//Checking if backup loaded
					if (resultIsBackup && backupTreatment)
					{
						showReminder(rBackup);
					}
					else//if (!resultIsBackup)
					{
						showReminder(rRefresh);
					}
				}
				else//if (!networkManager.online)
				{
					//Checking if backup loaded
					if (resultIsBackup && backupTreatment)
					{
						rOfflineDetails.show(200);
					}
					else //if (!resultIsBackup)
					{
						rOfflineDetails.hide(200);
					}
					//Show offline reminder
					showReminder(rOffline);
				}
			});
		});
	}
	//On request completed
	function onDataFailed(information)
	{
		//Checking if online
		if (networkManager.online)
		{
			//Forwarding to error page
			forwardToErrorPage(
				information.reason,
				contentLoadManagerName
			);
		}
		else//if (!networkManager.online)
		{
			forwardToOfflinePage(

			);
		}
	}
	//Requesting loader html
	sendMultipleRequests([
		{ url: loaderHtmlPath }
	]).then(onDataLoaded).catch(onDataFailed);
});
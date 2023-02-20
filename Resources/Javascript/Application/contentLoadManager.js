/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/09/23 11:04pm
	Version: 0.2
Updated on
	Version: 0.3.5

Description:
	Content loading manager

Changes:
	Version 0.3 - Added backup treatment option, collaboration with forwarder and requestHandler
	Version 0.3.5 - Added continuous loading support, removed transitions
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
			//Checking if loader is continuous
			let continuous = $(loader).attr('continuous') !== undefined;
			let relativeID = $(loader).attr('relativeID');
			let relative = $(`#${relativeID}`);

			//Checking if functions exist
			if (!(onRequest && onSuccess && onFail) || !(window[onRequest] && window[onSuccess] && window[onFail]))
			{
				//One of the functions is not specified
				console.log('Loader is missing one of the following functions: onRequest, onSuccess, onFail', loader);
				return;
			}
			//Checking if relative exists
			if (continuous && !relative.length)
			{
				//Relative doesn't exist
				console.log('Continuous loader is missing relative', loader);
				return;
			}
			//Adding html
			let parent = $(loader).parent();
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
			let loadingContent = false;
			let resultLoaded = false;
			let resultIsBackup = false;
			//Update button visibility
			function updateButtons()
			{
				if (backupExists && backupTreatment)
				{
					backupButtons.show();
				}
				else//if (!backupExists || !backupTreatment)
				{
					backupButtons.hide();
				}
				if (mustSignIn)
				{
					signInButtons.show();
				}
				else//if (!mustSignIn)
				{
					signInButtons.hide();
				}
			}
			//Shows reminder
			function showReminder(reminder)
			{
				//Hide overlays
				overlays.hide();
				rOffline.hide();
				rBackup.hide();
				rRefresh.hide();
				reminders.show();
				reminder.show();
			}
			//Shows overlay
			function showOverlay(overlay)
			{
				reminders.hide();
				oOffline.hide();
				oFetchFail.hide();
				oLoading.hide();
				overlays.show();
				overlay.show();
			}
			//Hides everything
			function hideEverything()
			{
				reminders.hide();
				overlays.hide();
			}
			//Loads content
			function loadContent()
			{
				//Checking if not loading content
				if (loadingContent)
				{
					return;
				}
				loadingContent = true;
				//Show loading overlay
				showOverlay(oLoading);
				//Result not loaded
				resultLoaded = false;
				resultIsBackup = false;

				//Getting requests
				let requests = window[onRequest]();
				//Checking if urls exist
				if (!requests || !requests.length)
				{
					//Successfully loaded since there is nothing to load
					window[onSuccess]();
					//Checking if continuous
					if (!continuous)
					{
						//Show refresh reminder
						showReminder(rRefresh);
					}
					else
					{
						//Refresh reminder not required, the content will be updated on scroll
						hideEverything();
					}
					loadingContent = false;
					return;
				}
				sendMultipleRequests(requests).then((responses) =>
				{
					loadingContent = false;
					//Checking if online or doesn't have backup treatment
					if (networkManager.online || !backupTreatment)
					{
						resultLoaded = true;
						//Checking if online
						if (networkManager.online)
						{
							//Checking if continuous
							if (!continuous)
							{
								//Show refresh reminder
								showReminder(rRefresh);
							}
							else
							{
								//Refresh reminder not required, the content will be updated on scroll
								hideEverything();
							}
						}
						else//if (!networkManager.online)
						{
							//Show offline reminder
							showReminder(rOffline);
						}
						//Send responses
						window[onSuccess](responses);
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
					loadingContent = false;

					//Checking if online
					if (!networkManager.online)
					{
						//Show offline overlay
						showOverlay(oOffline);
						loadingContent = false;
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
			//On scroll
			function onScroll()
			{
				//console.log('scroll');
				//Checking if not loading next page already
				if (loadingContent)
				{
					return;
				}
				//Getting scroll position
				//let scrollTop = $(relative).scrollTop();
				//let innerHeight = $(relative).innerHeight();
				//let scrollHeight = $(relative).prop('scrollHeight');
				//let loaderHeight = $(loader).height();
				//console.log('scrollTop', scrollTop, 'innerHeight', innerHeight, 'scrollHeight', scrollHeight, 'loaderHeight', loaderHeight);
				//Checking if it's time to load next page
				//if (scrollHeight - scrollTop - loaderHeight <= innerHeight)
				//{
					//Load next page
					loadContent();
				//}
			}


			//Update buttons on default
			updateButtons();
			//Checking if continuous
			if (continuous)
			{
				//Scroll events
				//$(window).scroll(onScroll);
				$(relative).on('DOMSubtreeModified', onScroll);
				//$(window).resize(onScroll);
				//onScroll();
			}
			else
			{
				loadContent();
			}
			//Click event listeners
			retryButtons.click(loadContent);
			//Checking if continuous
			if (!continuous)
			{
				//Adding refresh button click events
				refreshButtons.click(loadContent);
			}
			else
			{
				//Hide refresh buttons
				refreshButtons.hide();
			}
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
						//Checking if continuous
						if (!continuous)
						{
							//Add option to refresh
							showReminder(rRefresh);
						}
						else
						{
							//Next request on scroll
							hideEverything();
						}
					}
				}
				else//if (!networkManager.online)
				{
					//Checking if backup loaded
					if (resultIsBackup && backupTreatment)
					{
						rOfflineDetails.show();
					}
					else //if (!resultIsBackup)
					{
						rOfflineDetails.hide();
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
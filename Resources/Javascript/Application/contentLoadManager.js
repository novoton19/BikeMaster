/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/09/23 11:04pm
	Version: 0.1.2
Updated on
	Version: 0.1.2

Description:
	Content loading manager

Changes:
	 
*/
//Waiting for document to load
$(document).ready(() =>
{
	//Creating network manager
	let networkManager = new NetworkManager();

	//Adding css
	$('head').append($('<link>').attr('rel', 'stylesheet').attr('href', '/Pwa/BikeMaster/Resources/Css/Application/loader.css'));

	//Getting all loaders
	const loaders = $('[loader]');
	//Setting up loaders
	loaders.each((index, loader) =>
	{
		//Getting url
		let url = $(loader).attr('loaderUrl');
		//Getting action functions
		let onRequest = $(loader).attr('onRequest');
		let onSuccess = $(loader).attr('onSuccess');
		let onFail = $(loader).attr('onFail');
		//Getting html
		$.get('/Pwa/BikeMaster/Resources/Html/Application/loaderContent.html', {}, (contents) =>
		{
			//Appending contents
			$(loader).append(contents);
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
			let mustSignIn = false;
			let resultLoaded = false;
			let resultIsBackup = false;
			//Update button visibility
			function updateButtons()
			{
				if (backupExists)
				{
					backupButtons.show(200);
				}
				else//if (!backupExists)
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
			//Attempt to get result
			function sendRequest()
			{
				//Change state
				resultLoaded = false;
				//Calling on request action
				if (window[onRequest])
				{
					window[onRequest]();
				}
				//Showing loading overlay
				showOverlay(oLoading);
				//Getting content
				setTimeout(() =>
				{
					$.ajax({
						url : url,
						timeout : 5000,
						success : (response) =>
						{
							//Checking if online
							if (networkManager.online)
							{
								//Checking if valid response given
								if (typeof(response) === 'object')
								{
									//Checking if success
									if (response.success)
									{
										//Result loaded
										resultLoaded = true;
										//Response from server (or backup on slow network)
										showReminder(rRefresh);
										//Checking if on success function exists
										if (window[onSuccess])
										{
											window[onSuccess](response);
										}
									}
									else//if (!response.success)
									{
										//Text to show
										let text;
										//Checking if fail function exists
										if (window[onFail])
										{
											text, mustSignIn = window[onFail](response);
										}
										if (!text)
										{
											text = 'Request did not succeed';
										}
										updateButtons();
										//Add text
										oFetchFailText.text(text);
										showOverlay(oFetchFail);
									}
								}
								else//if (typeof(response) !== 'object')
								{
									oFetchFailText.text('Server responded with invalid information (1)');
									showOverlay(oFetchFail);
								}
							}
							else//if (!networkManager.online)
							{
								//Backup exists
								backupExists = true;
								backup = response;
								updateButtons();
								//User is offline
								showOverlay(oOffline);
							}
						},
						error : (response, textStatus) =>
						{
							//Backup 100% doesn't exist
							backupExists = false;
							updateButtons();
							//Checking if online
							if (!networkManager.online)
							{
								//Show offline overlay
								showOverlay(oOffline);
							}
							else//if (networkManager.online)
							{
								//Show error
								oFetchFailText.text(ResponseManager.getErrorText(response, textStatus));
								showOverlay(oFetchFail);
							}
						}
					});
				}, 400);
			}
			//Update buttons on default
			updateButtons();
			sendRequest();
			//Click event listeners
			retryButtons.click(sendRequest);
			refreshButtons.click(sendRequest);
			backupButtons.click(() =>
			{
				//Change state
				resultIsBackup = true;
				resultLoaded = true;
				//Call event
				if (window[onSuccess])
				{
					window[onSuccess](backup);
				}
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
					if (resultIsBackup)
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
					if (resultIsBackup)
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
	});
});
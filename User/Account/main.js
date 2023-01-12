/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/05/23 03:52pm
	Version: 0.0.3.2.2
Updated on
	Version: 0.1

Description:
	Load account information

Changes:
	Version 0.1 - Added topbar manager, network manager, offline treatment
*/
$(document).ready(() =>
{
	//Creating managers
	const topbarManager = new TopbarManager();
	const networkManager = new PageNetworkManager();

	//Getting overlays
	const loadingOverlay = $('.loadingOverlay');
	const loadErrorOverlay = $('.loadErrorOverlay');
	loadErrorOverlay.removeClass('uk-hidden');
	loadErrorOverlay.hide();
	const loadErrorText = loadErrorOverlay.find('.errorText');
	const reloadButton = loadErrorOverlay.find('.reloadButton');


	//Limits functionality when offline
	function onNetworkStateChanged()
	{
		//Checking if online
		if (!networkManager.online)
		{
			//Disable button, enable tooltip
			reloadButton.attr('disabled', '');
			reloadButton.attr('uk-tooltip', 'You are currently offline');
		}
		else //if (networkManager.online)
		{
			///Enable button, disable tooltip
			reloadButton.removeAttr('disabled');
			reloadButton.removeAttr('uk-tooltip');
		}
	}
	//Displays error overlay
	function showLoadError(message)
	{
		//Clearing error text
		loadErrorText.text(message);
		loadErrorOverlay.show(400);
	}


	//Tries to load login status
	function loadLoginStatus()
	{
		//Showing loading overlay
		loadErrorOverlay.hide(400);
		loadingOverlay.show(400, () =>
		{
			//Requesting login status
			$.ajax({
				url : '../../Api/User/status.php',
				timeout : 5000,
				success : (response) =>
				{
					//Hide loading overylay
					loadingOverlay.hide(400);
					//Checking if success
					if (response.success)
					{
						//Checking if not logged in
						if (!response.loggedIn)
						{
							//Redirect to login
							window.location = '../Login/';
						}
						else //if(response.loggedIn)
						{
							//Logged in, correct page
						}
					}
					else //if (!response.success)
					{
						//Showing error
						showLoadError(`Failed to get sign in information: ${response.reason}`);
					}
				},
				error : (response, textStatus) =>
				{
					//Hide loading overylay
					loadingOverlay.hide(400);
					//Showing error
					showLoadError('Failed to get sign in information: ' + ResponseManager.getErrorText(response, textStatus));
				}
			});
		});
	}


	//Network changes
	networkManager.addEventListener('onStatusChanged', () => onNetworkStateChanged());
	onNetworkStateChanged();
	
	//Login status requests
	reloadButton.click(() =>
	{
		loadLoginStatus();
	});
	//Loading login status
	loadLoginStatus();
});
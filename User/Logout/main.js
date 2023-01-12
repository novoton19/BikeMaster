/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/03/23 10:13pm
	Version: 0.1
Updated on
	Version: 0.1

Description:
	Login

Changes:
*/
$(document).ready(() =>
{
	//Creating TopbarManager
	const topbarManager = new TopbarManager();
	const networkManager = new PageNetworkManager();

	//Getting logoutbutton
	const logoutButton = $('#signoutButton');
	//Getting form reason
	const formReason = $('.formReason');
	formReason.removeClass('uk-hidden');
	formReason.hide();
	//Getting logout action
	const logoutAction = logoutButton.attr('js-action');

	//Limits functionality on offline
	function onNetworkStateChanged()
	{
		//Checking if online
		if (!networkManager.online)
		{
			//Disable button, enable tooltip
			logoutButton.attr('disabled', '');
			logoutButton.attr('uk-tooltip', 'You are currently offline');
		}
		else //if (networkManager.online)
		{
			///Enable button, disable tooltip
			logoutButton.removeAttr('disabled');
			logoutButton.removeAttr('uk-tooltip');
		}
	}
	//Shows error message
	function showError(message)
	{
		//Clearing previous error
		formReason.text('');
		formReason.addClass('uk-alert-danger');
		formReason.text(message);
		formReason.show(400);
	}


	//Adding event listener
	networkManager.addEventListener('onStatusChanged', () => onNetworkStateChanged());
	onNetworkStateChanged();
	//On click
	logoutButton.click((event) =>
	{
		//Prevent sending
		event.preventDefault();
		//Hide previous error
		formReason.hide(400, () =>
		{
			//Request url
			$.ajax({
				url : logoutAction,
				dataType : 'json',
				timeout : 2000,
				success : (response) =>
				{
					//Getting status
					let success = response.success;
					//Getting reason
					let reason = response.reason;
					//Checking if success
					if (success)
					{
						//Refreshing account
						topbarManager.refreshAccount();
						//Redirect to account
						window.location = '../Login/';
					}
					else //if (!success)
					{
						//Show error
						showError(reason);
					}
				},
				error : (response, textStatus) =>
				{
					//Checking status
					if (textStatus === 'timeout')
					{
						showError('Sign in error: Timed out');
					}
					else if (textStatus === 'abort')
					{
						showError('Sign in error: Aborted');
					}
					else if (textStatus === 'parsererror')
					{
						showError('Sign in error: Server responded with invalid information');
					}
					else //if (textStatus === 'error')
					{
						//Checking if offline
						if (!networkManager.online)
						{
							showError('Sign in error: Offline');
						}					
						else
						{
							//Checking status
							if (response.status === 404)
							{
								showError('Sign in error: Action page doesn\'t exist');
							}
							else
							{
								showError('Sign in error: ' + response.statusText);
							}
						}
					}
				}
			});
		});
	});
	//Requesting login status
	$.ajax({
		url : '../../Api/User/status.php',
		timeout : 2000,
		success : (response) =>
		{
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
					//Not logged in, correct page
				}
			}
			else //if (!response.success)
			{
				//Show notification
				UIkit.notification({
					message : 'Failed to load login information',
					status : 'danger',
					pos : 'top-center'
				});
			}
		},
		error : () =>
		{
			//Show notification
			UIkit.notification({
				message : 'Failed to load login information',
				status : 'danger',
				pos : 'top-center'
			});
		}
	});
});
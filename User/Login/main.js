/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/03/23 10:13pm
	Version: 0.0.3
Updated on
	Version: 0.1

Description:
	Login

Changes:
	Version 0.0.3.2.2 - Update redirect url
	Version 0.1 - Added topbar manager, network manager, offline treatment
*/
$(document).ready(() =>
{
	//Creating managers
	const topbarManager = new TopbarManager();
	const networkManager = new PageNetworkManager();
	const contentManager = new ContentManager(loadLoginStatus);

	//Getting login form
	const loginForm = $('#loginForm');
	const loginAction = loginForm.attr('js-action');
	const loginButton = loginForm.find('#submitButton');
	const formReason = loginForm.find('.formReason');
	formReason.removeClass('uk-hidden');
	formReason.hide();


	//Limits functionality when offline
	function onNetworkStateChanged()
	{
		//Checking if online
		if (!networkManager.online)
		{
			//Disable button, enable tooltip
			loginButton.attr('disabled', '');
			loginButton.attr('uk-tooltip', 'You are currently offline');
		}
		else //if (networkManager.online)
		{
			///Enable button, disable tooltip
			loginButton.removeAttr('disabled');
			loginButton.removeAttr('uk-tooltip');
		}
	}
	//Shows error message
	function showFormError(message)
	{
		//Clearing previous error
		formReason.text(message);
		formReason.addClass('uk-alert-danger');
		formReason.show(400);
	}


	//Tries to load login status
	function loadLoginStatus()
	{
		//Requesting login status
		$.ajax({
			url : '../../Api/User/status.php',
			timeout : 5000,
			success : (response) =>
			{
				//Checking if success
				if (response.success)
				{
					//Checking if logged in
					if (response.loggedIn)
					{
						//Redirect to account
						window.location = '../Account/';
					}
					else //if(!response.loggedIn)
					{
						//Not logged in, correct page
						//Getting account
						let account = response.account;
						//Adding username
						loginForm.find('#username').text(account.username);
					}
					contentManager.setStateSolved();
				}
				else //if (!response.success)
				{
					//Showing error
					contentManager.setStateError('Error while loading session information: ' + response.reason);
				}
			},
			error : (response, textStatus) =>
			{
				contentManager.setStateError('Error while loading session information: ' + ResponseManager.getErrorText(response, textStatus));
			}
		});
	}
	//Tries to send login information
	function sendLogin()
	{
		//Hide previous error
		formReason.hide(400, () =>
		{
			//Request url
			$.ajax({
				url : loginAction,
				type : 'POST',
				dataType : 'json',
				data : loginForm.serialize(),
				timeout : 5000,
				success : (response) =>
				{
					//Getting status
					let success = response.success;
					//Getting inputs
					let inputs = response.inputs;
					//Getting reason
					let reason = response.reason;
					//Checking if success
					if (success)
					{
						topbarManager.refreshAccount();
						//Redirect to account
						window.location = '../Account/';
					}
					else //if (!success)
					{
						//Show error
						showFormError(reason);
					}
				},
				error : (response, textStatus) =>
				{
					//Showing error
					showFormError('Sign in failed: ' + ResponseManager.getErrorText(response, textStatus));
				}
			});
		});
	}


	//Network changes
	networkManager.addEventListener('onStatusChanged', () => onNetworkStateChanged());
	onNetworkStateChanged();
	
	//Login requests
	loginForm.submit((event, data) =>
	{
		//Prevent sending
		event.preventDefault();
		//Sending login
		sendLogin();
	});
});
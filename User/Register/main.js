/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/02/23
	Version: 0.0.3
Updated on
	Version: 0.1

Description:
	Registration

Changes:
	Version 0.0.3.2.2 - Update redirect url
	Version 0.1 - Added topbar manager, network manager, offline treatment
*/
$(document).ready(() =>
{
	//Creating TopbarManager
	const topbarManager = new TopbarManager();
	const networkManager = new PageNetworkManager();
	const contentManager = new ContentManager(loadLoginStatus);


	//Getting registration form
	const registrationForm = $('#registrationForm');
	const registrationAction = registrationForm.attr('js-action');
	const registrationButton = registrationForm.find('#submitButton');
	const formReason = registrationForm.find('.formReason');
	formReason.removeClass('uk-hidden');
	formReason.hide();


	//Limits functionality when offline
	function onNetworkStateChanged()
	{
		//Checking if online
		if (!networkManager.online)
		{
			//Disable button, enable tooltip
			registrationButton.attr('disabled', '');
			registrationButton.attr('uk-tooltip', 'You are currently offline');
		}
		else //if (networkManager.online)
		{
			///Enable button, disable tooltip
			registrationButton.removeAttr('disabled');
			registrationButton.removeAttr('uk-tooltip');
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
						registrationForm.find('#username').text(account.username);
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
	function sendRegistration()
	{
		//Hide previous error
		formReason.hide(400, () =>
		{
			//Request url
			$.ajax({
				url : registrationAction,
				type : 'POST',
				dataType : 'json',
				data : registrationForm.serialize(),
				timeout : 5000,
				success : (response) =>
				{
					//Getting status
					let success = response.success;
					//Getting inputs
					let inputs = response.inputs;
					let inputReasons = response.inputReasons;
					//Getting reason
					let reason = response.reason;
					//Checking if success
					if (success)
					{
						topbarManager.refreshAccount();
						//Redirect to success page
						window.location = '../RegistrationComplete/';
					}
					else //if (!success)
					{
						//Show error
						console.log(response);
						showFormError(reason);
						//Showing input reasons
						Object.keys(inputReasons).forEach(inputName =>
						{
							//Getting inputReason
							let inputReason = inputReasons[inputName];
							let valid = inputReason.valid;
							//Getting input
							let input = $('#' + inputName);
							//Removing classes
							input.removeClass('uk-form-success');
							input.removeClass('uk-form-danger');
							//Checking if valid
							if (valid)
							{
								input.addClass('uk-form-success');
								//Removing tooltip
								input.attr('uk-tooltip', 'title: ;');
							}
							else
							{
								input.addClass('uk-form-danger');
								//Adding tooltip
								input.attr('uk-tooltip', inputReason.reason);
							}
						});
					}
				},
				error : (response, textStatus) =>
				{
					//Showing error
					showFormError('Sign up failed: ' + ResponseManager.getErrorText(response, textStatus));
				}
			});
		});
	}


	//Network changes
	networkManager.addEventListener('onStatusChanged', () => onNetworkStateChanged());
	onNetworkStateChanged();
	
	//Login requests
	registrationForm.submit((event, data) =>
	{
		//Prevent sending
		event.preventDefault();
		//Sending login
		sendRegistration();
	});
});
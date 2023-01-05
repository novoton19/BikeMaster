/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/05/23 03:52pm
	Version: 0.0.3.2.2
Updated on
	Version: 0.0.3.2.2

Description:
	Load account information

Changes:
	 
*/
$(document).ready(() =>
{
	//Getting login form
	const loginForm = $('#LoginForm');
	//Getting login action
	const loginAction = loginForm.attr('js-action');
	//On submit
	loginForm.submit((event, data) =>
	{
		//Prevent sending
		event.preventDefault();
		console.log("wait");
		//Request url
		$.post(loginAction, loginForm.serialize(), (response) =>
		{
			//Getting status
			let success = response.success;
			//Getting inputs
			let inputs = response.inputs;
			//Getting reason
			let reason = response.reason;
			//Displaying reason
			$('#Reason').text(reason);
			console.log(response);
			//Checking if success
			if (success)
			{
				//Redirect to main menu
				location.href = '../';
			}
		});
	});
});
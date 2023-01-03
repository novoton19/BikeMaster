/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/02/23
	Version: 0.0.3
Updated on
	Version: 0.0.3

Description:
	Registration

Changes:

*/
$(document).ready(() =>
{
	//Getting registration form
	const registrationForm = $('#RegistrationForm');
	//Getting registration action
	const registrationAction = registrationForm.attr('js-action');

	//On submit
	registrationForm.submit((event, data) =>
	{
		//Prevent sending
		event.preventDefault();
		console.log("wait");
		//Request url
		$.post(registrationAction, registrationForm.serialize(), (response) =>
		{
			//Getting status
			let success = response.success;
			//Getting inputs
			let inputs = response.inputs;
			//Getting reason
			let reason = response.reason;
			//Getting input reasons
			let inputReasons = response.inputReasons;
			//Displaying reason
			$('#Reason').text(reason);
			//Displaying input reasons
			Object.keys(inputReasons).forEach(inputName =>
				{
					//Displaying reason
					$('#' + inputName + 'Reason').text(inputReasons[inputName].reason);
				}
			);
			//Checking if success
			if (success)
			{
				//Redirect to login.html
				location.href = 'login.html';
			}
		});
	});
});
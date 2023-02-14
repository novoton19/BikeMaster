/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/14/23 03:03pm
	Version: 0.3.3
Updated on
	Version: 0.3.3

Description:
	Javascript specifically designed for description upload

Changes:
	 
*/
//Waiting for document to load
$(document).ready(() =>
{
	//Creating network manager
	const networkManager = new NetworkManager();
	//Getting page
	var page = $('#descriptionPage');
	//Getting form
	var form = page.find('form');
	var action = form.attr('jsAction');
	var method = form.attr('method') || 'post';
	//Getting description input
	var input = page.find('#description');
	var characterCounter = page.find('.characterCounter[for=\"description\"]');
	var inputReason = page.find('.inputReason[for=\"description\"]');
	var submitButton = page.find('.submitButton');
	
	function onFormSubmitted(event)
	{
		event.preventDefault();
		//Getting value
		let description = input.val();
		//Checking description
		if (description.length > 511)
		{
			inputReason.text('Description is too long. Please make sure it is not longer than 511 characters');
			return;
		}
		//Remove any input reason text
		inputReason.text('');

		sendRequest(
			action,
			form.serialize(),
			method,
		).then((response) =>
		{
			//Next page
			onPageFinished();
		}).catch((information) =>
		{
			//Checking if information is structured
			if (information.structured)
			{
				inputReason.text(information.response.inputReasons.profilePicture.reason);
			}
			else
			{
				inputReason.text(`Request error: ${information.reason}`);
			}
		});
	}
	//On network changed
	function onNetworkChange()
	{
		//Checking if online
		if (networkManager.online)
		{
			//Enable button
			submitButton.prop('disabled', false);
		}
		else//if (!networkManager.online)
		{
			//Disable button
			submitButton.prop('disabled', true);
		}
	}
	input.on('keyup change', (event) =>
	{
		characterCounter.text(`${input.val().length}/511`);
	});
	form.submit(onFormSubmitted);

	//On network changed
	networkManager.addEventListener('onStatusChanged', onNetworkChange);
	onNetworkChange();
});
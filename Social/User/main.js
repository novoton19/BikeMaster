/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/05/23 06:35pm
	Version: 0.0.3.3
Updated on
	Version: 0.0.3.3

Description:
	Finds an account and displays information

Changes:
	 
*/
//Path to api
const userInformationUrl = '../../Api/Social/getUserInformation.php';
//Getting get parameters
const getParams = new URLSearchParams(window.location.search);

//Waiting for document to load
$(document).ready(() =>
{
	//Getting elements
	let userIDElem = $('#UserID');
	let usernameElem = $('#Username');
	let registrationDateElem = $('#RegistrationDate');

	//Getting requested id
	let userID = getParams.get('id');
	//Requesting information
	$.get(userInformationUrl, {id : userID}, function(result)
	{
		//Getting status
		let success = result.success;
		//Checking if success
		if (success)
		{
			//Getting account
			let account = result.account;
			//Getting registration time
			let registrationTime = account.registrationTime * 1000;
			//Getting registration date
			let registrationDate = new Date(registrationTime);

			//Loading information
			userIDElem.text(account.id);
			usernameElem.text(account.username);
			registrationDateElem.text(registrationDate.toLocaleString());
		}
		else
		{
			//Cannot load
			console.log('Cannot get user information', result);
		}
	});
});
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/14/23 04:56pm
	Version: 1
Updated on
	Version: 1

Description:
	Loads account information

Changes:

*/
//Status path
var statusPath = '../../Api/User/status.php';
//Accountpath
var accountPath = '../';

//Waiting for document to load
$(document).ready(() =>
{
	//Require status
	sendRequest(statusPath).then((status) =>
	{
		//Getting account
		let account = status.account;
		//Checking if account is set up
		if (account.description && account.profilePictureUrl && account.latitude && account.longitude)
		{
			//Redirect to account page
			window.location.href = accountPath;
		}
	}).catch(() =>
	{
		//Error
	});
});
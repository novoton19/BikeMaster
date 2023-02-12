/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/10/23 09:55pm
	Version: 0.3
Updated on
	Version: 0.3

Description:
	Javascript for error page

Changes:
	 
*/
//Waiting for document to load
$(document).ready(() =>
{
	//Getting get params
	const getParams = new URLSearchParams(window.location.search);
	const message = getParams.get('message') || 'An error occured. That\'s all we know';
	const sourceScript = getParams.get('sourceScript') || 'error.js';
	const detailedMessage = getParams.get('detailedMessage') || 'I was not able to gather further information.';
	const sourcePage = getParams.get('sourcePage');

	//Getting random accident
	const randomAccident = $('.randomAccident');
	//Getting message
	const messageElem = $('.message');
	const sourceScriptElem = $('.details .sourceScript');
	const detailedMessageElem = $('.details .detailedMessage');
	const backButton = $('.previousPageButton');

	//Random gif number
	const accidentNum = Math.floor(Math.random() * 20) + 1;
	//Adding source to accident
	randomAccident.attr('src', `Assets/Gifs/BikeCrashes/${accidentNum}.gif`);

	//Showing messages
	messageElem.text(message);
	sourceScriptElem.text(sourceScript);
	detailedMessageElem.text(`\"${detailedMessage}\"`);

	//Checking if there is source page
	if (sourcePage)
	{
		backButton.attr('href', sourcePage);
	}
	else//if (!sourcePage)
	{
		backButton.hide();
	}
});
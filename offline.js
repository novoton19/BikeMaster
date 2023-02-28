/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/28/23 03:30am
	Version: 1
Updated on
	Version: 1

Description:
	Javascript for error page

Changes:
	 
*/
//Waiting for document to load
$(document).ready(() =>
{
	//Getting random accident
	const randomAccident = $('.randomAccident');
	const backButton = $('.previousPageButton');

	//Random gif number
	const accidentNum = Math.floor(Math.random() * 20) + 1;
	//Adding source to accident
	randomAccident.attr('src', `Assets/Gifs/BikeCrashes/${accidentNum}.gif`);

	backButton.click(() =>
	{
		window.history.back();
	});
});
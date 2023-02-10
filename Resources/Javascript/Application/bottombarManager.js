/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/09/23 02:26pm
	Version: 0.1.2
Updated on
	Version: 0.1.2

Description:
	Bottombar manager

Changes:
	 
*/
//Path to bottombar html
const bottombarUrl = '/Pwa/BikeMaster/Resources/Html/Application/bottombar.html';

//Getting current url
const url = location.protocol + '//' + location.host + location.pathname;
//Waiting for document to load
$(document).ready(() =>
{
	//Adding css
	$('head').append($('<link>').attr('rel', 'stylesheet').attr('href', '/Pwa/BikeMaster/Resources/Css/Application/bottombar.css'));

	//Bottombar parent
	const parent = $('body');

	//Getting bottombar
	$.get(bottombarUrl, {}, (bottombarHtml) =>
	{
		//Adding bottombar
		parent.append(bottombarHtml);
		//Getting bottombar
		let bottombar = parent.find('#bottombar');
		let navigation = bottombar.find('.navigation')[0];
		let buttons = $(navigation).find('.button');

		//Finding a button that matches current url (to highlight and disable it)
		buttons.each((index, button) =>
		{
			//Getting url
			let href = $(button).attr('href');
			let buttonUrlObj = new URL(href, document.baseURI);
			let buttonUrl = buttonUrlObj.protocol + '//' + buttonUrlObj.host + buttonUrlObj.pathname;
			
			//Checking if url matches
			if (url.startsWith(buttonUrl))
			{
				//Checking if previous button exists
				if (index > 0)
				{
					//Remove border right
					$(buttons[index - 1]).addClass('noBorderRight');
				}
				//Getting icon
				let icon = $(button).find('.icon');
				//Highlight button
				$(button).addClass('blue');
				$(button).removeClass('noRadiusLeft');
				icon.addClass('inverted');

				$(navigation).scrollLeft($(button).position().left + $(button).outerWidth() / 2 - window.innerWidth / 2);

				//Checking if url is the same
				if (url === buttonUrl)
				{
					//Disable click event
					$(button).click((event) => event.preventDefault());
				}
			}
		});
	});
});
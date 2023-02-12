/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/09/23 02:26pm
	Version: 0.2
Updated on
	Version: 0.3.1

Description:
	Bottombar manager

Changes:
	Version 0.3 - Show bottombar only if signed in
	Version 0.3.1 - Use var instead of const
*/
//Path to signin status api request
var statusUrl = '/Pwa/BikeMaster/Api/User/status.php';
//Path to bottombar files
var bottombarHtmlUrl = '/Pwa/BikeMaster/Resources/Html/Application/bottombar.html';
var bottombarCssUrl = '/Pwa/BikeMaster/Resources/Css/Application/bottombar.css';

//Getting current url
var url = location.protocol + '//' + location.host + location.pathname;
//Waiting for document to load
$(document).ready(() =>
{
	//Loads bottombar
	function loadBottombar()
	{
		//Adding css
		$('head').append($('<link>').attr('rel', 'stylesheet').attr('href', bottombarCssUrl));
	
		//Bottombar parent
		const parent = $('body');
	
		//Getting bottombar
		$.get(bottombarHtmlUrl, {}, (bottombarHtml) =>
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
	}
	//Requesting status
	$.ajax({
		url : statusUrl,
		timeout : 5000,
		success : (response) =>
		{
			//Checking if valid response given and if response succeeded
			if (typeof(response) === 'object' && response.success)
			{
				//Checking if logged in
				if (response.loggedIn)
				{
					loadBottombar()
				}
			}
		},
		error : (response, textStatus) =>
		{
			//Do nothing, problem will be resolved by sign in requirer
		}
	})

});
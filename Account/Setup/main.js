/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/10/23 03:55pm
	Version: 0.3.2
Updated on
	Version: 0.3.2

Description:
	Javascript for Setup

Changes:
	 
*/
//Urls
var statusUrl = '../../Api/User/status.php';
var getParams = new URLSearchParams(window.location.search);

//Waiting for document to load
$(document).ready(() =>
{
	//List of missing pages
	var missingPages = [];
	//Current page
	var currentPage = undefined;
	
	//Getting progressbar
	var progressbar = $('#content .progress .bar');

	//Updates progressbar
	function updateProgressbar()
	{
		let progress;
		//Checking current page
		if (currentPage == 'complete')
		{
			progress = 1;
		}
		else
		{
			//Getting index of page
			let pageIndex = missingPages.indexOf(currentPage);
			//Checking if index exists
			if (pageIndex === -1)
			{
				progress = 0;
			}
			else
			{
				progress = pageIndex / missingPages.length;
			}
		}
		progressbar.css('width', `${progress * 100}%`);
		progressbar.text(`${Math.round(progress * 100)}%`);
	}
	//Called when page changes
	function onPageChanged()
	{
		//Getting get params
		let newGetParams = new URLSearchParams(window.location.search);
		//Setting page
		newGetParams.set('page', currentPage);
		//Update get parms
		window.history.replaceState(null, null, '?' + newGetParams.toString());
		//Updating progressbar
		updateProgressbar();
	}
	//Called when user skips the page
	function onPageSkipped(button)
	{
		//Getting next page
		let nextPage = $(button).attr('nextPage');

		$(`#${currentPage}`).hide(200);
		$(`#${nextPage}`).show(200);
		currentPage = nextPage;
		onPageChanged();
	}
	//Called when user requests refresh
	function onRefresh()
	{
		//Hide all pages
		$('.page').hide(200);

		//Removing current page from get params
		//Getting get params
		let newGetParams = new URLSearchParams(window.location.search);
		//Setting page
		newGetParams.delete('page');
		//Update get parms
		window.history.replaceState(null, null, '?' + newGetParams.toString());
		//Requested urls
		return [{
			url : statusUrl
		}];
	}
	//On setup page loaded
	function onLoad(responses)
	{
		//Getting sign in information
		let status = responses[0];
		let account = status.account;
		//Clear pages
		missingPages = [];
		//Adding missing pages
		if (!account.profilePictureUrl)
		{
			missingPages.push('profilePicturePage');
		}
		if (!account.description)
		{
			missingPages.push('descriptionPage');
		}
		if (!account.location)
		{
			missingPages.push('locationPage');
		}
		//Checking if there are any missing pages
		if (!missingPages.length)
		{
			//Loading last page
			currentPage = 'complete';
		}
		else//if (missingPages.length)
		{
			//Load page from get params
			currentPage = getParams.get('page');
			//Checking if page exists
			if (!currentPage || !$(`#${currentPage}`))
			{
				//Loading first page
				currentPage = missingPages[0];
			}
		}
		$(`#${currentPage}`).show(200);
		onPageChanged();
	}

	window.onRefresh = onRefresh;
	window.onLoad = onLoad;
	window.onFail = (information) => information.reason;

	$('.skipButton').each((index, skipButton) =>
	{
		$(skipButton).click(() =>
		{
			onPageSkipped(skipButton);
		});
	});
});
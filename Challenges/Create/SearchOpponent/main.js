/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/26/23 07:44pm
	Version: 0.6
Updated on
	Version: 0.6

Description:
	Javascript for search opponent page

Changes:

*/
//Html files
var userHtmlPath = '../../../Resources/Html/Application/user.html';
//Profile pictures path
var profilePicturesPath = '../../../Assets/ProfilePictures/Users/';
var defaultProfilePicturePath = '../../../Assets/ProfilePictures/Default/default.png';
//View user path
var viewUserPath = '../../../Account/View/';
//Search users path
var searchPath = '../../../Api/search.php';
//Sign in status path
var statusPath = '../../../Api/User/status.php';
//Create challenge path
var createChallengePath = '../';

//Waiting for document to load
$(document).ready(() =>
{
	//Getting elements
	const content = $('#content');
	const formWrapper = content.find('.formWrapper');
	const form = formWrapper.find('form');
	const searchInput = form.find('#search');
	const submitButton = form.find('.submitButton');
	const recordsWrapper = content.find('.recordsWrapper');
	const resultsCountElem = recordsWrapper.find('.resultsCount');
	const records = recordsWrapper.find('.records');
	const noResults = recordsWrapper.find('.noResults');

	//User html
	var userHtml = '';
	//Account ID
	var accountID = null;
	//Next page
	var nextPage = 0;
	//Searched term
	var searchedTerm = '';

	//On page requested
	function onPageRequested()
	{
		return [{
			url : userHtmlPath
		}, {
			url : statusPath
		}]
	}
	//On page loaded
	function onPageLoaded(responses)
	{
		//Getting user html
		userHtml = responses[0];
		//Getting account ID
		accountID = responses[1].account.id;
		//Resetting page
		nextPage = 0;
		//Clearing records
		noResults.hide();
		resultsCountElem.text('?');
		records.text(' ');
	}
	//On form submitted
	function onFormSubmitted(event)
	{
		//Prevent sending
		event.preventDefault();
		//Resetting page
		nextPage = 0;
		//Getting search term
		searchedTerm = searchInput.val();
		
		//Getting search params
		let searchParams = new URLSearchParams(window.location.search);
		searchParams.set('term', searchedTerm);
		//Replacing history
		window.history.replaceState(null, null, `?${searchParams.toString()}`);

		//Clearing records
		noResults.hide();
		resultsCountElem.text('?');
		records.text(' ');
	}
	//On results requested
	function onResultsRequested()
	{
		//Checking if searched term is set
		if (searchedTerm === '')
		{
			//Hiding records wrapper
			recordsWrapper.hide();
			return [];
		}
		//Showing records wrapper
		recordsWrapper.show();
		//Returning required files
		return [{
			url : searchPath,
			data : {
				page : nextPage,
				term : searchedTerm,
				sector : 'users'
			}
		}];
	}
	//On results loaded
	function onResultsObtained(responses)
	{
		//Checking if responses are set
		if (!responses || !responses.length)
		{
			//Do nothing
			return;
		}
		//Getting search results
		let search = responses[0];
		let results = search.results;
		let resultsCount = search.resultsCount;
		let pagesCount = search.totalPages;
		let inputs = search.inputs;
		let page = inputs.page;

		//Checking page
		if (page !== nextPage)
		{
			//Not the page we want...
			return;
		}
		//Checking if last page
		if (page + 1 === pagesCount)
		{
			noResults.show();
		}
		nextPage++;
		
		//Adding results count
		resultsCountElem.text(resultsCount);
		//Loading results
		records.append(results.map((result) =>
		{
			//Getting record
			let record = $(userHtml);
			//Getting elements
			let usernameElem = record.find('.username');
			let descriptionElem = record.find('.description');
			let profilePictureElem = record.find('.profilePicture')
			let detailsButton = record.find('.detailsButton');
			let challengeButton = record.find('.challengeButton');
			
			//Getting account information
			let id = result.id;
			let username = result.username;
			let description = result.description;
			let profilePictureUrl = result.profilePictureUrl !== null ? profilePicturesPath + result.profilePictureUrl : defaultProfilePicturePath;
			
			//Creating search params for account details
			let detailsSearchParams = new URLSearchParams();
			detailsSearchParams.set('id', id);
			//Creating search params for challenge button
			let challengeSearchParams = new URLSearchParams();
			challengeSearchParams.set('id', id);
			
			//Loading information
			usernameElem.html(username);
			descriptionElem.html(description);
			profilePictureElem.attr('src', profilePictureUrl);
			detailsButton.attr('href', `${viewUserPath}?${detailsSearchParams.toString()}`);
			challengeButton.attr('href', `${createChallengePath}?${challengeSearchParams.toString()}`);
			
			//Checking if user is not signed in user himself
			if (result.id === accountID)
			{
				//Hide challenge button
				challengeButton.hide();
			}
			//Return record
			return record;
		}));
	}
	
	form.submit((event) => onFormSubmitted(event));
	searchInput.change(() => searchedTerm = searchInput.val());

	window.onPageRequested = onPageRequested;
	window.onPageLoaded = onPageLoaded;
	window.onPageFailed = (information) => information.reason;
	window.onResultsRequested = onResultsRequested;
	window.onResultsObtained = onResultsObtained;
	window.onResultsFailed = (information) => information.reason;

	//Getting search params
	var searchParams = new URLSearchParams(window.location.search);
	//Getting searched term
	searchedTerm = searchParams.get('term') || '';
	searchInput.val(searchedTerm);
});
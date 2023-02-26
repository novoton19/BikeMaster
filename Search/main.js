/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/26/23 08:35am
	Version: 0.5
Updated on
	Version: 0.5

Description:
	Searching

Changes:

*/
//Html files
var journeyHtmlUrl = '../Resources/Html/Application/journey.html';
var userHtmlUrl = '../Resources/Html/Application/user.html';
//Profile pictures
var userProfilePicturesUrl = '../Assets/ProfilePictures/Users/';
var defaultProfilePictureUrl = '../Assets/ProfilePictures/Default/default.png';
var viewAccountUrl = '../Account/View/';
//Api requests
var searchUrl = '../Api/search.php';

//Waiting for document to load
$(document).ready(() =>
{
	//Getting content
	const content = $('#content');
	//Getting tools section
	const tools = content.find('.tools');
	const searchInput = tools.find('#search');
	const sectorInput = tools.find('#sector');
	const submitButton = tools.find('.submitButton');
	//Getting search results section
	const searchResultsElem = content.find('.searchResults');
	const resultsCountElem = searchResultsElem.find('.resultsCount');
	const records = searchResultsElem.find('.records');
	const noResults = searchResultsElem.find('.noResults');
	
	var userHtml = undefined;
	var journeyHtml = undefined;

	var searchTerm = '';
	var searchSector = '';
	var nextPage = 0;

	//Called on page refresh
	function onPageRequested()
	{
		searchResultsElem.hide();
		//Required files
		return [{
			url : userHtmlUrl
		}, {
			url : journeyHtmlUrl
		}];
	}
	//Called on page load
	function onPageLoaded(responses)
	{
		//Reset page
		nextPage = 0;
		//Getting html files
		userHtml = responses[0];
		journeyHtml = responses[1];
		//Clear content
		resultsCountElem.text('?');
		noResults.hide();
		searchResultsElem.show();
		records.text(' ');
	}
	//Called on results request
	function onResultsRequested()
	{
		//Checking if there is any search term
		if (searchTerm === '')
		{
			searchResultsElem.hide();
			return [];
		}
		searchResultsElem.show();

		return [{
			url : searchUrl,
			data : {
				term : searchTerm,
				sector : searchSector,
				page : nextPage
			}
		}];
	}
	//Called when results loaded
	function onResultsObtained(responses)
	{
		if (!responses)
		{
			return;
		}
		//Getting searchResults
		let search = responses[0];
		//Checking page
		if (search.inputs.page !== nextPage)
		{
			//Not the page we want
			console.log('a');
			return;
		}
		//Getting results
		let results = search.results;
		let sector = search.inputs.sector;
		//Adding next page
		nextPage++;
		//Loading results
		records.append(search.results.map((result) =>
		{
			//Checking sector
			if (sector === 'users')
			{
				//Creating new record
				let record = $(userHtml);
				//Getting elements
				let profilePicture = record.find('.profilePicture');
				let username = record.find('.username');
				let description = record.find('.description');
				let detailsButton = record.find('.detailsButton');

				//Getting account info
				let id = result.id;
				let profilePictureUrl = result.profilePictureUrl;
				
				//Creating get params for details url
				let getParams = new URLSearchParams();
				getParams.set('id', id);
				
				//Getting full profile picture url
				if (profilePictureUrl)
				{
					profilePictureUrl = userProfilePicturesUrl + profilePictureUrl;
				}
				else
				{
					profilePictureUrl = defaultProfilePictureUrl;
				}

				profilePicture.attr('src', profilePictureUrl);
				username.html(result.username);
				description.html(result.description);
				detailsButton.attr('href', viewAccountUrl + '?' + getParams.toString());
				return record;
			}
			else if (sector === 'journeys')
			{

			}
			else if (sector === 'competitions')
			{

			}
			else//if (sector === 'achievements')
			{

			}
		}));
	}

	searchInput.change(() =>
	{
		searchTerm = searchInput.val();
	});
	sectorInput.change(() =>
	{
		searchSector = sectorInput.find('option:selected').val();
	});
	searchSector = sectorInput.find('option:selected').val();
	submitButton.click((event) =>
	{
		event.preventDefault();
		nextPage = 0;
		searchTerm = searchInput.val();
		searchSector = sectorInput.find('option:selected').val();
		records.text(' ');
	});

	window.onPageRequested = onPageRequested;
	window.onPageLoaded = onPageLoaded;
	window.onPageFailed = (information) => information.reason;
	window.onResultsRequested = onResultsRequested;
	window.onResultsObtained = onResultsObtained;
	window.onResultsFailed = (information) => information.reason;
});
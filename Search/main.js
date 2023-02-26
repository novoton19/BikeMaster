/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/26/23 08:35am
	Version: 0.5
Updated on
	Version: 0.5.1.1

Description:
	Searching

Changes:
	Search journeys
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
			noResults.show();
			return;
		}
		//Getting searchResults
		let search = responses[0];
		//Checking page
		if (search.inputs.page !== nextPage)
		{
			//Not the page we want
			return;
		}
		//Checking page
		if (nextPage === search.totalPages)
		{
			noResults.show();
		}
		//Getting results
		let results = search.results;
		let sector = search.inputs.sector;
		//Adding next page
		nextPage++;
		//Adding search results count
		resultsCountElem.text(search.resultsCount);
		//Loading results
		search.results.map((result) =>
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
				records.append(record);
				return;
			}
			else if (sector === 'journeys')
			{
				//Creating new record
				let record = $(journeyHtml);
				//Getting elements
				let userDetails = record.find('.userDetails');
				let profilePicture = userDetails.find('.profilePicture');
				let username = userDetails.find('.username');
				let userDescription = userDetails.find('.description');
				let detailsButton = record.find('.detailsButton');
				let map = record.find('.map');
				let information = record.find('.journeyInformation');
				let title = information.find('.title');
				let description = information.find('.description');
				let downloadButton = information.find('.downloadButton');
				let archiveButton = information.find('.archiveButton');
				let unarchiveButton = information.find('.unarchiveButton');
				let dateElem = record.find('span.date');
				let durationElem = record.find('span.duration');
				let length = record.find('span.distance');

				archiveButton.hide();				
				unarchiveButton.hide();
				//Getting journey info
				let id = result.id;
				let date = new Date(result.creationTime * 1000).toLocaleDateString();
				//Converting to gpx
				let gpx = GpxConverter.toGpx(result);

				//Getting seconds
				let seconds = result.endTime - result.startTime;
				//Getting minutes
				let minutes = Math.floor(seconds / 60);
				//Getting hours
				let hours = Math.floor(minutes / 60);
				//Updating values
				seconds -= minutes * 60 + hours * 60 * 60;
				minutes -= hours * 60;
				//Formatting values
				seconds = seconds >= 10 ? seconds : '0' + seconds;
				minutes = minutes >= 10 ? minutes : '0' + minutes;
				hours = hours >= 10 ? hours : '0' + hours;				
			
				//Getting account info
				let userID = result.owner.id;
				let profilePictureUrl = result.owner.profilePictureUrl;
				
				//Creating get params for details url
				let getParams = new URLSearchParams();
				getParams.set('id', userID);
				
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
				username.html(result.owner.username);
				userDescription.html(result.owner.description);
				detailsButton.attr('href', viewAccountUrl + '?' + getParams.toString());


				//Adding ID to map
				map.attr('id', 'onlineSource_' + id);
				//Adding texts
				dateElem.text(date);
				durationElem.text(`${hours}:${minutes}:${seconds}`);
				length.text(`${Math.round(result.length * 100) / 100} km`);
				
				//Creating get params for details url
				getParams = new URLSearchParams();
				getParams.set('id', id);
				
				title.html(result.title);
				description.html(result.description);
				//https://ourcodeworld.com/articles/read/189/how-to-create-a-file-and-generate-a-download-with-javascript-in-the-browser-without-a-server
				downloadButton.attr('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(gpx));
				downloadButton.attr('download', 'downloadedJourney_onlineSource_' + id + '.gpx');
				
				records.append($(record));
				//Creating map manager
				let mapManager = new MapManager('onlineSource_' + id, false);
				mapManager.loadGpx(gpx);
				mapManager.fitGpxLayer();
				return;
			}
			else if (sector === 'competitions')
			{

			}
			else//if (sector === 'achievements')
			{

			}
		});
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
		
		//Creating search params
		let searchParams = new URLSearchParams(window.location.search);
		searchParams.set('term', searchTerm);
		searchParams.set('sector', searchSector);
		//Adding params to the url
		window.history.replaceState(null, null, '?' + searchParams.toString());
		records.text(' ');
	});

	window.onPageRequested = onPageRequested;
	window.onPageLoaded = onPageLoaded;
	window.onPageFailed = (information) => information.reason;
	window.onResultsRequested = onResultsRequested;
	window.onResultsObtained = onResultsObtained;
	window.onResultsFailed = (information) => information.reason;

	//Loading search params
	let searchParams = new URLSearchParams(window.location.search);
	searchTerm = searchParams.get('term') || '';
	searchSector = searchParams.get('sector') || 'users';
	//Loading search term and sector
	searchInput.val(searchTerm);
	sectorInput.val(searchSector);
});
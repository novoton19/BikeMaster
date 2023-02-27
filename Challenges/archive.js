/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/27/23 08:13pm
	Version: 0.6.4
Updated on
	Version: 0.6.4

Description:
	Loads competitions

Changes:

*/
//Competition html
var competitionHtmlPath = '../Resources/Html/Application/competition.html';
//Profile pictures
var defaultProfilePicturePath = '../Assets/ProfilePictures/Default/default.png';
var userProfilePicturePath = '../Assets/ProfilePictures/Users/';
//View profile path
var viewProfilePath = '../Account/View'
//Login status
var statusPath = '../Api/User/status.php';
//Get competitions
var competitionsPath = '../Api/Challenges/Competitions/getCompetitions.php';

//Waiting for document to load
$(document).ready(() =>
{
	//Getting elements
	const content = $('#content');
	const competitionsElem = content.find('#competitions');
	const archive = competitionsElem.find('#archive');
	const searchResultsElem = archive.find('.searchResults');
	const resultsCountElem = searchResultsElem.find('.resultsCount');
	const records = searchResultsElem.find('.records');
	const noResults = searchResultsElem.find('.noResults');

	//Next page
	var nextPage = 0;
	var competitionHtml = '';
	var account = null;
	var accountID = null;


	function onArchivePageRequested()
	{
		searchResultsElem.hide();
		//Return requested urls
		return [{
			url : competitionHtmlPath
		}, {
			url : statusPath
		}];
	}
	function onArchivePageLoaded(responses)
	{
		//Getting competition html
		competitionHtml = responses[0];
		account = responses[1].account;
		accountID = account.id;
		//Reset page
		nextPage = 0;
		//Clear content
		noResults.hide();
		resultsCountElem.text('?');
		searchResultsElem.show();
		records.text(' ');
	}
	function onArchiveRequested()
	{
		return [{
			url : competitionsPath,
			data : {
				page : nextPage,
				viewingType : 'archive'
			}
		}];
	}
	function onArchiveLoaded(responses)
	{
		//Getting results
		let result = responses[0];
		let inputs = result.inputs;
		let page = inputs.page;
		let totalPages = result.totalPages;
		let competitions = result.results;
		let resultsCount = result.resultsCount;
		//Checking page
		if (page !== nextPage)
		{
			//Not the page we want...
			return;
		}
		nextPage++;
		//Checking if last page
		if (page + 1 >= totalPages)
		{
			noResults.show();
		}
		resultsCountElem.text(resultsCount);
		
		//Loading competitions
		records.append(competitions.map((competition) =>
		{
			let record = $(competitionHtml);
			//Getting elements
			let userDetails = record.find('.userDetails');
			let usernameElem = userDetails.find('.username');
			let opponentDescriptionElem = userDetails.find('.description');
			let profilePictureElem = userDetails.find('.profilePicture');
			let detailsButton = userDetails.find('.detailsButton');
			let acceptButton = userDetails.find('.acceptButton');
			let declineButton = userDetails.find('.declineButton');
			let competitionDetails = record.find('.competitionDetails');
			let titleElem = competitionDetails.find('.title');
			let competitionDescriptionElem = competitionDetails.find('.description');
			let progress = record.find('.progress');
			let distanceElem = progress.find('.distance');
			let opponentsNameElem = progress.find('.opponentsName');
			let teamBlueBar = progress.find('.bar.blue');
			let teamRedBar = progress.find('.bar.red');

			//Getting information
			let opponent = competition.sender.id === accountID ? competition.receiver : competition.sender;
			let username = opponent.username;
			let opponentDescription = opponent.description;
			let profilePictureUrl = opponent.profilePictureUrl === null ? defaultProfilePicturePath : userProfilePicturePath + opponent.profilePictureUrl;
			let title = competition.title;
			let competitionDescription = competition.description;
			let distance = competition.distance;
			let distanceDriven = competition.sender.id === accountID ? competition.senderDistanceDriven : competition.receiverDistanceDriven;
			let opponentDistanceDriven = competition.sender.id !== accountID ? competition.senderDistanceDriven : competition.receiverDistanceDriven;

			//Creating get params for view profile page
			let viewProfileGetParams = new URLSearchParams();
			viewProfileGetParams.set('id', opponent.id);

			//Adding information
			acceptButton.hide();
			declineButton.hide();
			usernameElem.text(username);
			opponentsNameElem.text(username);
			opponentDescriptionElem.text(opponentDescription);
			profilePictureElem.attr('src', profilePictureUrl);
			detailsButton.attr('href', `${viewProfilePath}?${viewProfileGetParams.toString()}`);
			titleElem.text(title);
			competitionDescriptionElem.text(competitionDescription);
			distanceElem.text(`${distance} km`);
			teamBlueBar.text(`${distanceDriven} km`);
			teamBlueBar.css('width', `${distanceDriven / distance * 100}%`);
			teamRedBar.text(`${opponentDistanceDriven} km`);
			teamRedBar.css('width', `${opponentDistanceDriven / distance * 100}%`);

			return record;
		}));
	}

	window.onArchivePageRequested = onArchivePageRequested;
	window.onArchivePageLoaded = onArchivePageLoaded;
	window.onArchivePageFailed = (information) => information.reason;
	window.onArchiveRequested = onArchiveRequested;
	window.onArchiveLoaded = onArchiveLoaded;
	window.onArchiveFailed = (information) => information.reason;
});
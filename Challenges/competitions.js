/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/27/23 02:12am
	Version: 0.6.2
Updated on
	Version: 0.6.2

Description:
	Loads competitions

Changes:

*/
//Competition html
var competitionHtmlPath = '../Resources/Html/competition.html';
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
	const actual = competitionsElem.find('#actual');
	const searchResultsElem = actual.find('.searchResults');
	const resultsCountElem = searchResultsElem.find('.resultsCount');
	const records = searchResultsElem.find('.records');
	const noResults = searchResultsElem.find('.noResults');

	//Next page
	var nextPage = 0;
	var competitionHtml = '';
	var account = null;
	var accountID = null;


	function onCompetitionsPageRequested()
	{
		searchResultsElem.hide();
		//Return requested urls
		return [{
			url : competitionHtmlPath
		}, {
			url : statusPath
		}];
	}
	function onCompetitionsPageLoaded(responses)
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
	function onCompetitionsRequested()
	{
		return [{
			url : competitionsPath,
			data : {
				page : nextPage,
				viewingType : 'actual'
			}
		}];
	}
	function onCompetitionsLoaded(responses)
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
		console.log(competitions);
	}

	window.onCompetitionsPageRequested = onCompetitionsPageRequested;
	window.onCompetitionsPageLoaded = onCompetitionsPageLoaded;
	window.onCompetitionsPageFailed = (information) => information.reason;
	window.onCompetitionsRequested = onCompetitionsRequested;
	window.onCompetitionsLoaded = onCompetitionsLoaded;
	window.onCompetitionsFailed = (information) => information.reason;
});
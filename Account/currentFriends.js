/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/20/23 02:39pm
	Version: 0.3.5
Updated on
	Version: 1

Description:
	Loads friend list

Changes:
	Version 0.3.5.1 - Add friends count
	Version 0.3.6 - View account url
*/
//Status request url
var friendHtmlUrl = '../Resources/Html/Application/user.html';
var friendsUrl = '../Api/Social/Account/getFriends.php';
var userProfilePicturesUrl = '../Assets/ProfilePictures/Users/';
var defaultProfilePictureUrl = '../Assets/ProfilePictures/Default/default.png';
var viewAccountUrl = 'View/';
var challengeUrl = '../Challenges/Create/';
//Getting current script name
var friendsName = document.currentScript.src.split('/').pop();

//Waiting for page to load
$(document).ready(() =>
{
	//Getting friends section
	var friends = $('#friends');
	var list = friends.find('#list');
	var recordsWrapper = list.find('.recordsWrapper');
	var friendsCountElem = recordsWrapper.find('.friendsCount');
	var records = recordsWrapper.find('.records');
	var noResults = recordsWrapper.find('.noResults');
	//Current friends page
	var currentPage = 0;
	var lastPage = false;
	var friendHtml = undefined;

	function onPageRequest()
	{
		//Clear current records
		recordsWrapper.hide();
		return [{
			url : friendHtmlUrl
		}];
	}
	function onPageSuccess(responses)
	{
		//Getting friend html
		friendHtml = responses[0];
		//Reset page
		currentPage = 0;
		lastPage = false;
		//Clear anything from records (' ' is there because of contentLoadManager)
		records.text(' ');
		friendsCountElem.text('?');
		//Show records wrapper
		noResults.hide();
		recordsWrapper.show();
	}
	function onRequest()
	{
		if (lastPage)
		{
			return [];
		}
		return [{
			url : friendsUrl,
			data : {
				page : currentPage,
				type : 'current'
			}
		}];
	}
	function onSuccess(responses)
	{
		if (lastPage)
		{
			return;
		}
		//Getting result
		let friendsResult = responses[0]
		let friendsCount = friendsResult.resultsCount;
		let friends = friendsResult.result;
		let page = friendsResult.inputs.page;
		//Checking page
		if (page !== currentPage)
		{
			//This is not the page we want next
			return;
		}
		//Checking maximum page
		if (currentPage < friendsResult.totalPages)
		{
			//Increment page
			currentPage += 1;
		}
		else
		{
			//Show no results page
			lastPage = true;
			noResults.show();
		}
		//Adding friends count
		friendsCountElem.text(friendsCount);
		//Appending friends
		records.append(friends.map((friend) =>
		{
			//Creating new record
			let record = $(friendHtml);
			//Getting elements
			let profilePicture = record.find('.profilePicture');
			let username = record.find('.username');
			let description = record.find('.description');
			let detailsButton = record.find('.detailsButton');
			let challengeButton = record.find('.challengeButton');

			//Getting account info
			let id = friend.id;
			let profilePictureUrl = friend.profilePictureUrl;
			
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
			username.text(friend.username);
			description.text(friend.description);
			detailsButton.attr('href', viewAccountUrl + '?' + getParams.toString());
			challengeButton.attr('href', challengeUrl + '?' + getParams.toString());
			return $(record);
		}));
	}

	window.onFriendListPageRequested = onPageRequest;
	window.onFriendListPageObtained = onPageSuccess;
	window.onFriendListPageFailed = (information) => information.reason;
	window.onFriendListRequested = onRequest;
	window.onFriendListObtained = onSuccess;
	window.onFriendListFailed = (information) => information.reason;
});
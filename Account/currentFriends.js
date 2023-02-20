/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/20/23 02:39pm
	Version: 0.3.5
Updated on
	Version: 0.3.5

Description:
	Loads friend list

Changes:
	 
*/
//Status request url
var friendHtmlUrl = '../Resources/Html/Application/friend.html';
var friendsUrl = '../Api/Social/Account/getFriends.php';
//Getting current script name
var friendsName = document.currentScript.src.split('/').pop();

//Waiting for page to load
$(document).ready(() =>
{
	//Getting friends section
	var friends = $('#friends');
	var list = friends.find('#list');
	var recordsWrapper = list.find('.recordsWrapper');
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

			//Getting profile picture url
			let profilePictureUrl = friend.profilePictureUrl;
			if (profilePictureUrl)
			{
				profilePictureUrl = `../Assets/ProfilePictures/Users/${profilePictureUrl}`;
			}
			else
			{
				profilePictureUrl = '../Assets/ProfilePictures/Default/default.png';
			}

			profilePicture.attr('src', profilePictureUrl);
			username.text(friend.username);
			description.text(friend.description);
			detailsButton.attr('href', friend.id);
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
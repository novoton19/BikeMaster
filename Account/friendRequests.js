/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/20/23 11:39pm
	Version: 0.3.5
Updated on
	Version: 0.3.5

Description:
	Loads friend requests list

Changes:
	 
*/
//Status request url
var friendRequestHtmlUrl = '../Resources/Html/Application/friendRequest.html';
var friendsUrl = '../Api/Social/Account/getFriends.php';
var respondUrl = '../Api/Social/Account/respondToFriendRequest.php';
//Getting current script name
var friendsName = document.currentScript.src.split('/').pop();

//Waiting for page to load
$(document).ready(() =>
{
	//Getting friends section
	var friends = $('#friends');
	var requests = friends.find('#requests');
	var recordsWrapper = requests.find('.recordsWrapper');
	var requestsCountElem = recordsWrapper.find('.requestsCount');
	var records = recordsWrapper.find('.records');
	var noResults = recordsWrapper.find('.noResults');
	//Current friends page
	var currentPage = 0;
	var lastPage = false;
	var friendRequestHtml = undefined;

	//Changes the disabled state of accept and decline buttons
	function changeButtonDisablity(disabled = true)
	{
		//Change state
		records.find('.acceptButton').prop('disabled', disabled);
		records.find('.declineButton').prop('disabled', disabled);
	}
	function onPageRequest()
	{
		//Clear current records
		recordsWrapper.hide();
		return [{
			url : friendRequestHtmlUrl
		}];
	}
	function onPageSuccess(responses)
	{
		//Getting friend html
		friendRequestHtml = responses[0];
		//Reset page
		currentPage = 0;
		lastPage = false;
		//Clear anything from records (' ' is there because of contentLoadManager)
		records.text(' ');
		requestsCountElem.text('?');
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
				type : 'requests'
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
		let requestsCount = friendsResult.resultsCount;
		let requests = friendsResult.result;
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
		//Adding friend requests count
		requestsCountElem.text(requestsCount);
		//Appending friends
		records.append(requests.map((friend) =>
		{
			//Creating new record
			let record = $(friendRequestHtml);
			//Getting elements
			let profilePicture = record.find('.profilePicture');
			let username = record.find('.username');
			let description = record.find('.description');
			let detailsButton = record.find('.detailsButton');
			let acceptButton = record.find('.acceptButton');
			let declineButton = record.find('.declineButton');

			//Getting user information
			let id = friend.id;
			let profilePictureUrl = friend.profilePictureUrl;
			//Getting profile picture url
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
			detailsButton.attr('href', id);

			function respondToRequest(type)
			{
				changeButtonDisablity();
				//Send request
				sendRequest(
					respondUrl,
					{
						id : id,
						responseType : type
					}
				).then((response) =>
				{
					changeButtonDisablity(false);
					acceptButton.remove();
					declineButton.remove();
				}).catch((information) =>
				{
					changeButtonDisablity(false);
				});
			}
			//Adding response button events
			acceptButton.click(() => respondToRequest('accept'));
			declineButton.click(() => respondToRequest('decline'));
			return $(record);
		}));
	}

	window.onFriendRequestsPageRequested = onPageRequest;
	window.onFriendRequestsPageObtained = onPageSuccess;
	window.onFriendRequestsPageFailed = (information) => information.reason;
	window.onFriendRequestsRequested = onRequest;
	window.onFriendRequestsObtained = onSuccess;
	window.onFriendRequestsFailed = (information) => information.reason;
});
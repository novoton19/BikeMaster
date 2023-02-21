/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/21/23 09:14am
	Version: 0.3.6
Updated on
	Version: 0.3.6

Description:
	View user information

Changes:

*/
//Url to request account information
var getInformationUrl = '../../Api/Social/Account/getInformation.php';
var getRelationUrl = '../../Api/Social/Account/getRelation.php';
var statusUrl = '../../Api/User/status.php';
var respondUrl = '../../Api/Social/Account/respondToFriendRequest.php';
var unfriendUrl = '../../Api/Social/Account/unfriend.php';
var addFriendUrl = '../../Api/Social/Account/sendFriendRequest.php';
var userProfilePicturesUrl = '../../Assets/ProfilePictures/Users/';
var defaultProfilePictureUrl = '../../Assets/ProfilePictures/Default/default.png';
//Getting current script name
var mainName = document.currentScript.src.split('/').pop();

//Waiting for page to load
$(document).ready(() =>
{
	//Creating network manager
	var networkManager = new NetworkManager();
	//Getting content
	var informationElem = $('#information');
	var content = informationElem.find('.content');
	//Getting actions
	var actions = content.find('.actions');
	var actionButtons = actions.find('.button');
	var addFriendButton = actions.find('.addFriendButton');
	var createCompetitionButton = actions.find('.createCompetitionButton');
	var acceptRequestButton = actions.find('.acceptRequestButton');
	var declineRequestButton = actions.find('.declineRequestButton');
	var unfriendButton = actions.find('.unfriendButton');
	var cancelRequestButton = actions.find('.cancelRequestButton');
	var reportButton = actions.find('.reportButton');
	var cancelReportButton = actions.find('.cancelReportButton');
	//Getting profile picture
	var profilePictureElem = content.find('.profilePicture');
	//Getting username and registration
	var usernameElem = $('.username');
	var registrationElem = content.find('.registrationTime');
	//Getting friends
	var friendsCountElem = $('.friendsCount');
	var friendsListButton = content.find('.friendsListButton');
	//Getting description
	var descriptionWrapper = content.find('.descriptionWrapper');
	var descriptionElem = descriptionWrapper.find('.description');
	var readMoreButton = descriptionWrapper.find('.readMoreDescription');
	var readLessButton = descriptionWrapper.find('.readLessDescription');
	//Getting navigational buttons
	var navigationalButtons = content.find('.navigationalButtons');
	var backButton = navigationalButtons.find('.backButton');
	//Getting search params
	var getParams = new URLSearchParams(window.location.search);
	var id = getParams.get('id');
	var returnUrl = getParams.get('returnUrl');

	var returnUrlExists = returnUrl !== null;
	//Checking if ID exists
	if (id === null)
	{
		//Checking if return url exists
		if (returnUrlExists)
		{
			//Return
			window.location.href = returnUrl;
		}
		else
		{
			forwardToErrorPage('User doesn\'t exist', mainName);
		}
	}

	function disableButtons()
	{
		actionButtons.prop('disabled', true);
	}
	function updateButtons()
	{
		actionButtons.prop('disabled', !networkManager.online);
	}
	function respondToRequest(type)
	{
		disableButtons();
		//Send request
		sendRequest(
			respondUrl,
			{
				id : id,
				responseType : type
			}
		).then((response) =>
		{
			updateButtons();
			acceptRequestButton.hide();
			declineRequestButton.hide();

			if (type === 'accept')
			{
				unfriendButton.show();
			}
			else
			{
				addFriendButton.show();
			}
		}).catch((information) =>
		{
			updateButtons();
		});
	}
	function unfriend()
	{
		disableButtons();
		sendRequest(
			unfriendUrl,
			{
				id : id
			}
		).then((response) =>
		{
			updateButtons();
			unfriendButton.hide();
			cancelRequestButton.hide();
			addFriendButton.show();
		}).catch((information) =>
		{
			updateButtons();
		});
	}
	function addFriend()
	{
		disableButtons();
		sendRequest(
			addFriendUrl,
			{
				receiverID : id
			}
		).then((response) =>
		{
			updateButtons();
			addFriendButton.hide();
			cancelRequestButton.show();
		}).catch((information) =>
		{
			updateButtons();
		});
	}
	function onAccountRequested()
	{
		content.hide();
		return [{
			url : getInformationUrl,
			data : {
				id : id
			}
		}, {
			url : getRelationUrl,
			data : {
				id : id
			}
		}, {
			url : statusUrl
		}];
	}
	function onAccountLoaded(responses)
	{
		//Getting account
		let account = responses[0].account;
		let relation = responses[1].relation;
		let loggedInAccount = responses[2].account;
		//Getting information
		let id = account.id;
		let profilePictureUrl = account.profilePictureUrl;
		let username = account.username;
		let registrationTime = account.registrationTime;
		let friendsCount = account.friendsCount;
		let description = account.description;

		if (profilePictureUrl)
		{
			profilePictureUrl = userProfilePicturesUrl + profilePictureUrl;
		}
		else
		{
			profilePictureUrl = defaultProfilePictureUrl;
		}
		if (!description)
		{
			descriptionWrapper.hide();
		}
		else
		{
			descriptionWrapper.show();
		}
		if (friendsCount > 0)
		{
			friendsListButton.show();
		}
		else
		{
			friendsListButton.hide();
		}
		descriptionElem.text('');
		descriptionElem.addClass('limited');
		readMoreButton.hide();
		readLessButton.hide();

		profilePictureElem.prop('src', profilePictureUrl);
		usernameElem.text(username);
		registrationElem.text('Member since ' + new Date(registrationTime * 1000).toLocaleDateString());
		friendsCountElem.text(friendsCount);
		descriptionElem.text(description);

		//Hide buttons by default
		actionButtons.hide();
		//Checking relation
		if (relation.exists)
		{
			if (relation.accepted)
			{
				unfriendButton.show();
			}
			else if (relation.senderID === id)
			{
				acceptRequestButton.show();
				declineRequestButton.show();
			}
			else//if (!relationAccepted && relation.receiverID === id)
			{
				cancelRequestButton.show();
			}
		}
		else//if (!relation.exists)
		{
			if (loggedInAccount.id !== id)
			{
				addFriendButton.show();
			}
		}
		if (loggedInAccount.id !== id)
		{
			createCompetitionButton.show();
		}
		content.show();

		if (descriptionElem.height() < descriptionElem[0].scrollHeight)
		{
			readMoreButton.show();
		}
	}
	window.onAccountRequested = onAccountRequested;
	window.onAccountLoaded = onAccountLoaded;
	window.onAccountLoadFailed = (information) => information.reason;

	if (returnUrlExists)
	{
		backButton.attr('href', returnUrl);
	}
	else
	{
		navigationalButtons.hide();
	}
	readMoreButton.click(() =>
	{
		descriptionElem.removeClass('limited');
		readMoreButton.hide();
		readLessButton.show();
	});
	readLessButton.click(() =>
	{
		descriptionElem.addClass('limited');
		readMoreButton.show();
		readLessButton.hide();
	});
	$(window).resize(() =>
	{
		if (descriptionElem.hasClass('limited'))
		{
			if (descriptionElem.height() < descriptionElem[0].scrollHeight)
			{
				readMoreButton.show();
			}
			else
			{
				readMoreButton.hide();
			}
		}
	});
	//Adding response button events
	acceptRequestButton.click(() => respondToRequest('accept'));
	declineRequestButton.click(() => respondToRequest('decline'));
	unfriendButton.click(unfriend);
	addFriendButton.click(addFriend);
	cancelRequestButton.click(unfriend);

	networkManager.addEventListener('onStatusChanged', updateButtons);
	updateButtons();
});
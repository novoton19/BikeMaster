/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/05/23 06:35pm
	Version: 0.0.3.3
Updated on
	Version: 0.0.5.2

Description:
	Finds an account and displays information

Changes:
	Version 0.0.5 - Send friend request
	Version 0.0.5.1 - Cancel/Accept/Decline/Unfriend friend actions
	Version 0.0.5.2 - Show friends count and list
*/
//Path to api
const userInformationUrl = '../../Api/Social/getUserInformation.php';
//Url to get friend relation
const getFriendRelationUrl = '../../Api/Social/getFriendRelation.php';
//Getting get parameters
const getParams = new URLSearchParams(window.location.search);

//Waiting for document to load
$(document).ready(() =>
{
	//Getting elements
	const userIDElem = $('#UserID');
	const usernameElem = $('#Username');
	const registrationDateElem = $('#RegistrationDate');
	const friendsCountElem = $('#FriendsCount');
	//Getting friends list
	const friendsList = $('#FriendsList');
	const moreFriendsButton = $('#MoreFriendsButton');
	//Getting friend relation buttons
	const addFriendButton = $('#AddFriendButton');
	const cancelRequestButton = $('#CancelRequestButton');
	const acceptRequestButton = $('#AcceptRequestButton');
	const declineRequestButton = $('#DeclineRequestButton');
	const unfriendButton = $('#UnfriendButton');

	//Getting requested id
	const requestedUserID = parseInt(getParams.get('id'));
	let relation = {
		exists : false,
		accepted : false,
		senderID : null,
		receiverID : null
	};

	//Current friends page
	let friendsPage = 0;
	//Checking if has next friends page
	let hasNextFriendsPage = true;
	//Loads next friends page
	function loadNextFriendsPage()
	{
		if (hasNextFriendsPage)
		{
			//Sending request
			$.get(moreFriendsButton.attr('js-action'), {id : requestedUserID, page : friendsPage}, function(response)
			{
				//Checking if success
				if (response.success)
				{
					//Getting friends
					let friends = response.friends;
					//Getting total pages
					let totalPages = response.totalPages;

					//Next page
					friendsPage++;
					//Checking if there is any other page
					if (friendsPage + 1 > totalPages)
					{
						//No more pages
						moreFriendsButton.hide();
						hasNextPage = false;
					}

					//Adding friends
					friends.forEach(friend =>
					{
						friendsList.append('<div>' + `<a href=\"?id=${friend.id}\">${friend.username}</a>` + '</div>');
					});
				}
			});
		}
	}

	//Refreshes the buttons
	function refreshButtons()
	{
		//Disabling all buttons by default
		addFriendButton.hide();
		cancelRequestButton.hide();
		acceptRequestButton.hide();
		declineRequestButton.hide();
		unfriendButton.hide();
		//Checking if relation exists
		if (relation.exists)
		{
			//Checking if accepted
			if (relation.accepted)
			{
				//Showing unfriend button
				unfriendButton.show();
			}
			else //if (!relation.accepted)
			{
				//Checking receiverID
				if (relation.senderID === requestedUserID)
				{
					//Sender is the other person
					acceptRequestButton.show();
					declineRequestButton.show();
				}
				else //if (relation.senderID !== requestedUserID)
				{
					//Sender is current user
					cancelRequestButton.show();
				}
			}
		}
		else //if (!relation.exists)
		{
			//Relation doesn't exist at all
			addFriendButton.show();
		}
	}
	//Gets user relation
	function loadRelation()
	{
		$.post(getFriendRelationUrl, {id : requestedUserID}, function(response)
		{
			//Checking if succeeded
			if (response.success)
			{
				//Adding relation
				relation = response.relation;
				//Refreshing buttons
				refreshButtons();
			}	
		});
	}
	//Requesting information
	$.get(userInformationUrl, {id : requestedUserID}, function(response)
	{
		//Getting status
		let success = response.success;
		//Checking if success
		if (success)
		{
			//Getting account
			let account = response.account;
			//Getting registration time
			let registrationTime = account.registrationTime * 1000;
			//Getting registration date
			let registrationDate = new Date(registrationTime);

			//Loading information
			userIDElem.text(account.id);
			usernameElem.text(account.username);
			registrationDateElem.text(registrationDate.toLocaleString());
			friendsCountElem.text(account.friendsCount);
			//Load friends page
			loadNextFriendsPage();
		}
		else
		{
			//Cannot load
			console.log('Cannot get user information', result);
		}
	});
	//Requesting friend relation
	loadRelation();
	//Load more friends
	moreFriendsButton.click(function(event)
	{
		//Preventing default
		event.preventDefault();
		//Loading next page
		loadNextFriendsPage();
	})
	//Add friend
	addFriendButton.click(function(event)
	{
		//Preventing default
		event.preventDefault();
		//Getting action url
		let actionUrl = addFriendButton.attr('js-action');
		//Post request
		$.post(actionUrl, {receiverID : requestedUserID}, function(response)
		{
			//Checking if succeeded
			if (response.success)
			{
				//Loading relation
				loadRelation();
			}
		});
	});
	//Cancel send
	cancelRequestButton.click(function(event)
	{
		//Preventing default
		event.preventDefault();
		//Getting action url
		let actionUrl = cancelRequestButton.attr('js-action');
		//Post request
		$.post(actionUrl, {id : requestedUserID}, function(response)
		{
			//Checking if succeeded
			if (response.success)
			{
				//Loading relation
				loadRelation();
			}
		});
	});
	//Accept request
	acceptRequestButton.click(function(event)
	{
		//Preventing default
		event.preventDefault();
		//Getting action url
		let actionUrl = acceptRequestButton.attr('js-action');
		//Post request
		$.post(actionUrl, {id : requestedUserID, responseType : 'accept'}, function(response)
		{
			//Checking if succeeded
			if (response.success)
			{
				//Loading relation
				loadRelation();
			}
		});	
	});
	//Decline request
	declineRequestButton.click(function(event)
	{
		//Preventing default
		event.preventDefault();
		//Getting action url
		let actionUrl = declineRequestButton.attr('js-action');
		//Post request
		$.post(actionUrl, {id : requestedUserID, responseType : 'decline'}, function(response)
		{
			//Checking if succeeded
			if (response.success)
			{
				//Loading relation
				loadRelation();
			}
		});	
	});
	//Unfriend
	unfriendButton.click(function(event)
	{
		//Preventing default
		event.preventDefault();
		//Getting action url
		let actionUrl = unfriendButton.attr('js-action');
		//Post request
		$.post(actionUrl, {id : requestedUserID}, function(response)
		{
			//Checking if succeeded
			if (response.success)
			{
				//Loading relation
				loadRelation();
			}
		});
	});
});
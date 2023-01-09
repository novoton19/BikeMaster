/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/05/23 06:35pm
	Version: 0.0.3.3
Updated on
	Version: 0.0.5.1

Description:
	Finds an account and displays information

Changes:
	Version 0.0.5 - Send friend request
	Version 0.0.5.1 - Cancel/Accept/Decline/Unfriend friend actions
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
	let userIDElem = $('#UserID');
	let usernameElem = $('#Username');
	let registrationDateElem = $('#RegistrationDate');
	//Getting friend relation buttons
	let addFriendButton = $('#AddFriendButton');
	let cancelRequestButton = $('#CancelRequestButton');
	let acceptRequestButton = $('#AcceptRequestButton');
	let declineRequestButton = $('#DeclineRequestButton');
	let unfriendButton = $('#UnfriendButton');

	//Getting requested id
	let requestedUserID = parseInt(getParams.get('id'));
	let relation = {
		exists : false,
		accepted : false,
		senderID : null,
		receiverID : null
	};

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
		}
		else
		{
			//Cannot load
			console.log('Cannot get user information', result);
		}
	});
	//Requesting friend relation
	loadRelation();
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
	})
});
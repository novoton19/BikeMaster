/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/26/23 09:41pm
	Version: 0.6.1
Updated on
	Version: 0.6.1

Description:
	Javascript for competition completion

Changes:

*/
//Profile pictures
var profilePicturesPath = '../../Assets/ProfilePictures/Users/';
var defaultProfilePicturePath = '../../Assets/ProfilePictures/Default/default.png';
//Get user information
var userInformationPath = '../../Api/Social/Account/getInformation.php';
//Get sign in status
var statusPath = '../../Api/User/status.php';
//Create competition
var createCompetitionPath = '../../Api/Challenges/Competitions/create.php';
//Competitions page
var competitionsPagePath = '../';

//Waiting for document to load
$(document).ready(() =>
{
	//Creating network manager
	const networkManager = new NetworkManager();
	//Getting elements
	const content = $('#content');
	const create = content.find('#create');
	const form = create.find('form');
	const formReasonElem = form.find('.formReason');
	const record = form.find('.record');
	const profilePictureElem = record.find('.profilePicture');
	const usernameElem = record.find('.username');
	const descriptionElem = record.find('.description');
	const titleInput = form.find('#title');
	const titleReason = form.find('label.information[for=\"title\"]');
	const descriptionInput = form.find('#description');
	const descriptionReason = form.find('label.information[for=\"description\"]');
	const conditionInput = form.find('#distance');
	const conditionReason = form.find('label.information[for=\"distance\"]');
	const submitButton = form.find('.submitButton');
	
	//Loading search params
	var searchParams = new URLSearchParams(window.location.search);
	var id = searchParams.get('id');
	//Verifying ID
	if (id === null || !id.length)
	{
		window.location.href = 'SearchOpponent/';
		return;
	}

	//On page requested
	function onPageRequested()
	{
		//Hide content
		create.hide();
		//Return required files
		return [{
			url : statusPath
		}, {
			url : userInformationPath,
			data : {
				id : id
			}
		}];
	}
	//On page loaded
	function onPageLoaded(responses)
	{
		//Show content
		create.show();
		//Getting account
		let loggedInAccount = responses[0].account;
		let requestedAccount = responses[1].account;
		//Verifying ID
		if (loggedInAccount.id === requestedAccount.id)
		{
			//Redirect to search opponent, cannot create competition with yourself
			window.location.href = 'SearchOpponent/';
		}
		//Getting information
		let username = requestedAccount.username;
		let description = requestedAccount.description;
		let profilePictureUrl = requestedAccount.profilePictureUrl !== null ? profilePicturesPath + requestedAccount.profilePictureUrl : defaultProfilePicturePath;
		//Loading information
		usernameElem.text(username);
		descriptionElem.text(description);
		profilePictureElem.attr('src', profilePictureUrl);
	}
	//On form changed
	function onFormChanged()
	{
		//Getting title and description
		let title = titleInput.val();
		let description = descriptionInput.val();
		let distance = parseFloat(conditionInput.val());
		//Getting lengths of title and description
		let titleLen = title.length;
		let descriptionLen = description.length;
		//Adding information
		titleReason.text(`${titleLen}/32`);
		descriptionReason.text(`${descriptionLen}/512`);
		conditionReason.text(`${distance}km`);
		//Update button
		submitButton.prop('disabled', titleLen > 32 || descriptionLen > 512 || distance < 5 || distance > 100 || !networkManager.online);

		//Creating search params
		let searchParams = new URLSearchParams(window.location.search);
		searchParams.set('title', title);
		searchParams.set('description', description);
		searchParams.set('distance', distance);
		window.history.pushState(null, null, `?${searchParams.toString()}`);
	}
	//On form submitted
	function onFormSubmitted(event)
	{
		//Cancel send
		event.preventDefault();
		//Sending request
		sendRequest(
			createCompetitionPath,
			{
				userID : id,
				title : titleInput.val(),
				description : descriptionInput.val(),
				distance : conditionInput.val()
			}
		).then((response) =>
		{
			//Request succeeded
			window.location.href = competitionsPagePath;
		}).catch((information) =>
		{
			formReasonElem.text(information.reason);
			formReasonElem.show();
		});
	}

	//Adding form event listeners
	networkManager.addEventListener('onStatusChanged', () => onFormChanged());
	titleInput.on('keyup change', () => onFormChanged());
	descriptionInput.on('keyup change', () => onFormChanged());
	conditionInput.on('input', () => onFormChanged());
	form.submit((event) => onFormSubmitted(event));

	//Adding loader events
	window.onPageRequested = onPageRequested;
	window.onPageLoaded = onPageLoaded;
	window.onPageFailed = (information) => information.reason;

	//Loading search params
	var title = searchParams.get('title') || '';
	var description = searchParams.get('description') || '';
	var distance = searchParams.get('distance') || 25;
	//Loading inputs
	titleInput.val(title);
	descriptionInput.val(description);
	conditionInput.val(distance);
	onFormChanged();
});
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/10/23 11:05pm
	Version: 0.3
Updated on
	Version: 0.3

Description:
	Javascript for Sign In

Changes:
	 
*/
//Status request url
const statusUrl = '../../Api/User/status.php';
const reasonIDsUrl = '../../Api/getAllReasonIDs.php';
//Getting current script name
const mainName = document.currentScript.src.split('/').pop();

//Waiting for page to load
$(document).ready(() =>
{
	//Creating network manager
	const networkManager = new NetworkManager();
	//Getting form
	const formWrapper = $('.formWrapper');
	const signInForm = $('#signInForm');
	const action = signInForm.attr('jsAction');
	const method = signInForm.attr('method') || 'post';
	const formReason = signInForm.find('.formReason');
	const usernameInput = signInForm.find('#usernameOrEmail');
	const passwordInput = signInForm.find('#password');
	const submitButton = signInForm.find('#submitButton');


	//Clears whole form
	function clearForm()
	{
		//Removing information from session storage
		sessionStorage.removeItem('signInForm');
		//Clearing input reason
		formReason.hide();
		//Clearing inputs
		signInForm.find('input').each((index, input) =>
		{
			//Getting reason
			let id = $(input).attr('id');
			let inputReasonElem = signInForm.find(`.inputReason[for=\"${id}\"]`);
			
			$(input).val('');
			$(input).removeClass('invalid');
			inputReasonElem.hide();
		});
	}
	//Loads form response
	function loadResponse(response)
	{
		if (response.reason)
		{
			//Showing reason
			formReason.text(response.reason);
			formReason.show(200);
		}
		else//if (!response.reason)
		{
			formReason.hide(200);
		}

		//Checking if success
		if (!response.success)
		{
			//Mark inputs as invalid
			usernameInput.addClass('invalid');
			passwordInput.addClass('invalid');
		}
		else//if (response.success)
		{
			//Mark inputs as invalid
			usernameInput.removeClass('invalid');
			passwordInput.removeClass('invalid');
		}
		//Getting inputs
		let inputs = response.inputs;
		let inputNames = Object.keys(inputs);
		//Loading input values
		for (let keyNum = 0; keyNum < inputNames.length; keyNum++)
		{
			//Getting input name
			let inputName = inputNames[keyNum];
			let inputValue = inputs[inputName];
			//Getting input
			let input = signInForm.find(`#${inputName}`);
			//Adding value
			input.val(inputValue);
		}
	}
	//Called when user submits the form
	function onFormSubmit(event)
	{
		//Prevent send
		event.preventDefault();
		//Send request
		sendRequest(action, signInForm.serialize(), method).then((response) =>
		{
			//Request succeeded
			//Remove previous information from session storage
			sessionStorage.removeItem('signInForm');
			//Forward to setup page
			window.location.replace('/Pwa/BikeMaster/Account/Welcome/');
		}).catch((information) =>
		{
			//Request did not succeed, show form reason
			//Checking if response is structured
			if (information.structured)
			{
				//Getting response
				let response = information.response
				//Loading response
				loadResponse(response);
				//Adding time to response
				response.time = new Date().getTime();
				//Add to session storage
				sessionStorage.setItem('signInForm', JSON.stringify(response));
			}
			else//if (!information.structured)
			{
				//Showing reason
				formReason.text(information.reason);
				formReason.show(200);
				//Mark inputs as invalid
				usernameInput.addClass('invalid');
				passwordInput.addClass('invalid');
			}
		});
	}
	//On network changed
	function onNetworkChange()
	{
		//Checking if online
		if (networkManager.online)
		{
			//Enable button
			submitButton.prop('disabled', false);
		}
		else//if (!networkManager.online)
		{
			//Disable button
			submitButton.prop('disabled', true);
		}
	}
	//Loads form values from session storage
	function fromSessionStorage(informationString)
	{
		//Checking if information exists
		if (!informationString)
		{
			return;
		}
		let information;
		//Attempting to convert to object
		try
		{
			information = JSON.parse(informationString);
		}
		catch(e)
		{
			return;
		}
		//Checking timeout
		if (!information.time || new Date().getTime() > information.time + 300000)
		{
			return;
		}
		//Loading information
		loadResponse(information);
	}
	//Called when user requests refresh
	function onFormRefresh()
	{
		//Hide form wrapper
		formWrapper.hide();
		//Return urls necessary for refresh
		return [{
			url : statusUrl
		}, {
			url : reasonIDsUrl
		}];
	}
	//Called when refresh completes
	function onFormRefreshCompleted(responses)
	{
		//Getting signin status
		let status = responses[0];
		let reasonIDs = responses[1].reasonIDs;

		//Showing wrapper
		formWrapper.show(200);
		//Whether timed out
		let timedOut = status.timeout && status.timeout <= new Date().getTime() / 1000
		//Checking if logged in
		if (status.loggedIn && !timedOut)
		{
			//Forward to setup page
			window.location.replace('/Pwa/BikeMaster/Account/Welcome/');
		}
		else//if (!status.loggedIn || timedOut)
		{
			//Correct page
			//Getting account
			let account = status.account;
			let username = status.username;
			let reasonID = status.reasonID;
			//Form reason
			let text;
			//Determining form reason
			if (reasonID === reasonIDs.TimedOut || timedOut)
			{
				text = 'Session expired, sign in again please';
			}
			else if (reasonID === reasonIDs.NotLoggedIn)
			{
				//Do not write reason
			}
			//Loading response
			let response = {
				reason : text,
				success : true,
				inputs : {
					username : username
				}
			};
			loadResponse(response);
		}
	}

	//Adding submit event listener
	signInForm.submit(onFormSubmit);
	//On network changed
	networkManager.addEventListener('onStatusChanged', onNetworkChange);
	onNetworkChange();

	fromSessionStorage(sessionStorage.getItem('signInForm'));

	window.onFormRefresh = onFormRefresh;
	window.onFormRefreshCompleted = onFormRefreshCompleted;
	window.onFormRefreshFailed = (information) => information.reason;
});
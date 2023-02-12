/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/11/23 08:04pm
	Version: 0.3
Updated on
	Version: 0.3

Description:
	Javascript for Sign Up

Changes:
	 
*/
//Status request url
const statusUrl = '../../Api/User/status.php';
//Getting current script name
const mainName = document.currentScript.src.split('/').pop();

//Waiting for page to load
$(document).ready(() =>
{
	//Creating network manager
	const networkManager = new NetworkManager();
	//Getting form
	const formWrapper = $('.formWrapper');
	const signUpForm = $('#signUpForm');
	const action = signUpForm.attr('jsAction');
	const method = signUpForm.attr('method') || 'post';
	//Getting form elements
	const formReason = signUpForm.find('.formReason');
	const usernameInput = signUpForm.find('#username');
	const emailInput = signUpForm.find('#email');
	const passwordInput = signUpForm.find('#password');
	const passwordConfirmationInput = signUpForm.find('#passwordConfirmation');
	const submitButton = signUpForm.find('#submitButton');


	//Clears whole form
	function clearForm()
	{
		//Removing information from session storage
		sessionStorage.removeItem('signUpForm');
		//Clearing input reason
		formReason.hide();
		//Clearing inputs
		signUpForm.find('input').each((index, input) =>
		{
			//Getting reason
			let id = $(input).attr('id');
			let inputReasonElem = signUpForm.find(`.inputReason[for=\"${id}\"]`);
			
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
		else
		{
			formReason.hide(200);
		}

		//Getting input reasons
		let inputReasons = response.inputReasons;
		//Getting inputs
		let inputs = response.inputs;
		//Getting input names
		let inputNames = Object.keys(inputReasons);
		//Loading input reasons
		for (let keyNum = 0; keyNum < inputNames.length; keyNum++)
		{
			//Getting input name
			let inputName = inputNames[keyNum];
			let inputReason = inputReasons[inputName];
			//Getting input
			let input = signUpForm.find(`#${inputName}`);
			//Getting input reason
			let inputReasonElem = signUpForm.find(`.inputReason[for=\"${inputName}\"]`);
			//Checking if input is valid
			if (inputReason.valid)
			{
				input.removeClass('invalid');
				inputReasonElem.hide(200);
			}
			else//if (!inputReason.valid)
			{
				input.addClass('invalid')
				inputReasonElem.text(inputReason.reason);
				inputReasonElem.show(200);
			}
		}
		inputNames = Object.keys(inputs);
		//Loading input values
		for (let keyNum = 0; keyNum < inputNames.length; keyNum++)
		{
			//Getting input name
			let inputName = inputNames[keyNum];
			let inputValue = inputs[inputName];
			//Getting input
			let input = signUpForm.find(`#${inputName}`);
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
		sendRequest(action, signUpForm.serialize(), method).then((response) =>
		{
			//Request succeeded
			//Remove previous information from session storage
			sessionStorage.removeItem('signUpForm');
			//Forward to welcome page
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
				sessionStorage.setItem('signUpForm', JSON.stringify(response));
			}
			else//if (!information.structured)
			{
				//Showing reason
				formReason.text(information.reason);
				formReason.show(200);
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
		}];
	}
	//Called when refresh completes
	function onFormRefreshCompleted(responses)
	{
		//Getting signin status
		let status = responses[0];

		//Showing wrapper
		formWrapper.show(200);
		//Whether timed out
		let timedOut = status.timeout && status.timeout <= new Date().getTime() / 1000
		//Checking if logged in
		if (status.loggedIn && !timedOut)
		{
			//Back to homepage
			window.location.replace('/Pwa/BikeMaster/Home/');
		}
		else//if (!status.loggedIn || timedOut)
		{
			//Correct page
		}
	}

	//Adding submit event listener
	signUpForm.submit(onFormSubmit);
	//On network changed
	networkManager.addEventListener('onStatusChanged', onNetworkChange);
	onNetworkChange();

	fromSessionStorage(sessionStorage.getItem('signUpForm'));
	
	window.onFormRefresh = onFormRefresh;
	window.onFormRefreshCompleted = onFormRefreshCompleted;
	window.onFormRefreshFailed = (information) => information.reason;
});
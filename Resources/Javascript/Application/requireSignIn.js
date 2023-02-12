/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/10/23 08:30pm
	Version: 0.3
Updated on
	Version: 0.3

Description:
	Checks if user is logged in, if not, send him to login page

Changes:
	 
*/
//Check if not error page
if (location.protocol + '//' + location.host + location.pathname !== new URL('/Pwa/BikeMaster/error.html', location.protocol + '//' + location.host).href)
{
	//Make a request for signIn status
	$.ajax({
		url : '/Pwa/BikeMaster/Api/User/status.php',
		timeout : 5000,
		success : (response) =>
		{
			//Checking if valid response given
			if (typeof(response) === 'object' && response.success)
			{
				//Checking if logged in
				if (response.loggedIn && response.timeout > new Date().getTime() / 1000)
				{
					//Everything in order
				}
				else
				{
					//Redirect to login
					window.location.replace('/Pwa/BikeMaster/Account/SignIn/');
				}
			}
			else//if (typeof(response) !== 'object' || !response.success)
			{
				let sourceScript = 'requireSignIn.js';
				let sourcePage = window.location.href;
				let message = 'Wrong request';
				let detailedMessage = 'I wanted to check if you are signed in, however it seems like the request did not succeed';

				//Create get params
				let getParams = new URLSearchParams();
				getParams.set('message', message);
				getParams.set('sourceScript', sourceScript);
				getParams.set('detailedMessage', detailedMessage);
				getParams.set('sourcePage', sourcePage);
				//Redirect
				window.location.replace(`/Pwa/BikeMaster/error.html?${getParams.toString()}`);
			}
		},
		error : (response, textStatus) =>
		{
			let sourceScript = 'requireSignIn.js';
			let sourcePage = window.location.href;
			let message;
			let detailedMessage = 'I wanted to check if you are signed in, however ';
			//Checking status
			if (textStatus === 'timeout')
			{
				message = 'Request timed out';
				detailedMessage += 'the request timed out and I was not able to complete my verification process';
			}
			else if (textStatus === 'abort')
			{
				message = 'Request has been aborted';
				detailedMessage += 'my request got aborted and I was not able to complete my verification process'
			}
			else if (textStatus === 'parsererror')
			{
				message = 'Wrong request';
				detailedMessage += 'the server returned information in unknown format therefore I was not able to complete my verification process';
			}
			else //if (textStatus === 'error')
			{
				//Checking if offline
				if (!navigator.onLine)
				{
					message = 'You are offline';
					detailedMessage += 'it seems like you are offline and I haven\'t found a backup to catch up to';
				}					
				else
				{
					//Checking status
					if (response.status === 404)
					{
						message = 'Wrong request';
						detailedMessage += 'it seems like the requested URL doesn\'t exist anymore';
					}
					else
					{
						message = response.statusText;
						detailedMessage += 'an error occured and I couldn\'t complete my verification process';
					}
				}
			}
			//Create get params
			let getParams = new URLSearchParams();
			getParams.set('message', message);
			getParams.set('sourceScript', sourceScript);
			getParams.set('detailedMessage', detailedMessage);
			getParams.set('sourcePage', sourcePage);
			//Redirect
			window.location.replace(`/Pwa/BikeMaster/error.html?${getParams.toString()}`);
		}
	});
}
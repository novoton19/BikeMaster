/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/10/23 08:30pm
	Version: 0.3
Updated on
	Version: 0.3.1

Description:
	Checks if user is logged in, if not, send him to login page

Changes:
	Version 0.3.1 - Functional script + colaborate with newest scripts
*/
//Path to files
var errorUrl = '/Pwa/BikeMaster/error.html';
var statusUrl = '/Pwa/BikeMaster/Api/User/status.php';
var signInUrl = '/Pwa/BikeMaster/Account/SignIn/';
//Getting current page url
var crtPageHost = location.protocol + '//' + location.host;
var crtPageUrl = crtPageHost + location.pathname;
var errorPageUrl = new URL(errorUrl, crtPageHost).href;
//Getting current script name
var requireSignInName = document.currentScript.src.split('/').pop();

//Checking current page (if on error page, it may redirect forever)
if (crtPageUrl !== errorPageUrl)
{
	//Requesting status
	sendMultipleRequests([{
		url : statusUrl
	}]).then((response) =>
	{
		//Everything in order
	}).catch((information) =>
	{
		//Request did not succeed
		//Checking if request is structured
		if (information.structured)
		{
			//Forwarding to sign in
			window.location.replace(signInUrl);
		}
		else if (!navigator.onLine)
		{
			//Forwarding to offline page
			forwardToOfflinePage();
		}
		else//if (!information.structured && navigator.onLine)
		{
			//Forwarding to error page
			forwardToErrorPage(
				information.reason,
				requireSignInName
			);
		}
	});
}
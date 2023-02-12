/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/11/23 05:49pm
	Version: 0.3
Updated on
	Version: 0.3

Description:
	Forwards to appropriate page

Changes:

*/
//Forwards user to error page
function forwardToErrorPage(message, sourceScript)
{
	//Creating get parameters
	let getParams = new URLSearchParams();
	//Adding information
	getParams.set('message', message);
	getParams.set('sourcePage', window.location.href);
	getParams.set('sourceScript', sourceScript);
	//Redirect
	window.location.replace(`/Pwa/BikeMaster/error.html?${getParams.toString()}`);
}
//Forwards user to offline page
function forwardToOfflinePage()
{

}
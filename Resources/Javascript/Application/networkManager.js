/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/10/23 11:45pm
	Version: 0.1
Updated on
	Version: 0.1

Description:
	Listens for network changes

Changes:

*/
//Network manager
class NetworkManager extends EventTarget
{
	//Whether online
	online;
	//Event
	#statusChangedEvent = new Event('onStatusChanged');
	//Constructor
	constructor()
	{
		super();
		this.online = navigator.onLine || false;

		//Listening for offline state
		window.addEventListener('offline', (event) =>
		{
			//Refresh status
			this.online = false;
			this.dispatchEvent(this.#statusChangedEvent);
		});
		//Listening for online status
		window.addEventListener('online', (event) =>
		{
			//Refresh status
			this.online = true;
			this.dispatchEvent(this.#statusChangedEvent);
		});
	}
}
//Network manager with notification
class PageNetworkManager extends NetworkManager
{
	alert;
	//Constructor
	constructor()
	{
		super();

		//Loading alert
		$.get('/Pwa/BikeMaster/Resources/Html/Application/offlineAlert.html', {}, (response) =>
		{
			$('#bodyWrapper').prepend(response);
			//Creating alert
			let alert = $('#bodyWrapper').find('.offlineAlert');
			this.alert = alert;
			//Hide by default
			alert.hide();

			//Refresh status on event
			this.addEventListener('onStatusChanged', () => this.refreshAlert());
			this.refreshAlert();
		});
	}
	refreshAlert()
	{
		//Checking if offline
		if (!this.online)
		{
			this.alert.show(400);
		}
		else //if (this.online)
		{
			this.alert.hide(400);
		}
	}
}
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/07/23 05:23pm
	Version: 0.1
Updated on
	Version: 0.1

Description:
	Manager for content status

Changes:

*/
//ContentManager
class ContentManager
{
	//Overlays
	loadingOverlay;
	loadErrorOverlay;
	reloadButton;
	//Network manager
	networkManager;
	//Function that will be called when the retry button is pressed 
	#reloadFunction;

	//Constructor
	constructor(reloadFunction, parentSelector = '#content')
	{
		//Adding reload function
		this.#reloadFunction = reloadFunction;
		//Network manager
		let networkManager = new PageNetworkManager();
		this.networkManager = networkManager;

		//Loading overlays
		$.get('/Pwa/BikeMaster/Resources/Html/Application/overlays.html', {}, (response) =>
		{
			//Adding overlays
			$(parentSelector).append(response);
			//Getting overlays
			let loadingOverlay = $(`${parentSelector} .loadingOverlay`);
			loadingOverlay.removeClass('uk-hidden');
			loadingOverlay.hide();
			let loadErrorOverlay = $(`${parentSelector} .loadErrorOverlay`);
			loadErrorOverlay.removeClass('uk-hidden');
			loadErrorOverlay.hide();
			let reloadButton = loadErrorOverlay.find('.reloadButton');
			this.reloadButton = reloadButton;

			//Adding overlays
			this.loadingOverlay = loadingOverlay;
			this.loadErrorOverlay = loadErrorOverlay
			
			//Adding event listener
			reloadButton.click((event) =>
			{
				event.preventDefault();
				//Reloading
				this.setStateLoading(reloadFunction);
			});
			//Reloading by default
			this.setStateLoading(reloadFunction);
			this.onNetworkStateChanged();
		});
		//Network changes
		networkManager.addEventListener('onStatusChanged', () => this.onNetworkStateChanged());
	}
	//Limits functionality when offline
	onNetworkStateChanged()
	{
		//Checking if online
		if (!this.networkManager.online)
		{
			//Disable button, enable tooltip
			this.reloadButton.attr('disabled', '');
			this.reloadButton.attr('uk-tooltip', 'You are currently offline');
		}
		else //if (networkManager.online)
		{
			///Enable button, disable tooltip
			this.reloadButton.removeAttr('disabled');
			this.reloadButton.removeAttr('uk-tooltip');
		}
	}
	//Show loading overlay
	setStateLoading(callback)
	{
		if (this.loadErrorOverlay.is(':hidden'))
		{
			this.loadingOverlay.show(400, callback);
		}
		else
		{
			this.loadErrorOverlay.hide(400, () =>
			{
				this.loadingOverlay.show(400, callback);
			});
		}
	}
	//Show error overlay
	setStateError(errorMessage, callback)
	{
		//Adding error message
		this.loadErrorOverlay.find('.errorText').text(errorMessage);

		if (this.loadingOverlay.is(':hidden'))
		{
			this.loadErrorOverlay.show(400, callback);
		}
		else
		{
			this.loadingOverlay.hide(400, () =>
			{
				this.loadErrorOverlay.show(400, callback);
			});
		}
	}
	//Hide overlays
	setStateSolved(callback)
	{
		//Hiding all overlays
		this.loadingOverlay.hide(400);
		this.loadErrorOverlay.hide(400, callback);
	}
}
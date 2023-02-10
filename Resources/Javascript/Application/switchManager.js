/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/26/23 9:18am
	Version: 0.1.2
Updated on
	Version: 0.1.2

Description:
	Switch manager

Changes:
	 
*/
//Getting get params
let getParams = new URLSearchParams(window.location.search);

//Waiting for document to load
$(document).ready(() =>
{
	//Getting page switchers
	const switchers = $('[switcher]');
	//Getting distinct switcher IDs
	let switcherIDs = switchers.toArray().map((switcher) =>
	{
		//Get switcher ID
		return $(switcher).attr('switcherID');
	}).filter((switcherID, index, array) =>
	{
		//Filter out duplicate IDs
		return array.indexOf(switcherID) == index;
	});
	
	//Setting up switchers
	switcherIDs.forEach((switcherID) =>
	{
		//Getting switchers
		let switchers = $(`[switcher][switcherID=\"${switcherID}\"]`);
		//Getting all switches
		let switches = switchers.find('[switchID]');
		//Getting all switch IDs
		let switchIDs = switches.toArray().map((switchElem) =>
		{
			//Get switch ID
			return $(switchElem).attr('switchID');
		}).filter((switchID, index, array) =>
		{
			//Filter out duplicate IDs
			return array.indexOf(switchID) == index;
		});

		//Checking if there are any switchIds
		if (switchIDs.length === 0)
		{
			return;
		}
		//Getting default switch ID
		let defaultSwitchID = switchIDs.find((switchID) =>
		{
			//Getting switch
			let switchElem = $(`[id=\"${switchID}\"]`);
			//Checking if should be treated as default
			return switchElem.attr('switchDefault') !== undefined;
		}) || switchIDs[0];

		//Switches element
		function switchElement(targetSwitchID)
		{
			//Remove class from each switch except for selected ones
			switches.each((index, switchElem) =>
			{
				//Getting switchID
				let switchID = $(switchElem).attr('switchID');
				let switchTarget = $(`[id=\"${switchID}\"]`);
				//Checking if selected
				if (switchID === targetSwitchID)
				{
					$(switchElem).addClass('blue');
					switchTarget.show(200);
				}
				else
				{
					$(switchElem).removeClass('blue');
					switchTarget.hide(200);
				}
			});
			//Getting get params
			let getParams = new URLSearchParams(window.location.search);
			//Checking if is default
			if (targetSwitchID === defaultSwitchID)
			{
				//Removing section
				getParams.delete(switcherID);
			}
			else
			{
				//Setting new section
				getParams.set(switcherID, targetSwitchID);
			}
			//Updating information
			if (getParams.toString())
			{
				window.history.replaceState(null, null, '?' + getParams.toString());
			}
			else
			{
				window.history.replaceState(null, null, window.location.pathname);
			}
		}
		//Getting current switch id
		let switchID = getParams.get(switcherID);
		let target = $(`[id=\"${switchID}\"]`);
		//Checking if switch exists
		if (switchID !== null && target.length !== 0)
		{
			switchElement(switchID);
		}
		else
		{
			switchElement(defaultSwitchID);
		}
		//Loading switches
		switches.each((switchNum, switchElem) =>
		{
			//Getting switch ID
			let switchID = $(switchElem).attr('switchID');
			//Adding click event listener
			$(switchElem).click(() =>
			{
				switchElement(switchID);
			});
		});
	});
});
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/24/23 04:44pm
	Version: 0.4.4
Updated on
	Version: 1

Description:
	Loads journey history

Changes:
	Version 0.4.4.1 - Download button
*/
//Files
//Html files
var journeyHtmlPath = '../Resources/Html/Application/journey.html';
//Api requestes
var getJourneysPath = '../Api/Journey/getJourneys.php';
var changeArchivationPath = '../Api/Journey/changeArchivation.php';
//Redirects
var viewJourneyPath = 'View/';

//Waiting for document to load
$(document).ready(() =>
{
	//Getting page elements
	var history = $('#history');
	var recordsWrapper = history.find('.recordsWrapper');
	var journeysCountElem = recordsWrapper.find('.journeysCount');
	var records = recordsWrapper.find('.records');
	var noResults = recordsWrapper.find('.noResults');

	//Journey.html
	var journeyHtml = null;
	//Current page
	var currentPage = 0;
	//Whether on last page
	var lastPage = false;

	//On page requested
	function onHistoryPageRequested()
	{
		//Hiding previous results
		recordsWrapper.hide();
		//Return required files
		return [{
			url : journeyHtmlPath
		}];
	}
	//On page loaded
	function onHistoryPageLoaded(responses)
	{
		//Getting journey html
		journeyHtml = responses[0];
		//Resetting page
		currentPage = 0;
		lastPage = false;
		//Clear anything from records (' ' is there because of contentLoadManager)
		records.text(' ');
		journeysCountElem.text('?');
		//Show records wrapper
		noResults.hide();
		recordsWrapper.show();
	}
	//On history requested
	function onHistoryRequested()
	{
		//Checking if on last page
		if (lastPage)
		{
			//Return empty set
			return [];
		}
		//Return reuqest
		return [{
			url : getJourneysPath,
			data : {
				page : currentPage,
				type : 'current'
			}
		}];
	}
	//On results loaded
	function onHistoryLoaded(responses)
	{
		//Checking if reached last page
		if (lastPage)
		{
			return;
		}
		//Getting result
		let journeysResult = responses[0];
		let journeysCount = journeysResult.resultsCount;
		let journeys = journeysResult.result;
		let page = journeysResult.inputs.page;
		//Checking page
		if (page !== currentPage)
		{
			//This is not the page we want next
			return;
		}
		//Checking maximum page
		if (currentPage < journeysResult.totalPages)
		{
			//Increment page
			currentPage += 1;
		}
		else
		{
			//Show no results page
			lastPage = true;
			noResults.show();
		}
		//Adding journeys count
		journeysCountElem.text(journeysCount);
		//Appending journeys
		journeys.forEach((journey) =>
		{
			//Creating new record
			let record = $(journeyHtml);
			//Getting elements
			let userDetails = record.find('.userDetails');
			let map = record.find('.map');
			//Journey information
			let titleElem = record.find('.title');
			let descriptionElem = record.find('.description');
			let dateElem = record.find('span.date');
			let durationElem = record.find('span.duration');
			let lengthElem = record.find('span.distance');
			let speedElem = record.find('span.speed');
			//Action buttons
			let downloadButton = record.find('.downloadButton');
			let detailsButton = record.find('.detailsButton');
			let archiveButton = record.find('.archiveButton');
			let unarchiveButton = record.find('.unarchiveButton');

			//Hide information about owner (owner is known)
			userDetails.hide();
			//Journey cannot be unarchived
			unarchiveButton.hide();
			
			//Getting journey information
			let id = journey.id;
			let title = journey.title;
			let description = journey.description;
			let creationTime = journey.creationTime;
			let startTime = journey.startTime;
			let endTime = journey.endTime;
			let duration = endTime - startTime;
			let length = journey.length;
			

			//Calculating journey time
			let seconds = duration;
			let minutes = Math.floor(seconds / 60);
			let hours = Math.floor(minutes / 60);
			//Updating values
			seconds -= minutes * 60 + hours * 60 * 60;
			minutes -= hours * 60;
			//Formatting values
			seconds = seconds >= 10 ? seconds : '0' + seconds;
			minutes = minutes >= 10 ? minutes : '0' + minutes;
			hours = hours >= 10 ? hours : '0' + hours;

			
			//Getting texts
			let dateText = new Date(creationTime * 1000).toLocaleDateString();
			let durationText = `${hours}:${minutes}:${seconds}`
			let lengthText = `${Math.floor(length * 100) / 100}km`;
			let speedText = `${Math.floor(length / (duration / 3600) * 100) / 100}km/h`;
			
			//Getting journey GPX
			let gpx = GpxConverter.toGpx(journey);
			//Creating get params for details url
			let getParams = new URLSearchParams();
			getParams.set('id', id);
			

			//Adding ID to map
			map.attr('id', 'onlineSource_' + id);
			//Adding texts
			dateElem.text(dateText);
			durationElem.text(durationText);
			lengthElem.text(lengthText);
			speedElem.text(speedText);
			titleElem.text(title);
			descriptionElem.text(description);


			//Loading buttons
			//https://ourcodeworld.com/articles/read/189/how-to-create-a-file-and-generate-a-download-with-javascript-in-the-browser-without-a-server
			downloadButton.attr('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(gpx));
			downloadButton.attr('download', 'downloadedJourney_onlineSource_' + id + '.gpx');
			detailsButton.attr('href', viewJourneyPath + '?' + getParams.toString());
			
			//On archive button pressed
			archiveButton.click(() =>
			{
				//Send to archive
				sendRequest(changeArchivationPath, { id : id }).then(() =>
				{
					archiveButton.hide();
					unarchiveButton.show();
				}).catch(() =>
				{

				});
			});
			//On unarchive button pressed
			unarchiveButton.click(() =>
			{
				//Send back to history
				sendRequest(changeArchivationPath, { id : id }).then(() =>
				{
					unarchiveButton.hide();
					archiveButton.show();
				}).catch(() =>
				{

				});
			});
			//Adding record to the page
			records.append($(record));
			//Creating map manager
			let mapManager = new MapManager('onlineSource_' + id, false);
			mapManager.loadGpx(gpx);
			mapManager.fitGpxLayer();
		});
	}

	window.onHistoryPageRequested = onHistoryPageRequested;
	window.onHistoryPageLoaded = onHistoryPageLoaded;
	window.onHistoryPageNotLoaded = (information) => information.reason;
	window.onHistoryRequested = onHistoryRequested;
	window.onHistoryLoaded = onHistoryLoaded;
	window.onHistoryNotLoaded = (information) => information.reason;
});
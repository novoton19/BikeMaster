/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/24/23 04:44pm
	Version: 0.4.4
Updated on
	Version: 0.4.4.1

Description:
	Loads journey history

Changes:
	Version 0.4.4.1 - Download button
*/
//Url to journey html
var journeyHtmlUrl = '../Resources/Html/Application/journey.html';
var viewJourneyUrl = 'View/';
//Url to get journeys
var getJourneysUrl = '../Api/Journey/getJourneys.php';
//Url to change archivation
var changeArchivationUrl = '../Api/Journey/changeArchivation.php';

//Waiting for document to load
$(document).ready(() =>
{
	var history = $('#history');
	var recordsWrapper = history.find('.recordsWrapper');
	var journeysCountElem = recordsWrapper.find('.journeysCount');
	var records = recordsWrapper.find('.records');
	var noResults = recordsWrapper.find('.noResults');

	var journeyHtml = null;
	var currentPage = 0;
	var lastPage = false;

	function onHistoryPageRequested()
	{
		recordsWrapper.hide();
		return [{
			url : journeyHtmlUrl
		}];
	}
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
	function onHistoryRequested()
	{
		if (lastPage)
		{
			return [];
		}
		return [{
			url : getJourneysUrl,
			data : {
				page : currentPage,
				type : 'current'
			}
		}];
	}
	function onHistoryLoaded(responses)
	{
		if (lastPage)
		{
			return;
		}
		//Getting result
		let journeysResult = responses[0]
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
			let map = record.find('.map');
			let title = record.find('.title');
			let description = record.find('.description');
			let downloadButton = record.find('.downloadButton');
			let detailsButton = record.find('.detailsButton');
			let archiveButton = record.find('.archiveButton');
			let unarchiveButton = record.find('.unarchiveButton');
			let dateElem = record.find('span.date');
			let durationElem = record.find('span.duration');
			let length = record.find('span.distance');

			unarchiveButton.hide();
			
			//Getting journey info
			let id = journey.id;
			let date = new Date(journey.creationTime * 1000).toLocaleDateString();
			//Converting to gpx
			let gpx = GpxConverter.toGpx(journey);

			//Getting seconds
			let seconds = journey.endTime - journey.startTime;
			//Getting minutes
			let minutes = Math.floor(seconds / 60);
			//Getting hours
			let hours = Math.floor(minutes / 60);
			//Updating values
			seconds -= minutes * 60 + hours * 60 * 60;
			minutes -= hours * 60;
			//Formatting values
			seconds = seconds >= 10 ? seconds : '0' + seconds;
			minutes = minutes >= 10 ? minutes : '0' + minutes;
			hours = hours >= 10 ? hours : '0' + hours;


			//Adding button click events
			archiveButton.click(() =>
			{
				//Send to archive
				sendRequest(changeArchivationUrl, { id : id }).then(() =>
				{
					archiveButton.hide();
					unarchiveButton.show();
				}).catch(() =>
				{

				});
			});
			unarchiveButton.click(() =>
			{
				//Send back to history
				sendRequest(changeArchivationUrl, { id : id }).then(() =>
				{
					unarchiveButton.hide();
					archiveButton.show();
				}).catch(() =>
				{

				});
			});

			
		
			//Adding ID to map
			map.attr('id', 'onlineSource_' + id);
			//Adding texts
			dateElem.text(date);
			durationElem.text(`${hours}:${minutes}:${seconds}`);
			length.text(`${Math.round(journey.length * 100) / 100} km`);
			
			//Creating get params for details url
			let getParams = new URLSearchParams();
			getParams.set('id', id);
			
			title.text(journey.title);
			description.text(journey.description);
			//https://ourcodeworld.com/articles/read/189/how-to-create-a-file-and-generate-a-download-with-javascript-in-the-browser-without-a-server
			downloadButton.attr('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(gpx));
			downloadButton.attr('download', 'downloadedJourney_onlineSource_' + id + '.gpx');
			detailsButton.attr('href', viewJourneyUrl + '?' + getParams.toString());
			
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
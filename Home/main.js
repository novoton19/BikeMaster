/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/07/23 09:39pm
	Version: 0.0.4.3
Updated on
	Version: 0.0.4.3

Description:
	Loads homepage information

Changes:
	 
*/
//Api url
const getActitivitiesUrl = '../Api/getActivities.php';
//Waiting for document to load
$(document).ready(() =>
{
	//Getting elements
	let activitiesElem = $('#Activities');
	//Getting activities
	$.get(getActitivitiesUrl, {}, function(response)
	{
		//Getting activities
		let activities = response.activities;
		//Loading journeys
		activities.forEach(activity =>
		{
			//Getting segments
			let activitySegments = activity.information.Segments;
			//List of segments
			let segments = [];
			//Loading segments
			activitySegments.forEach(activitySegment =>
			{
				//Getting points
				let activityPoints = activitySegment.Points;
				//List of points
				let points = [];
				//Loading points
				activityPoints.forEach(activityPoint =>
				{
					//Adding point
					points.push(new TrackPoint(
						{
							coords : {
								latitude : activityPoint.Latitude,
								longitude : activityPoint.Longitude,
								accuracy : activityPoint.Accuracy,
								altitude : activityPoint.Altitude,
								altitudeAccuracy : activityPoint.AltitudeAccuracy
							},
							timestamp : parseInt(activityPoint.Timestamp)
						}
					));
				});
				//Adding segment
				segments.push(new Segment(points));
			});
			//Creating journey
			let journey = new Journey(segments, jmStatuses.Finished);
			//Converting to GPX
			let gpx = GpxConverter.toGpx(journey);
			
			//Creating element for journey
			activitiesElem.append(`<div id=\"Journey_${activity.information.ID}\" style=\"width:300px;height:300px;\"></div>`)
			
			//Creating mapManager
			let mapManager = new MapManager(`Journey_${activity.information.ID}`, false);
			//Loading gpx
			mapManager.loadGpx(gpx);
			//Fitting gpx
			mapManager.fitGpxLayer();
		});
	});
});
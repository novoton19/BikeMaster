/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/07/23
	Version: 0.0.4.2
Updated on
	Version: 0..4

Description:
	Converts Journey to GPX
	https://www.topografix.com/gpx_manual.asp

Changes:

*/
//Converts Journey to Gpx 
class GpxConverter
{
	static toGpx(journey)
	{
		//Getting segments
		let segments = journey.segments;
		//Resulting gpx
		let gpx = '';
		
		//Adding root element
		gpx += '<gpx version=\"1.1\" creator=\"BikeMaster - https://novotnyondrej.com/Pwa/BikeMaster\">';
		//Adding track element
		gpx += '<trk>';
		//Adding segments
		segments.forEach(segment =>
		{
			//Getting points
			let points = segment.points;
			//Adding segment element
			gpx += '<trkseg>';
			//Adding points
			points.forEach(point =>
			{
				//Adding point element
				gpx += `<trkpt lat=\"${point.latitude}\" lon=\"${point.longitude}\" time=\"${new Date(point.timestamp).toUTCString()}\">`;
			});
			//Ending segment element
			gpx += '</trkseg>';
		});
		//Ending track element
		gpx += '</trk>';
		//Ending root element
		gpx += '</gpx>';
		//Result
		return gpx;
	}
}
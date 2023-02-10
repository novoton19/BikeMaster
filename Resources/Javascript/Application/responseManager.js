/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/11/23 08:12pm
	Version: 0.1
Updated on
	Version: 0.2

Description:
	Https response manager

Changes:
	Changed return texts
*/
//Response manager
class ResponseManager
{
	static getErrorText(response, textStatus)
	{
		//Checking status
		if (textStatus === 'timeout')
		{
			return 'Request took too long';
		}
		else if (textStatus === 'abort')
		{
			return 'Request has been aborted';
		}
		else if (textStatus === 'parsererror')
		{
			return 'Server responded with invalid information';
		}
		else //if (textStatus === 'error')
		{
			//Checking if offline
			if (!navigator.onLine)
			{
				return 'You are offline';
			}					
			else
			{
				//Checking status
				if (response.status === 404)
				{
					return 'Application has misunderstood the request';
				}
				else
				{
					return response.statusText;
				}
			}
		}
	}
}
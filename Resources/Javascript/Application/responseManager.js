/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/11/23 08:12pm
	Version: 0.1
Updated on
	Version: 0.1

Description:
	Https response manager

Changes:

*/
//Response manager
class ResponseManager
{
	static getErrorText(response, textStatus)
	{
		//Checking status
		if (textStatus === 'timeout')
		{
			return 'Timed out';
		}
		else if (textStatus === 'abort')
		{
			return 'Aborted';
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
				return 'Offline';
			}					
			else
			{
				//Checking status
				if (response.status === 404)
				{
					return 'Action page doesn\'t exist';
				}
				else
				{
					return response.statusText;
				}
			}
		}
	}
}
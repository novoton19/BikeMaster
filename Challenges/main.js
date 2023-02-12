/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/24/23 11:35am
	Version: 0.2
Updated on
	Version: 0.3.1

Description:
	Basic page functionality

Changes:
	 Version 0.3.1 - Support for newest script versions
*/
//Api urls
var competitionsUrl = '../Api/Challenges/Competitions/getCompetitions.php';

//Waiting for document to load
$(document).ready(() =>
{
	//Getting competitions
	const actualList = $('#actual .list');
	const invitationsList = $('#invitations .list');
	const archiveList = $('#archive .list');

	function onCompetitionsRequested()
	{
		actualList.text('');
		//Return requested urls
		return [{
			url : competitionsUrl,
			data : {
				category : 'actual'
			}
		}];
	}
	function onInvitationsRequested()
	{
		invitationsList.text('');
		//Return requested urls
		return [{
			url : competitionsUrl,
			data : {
				category : 'invitations'
			}
		}]
	}
	function onArchiveRequested()
	{
		archiveList.text('');
		//Return requested urls
		return [{
			url : competitionsUrl,
			data : {
				category : 'archive'
			}
		}]
	}
	function onCompetitionsLoaded(competitions)
	{

	}
	function onInvitationsLoaded(invitations)
	{

	}
	function onArchiveLoaded(archive)
	{

	}

	window.onCompetitionsRequested = onCompetitionsRequested;
	window.onInvitationsRequested = onInvitationsRequested;
	window.onArchiveRequested = onArchiveRequested;
	window.onCompetitionsLoaded = onCompetitionsLoaded;
	window.onInvitationsLoaded = onInvitationsLoaded;
	window.onArchiveLoaded = onArchiveLoaded;
	window.onRequestFailed = (information) => information.reason;
});
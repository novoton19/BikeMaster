/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/24/23 11:35am
	Version: 0.1.2
Updated on
	Version: 0.1.2

Description:
	Basic page functionality

Changes:
	 
*/
//Waiting for document to load
$(document).ready(() =>
{
	//Getting competitions
	const actualList = $('#actual .list');
	const invitationsList = $('#invitations .list');
	const archiveList = $('#archive .list');

	function onCompetitionsLoaded(competitions)
	{

	}
	function onInvitationsLoaded(invitations)
	{

	}
	function onArchiveLoaded(archive)
	{

	}
	function onRequestFailed(response)
	{

	}

	window.onCompetitionsRequested = () => actualList.text('');
	window.onInvitationsRequested = () => invitationsList.text('');
	window.onArchiveRequested = () => archiveList.text('');
	window.onCompetitionsLoaded = onCompetitionsLoaded;
	window.onInvitationsLoaded = onInvitationsLoaded;
	window.onArchiveLoaded = onArchiveLoaded;
	window.onRequestFailed = onRequestFailed;
});
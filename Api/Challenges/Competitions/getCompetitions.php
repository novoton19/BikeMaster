<?php
	/*
	Developer: Ondrej Novotny
	Contact: contact.bike@novotnyondrej.com

	Created on
		Date: 02/09/23 11:03pm
		Version: 0.1.2
	Updated on
		Version: 0.1.2

	Description:
		Returns user's competitions

	Changes:

	*/
	#Making sure that this script is running independently
	if (count(debug_backtrace()))
	{
		return;
	}
	#Return json
	header('Content-Type: application/json; charset=utf-8');

	echo json_encode([
		'success' => true
	]);
?>
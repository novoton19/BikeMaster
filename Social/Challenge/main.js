/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/10/23 01:11pm
	Version: 0.0.5.5
Updated on
	Version: 0.0.5.5

Description:
	Allows user to challenge someone

Changes:
	 
*/
//Url to get user information
const getUserInformationUrl = '../../Api/Social/getUserInformation.php';
//Getting get parameters
const getParams = new URLSearchParams(window.location.search);

//Waiting for document to load
$(document).ready(() =>
{
	//Getting elements
	const teamBlueElem = $('#TeamBlue');
	const teamRedElem = $('#TeamRed');


	//Getting elements
	const searchForm = $('#SearchForm');
	//Search input
	const searchInput = $(searchForm).find('#Search');
	const searchResultsElem = $('#SearchResults');
	//Button to load more results
	const moreResultsButton = $('#MoreResults');
	//Getting url to api
	const apiUrl = searchForm.attr('js-action');

	//Users in teams
	let teamBlue = [];
	let teamRed = [];
	//Loads user information in team element
	function loadUserInformationInTeam(id, teamElem)
	{
		$.get(getUserInformationUrl, {id : id}, function(response)
		{
			//Checking if success
			if (response.success)
			{
				//Getting account
				let account = response.account;
				//Create wrapper
				let wrapper = $('<div>');
				let userElem = $('<a>').attr('href', `../User/?id=${account.id}`).addClass(`User_${account.id}`).text(account.username);
				//Adding elements
				wrapper.append(userElem)
				//Show user
				teamElem.append(wrapper);
			}
			else //if (!response.success)
			{
				//Show fail
				teamElem.append('<div>?</div>');
			}
		});
	}
	//Unloads user information from team element
	function unloadUserInformationFromTeam(id, teamElem)
	{
		teamElem.find(`.User_${id}`).remove();
	}
	//Adds user to team
	function addUserToTeam(id, addToBlueTeam)
	{
		//Getting team
		let team = addToBlueTeam ? teamBlue : teamRed;
		let teamElem = addToBlueTeam ? teamBlueElem : teamRedElem;

		//Checking if user is not in team
		if (!team.includes(id))
		{
			//Adding user
			team.push(id);
			//Loading user
			loadUserInformationInTeam(id, teamElem);
		}
		else //if (team.includes(id))
		{
			//Already in team
		}
	}
	//Removes user from team
	function removeUserFromTeam(id, removeFromBlueTeam)
	{
		//Getting team
		let team = removeFromBlueTeam ? teamBlue : teamRed;
		let teamElem = removeFromBlueTeam ? teamBlueElem : teamRedElem;

		//Checking if user is in team
		if (team.includes(id))
		{
			//Removing user
			team.splice(team.indexOf(id), 1);
			//Removing user from team
			unloadUserInformationFromTeam(id, teamElem);
		}
		else //if (!team.includes(id))
		{
			//Not in team
		}
	}
	//Reloads list of users in teams
	function reloadUsersInTeams()
	{
		//Clearing lists
		teamBlueElem.text('');
		teamRedElem.text('');
		//Loading lists
		teamBlue.forEach(id =>
		{
			loadUserInformationInTeam(id, teamBlueElem);
		});
		teamRed.forEach(id =>
		{
			loadUserInformationInTeam(id, teamRedElem);
		});
	}

	//Reloading users in team by default
	reloadUsersInTeams();

	



	//Getting requested username
	let getUsername = getParams.get('search');
	let username = undefined;
	//Current page
	let getPage = parseInt(getParams.get('page')) || 0;
	let page = undefined;
	//Whether has next page
	let hasNextPage = false;

	//Loads next page
	function loadNextPage()
	{
		//Checking if has next page
		if (hasNextPage)
		{
			//Sending request to api
			$.get(apiUrl, {search : username, page : page}, function(response)
			{
				//Getting status
				let success = response.success;
				//Checking if success
				if (success)
				{
					//Getting search results
					let results = response.searchResults;
					//Getting total pages
					let totalPages = response.totalPages;

					//Next page
					page++;
					//Checking if there is any other page
					if (page + 1 > totalPages)
					{
						//No more pages
						moreResultsButton.hide();
						hasNextPage = false;
					}
					
					//Adding results
					results.forEach(user =>
					{
						//Getting user ID
						let id = user.id;

						//Creating wrapper
						let wrapper = $('<div>');
						//Creating name element
						let nameElem = $('<div>');
						//Creating buttons element
						let buttonsElem = $('<div>');

						//Creating buttons
						let buttons = {
							addToBlueTeamButton : $('<button>').text('Add to Team Blue'),
							addToRedTeamButton : $('<button>').text('Add to Team Red'),
							removeFromTeamBlueButton : $('<button>').text('Remove from Team Blue'),
							removeFromTeamRedButton : $('<button>').text('Remove from Team Red'),
							moveToTeamBlueButton : $('<button>').text('Move to Team Blue'),
							moveToTeamRedButton : $('<button>').text('Move to Team Red')
						};

						//Adding information
						nameElem.text(user.username);

						//Reloads particular buttons
						function reloadButtons()
						{
							//Hiding all buttons by default
							Object.values(buttons).forEach(button =>
							{
								button.hide();
							});
							//Checking state
							if (teamBlue.includes(id))
							{
								//In Team Blue
								buttons.removeFromTeamBlueButton.show();
								buttons.moveToTeamRedButton.show();
							}
							else if (teamRed.includes(id))
							{
								//In Team Red
								buttons.removeFromTeamRedButton.show();
								buttons.moveToTeamBlueButton.show();
							}
							else //if (!(teamBlue.includes(id) or (teamRed.includes(id))))
							{
								//Not in any team
								buttons.addToBlueTeamButton.show();
								buttons.addToRedTeamButton.show();
							}
						}
						//Button actions
						buttons.addToBlueTeamButton.click(function()
						{
							addUserToTeam(id, true);
							reloadButtons();
						})
						buttons.addToRedTeamButton.click(function()
						{
							addUserToTeam(id, false);
							reloadButtons();
						});
						buttons.removeFromTeamBlueButton.click(function()
						{
							removeUserFromTeam(id, true);
							reloadButtons();
						});
						buttons.removeFromTeamRedButton.click(function()
						{
							removeUserFromTeam(id, false);
							reloadButtons();
						});
						buttons.moveToTeamBlueButton.click(function()
						{
							removeUserFromTeam(id, false);
							addUserToTeam(id, true);
							reloadButtons();
						});
						buttons.moveToTeamRedButton.click(function()
						{
							removeUserFromTeam(id, true);
							addUserToTeam(id, false);
							reloadButtons();
						});
						reloadButtons();

						//Adding name and buttons
						wrapper.append(nameElem);
						nameElem.append(buttonsElem);
						//Adding buttons
						Object.values(buttons).forEach(button =>
						{
							buttonsElem.append(button);
						});
						//Adding result
						searchResultsElem.append(wrapper);
					});
				}
				else //if (!success)
				{

				}
			});
		}
	}
	//Loads search results
	function loadSearchResults(searchedUsername, untilPage)
	{
		//Checking if searched username matches
		if (username === searchedUsername)
		{
			loadNextPage();
		}
		else
		{
			//Set username
			username = searchedUsername;
			//Resetting page
			page = 0;
			hasNextPage = true;
			
			//Clearing search results
			searchResultsElem.text('');
			//Showing button for loading more
			moreResultsButton.show();
			
			//Loading pages
			for (let pageNum = 0; pageNum <= untilPage; pageNum++)
			{
				//Loading page
				loadNextPage();
				//Checking if has any other pages
				if (!hasNextPage)
				{
					break;
				}
			}
		}
	}
	//On form submitted
	searchForm.submit(function(event)
	{
		//Cancel send
		event.preventDefault();
		//Loading search results
		loadSearchResults(searchInput.val());
	});
	moreResultsButton.click(function(event)
	{
		//Cancel send
		event.preventDefault();
		//Loading next pafge
		loadNextPage();
	});
	//Checking if searched username exists
	if (getUsername !== undefined && getUsername !== null)
	{
		//Adding username to search input
		searchInput.val(getUsername);
		loadSearchResults(getUsername, getPage);
	}
});
/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/07/23 05:23pm
	Version: 0.0.4.4
Updated on
	Version: 0.0.4.4

Description:
	Displays find results

Changes:
	 
*/
//Getting get parameters
const getParams = new URLSearchParams(window.location.search);

//Waiting for document to load
$(document).ready(() =>
{
	//Getting elements
	const searchForm = $('#SearchForm');
	//Search input
	const searchInput = $(searchForm).find('#Search');
	const searchResultsElem = $('#SearchResults');
	//Button to load more results
	const moreResultsButton = $('#MoreResults');
	//Getting url to api
	const apiUrl = searchForm.attr('js-action');

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
						//Adding result
						searchResultsElem.append('<div>' + `<a href=\"../User/?id=${user.id}\">${user.username}</a>` + '</div>');
					});
					//Creating new url
					let url = new URL(window.location);
					//Adding parameters
					url.searchParams.set('search', username);
					url.searchParams.set('page', page);
					//Replacing parameters
					window.history.pushState(null, '', url.toString());
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
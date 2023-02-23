/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/07/23 05:23pm
	Version: 0.0.4.4
Updated on
	Version: 0.1

Description:
	Displays find results

Changes:
	 Version 0.1 - Added topbar manager, network manager, offline treatment
*/
//Getting get parameters
const getParams = new URLSearchParams(window.location.search);
let keyword = getParams.get('keyword');

//Waiting for document to load
$(document).ready(() =>
{
	//Creating managers
	const topbarManager = new TopbarManager();
	const networkManager = new PageNetworkManager();

	//Getting search form
	const searchForm = $('.searchForm');
	const searchAction = searchForm.attr('js-action');
	const searchInput = searchForm.find('input[type=\"search\"]');
	const keywordText = $('.keyword');
	const noResults = $('.noResults');
	noResults.removeClass('uk-hidden');
	noResults.hide();

	//Getting search results
	const searchContent = $('#searchContent');
	const searchResults = searchContent.find('.searchResults');
	const oldSearchResults = searchContent.find('.oldSearchResults');
	const searchProfilePreset = searchResults.find('.profile');
	searchProfilePreset.hide();
	searchResults.remove(searchProfilePreset);

	//Loading keyword
	keywordText.text(keyword);
	searchInput.val(keyword);

	//Current page
	let page = 0;
	let hasNextPage = true;
	let nextPageLoading = false;
	//List of all results
	let allResults = [];

	//Loads next page
	function loadNextPage(onSuccess, onError)
	{
		//Checking if has next page
		if (hasNextPage)
		{
			//Checking if not loading
			if (!nextPageLoading)
			{
				//Set page to loading
				nextPageLoading = true;
				keywordText.text(keyword);

				topbarManager.setSearchKeyword(keyword);
				//Loading results
				$.ajax({
					url : searchAction,
					type : 'get',
					dataType : 'json',
					data : {
						search : keyword,
						page : page
					},
					timeout : 5000,
					success : (response) =>
					{
						if (response.success)
						{
							//Getting search results
							let results = response.searchResults;
							//Adding result
							allResults = allResults.concat(results);
							//Getting previous search results
							let searchProfiles = searchResults.children().toArray();
							//Unloading search results that don't match anymore
							searchProfiles.forEach(searchProfile =>
							{
								//Checking if has search result
								if (!allResults.some(result => result.id === parseInt($(searchProfile).attr('userID'))))
								{
									//Changing parent
									searchResults.remove($(searchProfile));
									oldSearchResults.append($(searchProfile));
									$(searchProfile).hide(400, () => searchProfile.remove());
								}
							});
							//Loading results
							results.forEach(result =>
							{
								//Checking if not exists
								if (!searchProfiles.some(searchProfile => result.id === parseInt($(searchProfile).attr('userID'))))
								{
									//Creating profile
									let searchProfile = searchProfilePreset.clone();
									//Adding username
									searchProfile.attr('userID', result.id);
									searchProfile.find('.username').text(result.username);
									searchProfile.find('.viewProfileButton').attr('href', `../User/?id=${result.id}`);
									searchResults.append(searchProfile);
									searchProfile.show(400);
								}
							});
							//Adding page
							page++;
							hasNextPage = page < response.totalPages;
							nextPageLoading = false;
							
							onSuccess();
						}
						else //if (!response.success)
						{
							hasNextPage = page < response.totalPages;
							nextPageLoading = false;
							
							onError(response.reason);
						}
					},
					error : (data, textStatus) =>
					{
						onError(ResponseManager.getErrorText(data, textStatus));
					}
				});
			}
		}
	}
	//Content reload
	function searchContentReload()
	{
		//Reset page
		page = 0;
		//Getting keyword
		keyword = searchInput.val();
		hasNextPage = true;
		nextPageLoading = false;
		allResults = [];
		//Loading next page
		loadNextPage(() =>
		{
			checkNextPageLoad();
		}, (message) =>
		{
			
		});
	}
	//Checks if should load another page
	function checkNextPageLoad()
	{
		//Getting values
		let scrollY = window.scrollY;
		let sizeY = window.innerHeight;
		let posY = searchResults.offset().top + searchResults.height();
		//Checking if visible
		if (posY - scrollY <= sizeY)
		{
			//Checking if has next page
			if (hasNextPage)
			{
				//Checking if not loading next page
				if (!nextPageLoading)
				{
					//Load next page
					loadNextPage(() =>
					{
						//Checking if has next page
						if (hasNextPage)
						{
							checkNextPageLoad();
						}
					}, () =>
					{

					});
				}
			}
		}
	}
	//On input changed
	searchInput.on('input', () => searchContentReload());
	$(searchForm).submit((event) =>
	{
		event.preventDefault();
		searchContentReload();
	})

	window.onscroll = checkNextPageLoad;
	window.onresize = checkNextPageLoad;
	searchContentReload();
});
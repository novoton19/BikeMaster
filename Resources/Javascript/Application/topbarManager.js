/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/10/23 05:22pm
	Version: 0.1
Updated on
	Version: 0.1

Description:
	Manages topbar

Changes:

*/
//TopbarManager
class TopbarManager
{
	//Topbar element
	topbar;
	//Constructor
	constructor()
	{
		//Getting get parameters
		let getParams = new URLSearchParams(window.location.search);

		//Loading topbar
		$.get('/Pwa/BikeMaster/Resources/Html/Application/topbar.html', {}, (response) =>
		{
			//Adding topbar
			$('body').prepend(response);
			//Getting topbar
			let topbar = $('#topbar');
			//Setting topbar
			this.topbar = topbar;

			//Search
			let searchForms = topbar.find('.searchForm');

			//Topbar elements
			topbar.find('.showOnLoggedIn').hide();
			topbar.find('.showOnNotLoggedIn').hide();
			topbar.find('.backButton').click((event) =>
			{
				event.preventDefault();
				history.back();
			});
			
			//Account
			let loadError = topbar.find('.loadError');
			loadError.removeClass('uk-hidden');
			loadError.hide();


			//Loading search actions
			searchForms.each((index, searchForm) =>
			{
				let actionUrl = $(searchForm).attr('js-action');
				let searchInput = $(searchForm).find('input[type=\"search\"]');
				let searchButton = $(searchForm).find('.searchButton');

				this.setSearchKeyword(getParams.get('keyword'));
				//On search request
				function onSearch()
				{
					//Checking if has search information
					if (searchInput.val() !== '')
					{
						//Create search params for redirect
						let searchParams = new URLSearchParams();
						searchParams.set(searchInput.attr('name'), searchInput.val());
						//Redirect
						window.location = `${actionUrl}?${searchParams.toString()}`;
					}
				}
				//Events
				searchButton.click((event) =>
				{
					event.preventDefault();
					onSearch();
				});
				$(searchForm).submit((event) =>
				{
					event.preventDefault();
					onSearch();
				});
			});

			//Loading account
			this.refreshAccount();
		});
	}
	setSearchKeyword(keyword)
	{
		if (this.topbar)
		{
			//Search
			let searchForms = this.topbar.find('.searchForm');
			//Loading search actions
			searchForms.each((index, searchForm) =>
			{
				let searchInput = $(searchForm).find('input[type=\"search\"]');

				searchInput.val(keyword);
			});
		}
	}
	//Refreshes the topbar account navigation
	refreshAccount()
	{
		//Getting topbar
		let topbar = this.topbar;
		//Getting elements
		let loginUsername = topbar.find('.loginUsername');
		let loadingSpinner = topbar.find('.loadingSpinner');
		let loadError = topbar.find('.loadError');

		//Show spinner by default
		topbar.find('.showOnLoggedIn').hide(200);
		topbar.find('.showOnNotLoggedIn').hide(200);
		loadError.hide(200);
		loadingSpinner.show(200, () =>
		{
			//Requesting login status
			$.ajax({
				url : '/Pwa/BikeMaster/Api/User/status.php',
				timeout : 5000,
				success : (response) =>
				{
					//Hide spinner
					loadingSpinner.hide(200, () =>
					{
						//Checking if success
						if (response.success)
						{
							//Checking if logged in
							if (response.loggedIn)
							{
								//Getting account
								let account = response.account;
								//Getting username
								let username = account.username;
								//Loading username
								loginUsername.text(username);
								//Showing login navigation
								topbar.find('.showOnLoggedIn').show(200);
							}
							else //if(!response.loggedIn)
							{
								//Not logged in
								topbar.find('.showOnNotLoggedIn').show(200);
							}
						}
						else //if (!response.success)
						{
							//Show error
							loginStatusError.attr('uk-tooltip', response.reason);
							loginStatusError.show(200);
						}
					});
				},
				error : (response, textStatus) =>
				{
					//Show error
					loadingSpinner.hide(200, () =>
					{
						loginStatusError.attr('uk-tooltip', ResponseManager.getErrorText(response, textStatus));
						loginStatusError.show(200);
					});
				}
			});
		});
	}
}
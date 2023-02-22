/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 01/09/23 11:09pm
	Version: 0.0.5.4
Updated on
	Version: 0.0.5.4

Description:
	Manages notifications

Changes:
	 
*/
class NotificationManager
{
	//Url to api
	static getNotificationsUrl = '/Pwa/BikeMaster/Api/User/getNotifications.php';
	static getNotificationTypesUrl = '/Pwa/BikeMaster/Api/getAllNotificationTypes.php';
	static getUserInformationUrl = '/Pwa/BikeMaster/Api/Social/getUserInformation.php';
	//Notification types
	notificationTypes;
	//Current page of notifications
	page;
	//Whether has next page
	hasNextPage;
	//List containing notifications
	list;

	//Constructor
	constructor(viewListButton, wrapper)
	{
		//Element containing notifications
		let list = wrapper.find('div.List');
		//Getting refresh button
		let refreshButton = wrapper.find('button.Refresh');
		//Getting loadMore button
		let loadMoreButton = wrapper.find('button.LoadMore');
		//Getting notification types
		$.get(NotificationManager.getNotificationTypesUrl, {}, (response) =>
		{
			//Checking if success
			if (response.success)
			{
				//Loading notificationTypes
				this.notificationTypes = response.notificationTypes;
			}
		})
		

		this.list = list;
		//Resetting notifications
		this.reset();
		//Adding event listeners
		viewListButton.click(() =>
		{
			if (wrapper.is(':hidden'))
			{
				//Showing wrapper
				wrapper.show();
			}
			else
			{
				//Hiding wrapper
				wrapper.hide();
			}
		});
		refreshButton.click(() =>
		{
			this.reset();
		});
		loadMoreButton.click(() =>
		{
			this.loadNextPage();
		});
	}
	//Resets values
	reset()
	{
		//Reset pagination and clear elements
		this.page = 0;
		this.hasNextPage = true;
		this.list.text('');
		//Loading first page
		this.loadNextPage();
	}
	//Loads next page
	loadNextPage()
	{
		//Checking if has next page
		if (this.hasNextPage)
		{
			//Load next page
			$.get(NotificationManager.getNotificationsUrl, {page : this.page}, (response) =>
			{
				//Checking if success
				if (response.success)
				{
					//Getting total pages count
					let totalPages = response.totalPages;
					//Adding page
					this.page++;
					//Whether has next page
					this.hasNextPage = this.page + 1 <= totalPages;

					//Getting notifications
					let notifications = response.notifications;
					//Loading notifications
					notifications.forEach(notification =>
					{
						//Checking notificaiton type
						if (notification.type === this.notificationTypes.SendRequest)
						{
							//Loading new request notification
							$.get(NotificationManager.getUserInformationUrl, {id : notification.triggerID}, (response) =>
							{
								//Checking if request succeeded
								if (response.success)
								{
									this.list.append(`<div><a href=\"/Pwa/BikeMaster/Social/User?id=${response.account.id}\">${response.account.username}</a> has sent you a friend request at ${new Date(notification.triggerTime * 1000).toLocaleString()}!</div>`);
								}
							});
						}
						else if (notification.type === this.notificationTypes.AcceptRequest)
						{
							//Loading accepted request notification
							$.get(NotificationManager.getUserInformationUrl, {id : notification.triggerID}, (response) =>
							{
								//Checking if request succeeded
								if (response.success)
								{
									this.list.append(`<div><a href=\"/Pwa/BikeMaster/Social/User?id=${response.account.id}\">${response.account.username}</a> has accepted your friend request at ${new Date(notification.triggerTime * 1000).toLocaleString()}!</div>`);
								}
							});
						}
						else if (notification.type === this.notificationTypes.Unfriend)
						{
							//Loading unfriend notification
							$.get(NotificationManager.getUserInformationUrl, {id : notification.triggerID}, (response) =>
							{
								//Checking if request succeeded
								if (response.success)
								{
									this.list.append(`<div><a href=\"/Pwa/BikeMaster/Social/User?id=${response.account.id}\">${response.account.username}</a> has unfriended you at ${new Date(notification.triggerTime * 1000).toLocaleString()}.</div>`);
								}
							});
						}
					});
				}
			});
		}
	}
}
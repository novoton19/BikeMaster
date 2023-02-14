/*
Developer: Ondrej Novotny
Contact: contact.bike@novotnyondrej.com

Created on
	Date: 02/10/23 10:21pm
	Version: 0.3.2
Updated on
	Version: 0.3.2

Description:
	Javascript specifically designed for profile picture upload page

Changes:
	 
*/
//Waiting for document to load
$(document).ready(() =>
{
	//Creating network manager
	const networkManager = new NetworkManager();
	//Getting page
	var page = $('#profilePicturePage');
	//Getting form
	var form = page.find('form');
	var action = form.attr('jsAction');
	var method = form.attr('method') || 'post';
	//Getting profile picture input
	var input = page.find('#profilePicture');
	var inputReason = page.find('.inputReason[for=\"profilePicture\"]');
	var submitButton = page.find('.submitButton');
	
	//Getting image preview
	var imageWrapper = page.find('.imageWrapper');
	var imagePreview = imageWrapper.find('.imagePreview');
	var image = new Image();

	//Creating file reader
	var fileReader = new FileReader();

	function onFormSubmitted(event)
	{
		event.preventDefault();
		//Checking if input has any files
		if (!input[0].files.length)
		{
			inputReason.text('Image is missing');
			return;
		}
		sendRequest(
			action,
			new FormData(form[0]),
			method,
			false,
			false
		).then((response) =>
		{
			//Next page
			onPageFinished();
		}).catch((information) =>
		{
			//Checking if information is structured
			if (information.structured)
			{
				inputReason.text(information.response.inputReasons.profilePicture.reason);
			}
			else
			{
				inputReason.text(`Request error: ${information.reason}`);
			}
		});
	}
	//On network changed
	function onNetworkChange()
	{
		//Checking if online
		if (networkManager.online)
		{
			//Enable button
			submitButton.prop('disabled', false);
		}
		else//if (!networkManager.online)
		{
			//Disable button
			submitButton.prop('disabled', true);
		}
	}

	//Hide image wrapper by default
	imageWrapper.hide();
	//On image loaded
	image.onload = (event) =>
	{
		//Getting image size
		let width = image.width;
		let height = image.height;
		let ratio = width / height;
		//Checking image size
		if (width < 32 || height < 32)
		{
			inputReason.text('Image must be at least 32x32 pixels in size');
			submitButton.prop('disabled', true);
		}
		else if (width > 4096 || height > 4096)
		{
			inputReason.text('Image must not be bigger than 4096x4096 pixels in size');
			submitButton.prop('disabled', true);
		}
		else
		{
			inputReason.text('');
			submitButton.prop('disabled', false);
		
			let newWidth;
			let newHeight;
			//Checking smaller dimension
			if (width < height)
			{
				newWidth = 128;
				newHeight = 128 / ratio;
			}
			else
			{
				newWidth = ratio * 128;
				newHeight = 128;
			}
			//Resize image
			imagePreview.width(newWidth);
			imagePreview.height(newHeight);
			imageWrapper.show(200);
		}
	};
	//On file loaded
	fileReader.addEventListener('load', (event) =>
	{
		imagePreview.css('width', 'auto');
		imagePreview.css('height', 'auto');
		imagePreview.attr('src', fileReader.result);
		image.src = fileReader.result;
	});
	input.change((event) =>
	{
		imageWrapper.hide(200);
		//Getting file
		let file = input[0].files[0];
		fileReader.readAsDataURL(file);
	});
	form.submit(onFormSubmitted);

	//On network changed
	networkManager.addEventListener('onStatusChanged', onNetworkChange);
	onNetworkChange();
});
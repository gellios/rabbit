"use strict";

$(function () {

	var rabbitClient = new Rabbit('localhost', 8181);

	var $overlay = $('.overlay');
	var $loader = $('.loader');
	var $authForm = $('#auth_form');

	$overlay.show();
	showPopup('auth');

	$authForm.submit(function () {
		showLoader();
		rabbitClient.auth(
			$authForm.find('input[name=login]').val(),
			$authForm.find('input[name=password]').val()
		);
		return false;
	});

	rabbitClient.addEventListener(Rabbit.Event.AUTH_SUCCESS, function (data) {
		hideLoader();
	});

	rabbitClient.addEventListener(Rabbit.Event.AUTH_ERROR, function (data) {
		hideLoader();
		alert('Ошибка авторизации!');
	});

	rabbitClient.connect();

	function showOverlay () {
		$overlay.show();
	}

	function showPopup (cls) {
		var selector = '.popup';
		if (typeof(cls) == 'string') {
			selector += '.' + cls;
		}
		showOverlay();
		$(selector)
			.css({
				'margin-left': '-' + Math.floor($('.popup.auth').outerWidth() / 2) + 'px',
				'margin-top': '-' + Math.floor($('.popup.auth').outerHeight() / 2) + 'px'
			})
			.show()
		;
	}

	function showLoader () {
		$loader.show();
	}

	function hideLoader () {
		$loader.hide();
	}
});
"use strict";

var Rabbit = function (host, port) {
	this.host = host;
	this.port = port;
	this.eventListenters = [];
};

Rabbit.prototype.connect = function () {
	this.ws = new WebSocket('ws://' + this.host + ':' + this.port);
	var _client = this;
	this.ws.onopen = function(event) {
		_client.triggerEvent(Rabbit.Event.WS_OPEN, event);
	};
	this.ws.onmessage = function(event) {
		_client.triggerEvent(Rabbit.Event.WS_MESSAGE, event);
		var data = JSON.parse(event.data);
		_client.triggerEvent(data.event, data.data);

	};
	this.ws.onclose = function(event) {
		_client.triggerEvent(Rabbit.Event.WS_CLOSE, event);
	};
};

Rabbit.Event = {
	WS_CLOSE: 0,
	WS_OPEN: 1,
	WS_MESSAGE : 2,
	AUTH_SUCCESS: 3,
	AUTH_ERROR: 4,
	MESSAGE_NEW: 5,
	USER_LOGIN: 6,
	USER_LOGOUT: 7
};

Rabbit.prototype.addEventListener = function (type, listener) {
	if (typeof(listener) != 'function') {
		return false;
	}
	if (!(type in this.eventListenters)) {
		this.eventListenters[type] = [];
	}
	this.eventListenters[type].push(listener);
	return true;
};

Rabbit.prototype.triggerEvent = function (type, data) {
	if (!(type in this.eventListenters)) {
		return;
	}
	for (var i in this.eventListenters[type]) {
		this.eventListenters[type][i](data);
	}
};

Rabbit.prototype.execute = function (command, params) {
	var jsonData = JSON.stringify({
		command: command,
		params: params
	});
	this.ws.send(jsonData);
};

Rabbit.prototype.auth = function (login, password) {
	this.execute('auth', {
		login: login,
		password: password
	});
};

Rabbit.prototype.sendMessage = function (message) {
	this.execute('sendMessage', {
		message: message
	});
};
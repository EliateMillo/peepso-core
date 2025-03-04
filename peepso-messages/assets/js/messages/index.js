import $ from 'jquery';
import { throttle } from 'underscore';
import { hooks, observer } from 'peepso';
import MessageList from './list';
import MessageNew from './new';
import MessageHeader from './header';
import MessageThread from './thread';
import MessageBox from './messagebox';

// Prevents chat windows from opening on the messages page.
observer.addFilter('chat_enabled', function (enabled) {
	if (document.querySelector('.ps-js-messages-list')) {
		enabled = false;
	}

	return enabled;
});

function parseHash() {
	return location.hash
		.replace(/^#/, '')
		.split(',')
		.reduce((accu, curr) => {
			let [key, value] = curr.split('=');
			if (key) accu[key] = value || '';
			return accu;
		}, {});
}

$(function () {
	let args = parseHash();

	let listElement = document.querySelectorAll('.ps-js-messages-list');
	listElement.forEach(el => new MessageList({ el, args: Object.assign({}, args) }));

	let newElement = document.querySelectorAll('[data-id=ps-new-message-form]');
	newElement.forEach(el => new MessageNew({ el, args: Object.assign({}, args) }));

	let messageHeader = document.querySelector('[data-ps=message-header]');
	if (messageHeader) new MessageHeader(messageHeader);

	let messageThread = document.querySelector('[data-ps=message-thread]');
	if (messageThread) new MessageThread(messageThread);

	let messageBox = document.querySelector('[data-ps=messagebox]');
	if (messageBox) new MessageBox(messageBox);

	// So message listing view by default on mobile.
	let $root = $('.pso-messages');
	let $side = $root.children('.pso-messages__side');

	// Handle toggle sidebar buttonn.
	$root.on('click', '[data-ps=btn-toggle]', () => {
		$side.toggleClass('pso-messages__side--open');
	});

	// Handle focus button.
	$root.on('click', '[data-ps=btn-focus]', () => {
		$root.toggleClass('pso-messages--focus');
	});

	// Attach "narrow" class to the messages container when necessary.
	if (listElement.length) {
		let $container = $('.ps-js-messages');
		let narrowClass = 'ps-messages--narrow';
		let evtName = 'resize.ps-message-conversation';

		$(window)
			.off(evtName)
			.on(
				evtName,
				throttle(function () {
					$container.width() < 800
						? $container.addClass(narrowClass)
						: $container.removeClass(narrowClass);
				})
			)
			.triggerHandler(evtName);
	}
});

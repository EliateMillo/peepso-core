import $ from 'jquery';
import { hooks } from 'peepso';

class MessageboxMoods extends peepso.class('MessageboxOption') {
	constructor(messagebox) {
		super(messagebox);

		this.mood = null;

		this.messagebox = messagebox;
		this.$el = messagebox.$el;
		this.$toggle = this.$el.find('[data-ps=option][data-id=moods]');
		this.$dropdown = this.$el.find('[data-ps=dropdown][data-id=moods]');
		this.$cancel = this.$dropdown.find('[data-ps=btn-cancel]');
		this.$remove = this.$dropdown.find('[data-ps=btn-remove]');

		// parse opts
		this.titleTemplate = this.$dropdown.find('script[data-tmpl=title]').text();

		this.$toggle.on('click', () => this.toggle());
		this.$dropdown.on('click', '[data-mood]', e => this.onItemClick(e));
		this.$cancel.on('click', e => this.onCancelClick(e));
		this.$remove.on('click', e => this.onRemoveClick(e));

		hooks.addFilter('messagebox_data', 'moods', (...args) => this.onFilterData(...args));
		hooks.addFilter('messagebox_validate', (...args) => this.onFilterValidate(...args));
		hooks.addFilter('messagebox_is_empty', (...args) => this.onFilterIsEmpty(...args));
		hooks.addFilter('messagebox_title_extra', (...args) => this.onMessageboxTitle(...args));
		hooks.addAction('messagebox_reset', 'moods', (...args) => this.onMessageboxReset(...args));
	}

	set(id, mood) {
		if (!id) {
			this.mood = null;
			this.$toggle.removeClass('pso-messages-post__addon--active');
			this.$dropdown.find('.active').removeClass('active');
			this.$remove.hide();
		} else {
			let $item = this.$dropdown.find(`[data-id=${id}]`).addClass('active');

			this.mood = [id, mood];
			this.$toggle.addClass('pso-messages-post__addon--active');
			this.$dropdown.find('.active').not($item).removeClass('active');
			this.$remove.show();
		}

		this.messagebox.render();
		this.messagebox.$textarea.trigger('input');
	}

	onFilterData(data, messagebox) {
		if (messagebox === this.messagebox) {
			data.mood = this.mood ? this.mood[0] : undefined;
		}

		return data;
	}

	onFilterValidate(valid, messagebox, data) {
		if (messagebox === this.messagebox) {
			valid = data.mood ? true : valid;
		}

		return valid;
	}

	onFilterIsEmpty(empty, messagebox, data) {
		if (messagebox === this.messagebox) {
			if (data.mood) empty = false;
		}

		return empty;
	}

	onMessageboxTitle(list = [], data, messagebox) {
		if (messagebox === this.messagebox) {
			if (this.mood) {
				let html = this.titleTemplate
					.replace('##icon##', this.mood[0])
					.replace('##mood##', this.mood[1]);

				list.push(html);
			}
		}

		return list;
	}

	onMessageboxReset(messagebox) {
		if (messagebox === this.messagebox) {
			this.set(null);
		}
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let data = $(e.currentTarget).data();
		let remove = this.mood && this.mood[0] === data.id;

		remove ? this.set(null) : this.set(data.id, data.mood);
		this.toggle(false);
	}

	onRemoveClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.set(null);
		this.toggle(false);
	}

	onCancelClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.toggle(false);
	}
}

hooks.addAction('messagebox_init', 'moods', messagebox => new MessageboxMoods(messagebox));

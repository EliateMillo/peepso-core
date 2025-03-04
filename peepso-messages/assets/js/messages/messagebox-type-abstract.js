peepso.class('MessageboxType', function (name, peepso, $) {
	const { hooks } = peepso;

	return class {
		constructor(messagebox, type) {
			this.type = type;
			this.active = false;

			this.messagebox = messagebox;
			this.$el = messagebox.$el;
			this.$types = this.$el.find('[data-ps=types]');
			this.$typeInputs = this.$el.find('[data-ps=type_inputs]');
			this.$typeInput = this.$typeInputs.children(`[data-id=${this.type}]`);

			this.attachEvents();
			this.attachHooks();
		}

		attachEvents() {
			this.$types.on('click', `[data-id=${this.type}]`, e => this.onTypeClick(e));
		}

		attachHooks() {
			hooks.addFilter('messagebox_data', (...args) => this.onFilterData(...args));
			hooks.addFilter('messagebox_validate', (...args) => this.onFilterValidate(...args));
			hooks.addFilter('messagebox_is_empty', (...args) => this.onFilterIsEmpty(...args));
			hooks.addAction('messagebox_saved', (...args) => this.onActionSaved(...args));
			hooks.addAction('messagebox_reset', (...args) => this.onActionReset(...args));
			hooks.addAction('messagebox_toggle_type', (...args) =>
				this.onActionToggleType(...args)
			);
			hooks.addAction('messagebox_toggle_type', (...args) => this._triggerInput(...args), 99);
		}

		show() {
			this.active = true;
			this.$types.children(`[data-id=${this.type}]`).addClass('pso-active');
			this.$typeInput.show();
			this.$typeInput.length && this.$typeInputs.show();

			hooks.doAction('messagebox_toggle_type', this.type, this.messagebox);
		}

		hide() {
			this.active = false;
			this.$types.children(`[data-id=${this.type}]`).removeClass('pso-active');
			this.$typeInput.hide();
			this.$typeInputs.children('[data-id]:visible').length || this.$typeInputs.hide();
		}

		onFilterData(data, messagebox) {
			if (messagebox === this.messagebox && this.active) {
				// filter data here
			}

			return data;
		}

		onFilterValidate(valid, messagebox, data) {
			if (messagebox === this.messagebox && this.active) {
				// validate data here
			}

			return valid;
		}

		onFilterIsEmpty(empty, messagebox, data) {
			if (messagebox === this.messagebox && this.active) {
				// add checking here
			}

			return empty;
		}

		onActionSaved(messagebox) {
			if (messagebox === this.messagebox) {
				// add action here
			}
		}

		onActionReset(messagebox) {
			if (messagebox === this.messagebox) {
				this.hide();
			}
		}

		onActionToggleType(type, messagebox) {
			if (messagebox === this.messagebox && type !== this.type) {
				this.hide();
			}
		}

		onTypeClick(e) {
			e.preventDefault();
			e.stopPropagation();

			this.show();
		}

		_triggerInput(type, messagebox) {
			if (messagebox === this.messagebox && type === this.type) {
				this.messagebox.$textarea.trigger('input');
			}
		}
	};
});

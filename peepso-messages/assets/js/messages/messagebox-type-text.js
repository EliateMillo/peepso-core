peepso.class('MessageboxTypeText', function (name, peepso, $) {
	const { hooks } = peepso;

	hooks.addAction('messagebox_init', 'text', messagebox => new (peepso.class(name))(messagebox));

	return class extends peepso.class('MessageboxType') {
		constructor(messagebox, type = 'text') {
			super(messagebox, type);

			this.active = true;
		}

		onActionReset(messagebox) {
			if (messagebox === this.messagebox) {
				this.active = true;
				this.$types.children(`[data-id=${this.type}]`).addClass('pso-active');
			}
		}
	};
});

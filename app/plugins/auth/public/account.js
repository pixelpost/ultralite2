// this object manage account entity data and dom event
// see: template block entity
account_entity = function(dom_item)
{
	var self = this;

	this.delete_event = function(ev)
	{
		ev.preventDefault();

		var modal = $('#modal_del_entity');

		modal.find('strong').text(self.name.text());

		modal.find('.btn-primary').off('click').click(function(ev) {
			ev.preventDefault();

			api_call('auth.entity.del', { 'entity': self.id }, function() {
				modal.modal('hide');
				self.dom.fadeOut();
			}, function() {
				notify('error', 'Can\'t delete public key '+self.name.text()+'.');
			});
		});

		modal.modal();
	};

	this.load_event = function(ev)
	{
		ev.preventDefault();

		if (self.btn_list.attr('pp-loaded')) return;

		api_call('auth.grant.list', { 'entity': self.id }, function(rep) {
			self.btn_list.attr('pp-loaded', 1);
			for (var i in rep.list) {
				self.grant.find('input[value="'+rep.list[i].grant+'"]').attr('checked', true);
			}
		}, function () {
			notify('error', 'Can\'t load '+self.name.text()+' public key\'s grants.');
		});
	};

	this.rename_event = function(ev)
	{
		ev.preventDefault();

		var newname = self.rename.val();
		var data    = { 'entity': self.id, 'name': newname };

		if (newname == '') return;

		api_call('auth.entity.set', data, function() {
			self.rename.val('');
			self.name.text(newname);
		}, function() {
			notify('error', 'Can\'t change the '+self.name.text()+' name.');
		});
	};

	this.grant_event = function(ev)
	{
		ev.preventDefault();

		var input  = $(this);
		var gid    = input.val();
		var method = input.attr('checked') ? 'add' : 'del';
		var data   = { 'entity': self.id, 'grant': gid };
		var name   = self.name.text();

		api_call('auth.entity.grant.' + method, data, function() {
			notify('success', 'Grant updated for '+ name);
		}, function() {
			notify('error', 'Can\'t ' + method + ' ' + gid + ' access to ' + name);
		});
	};

	if (dom_item) this.init(dom_item);
	else          this.init($('#entity').children().clone());
};

account_entity.prototype = {
	init: function(element)
	{
		this.dom      = $(element);
		this.name     = this.dom.find('strong');
		this.btn_list = this.dom.find('.btn-info');
		this.btn_del  = this.dom.find('.btn-danger');
		this.form     = this.dom.find('.collapse');
		this.id       = this.form.attr('id');
		var tds       = this.form.find('td');
		this.pub      = tds.eq(0);
		this.priv     = tds.eq(1);
		this.grant    = tds.eq(2);
		this.rename   = this.form.find('input[type="text"]');
		this.btn_up   = this.form.find('.btn');
	},
	bind: function()
	{
		this.btn_del.click(this.delete_event);
		this.btn_list.click(this.load_event);
		this.btn_up.click(this.rename_event);
		this.grant.find('input').change(this.grant_event);
	},
	fill: function(data)
	{
		this.name.text(data.name);
		this.btn_list.attr('href', '#'+data.entity);
		this.form.attr('id', data.entity);
		this.id = data.entity;
		this.pub.text(data.public_key);
		this.priv.text(data.private_key);
	}
};

$(document).ready(function() {

	// user form management
	$('#form-account').submit(function(e)
	{
		var pass = $('#password');
		var val  = pass.val();
		if (val != '') pass.val(md5(val));
	});

	// key management
	// for each existing entity assign boutons methods etc…
	$('#entities').find('.entity').each(function()
	{
		var e = new account_entity(this);
		e.bind();
		e.dom.show();
	});

	$('#key_add').click(function()
	{
		// the new key name
		var name = $('#key_name').val();

		if (name == '') name = 'default #' + ($('.entity').length + 1);

		// add a new public key
		var data = { 'name': name, 'user': $('#name').val() };

		// add and get new entity info
		api_call('auth.entity.add', data, function (add_rep)
		{
			api_call('auth.entity.get', { 'entity': add_rep.entity }, function (get_rep)
			{
				// hide alert if exist
				$('#entities').find('.alert').fadeOut('slow');

				// create a new entity and fill it
				var e = new account_entity();
				e.fill({
					'entity'     : add_rep.entity,
					'name'       : get_rep.name,
					'public_key' : get_rep.public_key,
					'private_key': get_rep.private_key
				});
				e.bind();
				e.form.removeClass('collapse');
				e.dom.appendTo('#entities').fadeIn('slow');

			}, function() { location.reload(); });
		}, function() { notify('error', 'Can\'t add a new public key.'); });
	});
});

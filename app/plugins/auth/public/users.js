// this object manage account entity data and dom event
// see: template block entity
user_entity = function(dom_item)
{
	var self = this;

	this.delete_event = function(ev)
	{
		ev.preventDefault();

		var modal = $('#modal_del_user');

		modal.find('strong').text(self.name.text());

		modal.find('.btn-primary').off('click').click(function(ev) {
			ev.preventDefault();

			api_call('auth.user.del', { 'user': self.id }, function() {
				modal.modal('hide');
				self.dom.fadeOut();
			}, function() {
				notify('error', 'Can\'t delete user '+self.name.text()+'.');
			});
		});

		modal.modal();
	};

	if (dom_item) this.init(dom_item);
	else          this.init($('#user').children().clone());
};

user_entity.prototype = {
	init: function(element)
	{
		this.dom      = $(element);
		this.name     = this.dom.find('.user-name');
		this.email    = this.dom.find('.user-email');
		this.gravatar = this.dom.find('img');
		this.btn_edit = this.dom.find('.btn-edit');
		this.btn_del  = this.dom.find('.btn-danger');
		this.id       = this.dom.attr('data-id');
	},
	bind: function()
	{
		this.btn_del.click(this.delete_event);
	},
	fill: function(data)
	{
		var gravatar_data = this.gravatar.attr('src').split('?');
		var gravatar_hash = md5(data.email.toLowerCase());
		var gravatar_url  = gravatar_data[0]; + gravatar_hash + gravatar_data[1];

		this.name.text(data.name);
		this.email.text(data.email);
		this.gravatar.attr('src', gravatar_url);
		this.btn_edit.attr('href', this.btn_edit.attr('href') + data.user);
		this.dom.attr('data-id', data.user);
		this.id = data.user;
	}
};

$(document).ready(function()
{
	// user management
	// for each existing user assign boutons methods etc…
	$('#users').find('.user').each(function()
	{
		var e = new user_entity(this);
		e.bind();
		e.dom.show();
	});

	$('#user_add').click(function()
	{
		// the new key name
		var name  = $('#user_name').val();
		var email = $('#user_email').val();
		var pass  = $('#user_pass').val();

		if (name == '')
		{
			notify('error', 'please enter a name');
			return false;
		}

		if (pass == '')
		{
			notify('error', 'please enter a password');
			return false;
		}

		if (email == '')
		{
			notify('error', 'please enter a mail address');
			return false;
		}

		// add a new user
		var data = {
			'name'     : name,
			'email'    : email,
			'password' : md5(pass),
		};

		// add and get new user info
		api_call('auth.user.add', data, function (add_rep)
		{
			api_call('auth.user.get', { 'user': add_rep.user }, function (get_rep)
			{
				// create a new entity and fill it
				var e = new user_entity();
				e.fill({
					'user'       : add_rep.user,
					'name'       : get_rep.name,
					'email'      : get_rep.email
				});
				e.bind();
				e.dom.appendTo('#users').fadeIn('slow');

			}, function() { location.reload(); });
		}, function() {
			notify('error', 'Can\'t add a new user.');
		});
	});
});

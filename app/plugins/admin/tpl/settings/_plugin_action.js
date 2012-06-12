$(document).ready(function()
{
	$('div.btn-group').each(function()
	{
		var self   = $(this);
		var plugin = self.attr('data-plugin-name');

		$('.btn', self).click(function()
		{
			var btn   = $(this);
			var action = btn.attr('data-action');

			if (btn.attr('active')) return false;

			var data = { 'plugin':plugin, 'action':action };

			$.getJSON('settings/manage', data, function(data)
			{
				if (data.error)
				{
					notify('error', data.message);
				} else {
					notify('success', plugin + ' plugin is now ' + action);
				}

				if (action == 'clean')
				{
					var todo = function() { location.replace('settings/plugins') };
				} else {
					var todo = function() { location.reload() };
				}

				setTimeout(todo, 1000);
			});
		});
	});
});

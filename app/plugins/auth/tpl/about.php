<h3>Configuration</h3>

<form class="well form-inline">
	<label class="control-label" for="lifetime">Lifetime</label>
	<input class="input-small" id="lifetime" placeholder="300">
	<button class="btn btn-primary" type="submit">Update</button>
</form>

<script class="defered-js" data-defer="defer_plugin_admin_form">
function defer_plugin_admin_form()
{
	var lifetime = $('#lifetime');

	api_call('auth.config.get', {}, function(data) {
		lifetime.val(data.lifetime);
	}, function() {
		notify('error', 'Can\t load auth configuration.', false);
	});

	$('#lifetime').parent().submit(function(e) {
		e.preventDefault();
		e.stopPropagation();
		api_call('auth.config.set', {'lifetime':lifetime.val()}, function() {
			notify('success', 'Configuration was updated.');
		}, function() {
			notify('error', 'Configuration was not updated.');
		})
	});
}
</script>

<h3>About</h3>
<p>
	The Auth plugin is a piece of Pixelpost base installation, it provides
	security methods and controls. This part of pixelpost
	handles all account permissions, connection to the API or the web admin.
	<br>
	This plugin is managed by the Pixelpost community.
</p>
<p>
	<strong>
		You can't uninstall or delete this plugin.
	</strong>
	<br>
	You can inactive this plugin, however this seems to be a bad idea, your
	pixelpost installation will be unsecure at all. So anyone can access to the
	admin interface, change settings, etc…
</p>
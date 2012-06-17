{% extends admin/tpl/settings/_main.php %}

{% block Content %}
<form class="form-horizontal" accept-charset="utf-8" method="post">
	<div class="row-fluid">
		<fieldset class="span6">
			<legend>Basics</legend>
			<div class="control-group">
				<label for="conftitle" class="control-label">
					Website name
				</label>
				<div class="controls">
					<input class="input-xlarge" id="conftitle" name="conftitle" value="{{ conftitle }}" placeholder="My photoblog" required>
				</div>
			</div>
			<div class="control-group">
				<label for="confemail" class="control-label">
					Admin contact
				</label>
				<div class="controls">
					<input class="input-xlarge" id="confemail" name="confemail" value="{{ confemail }}" placeholder="contact@administrator.org" required>
				</div>
			</div>
			<div class="control-group">
				<label for="conftz" class="control-label">
					Timezone
				</label>
				<div class="controls">
					<select class="input-xlarge" id="conftz" name="conftz" required>
						{% for tz in timezones %}
						<option {{ (tz == conftz)|if('selected', '') }}>{{ tz }}</option>
						{% endfor %}
					<select>
				</div>
			</div>
		</fieldset>
		<fieldset class="span6">
			<legend>Routing</legend>
			<div class="control-group">
				<label for="confurl" class="control-label">
					Url
				</label>
				<div class="controls">
					<input class="input-xlarge" id="confurl" name="confurl" value="{{ confurl }}" placeholder="http://www.my-website.com/" required>
				</div>
			</div>
			<div class="control-group">
				<label for="confadmin" class="control-label">
					Admin location
				</label>
				<div class="controls">
					<input class="input-small" id="confadmin" name="confadmin" value="{{ confadmin }}" placeholder="admin" required>
				</div>
			</div>
			<div class="control-group">
				<label for="confapi" class="control-label">
					Api location
				</label>
				<div class="controls">
					<input class="input-small" id="confapi" name="confapi" value="{{ confapi }}" placeholder="api" required>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="row-fluid">
		<fieldset class="span6">
			<legend>System</legend>
			<div class="control-group">
				<label for="confdebug" class="control-label">
					Debug mode
				</label>
				<div class="controls">
					<label class="radio inline">
						<input type="radio" name="confdebug" value="1" {{ confdebug|if('checked', '') }}> Active
					</label>
					<label class="radio inline">
						<input type="radio" name="confdebug" value="0" {{ confdebug|if('', 'checked') }}> Inactive
					</label>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">
					Cache
				</label>
				<div class="controls">
					<button id="cache-flush" class="btn">
						<i class="icon icon-trash"></i> Flush
					</button>
					<span id="cache-flush-help"><i class="icon icon-question-sign"></i> what is this?</span>
				</div>
			</div>
		</fieldset>
		<fieldset class="span6">
			<legend>Informations</legend>
			<br>
			<table class="table table-condensed table-simple">
				<tr>
					<th>Version</th>
					<td>{{ confversion }}</td>
				</tr>
				<tr>
					<th>API url</th>
					<td>{{ confurl }}{{ confapi }}</td>
				</tr>
				<tr>
					<th>UID</th>
					<td>{{ confuid }}</td>
				</tr>
				{% if @DEBUG %}
				<tr>
					<th>Debug</th>
					<td>
						<span id="badge-debug" class="badge badge-warning">
							Debug mode is active
						</span>
						{% if ! confdebug %}
						<br>
						Debug mode is actived via `virtualhost` or `.htaccess`.
						{% endif %}
					</td>
				</tr>
				{% endif %}
			</table>
		</fieldset>
	</div>
	<div class="row-fluid">
		<div class="form-actions">
			<button class="btn btn-primary" type="submit">Update</button>
			<button class="btn" type="reset">Reset</button>
		</div>
	</div>
</form>
{% endblock %}

{% block Js %}
<script>
$(document).ready(function() {
	$('#badge-debug').popover({
		placement :'top',
		title     :'This should be off',
		content   :'Debug consumes more system resources as well as a log file which continuously grows.'
	});

	$('#cache-flush-help').popover({
		placement :'top',
		title     :'Flushing the cache',
		content   :'This deletes all pages in cache, this may help if you are encountering display problems.'
	});

	$('#cache-flush').click(function() {
		$.get('settings/cache-flush', function() {
			notify('success', 'cache is now empty.');
		});
		return false;
	});
});
</script>
{% endblock %}
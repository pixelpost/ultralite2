<form id="form-account" method="post" accept-charset="utf-8" class="form-horizontal">
	<fieldset>
		<legend>Update account settings</legend>
		{% if form.flag_success %}
		<p class="alert alert-success fade in">
			<a class="close" data-dismiss="alert">&times;</a>
			Updated !
		</p>
		{% endif %}
		{% if form.flag_reconnect %}
		<p class="alert alert-warning fade in">
			<a class="close" data-dismiss="alert">&times;</a>
			You need to reconnect on next page.
		</p>
		{% endif %}
		<div class="control-group">
			<label class="control-label" for="name">Username:</label>
			<div class="controls">
				<input id="name" name="name" value="{{ user.name }}" placeholder="username" required>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="email">Email:</label>
			<div class="controls">
				<input type="email" id="email" name="email" value="{{ user.email }}" placeholder="user@example.com" required>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="password">Password:</label>
			<div class="controls">
				<input type="password" id="password" name="password" placeholder="keep it empty will not change it">
			</div>
		</div>
		<div class="form-actions">
			<button class="btn btn-primary" type="submit">Update</button>
			<button class="btn" type="reset">Reset</button>
		</div>
	</fieldset>
</form>
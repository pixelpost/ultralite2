{% extends admin/tpl/settings/_main.tpl %}

{% block Js %}
<script src="{{ 'auth::account.js'|asset }}"></script>
<script>
$(document).ready(function()
{
	// set the url to the good one (after a potential username update)
	$('#form-account').attr('action', '{{ @ADMIN_URL }}settings/user/{{ user.user|encode }}');

	var form = $('#form-grant');
	var user = form.attr('data-user');
	var name = '{{ user.name }}';

	// grant event
	form.find('input').change(function(ev)
	{
		ev.preventDefault();

		var input  = $(this);
		var grant  = input.val();
		var method = input.attr('checked') ? 'add' : 'del';
		var data   = { 'user': user, 'grant': grant };

		api_call('auth.user.grant.' + method, data, function() {
			notify('success', 'Grant updated for '+ name);
		}, function() {
			notify('error', 'Can\'t ' + method + ' ' + grant + ' access to ' + name);
		});
	});
});
</script>
{% endblock %}

{% block Content %}
<div class="row-fluid">
	<div class="span6">
		{{ form_tpl }}
		<form id="form-grant" class="form-horizontal" data-user="{{ user.user }}">
			<fieldset>
				<legend>Update grant access</legend>
				<div class="control-group">
					<div class="controls">
						{% for g in grants %}
						<label class="checkbox inline">
							<input {{ granted[g.grant]|exists|if('checked', '') }}
									type="checkbox" value="{{ g.grant }}">
							{{ g.name }}
						</label>
						{% endfor %}
					</div>
				</div>
				<div class="form-actions"></div>
			</fieldset>
		</form>
	</div>
	<div class="span6">
		<h2 class="lead">Manage public keys</h2>

		{% include auth/tpl/_entity_form.tpl %}

		<div id="entities">
			{% for entity in entities %}
				{% display entity %}
			{% elsefor %}
			<div class="alert alert-warning alert-block">
				<h3 class="alert-heading">Information</h3>
				<p>This user have actually no entity.
			</div>
			{% endfor %}
		</div>
	</div>
</div>
{% endblock Content %}

{% block entity %}
{% include auth/tpl/_entity.tpl %}
{% endblock %}

{% block Footer %}
{% include auth/tpl/_entity_modal.tpl %}
<aside id="entity" class="hide">
	{:
		assign('entity',
			@array(
				'entity'      => '',
				'name'        => '',
				'public_key'  => '',
				'private_key' => '',
			)
		)
	:}
	{% display entity %}
</aside>
{% endblock %}

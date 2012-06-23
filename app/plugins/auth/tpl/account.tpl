{% extends admin/tpl/_main.tpl %}

{% block Title %}
	Account settings
	<img class="pull-right" src="http://gravatar.com/avatar/{{ user.gravatar }}?s=60">
{% endblock %}

{% block Js %}
<script src="{{ 'auth::account.js'|asset }}"></script>
{% endblock %}

{% block Content %}
<div class="row-fluid">
	<div class="span6">
		{{ form_tpl }}
	</div>
	<div class="span6">
		<h2 class="lead">Manage public keys</h2>

		{% include auth/tpl/_entity_form.tpl %}

		<div id="entities">
			{% for entity in entities %}
				{% display entity %}
			{% elsefor %}
			<div class="alert alert-info alert-block">
				<h3 class="alert-heading">Tip!</h3>
				<p>A public key allows you to use external apps and services
					to access and manage your website remotely, using the
					<a href="https://github.com/pixelpost/ultralite2/wiki/api-home">Pixelpost API</a>,
					without requiring you to disclose your username and password.
				<p><strong>Create your first one!</strong>
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

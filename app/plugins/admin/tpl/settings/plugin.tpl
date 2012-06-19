{% extends admin/tpl/settings/_main.tpl %}

{% block Content %}
<div class="row-fluid">
	<h2 class="span6">
		{{ plugin|title }}

		<small>{{ plugin.version() }}</small>

		{% include admin/tpl/settings/_plugin_label.tpl %}
	</h2>

	<div class="span6">
		{% include admin/tpl/settings/_plugin_action.tpl %}
	</div>
</div>
<p>
	<strong>Required:</strong>
	{% for addon, version in plugin.dependencies() %}
		{{ addon }} <em>{{ version }}</em>
		{{ loop.last|if('', ' &mdash; ') }}
	{% elsefor %}
		there is no required plugin
	{% endfor %}
</p>

<div>{{ plugin.data() }}</div><hr>

<a href="settings/plugins">&larr; go back to the list</a>
{% endblock %}

{% block Js %}
<script>
{% include admin/tpl/settings/_plugin_action.js %}
</script>
{% endblock %}
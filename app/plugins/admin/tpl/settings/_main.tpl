{% extends admin/tpl/_main.tpl %}

{% block Title %}Settings{% endblock %}

{% block Content %}
<ul class="row-fluid nav nav-tabs">
	<li class="{{ is_tab_index|default(false)|if('active', '') }}">
		<a href="settings/index">Base</a>
	</li>
	<li class="{{ is_tab_plugins|default(false)|if('active', '') }}">
		<a href="settings/plugins">Plugins</a>
	</li>
	{{ 'settings.template.tabs'|event|join }}
</ul>
{% child %}
{% endblock %}
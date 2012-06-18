{% extends admin/tpl/settings/_main.php %}

{% block Js %}
<script>
$(document).ready(function() {
	$('i.close').click(function() {
		$(this).parent().fadeOut(function() {
			$('div.span6').removeClass('span6');
		});
	});

	$('span.btn-plugin-name').hide();
});
{% include admin/tpl/settings/_plugin_action.js %}
</script>
{% endblock %}



{% block Content %}
{% if is_new %}
<p class="alert alert-info">
	<strong>
	{% if new|length > 1 %}
		New plugins are detected:
	{% else %}
		A new plugin is detected:
	{% endif %}
	</strong>
	{{ new.to_array()|join(', ')|title }}.
</p>
{% endif %}
<div class="row-fluid">

	<div class="span6">
		<div class="well">
			<i class="close">&times;</i>
			<table class="table table-simple">
				<tr>
					<td><span class="label label-info">protected</span></td>
					<td>can't be removed.</td>
				</tr>
				<tr>
					<td><span class="label">packaged</span></td>
					<td>can't be uninstalled.</td>
				</tr>
				<tr>
					<td><span class="label label-success">active</span></td>
					<td>running.</td>
				</tr>
				<tr>
					<td><span class="label label-warning">inactive</span></td>
					<td>not working, data preserved.</td>
				</tr>
				<tr>
					<td><span class="label label-inverse">uninstalled</span></td>
					<td>not working, data erased.</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="span6">
		<table class="table">
			<thead>
				<tr>
					<th>Name</th>
					<th>State</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				{% for plugin in all %}
				<tr>
					<td>
						<a href="settings/plugin/{{ plugin()|url }}"><i class="icon-info-sign visible-hover"></i> {{ plugin.name()|title }}</a>
					</td>
					<td>
						{% include admin/tpl/settings/_plugin_label.php %}
					</td>
					<td>
						{% include admin/tpl/settings/_plugin_action.php %}
					</td>
				</tr>
				{% endfor %}
			</tbody>
		</table>
	</div>
</div>
{% endblock %}

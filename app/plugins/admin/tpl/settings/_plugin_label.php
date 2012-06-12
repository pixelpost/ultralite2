
{#
	This piece of template require a var named `plugin`
	which is an `pixelpost\plugins\admin\classes\Plugin` class
#}

{% if plugin.is_active() %}
	<span class="label label-success">active</span>
{% elseif plugin.is_inactive() %}
	<span class="label label-warning">inactive</span>
{% else %}
	<span class="label label-inverse">uninstalled</span>
{% endif %}

{% if plugin.is_protected() %}
	<span class="label label-info">protected</span>
{% elseif plugin.is_packaged() %}
	<span class="label">packaged</span>
{% endif %}

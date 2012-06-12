{#
	This piece of template require a var named `plugin`
	which is an `pixelpost\plugins\admin\classes\Plugin` class
#}

{% if ! plugin.is_protected() %}
<div class="btn-group" data-toggle="buttons-radio" data-plugin-name="{{ plugin() }}">
	<span class="btn btn-small {{ plugin.is_active()|if('active', '') }}" data-action="active">
		<i class="icon icon-play"></i> <span class="btn-plugin-name">active</span>
	</span>
	<span class="btn btn-small {{ plugin.is_inactive()|if('active', '') }}" data-action="inactive">
		<i class="icon icon-pause"></i> <span class="btn-plugin-name">inactive</span>
	</span>
	{% if ! plugin.is_packaged() %}
	<span class="btn btn-small {{ plugin.is_uninstalled()|if('active', '') }}" data-action="uninstall">
		<i class="icon icon-stop"></i> <span class="btn-plugin-name">uninstall</span>
	</span>
	<span class="btn btn-small" data-action="clean">
		<i class="icon icon-eject"></i> <span class="btn-plugin-name">delete</span>
	</span>
	{% endif %}
</div>
{% endif %}

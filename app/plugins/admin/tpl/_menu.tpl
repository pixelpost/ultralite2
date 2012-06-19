<li class="{{ is_active|default(false)|if('active', '') }}">
	<a href="{{ url|default('#') }}">
		{{ name|default('') }}
	</a>
</li>
<a class="widget span2" href="{{ url|default('#') }}">
	{% if star|default(false) %}<b></b><i class="icon-white icon-star"></i>{% endif %}
	<h2>{{ count|default('') }}</h2>
	{{ text|default('') }}
</a>

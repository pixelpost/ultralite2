{% extends admin/tpl/_main.tpl %}

{% block Content %}
<div class="row-fluid">
	{% for w in widgets %}{{ w }}{% endfor %}
</div>
{% endblock %}

{% extends admin/tpl/_main.php %}

{% block Content %}
<div id="widget">
	{% for w in widgets %}{{ w }}{% endfor %}
</div>			
{% endblock %}

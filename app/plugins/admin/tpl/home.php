{% extends admin/tpl/_main.php %}

{% block Title %}{% parent %}home{% endblock %}

{% block Content %}
<div id="widget">
	{% for w in widgets %}{{ w }}{% endfor %}
</div>			
{% endblock %}

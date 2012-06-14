{% extends admin/tpl/_main.php %}

{% block Title %}{{ title|default('') }}{% endblock %}

{% block Content %}
<h2>Ungranted</h2>

<p>
	Sorry, you have not the necessery right to access to this section. This page
	can’t be displayed.
</p>
{% endblock %}
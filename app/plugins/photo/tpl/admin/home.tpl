{% extends admin/tpl/_main.tpl %}

{% block Title %}Photos managment{% endblock %}

{% block Css %}
<link rel="stylesheet" href="{{ 'photo::css/admin.css'|asset }}">
{% endblock %}


{% block Js %}
<script src="{{ 'photo::js/dropbox.js'|asset }}"></script>
<script>
	PP_DROPBOX_MAX_SIZE = {{ post_max_size }};
</script>
{% endblock %}

{% block Content %}
<div id="dropbox">
	<span id="droplabel">Drop files here…</span>
	<div id="dropaction">
		<div class="progressbar"></div>
		<button class="btn">Upload Files</button>
	</div>
	<a id="dropselect" href="#">…or select a file.</a>
	<input id="dropfiles" accept="image/*" type="file" multiple>
</div>

<ul class="thumbnails">
	{% for photo in photos %}
	<li>
		<figure class="thumbnail">
			<img src="{{ photo.thumb-url }}">
			<figcaption>
				<strong>{{ photo.id }}</strong>
				<a href="#">edit</a>
				<br>
				{{ photo.title }}
				<br>
				{{ photo.visible|if('published', 'hidden') }}
				<br>
				<time pubdate datetime="{{ photo.publish-date|datetime('iso', true) }}">
					{{ photo.publish-date|date }}
				</time>
			</figcaption>
		</figure>
	</li>
	{% elsefor %}
	<li>
		You haven't uploaded any photos yet.
	</li>
	{% endfor %}
</ul>
{% endblock %}

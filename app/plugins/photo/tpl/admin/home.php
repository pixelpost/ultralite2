{% extends admin/tpl/_main.php %}


{% block Css %}
<link rel="stylesheet" href="{{ @CONTENT_URL }}photo/public/css/admin.css" />
{% endblock %}


{% block Js %}
<script src="{{ @CONTENT_URL }}photo/public/js/dropbox.js"></script>
<script>
	PP_DROPBOX_MAX_SIZE = {{ post_max_size }};
</script>
{% endblock %}

{% block Content %}
<div id="dropbox">
	<span id="droplabel">Drop files here…</span>
	<div id="dropaction">
		<div class="progressbar"></div>
		<button class="btn">Upload !</button>
	</div>
	<a id="dropselect" href="#">…or select files.</a>
	<input id="dropfiles" accept="image/*" type="file" multiple />
</div>

<div class="photos">
	{% for photo in photos %}
	<div class="photo">
		<figure>
			<strong>{{ photo.id }}</strong>
			<a href="#">edit</a>
			<br />
			<img src="{{ photo.thumb-url }}" />
			<figcaption>
				{{ photo.title }}
				<br />
				{{ photo.visible|if('published', 'hidden') }}
				<br />
				<time pubdate datetime="{{ photo.publish-date|datetime('iso', true) }}">
					{{ photo.publish-date|date }}
				</time>
			</figcaption>
		</figure>
	</div>
	{% elsefor %}
	<p>
		You have not uploaded photo yet.
	</p>
	{% endfor %}
</div>
{% endblock %}


{% extends admin/tpl/_main.php %}

{% block Title %}JSON API test{% endblock %}

{% block Content %}
<p>
	Welcome to the api tester.
</p>
<form>
	<fieldset>
		<legend>API request</legend>
		<ol>
			<li>
				<label for="method">Method:</label>
				<input id="method" placeholder="photo.get" required />
			</li>
			<li>
				<label for="request">Request:</label>
				<textarea id="request" rows="15" cols="35" placeholder="{...}" required></textarea>
			</li>
		</ol>
	</fieldset>
	<fieldset>
		<button type="submit">Submit</button>
	</fieldset>
</form>
<h2>Response</h2>
<div id="response">

</div>
{% endblock Content %}
		
		
{% block Js %}
<script type="text/javascript">
	$(document).ready(function()
	{
		$('form').submit(function() {

			// stop propagation
			event.preventDefault();

			var method = $('#method').val();
			var request = $('#request').val();

			$.ajax({
			  'type'       : "put",
			  "url"        : "{{ @API_URL }}json/",
			  "dataType"   : "text",
			  "processData": false,
			  "data"       : '{"method":"'+method+'","request":'+request+'}',
			  "success"    : function(response) { $('#response').text(response); }
			});
		});
	});
</script>
{% endblock Js %}

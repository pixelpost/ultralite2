
{% extends admin/tpl/_main.php %}

{% block Title %}API test{% endblock %}

{% block Content %}
<p>
	Welcome to the api tester.
</p>
<form>
	<fieldset>
		<legend>API request</legend>
		<ol>
			<li>
				<label for="protocol">Protocol:</label>
				<select id="protocol">
					<option value="0">JSON</option>
					<option value="1">XML</option>
					<option value="2">GET return JSON</option>
					<option value="3">GET return XML</option>
					<option value="4">POST return JSON</option>
					<option value="5">POST return XML</option>
				</select> 
			</li>
			<li>
				<label for="method">Method:</label>
				<input type="text" id="method" placeholder="photo.get" required />
			</li>
			<li>
				<label for="request">Request:</label>
				<textarea id="request" rows="10" cols="35" placeholder="put your request here" required></textarea>
			</li>
		</ol>
	</fieldset>
	<fieldset>
		<button class="btn" type="submit">Submit</button>
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

			var protocol = $('#protocol').val();
			var method   = $('#method').val();
			var request  = $('#request').val();

			var reqType = 'get';
			var reqUrl  = '{{ @API_URL }}';
			var reqData = '';

			if (protocol == 0)  // SEND JSON DATA
			{
				reqType = 'put';
				reqUrl  = reqUrl + 'json/';
				reqData = '{"method":"'+method+'","request":'+request+'}';
			}
			else if (protocol == 1)  // SEND XML DATA
			{
				reqType = 'put';
				reqUrl  = reqUrl + 'xml/';
				reqData = '<xml><method>'+method+'</method><request>'+request+'<request><xml>'
			}
			else if (protocol == 2)  // SEND GET RETURN JSON
			{
				reqType = 'get';
				reqUrl  = reqUrl + 'get/' + method + '/json/';
				reqData = request;
			}
			else if (protocol == 3)  // SEND GET RETURN XML
			{
				reqType = 'get';
				reqUrl  = reqUrl + 'get/' + method + '/xml/';
				reqData = request;
			}
			else if (protocol == 4)  // SEND POST RETURN JSON
			{
				reqType = 'post';
				reqUrl  = reqUrl + 'get/' + method + '/json/';
				reqData = request;
			}
			else if (protocol == 5)  // SEND POST RETURN XML
			{
				reqType = 'post';
				reqUrl  = reqUrl + 'get/' + method + '/xml/';
				reqData = request;
			}
    			
			$.ajax({
			  'type'       : reqType,
			  "url"        : reqUrl,
			  "data"       : reqData,
			  "dataType"   : "text",
			  "processData": false,
			  "success"    : function(response) 
  			    { 
					$('#response').text(response); 
			    }
			});
		});
	});
</script>
{% endblock Js %}

<!DOCTYPE html>
<html>
	<head>
		<title>API Tester</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		<h1>API Tester</h1>
		<p>
			Welcome on api tester.
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
				<button type="submit">Envoyer</button>
			</fieldset>
		</form>
		<h2>Response</h2>
		<div id="response">
			
		</div>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
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
					  "url"        : "<?php echo API_URL ?>json/",
					  "dataType"   : "text",
					  "processData": false,
					  "data"       : '{"method":"'+method+'","request":'+request+'}',
					  "success"    : function(response) { $('#response').text(response); }
					});
				});
			});
		</script>
	</body>
</html>

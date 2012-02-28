<script>
function api_call(method, request, success) {
	{% if @DEBUG %}
	if (method == 'upload.send')
		api_debug('call', method, {'chunk':request.chunk, 'name':request.name});
	else
		api_debug('call', method, json_serialize(request));
	{% endif %}

	$.ajax({
	  'type'        : 'put',
	  "url"         : 'auth/api-bridge/json/',
	  "dataType"    : "json",
	  "processData" : false,
	  "data"        : json_serialize({'method':method, 'request':request}),
	  {% if @DEBUG %}
	  "complete"    : function(xhr) { api_debug('reply', method, xhr.responseText); },
	  {% endif %}
	  "success"     : success
	});
}
{% if @DEBUG %}
function api_debug(type, method, txt) {
	if (console.log) console.log('API '+type+' '+method+': '+txt);
}
{% endif %}
</script>
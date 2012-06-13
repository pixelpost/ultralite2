<script>
function api_call(method, request, onvalid, onerror) {
	api_call_raw(method, request, function(reply) {
		if (reply.status == 'valid') {
			if (onvalid) onvalid(reply.response);
		} else {
			{% if @DEBUG %}
			notify('error', reply.code+': '+reply.message, false);
			{% endif %}
			if (onerror) onerror(reply.code, reply.message);
		}
	});
}
function api_call_raw(method, request, success) {
	{% if @DEBUG %}
	if (method == 'upload.send')
		api_debug('call', method, {'chunk':request.chunk, 'name':request.name});
	else
		api_debug('call', method, json_serialize(request));
	{% endif %}

	$.ajax({
		'type'        : 'put',
		"url"         : 'api/bridge/json/',
		"dataType"    : "json",
		"processData" : false,
		"data"        : json_serialize({'method':method, 'request':request}),
		"success"     : success,
		// This add raw message in console, even if error occurs.
		{% if @DEBUG %}
		"complete"    : function(xhr) { api_debug('reply', method, xhr.responseText); },
		{% endif %}
		// Thrown in case of non json data. This should appen only when auth form is replied.
		"error"       : function() {
			notify('error', '<h3 class="alert-heading">Error:</h3>'
				+ 'Impossible to continue, your are disconnected from the admin.<br> '
				+ '<b>Please refresh this page and do it again.</b>', false);
		}
	});
}
{% if @DEBUG %}
function api_debug(type, method, txt) {
	if (console.log) console.log('API '+type+' '+method+': '+txt);
}
{% endif %}
</script>
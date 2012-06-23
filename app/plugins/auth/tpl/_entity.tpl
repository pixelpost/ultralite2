<div class="entity well well-small">
	<p>
		<strong>{{ entity.name }}</strong>
		<span class="hide">&mdash;</span>
		<span class="pull-right">
			<a class="btn btn-info btn-mini" data-toggle="collapse" data-parent="#entities" href="#{{ entity.entity }}">
				<i class="icon-list icon-white"></i> info
			</a>
			<span class="hide">&mdash;</span>
			<a class="btn btn-danger btn-mini">
				<i class="icon-remove icon-white"></i> delete
			</a>
		</span>
	</p>
	<form id="{{ entity.entity }}" class="collapse form-inline">
		<table class="table table-condensed">
			<tr>
				<th>Public key</th>
				<td>{{ entity.public_key }}</td>
			</tr>
			<tr>
				<th>Private key</th>
				<td>{{ entity.private_key }}</td>
			</tr>
			<tr>
				<th>Grants</th>
				<td>
					{% for g in grants %}
					<label class="checkbox inline">
						<input type="checkbox" value="{{ g.grant }}"> {{ g.name }}
					</label>
					{% endfor %}
				</td>
			</tr>
		</table>
		<p class="input-append">
			<input type="text" placeholder="rename that public key"><button
				class="btn"><i class="icon-pencil"></i>update</button>
		</p>
	</form>
</div>

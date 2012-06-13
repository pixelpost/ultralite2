<form id="config-form" class="form-horizontal">
	<div class="row-fluid">
		<div class="span6">
			<fieldset id="resized-style">
				<legend>Resized image</legend>
				<div class="control-group">
					<label class="control-label" for="resized-type">Type</label>
					<div class="controls">
						<select id="resized-type">
							<option value="square">Square</option>
							<option value="larger-border">Larger Border</option>
							<option value="fixed-width">Fixed Width</option>
							<option value="fixed-height">Fixed Height</option>
							<option value="fixed">Fixed</option>
						</select>
					</div>
				</div>
				<div class="control-group control-mono-size">
					<label class="control-label" for="resized-size">Size</label>
					<div class="controls">
						<input class="input-small" id="resized-size" placeholder="500"> pixels
					</div>
				</div>
				<div class="control-group control-multi-size hide">
					<label class="control-label" for="resized-width">Width</label>
					<div class="controls">
						<input class="input-small" id="resized-width" placeholder="500"> pixels
					</div>
					<br>
					<label class="control-label" for="resized-height">Height</label>
					<div class="controls">
						<input class="input-small" id="resized-height" placeholder="500"> pixels
					</div>
				</div>
			</fieldset>
			<fieldset id="thumb-style">
				<legend>Thumb image</legend>
				<div class="control-group">
					<label class="control-label" for="thumb-type">Type</label>
					<div class="controls">
						<select id="thumb-type">
							<option value="square">Square</option>
							<option value="larger-border">Larger Border</option>
							<option value="fixed-width">Fixed Width</option>
							<option value="fixed-height">Fixed Height</option>
							<option value="fixed">Fixed</option>
						</select>
					</div>
				</div>
				<div class="control-group control-mono-size">
					<label class="control-label" for="thumb-size">Size</label>
					<div class="controls">
						<input class="input-small" id="thumb-size" placeholder="150"> pixels
					</div>
				</div>
				<div class="control-group control-multi-size hide">
					<label class="control-label" for="thumb-width">Width</label>
					<div class="controls">
						<input class="input-small" id="thumb-width" placeholder="150"> pixels
					</div>
					<br>
					<label class="control-label" for="thumb-height">Height</label>
					<div class="controls">
						<input class="input-small" id="thumb-height" placeholder="150"> pixels
					</div>
				</div>
			</fieldset>
		</div>
		<div class="span6">
			<fieldset>
				<legend>Image quality</legend>
				<div class="control-group">
					<label class="control-label" for="quality">Jpeg compression</label>
					<div class="controls">
						<input id="quality" class="input-small" placeholder="90"> %
						<span id="quality-help"><i class="icon icon-question-sign"></i></span>
					</div>
				</div>
			</fieldset>
			<fieldset>
				<legend>Locations</legend>
				<div class="control-group">
					<label class="control-label" for="directory">Main</label>
					<div class="controls">
						<input id="directory" class="input-small" placeholder="photos">
						<span id="directory-help"><i class="icon icon-question-sign"></i></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="original">Original</label>
					<div class="controls">
						<input id="original" class="input-small" placeholder="original">
						<span id="original-help"><i class="icon icon-question-sign"></i></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="resized">Resized</label>
					<div class="controls">
						<input id="resized" class="input-small" placeholder="resized">
						<span id="resized-help"><i class="icon icon-question-sign"></i></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="thumb">Thumb</label>
					<div class="controls">
						<input id="thumb" class="input-small" placeholder="thumb">
						<span id="thumb-help"><i class="icon icon-question-sign"></i></span>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<div class="row-fluid">
		<div class="form-actions">
			<button class="btn btn-primary" type="submit">Update</button>
		</div>
	</div>
</form>

<script class="defered-js" data-defer="defer_plugin_photo_form">
function defer_plugin_photo_form()
{
	var directory      = $('#directory');
	var original       = $('#original');
	var quality        = $('#quality');
	var resized        = $('#resized');
	var resized_style  = $('#resized-style');
	var resized_type   = $('#resized-type');
	var resized_size   = $('#resized-size');
	var resized_width  = $('#resized-width');
	var resized_height = $('#resized-height');
	var thumb          = $('#thumb');
	var thumb_style    = $('#thumb-style');
	var thumb_type     = $('#thumb-type');
	var thumb_size     = $('#thumb-size');
	var thumb_width    = $('#thumb-width');
	var thumb_height   = $('#thumb-height');

	api_call('photo.config.get', {}, function(data)
	{
		directory.val(data.directory);
		original.val(data.original);
		resized.val(data.resized);
		thumb.val(data.thumb);
		quality.val(data.quality);
		// resize info
		resized_type.val(data.sizes.resized.type);
		if (data.sizes.resized.type == 'fixed') {
			resized_width.val(data.sizes.resized.width);
			resized_height.val(data.sizes.resized.height);
			$('.control-multi-size', resized_style).show();
			$('.control-mono-size', resized_style).hide();
		} else {
			resized_size.val(data.sizes.resized.size);
		}
		// thumb info
		thumb_type.val(data.sizes.thumb.type);
		if (data.sizes.thumb.type == 'fixed') {
			thumb_width.val(data.sizes.thumb.width);
			thumb_height.val(data.sizes.thumb.height);
			$('.control-multi-size', thumb_style).show();
			$('.control-mono-size', thumb_style).hide();
		} else {
			thumb_size.val(data.sizes.thumb.size);
		}
	}, function() {
		notify('error', 'Can\t load configuration.', false);
	});

	resized_type.change(function() {
		if (resized_type.val() == 'fixed') {
			$('.control-multi-size', resized_style).show();
			$('.control-mono-size', resized_style).hide();
		} else {
			$('.control-mono-size', resized_style).show();
			$('.control-multi-size', resized_style).hide();
		}
	});

	thumb_type.change(function() {
		if (thumb_type.val() == 'fixed') {
			$('.control-multi-size', thumb_style).show();
			$('.control-mono-size', thumb_style).hide();
		} else {
			$('.control-mono-size', thumb_style).show();
			$('.control-multi-size', thumb_style).hide();
		}
	});

	$('#config-form').submit(function(e)
	{
		e.preventDefault();
		e.stopPropagation();

		if (resized_type.val() == 'fixed') {
			var resized_data = {
				'type'  : resized_type.val(),
				'width' : resized_width.val(),
				'height': resized_height.val()
			};
		} else {
			var resized_data = {
				'type' : resized_type.val(),
				'size' : resized_size.val(),
			};
		}

		if (thumb_type.val() == 'fixed') {
			var thumb_data = {
				'type'  : thumb_type.val(),
				'width' : thumb_width.val(),
				'height': thumb_height.val()
			};
		} else {
			var thumb_data = {
				'type' : thumb_type.val(),
				'size' : thumb_size.val(),
			};
		}

		var data = {
			'directory' : directory.val(),
			'original'  : original.val(),
			'resized'   : resized.val(),
			'thumb'     : thumb.val(),
			'quality'   : quality.val(),
			'sizes'     : {
				'resized' : resized_data,
				'thumb'   : thumb_data
			}
		};

		api_call('photo.config.set', data, function() {
			notify('success', 'Configuration was updated.');
		}, function() {
			notify('error', 'Configuration was not updated.');
		})
	});

	$('#directory-help').popover({
		placement :'top',
		title     :'Main Location',
		content   :'All photos directory (original, resized, thumb) are located under this directory.'
	});

	$('#original-help').popover({
		placement :'top',
		title     :'Original Location',
		content   :'All uploaded photos are located in this directory.'
	});

	$('#resized-help').popover({
		placement :'top',
		title     :'Resized Location',
		content   :'All resized photos are located in this directory.'
	});

	$('#thumb-help').popover({
		placement :'top',
		title     :'Thumb Location',
		content   :'All thumbnail photos are located in this directory.'
	});

	$('#quality-help').popover({
		placement :'top',
		title     :'jpeg quality',
		content   :'Compression rate for original, resized and thumb jpeg file.'
	});
}
</script>

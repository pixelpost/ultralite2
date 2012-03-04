$(document).ready(function() {
	// create a new dropbox
	new dropbox({
		'container'       : $('#dropbox'),
		'label'           : $('#droplabel'),
		'progressbar'     : $('#dropaction').find('.progressbar'),
		'button'          : $('#dropaction').find('.btn'),
		'input'           : $('#dropfiles'),
		'link'            : $('#dropselect'),
		'post_max_size'   : PP_DROPBOX_MAX_SIZE,
	});
});

dropbox = function(config) {
	// hack for methods
	var self = this;
	// objects
	this.files         = new dropbox_file_list();
	this.box_alt       = new dropbox_container_alt(config.input, config.link, self);
	this.box           = new dropbox_container(config.container, self);
	this.lbl           = new dropbox_label(config.label);
	this.btn           = new dropbox_button(config.button, self);
	this.progress      = new dropbox_progressbar(config.progressbar);

	// methods
	this.read_file = function(file, callback) {

		// process next file if it's not an image
		if (file.type.substr(0, 5) != 'image') return callback();
		// process next file if it's already loaded
		if (self.files.is_in(file)) return callback();

		var loaded = 0;
		var reader = new FileReader();

		// on start reading
		reader.onloadstart = function(e) {
			self.progress.valid();
		};
		// on progress
		reader.onprogress = function(e) {
			if (e.lengthComputable) {
				var old = loaded;
				loaded = Math.round(100 * e.loaded / e.total);
				self.progress.add(loaded - old);
			}
		};
		// on success
		reader.onload = function(e) {
			var id = self.files.add(file);
			self.box.add_thumb(new dropbox_thumb(id, file.name, e.target.result));
		};
		// on error
		reader.onerror = function(e) {
			self.progress.error();
		};
		// on abort
		reader.onabort = function(e) {
			self.progress.add(100 - loaded);
			callback();
		};
		// finally
		reader.onloadend = function(e) {
			self.progress.add(100 - loaded);
			callback();
		};
		// read the file
		reader.readAsDataURL(file);
	};

	this.read_files = function(files) {

		var it = new iterator(files);

		// before read
		it.on_start = function() {
			// start processing added files
			self.btn.hide();
			self.progress.begin(files.length * 100);
		};
		// after reading
		it.on_end = function() {
			self.progress.end(function() {
				self.lbl.reset();
				if (self.files.get_list().length > 0) self.btn.show();
			});
		};
		// reading...
		it.on_progress = function() {
			self.lbl.process(it.current().name);
			self.read_file(it.current(), it.next);
		};

		// processing...
		it.iterate();
	};

	this.get_chunks = function(thumb) {
		// thumb.get_src() value is like:
		// data:image/png;base64,iVBORw0KGgoAAAAN...
		// remove data: image/... ;base64,
		var max_size = config.post_max_size;
		var src      = thumb.get_src();
		var data     = src.substring(src.indexOf(';', 0) + 8);

		// using chunk size of 300 * 76 character: ~25 Ko
		// (76 chars can be safely base64 decoded)
		var min    = 1024 * 250;  // 250K
		var max    = 1024 * 1024; // 1M
		var size   = Math.round(data.length / 4, 0) + 1;
		if (size < min)      size = min;
		if (size > max)      size = max;
		if (size > max_size) size = max_size;

		// create the chunks array
		var regex  = new RegExp('.{0,' + size + '}', 'g');
		var chunks = data.match(regex);

		// remove the empty string at the end of the array, I don't know why
		chunks.pop();

		return chunks;
	};

	this.upload_file = function(file, thumb, callback) {

		// retrieve chunks of the file
		var chunks = self.get_chunks(thumb);

		// this data will be sent to the server
		var data   = {
			'name'   : file.name,
			'size'   : file.size,
			'type'   : file.type,
			'chunks' : chunks.length,
		};

		// before chunks are uploaded
		var upload_begin = self.progress.valid;

		// after chunks are uploaded
		var upload_end = function(it) {
			api_call('upload.end', {'id': it.context().id}, function(resp) {
				if (resp.status == 'valid')
				{
					api_call('photo.add', {'file': resp.response.file});
				}
			});
			callback();
		};

		// upload a chunk
		var upload_chunk = function(it) {
			var data = {
				'id'    : it.context().id,
				'chunk' : it.key(),
				'data'  : it.current(),
			};
			api_call('upload.send', data, function() {
				self.progress.add(it.current().length);
				it.next();
			});
		};

		// UPLOAD THE FILE
		api_call('upload.init', data, function(upload) {
			if (upload.status == 'valid') {
				// server accept and provide an "upload" id, process all chunks
				var it = new iterator(chunks, {
					'id'          : upload.response.id,
					'on_start'    : upload_begin,
					'on_progress' : upload_chunk,
					'on_end'      : upload_end,
				});
				it.iterate();
			} else {
				// server won't accept the upload, move the next file
				self.progress.add(file.size * 4/3);
				self.progress.error();
				callback();
			}
		});
	};

	this.upload_files  = function() {

		var it = new iterator(self.files.get_list());

		// before upload
		it.on_start = function() {
			self.btn.hide();
			// calc the total base64 size (4/3 is the base64 ratio)
			var total = 0;
			for(var i in self.files) {
				if (self.files[i]) {
					total += Math.round(self.files[i].size * 4/3);
				}
			}
			// set the progress bar
			self.progress.begin(total);
		};

		// after upload
		it.on_end = function() {
			self.progress.end(function() {            // progress bar to 100 %
				self.files = new dropbox_file_list(); // clean file list
				self.box.del_all();                   // remove all thumbs
				self.lbl.set('Uploaded !');           // show during 1600ms : Uploaded !
				setTimeout(self.lbl.reset, 1600);
			});
		};

		// file upload
		it.on_progress = function() {
			// retrieve the thumbnail (it contains the base64 data)
			var thumb = self.box.get_thumb(it.key() - 1);
			var file  = it.current();

			self.lbl.upload(it.current().name);

			// we have the file data, upload it or go to the next file
			if (thumb) {
				self.upload_file(file, thumb, function() {
					self.box.del_thumb(thumb.get_id());
					it.next();
				});
			} else {
				self.progress.add(file.size * 4/3);
				self.progress.error();
				it.next();
			}
		};

		// start process
		it.iterate();
	};
};

dropbox_thumb = function(id, name, src) {
	// methods
	this.as_html = function() {
		return $('<img>').attr({
			'class'   : 'thumb',
			'width'   : '100px',
			'file_id' : id,
			'src'     : src,
			'alt'     : name,
			'title'   : name,
		});
	};
	this.find_in = function(element) {
		return element.find('img[file_id='+id+']');
	};
	this.get_id  = function() { return id; };
	this.get_src = function() { return src; };
};

dropbox_container = function(container, dropbox) {
	var self   = this;
	var thumbs = $('<div></div>');
	var list   = [];
	// methods
	this.add_thumb = function(thumb) {
		thumbs.append(thumb.as_html());
		list[thumb.get_id()] = thumb;
	};
	this.get_thumb = function(id) {
		return list[id];
	};
	this.del_thumb = function(id) {
		var thumb = list[id];
		if (thumb) {
			list[id] = null;
			thumb.find_in(thumbs).fadeOut(400).delay(400).remove();
		}
	}
	this.del_all   = function() {
		for(var i in list) self.del_thumb(i);
	};
	// constructor
	container.prepend(thumbs).bind({
		'drag'      : function() { return false; },
		'dragstart' : function() { return false; },
		'dragend'   : function() { return false; },
		'dragenter' : function() { return false; },
		'dragleave' : function() { return false; },
		'dragover'  : function() { return false; },
		'drop'      : function(e) {
			e.stopPropagation();
			e.preventDefault();
			dropbox.read_files(e.originalEvent.dataTransfer.files);
		}
	});
};

dropbox_container_alt = function(input, link, dropbox) {
	// constructor
	input.bind('change', function(e) {
		$(this).hide();
		e.stopPropagation();
		e.preventDefault();
		dropbox.read_files(e.originalEvent.target.files);
	});
	link.bind('click', function(e) {
		e.stopPropagation();
		e.preventDefault();
		input.click().show(); // don't work on chrome, so we show the button
	});
};

dropbox_label  = function(label) {
	// hack for methods
	var self   = this;
	// the original text
	var origin = label.text();
	// methods
	this.set     = function(text) { label.text(text); };
	this.reset   = function()     { self.set(origin); };
	this.process = function(name) { self.set('Processing ' + name); };
	this.upload  = function(name) { self.set('Uploading ' + name); };
	this.error   = function(name) { self.set('Error ' + name); };
};

dropbox_button = function(button, dropbox) {
	var self   = this;
	// methods
	this.action = function()     { dropbox.upload_files() };
	this.show   = function()     { button.show(); };
	this.hide   = function()     { button.hide(); };
	this.text   = function(text) { button.text(text); };
	// constructor
	button.click(function() { self.action() });
};

dropbox_file_list = function() {
	var self  = this;
	this.list = [];
	this.ids  = [];
	// methods
	this.get_list  = function() {
		return self.list;
	};
	this.get_index = function(file) {
		return file.name + " " + file.size + " " + file.type;
	};
	this.get_id    = function(file) {
		return self.ids[self.get_index(file)];
	};
	this.add_all   = function(list) {
		for(var i = 0; i < list.length; ++i) self.add(list[i]);
	};
	this.add       = function(file) {
		var index = self.get_index(file);
		var id    = self.list.length;
		if (!self.ids[index]) {
			self.ids[index] = id;
			self.list[id] = file;
		}
		return id;
	};
	this.del       = function(id) {
		self.list[id] = null;
	};
	this.is_in     = function(file) {
		return (self.get_id(file) != undefined);
	};
};

dropbox_progressbar = function(bar) {
	// add a inner div
	var self   = this;
	var inner  = $('<div></div>');
	var total  = 0;
	var loaded = 0;
	// methods
	this.begin = function(size) {
		total  = size;
		loaded = 0;
		self.valid();
		inner.width('0%');
		bar.show();
		self.load(2);
	};
	this.end   = function(finished) {
		self.load(100);
		// this waitting for progress bar goes to 100% before fadeOut
		inner.queue('fx', function(next) { bar.fadeOut('slow', finished); next() });
	};
	this.load  = function(size) {
		inner.animate({'width': size + '%'}, 50);
	};
	this.add   = function(more) {
		loaded += more;
		self.load(Math.round(100 * loaded / total));
	};
	this.error = function() {
		inner.addClass('error');
	};
	this.valid = function() {
		inner.removeClass('error');
	};
	// constructor
	bar.prepend(inner);
};

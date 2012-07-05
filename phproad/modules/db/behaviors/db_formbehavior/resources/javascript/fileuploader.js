var FileUploader = new Class({
	Extends: FancyUpload2,
	
	initialize: function(fieldId, options) {
		this.files = [];
		this.fieldId = fieldId;

		var defaultOptions = {
			instantStart: true,
			allowDuplicates: true,
			processResponse: true,
			fieldName: 'file',
			debug: false,
			path: ls_root_url('/phproad/modules/db/behaviors/db_formbehavior/resources/swf/Swiff.Uploader.swf')
		};
		
		options = $merge(defaultOptions, options);
		if (options.callBacks) {
			this.addEvents(options.callBacks);
			options.callBacks = null;
		}

		this.parent(null, null, options);
	},
	
	showProgress: function()
	{
		if (this.uploadProgress) {
			this.linkElement.setStyle('visibility', 'hidden');
			this.uploadProgress.removeClass('invisible');
		}
			
		if (this.options.embedProgressTo !== undefined) {
			if (this.containerOverlay === undefined) {
				var containerSize = $(this.options.embedProgressTo).getSize();
				$(this.options.embedProgressTo).addClass('overlay')

				this.containerOverlay = new Element('div', {
					'class': 'ui-upload-overlay',
					'width': containerSize.x + 'px',
					'height': containerSize.y + 'px'
				}).inject($(this.options.embedProgressTo));
				
				this.progressBar = new Element('div', {
					'class': 'progress-bar'
				}).inject(this.containerOverlay);
				
				this.progressBarValue = new Element('div', {
					'class': 'bar'
				}).inject(this.progressBar);
			}
		}
	},
	
	hideProgress: function()
	{
		if (this.uploadProgress) {
			this.linkElement.setStyle('visibility', 'visible');
			this.uploadProgress.addClass('invisible');
		}

		if (this.containerOverlay !== undefined) {
			$(this.options.embedProgressTo).removeClass('overlay');
			this.containerOverlay.destroy();
			this.containerOverlay = undefined;
		}
	},
	
	onSelect: function(file, index, length) {
		var errors = [];

		if (this.options.limitSize && (file.size > this.options.limitSize)) errors.push('size');
		if (this.options.limitFiles && (this.countFiles() >= this.options.limitFiles)) errors.push('length');
		if (!this.options.allowDuplicates && this.getFile(file)) errors.push('duplicate');
		if (!this.options.validateFile.call(this, file, errors)) errors.push('custom');
		if (errors.length) {
			var fn = this.options.fileInvalid;
			if (fn) fn.call(this, file, errors);
			return false;
		}
		this.files.push(file);
		this.showProgress();
		return true;
	},
	
	render: function() {
		this.field = $(this.fieldId);
		this.linkElement = this.field.getElement('a');
		this.uploadProgress = this.field.getElement('img');

		this.linkElement.addEvent('click', (function() {
			this.browse();
			return false;
		}).bind(this));

		this.overallProgress = null;
		if (!this.options.embedProgressTo)
			this.overallProgress = new Fx.ProgressBar(this.uploadProgress, {});
	},
	
	updateOverall: function(bytesTotal) {
		this.bytesTotal = bytesTotal;
	},
	
	onOpen: function(file, overall) {
		this.log('Starting upload "{name}".', file);
		file = this.getFile(file);
		if(file.element) file.element.addClass('file-uploading');
	},
	
	onProgress: function(file, current, overall) {
		if (this.overallProgress)
			this.overallProgress.start(overall.bytesLoaded, overall.bytesTotal);
		else if (this.progressBarValue !== undefined) 
			this.progressBarValue.setStyle('width', Math.round(overall.bytesLoaded/overall.bytesTotal*100) + '%');
	},

	onComplete: function(file, response) {
		this.log('Completed upload "' + file.name + '".', arguments);
		
		(this.options.fileComplete || this.fileComplete).call(this, this.finishFile(file), response);
	},
	
	onAllSelect: function(files, current, overall) {
		this.log('Added ' + files.length + ' files, now we have (' + current.bytesTotal + ' bytes).', arguments);
		this.updateOverall(current.bytesTotal);
		
		if (this.files.length && this.options.instantStart) this.upload.delay(10, this);
	},
	
	onAllComplete: function(current) {
		this.log('Completed all files, ' + current.bytesTotal + ' bytes.', arguments);
		this.updateOverall(current.bytesTotal);
		if (this.overallProgress) 
			this.overallProgress.start(100);
		else if (this.progressBarValue !== undefined) 
			this.progressBarValue.setStyle('width', '100%');
		
		this.fireEvent.delay(500, this, ['uploadComplete', this]);
		this.hideProgress.delay(500, this);
	},
	
	fileComplete: function(file, response) {
		var json = $H(JSON.decode(response, true));

		if (json.get('result') != 'success') {
			alert('Error uploading file '+file.name+'. '+json.get('error'));
		}
	}
});
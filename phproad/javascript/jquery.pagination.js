(function( $, undefined ) {
	jQuery.widget( "ui.pagination", {
		options: { 
			pageSize: 20,
			recordCount: 0
		},
		
		getFirstPageRowIndex: function() {
			return this.options.pageSize*this.pageIndex;
		},
		
		getLastPageRowIndex: function()
		{
			var index = this.getFirstPageRowIndex();
			index += this.options.pageSize-1;

			if (index > this.recordCount-1)
				index = this.recordCount-1;
				
			return index;
		},
		
		setRecordCount: function(recordCount)
		{
			this.pageCount = this._calculatePageCount(this.options.pageSize, recordCount);
			this.currentPageIndex = this._fixCurrentPageIndex(this.pageIndex, this.pageCount);
			this.recordCount = recordCount;
			this._refresh();
		},
		
		setCurrentPageIndex: function(pageIndex)
		{
			var lastPageIndex = this.pageCount - 1;

			if (pageIndex < 0)
				pageIndex = 0;

			if ( pageIndex > lastPageIndex )
				pageIndex = lastPageIndex;

			this.currentPageIndex = pageIndex;
			this._refresh();

			return pageIndex;
		},
		
		_create: function() {
			this.pageIndex = 0;
			this._build();
			this.setRecordCount(this.options.recordCount);
		},
		
		_build: function() {
			this.pageList = jQuery('<p/>');
			this.intervalInfo = jQuery('<strong/>');
			this.totalRecords = jQuery('<strong/>');
			this.pages = jQuery('<span/>').addClass('numbers');
			
			this.pageList.append(
				document.createTextNode("Showing "),
				this.intervalInfo,
				document.createTextNode(" of "),
				this.totalRecords,
				document.createTextNode(" records. Page: "),
				this.pages
			)

			this.element.append(this.pageList);
		},
		
		_refresh: function() {
			this.pages.empty();
			this.totalRecords.empty();
			this.intervalInfo.empty();
			
			var self = this;

			this.intervalInfo.text((this.getFirstPageRowIndex()+1)+'-'+(this.getLastPageRowIndex()+1));
			this.totalRecords.text(this.recordCount);
			
			if (this.pageCount < 11) {
				for (var i = 1; i <= this.pageCount; i++) {
					if (i != this.currentPageIndex+1) {
						this._createPageLink(i);
					} else
						this._createCurrentPageMark(i);
						
					this._createSpace();
				}
			} else {
				var 
					startIndex = this.currentPageIndex-5,
					endIndex = this.currentPageIndex+5;
					lastPageIndex = this.pageCount-1;
				
				if (startIndex < 0) startIndex = 0;
				if (endIndex > lastPageIndex) endIndex = lastPageIndex;
				if ((endIndex - startIndex) < 11) endIndex = startIndex + 11;
				if (endIndex > lastPageIndex) endIndex = lastPageIndex;
				if ((endIndex - startIndex) < 11) startIndex = endIndex - 11;
				if (startIndex < 0) startIndex = 0;
					
				for (i = startIndex+1; i <= endIndex+1; i++) {
					if (i != this.currentPageIndex+1)
						this._createPageLink(i);
					else
						this._createCurrentPageMark(i);

					this._createSpace();
				}
				
				if (startIndex > 0) {
					if (startIndex > 1)
						this.pages.prepend(document.createTextNode(' ... '));
					
					this.pages.prepend(
						jQuery('<a></a>')
							.text(1)
							.bind('click', function(){self.element.trigger('pagination-click', 0); return false;})
							.attr('href', '#')
					);
				}
				
				if (endIndex < lastPageIndex)
				{
					if (lastPageIndex-endIndex > 1)
						this.pages.append(document.createTextNode(' ... '));

					this.pages.append(
						jQuery('<a></a>')
							.text(lastPageIndex+1)
							.bind('click', function(){self.element.trigger('pagination-click', lastPageIndex); return false; })
							.attr('href', '#')
					);
				}
				
				this.pages.append(document.createTextNode(' '));
			}
		},
		
		_createPageLink: function(pageIndex) {
			var self = this;
			this.pages.append(
				jQuery('<a></a>')
					.text(pageIndex)
					.bind('click', function(){self.element.trigger('pagination-click', pageIndex-1); return false; })
					.attr('href', '#')
			);
		},
		
		_createCurrentPageMark: function(pageIndex) {
			this.pages.append(
				jQuery('<span class="current"></span>')
					.text(pageIndex)
			);
		},
		
		_createSpace: function() {
			this.pages.append(document.createTextNode(' '));
		},
		
		_calculatePageCount: function( pageSize, recordCount ) {
			var result = Math.ceil(recordCount/pageSize);

			if (result === 0)
				result = 1;

			return result;
		},
		
		_fixCurrentPageIndex: function( currentPageIndex, pageCount ) {
			var lastPageIndex = pageCount - 1;

			if ( currentPageIndex > lastPageIndex )
				currentPageIndex = lastPageIndex;

			return currentPageIndex;
		}
	})
})( jQuery );
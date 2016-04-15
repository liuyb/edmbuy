/*!
 * v 1.1: 
 *  a.加入了对form input hidden数据的支持
 * v 1.2: 
 *  a.加入了对form input hidden, input text数据的支持
 *  b.如果传入outFormId,认为已存在一个用于上传的form，不再新建form，使用存在的form
 *    同时不删除该form(新建form时会删除) 
 * v 1.3:
 *  支持传入的 fileElementId 为一个jQuery元素
 */
jQuery.extend({
	
    createUploadIframe: function(id, uri)
	{
		//create frame
        var frameId = 'jUploadFrame' + id;
        var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
		if(window.ActiveXObject)
		{
            if(typeof uri== 'boolean'){
				iframeHtml += ' src="' + 'javascript:false' + '"';

            }
            else if(typeof uri== 'string'){
				iframeHtml += ' src="' + uri + '"';

            }	
		}
		iframeHtml += ' />';
		jQuery(iframeHtml).appendTo(document.body);

        return jQuery('#' + frameId).get(0);
    },

	createUploadForm: function(id, fileElementId, outFormId) {
		//create form	
		if (typeof outFormId != 'undefined' && outFormId != '') {
			var form = $('#'+outFormId);
			$(form).attr('name', outFormId);
			return form;
		}
		var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;		
		var form   = $('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	
		var oldElement = null;
		if (typeof (fileElementId) == 'object') { //fileElementId is a jQuery object. Modified by Gavin
			oldElement = fileElementId;
		}
		else {
			oldElement = $('#' + fileElementId)
		}
		var newElement = $(oldElement).clone();
		$(oldElement).attr('id', fileId);
		$(oldElement).before(newElement);
		$(oldElement).appendTo(form);
		//set attributes
		$(form).css('position', 'absolute');
		$(form).css('top', '-1200px');
		$(form).css('left', '-1200px');
		//set form fields
		if (typeof outFormId != 'undefined' && outFormId != '') {
			$('#'+outFormId+' input:hidden,#'+outFormId+' input:text').each(function(){
				var newInput = $(this).clone();
				$(newInput).removeAttr('id').hide().appendTo($(form));			
			});
		}
		$(form).appendTo('body');
		return form;
	},

	ajaxFileUpload: function(s) {
		// TODO introduce global settings, allowing the client to modify them for all requests, not only timeout		
		s = jQuery.extend({}, jQuery.ajaxSettings, s);
		var id   = new Date().getTime()        
		var form = jQuery.createUploadForm(id, s.fileElementId, s.outFormId);
		var io   = jQuery.createUploadIframe(id, s.secureuri);
		var frameId = 'jUploadFrame' + id;
		var formId  = 'jUploadForm' + id;
		// use exists an upload form, use it 
		var useForeignForm = false;
		if (typeof s.outFormId != 'undefined' && s.outFormId != '') {
			formId = s.outFormId;
			useForeignForm = true;
		}		
		// Watch for a new set of requests
		if ( s.global && ! jQuery.active++ ) {
			jQuery.event.trigger( "ajaxStart" );
		}            
		var requestDone = false;
		// Create the request object
		var xml = {}   
		if ( s.global )
			jQuery.event.trigger("ajaxSend", [xml, s]);
			
		// Wait for a response to come back
		var uploadCallback = function(isTimeout) {			
			var io = document.getElementById(frameId);
			try {				
				if(io.contentWindow) {
					xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
					xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;
				}
				else if(io.contentDocument) {
					xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
					xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
				}
			} catch(e) {
				jQuery.handleError(s, xml, null, e);
			}
			
			if ( xml || isTimeout == "timeout") {				
				requestDone = true;
				var status;
				try {
					status = isTimeout != "timeout" ? "success" : "error";
					// Make sure that the request was successful or notmodified
					if ( status != "error" ) {
						// process the data (runs the xml through httpData regardless of callback)
						var data = jQuery.uploadHttpData( xml, s.dataType );
						// If a local callback was specified, fire it and pass it the data
						if ( s.success )
							s.success( data, status );
	    
						// Fire the global callback
						if( s.global )
							jQuery.event.trigger( "ajaxSuccess", [xml, s] );
					} else {
						jQuery.handleError(s, xml, status);
					}	
				} catch(e) {
					status = "error";
					jQuery.handleError(s, xml, status, e);
				}
	
				// The request was completed
				if( s.global )
					jQuery.event.trigger( "ajaxComplete", [xml, s] );
	
				// Handle the global AJAX counter
				if ( s.global && ! --jQuery.active )
					jQuery.event.trigger( "ajaxStop" );
	
				// Process result
				if ( s.complete )
					s.complete(xml, status);
	
				jQuery(io).unbind()
	
				setTimeout(function() {	
							try {
								$(io).remove();
								if (!useForeignForm) {	//edited by Gavin
									$(form).remove();
								}
						 	} catch(e) {
								jQuery.handleError(s, xml, null, e);
							}
						}, 100);
										
				xml = null;
			}
		}// end uploadCallback
		
		// Timeout checker
		if ( s.timeout > 0 ) {
			setTimeout(function(){
		    	// Check to see if the request is still happening
		    	if( !requestDone ) uploadCallback( "timeout" );
				}, s.timeout);
		}
		
		try {
			var form = $('#' + formId);
			$(form).attr('action', s.url);
			$(form).attr('method', 'POST');
			$(form).attr('target', frameId);
			if(form.encoding) {
				form.encoding = 'multipart/form-data';				
			} else {				
				form.enctype = 'multipart/form-data';
			}			
			$(form).submit();
	
		} catch(e) {			
			jQuery.handleError(s, xml, null, e);
		}
	  if(window.attachEvent){
			document.getElementById(frameId).attachEvent('onload', uploadCallback);
	  }
	  else{
			document.getElementById(frameId).addEventListener('load', uploadCallback, false);
	  }
	  return {abort: function () {}};	

	},	// end ajaxFileUpload

	uploadHttpData: function( r, type ) {
		var data = !type;
		data = (type == "xml" || data) ? r.responseXML : r.responseText;
		// If the type is "script", eval it in global context
		if ( type == "script" )
			jQuery.globalEval( data );
		// Get the JavaScript object, if JSON is used.
		if ( type == "json" ) {
			data = data.replace( /(^<[^>]+>)|(<\/[^>]+>$)/g, '' ); //strip like <pre></pre> wrapper
			eval( "data = " + data );
		}
		// evaluate scripts within html
		if ( type == "html" )
			jQuery("<div>").html(data).evalScripts();
		
		return data;
	}
}) 
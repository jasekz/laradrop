jQuery.fn.laradrop = function(options) {	
	// options/config
    Dropzone.autoDiscover = false;
    var fileHandler = options.fileHandler,
    	fileDeleteHandler = options.fileDeleteHandler,
    	fileSrc = options.fileSrc,
    	csrfToken = options.csrfToken,
    	csrfTokenField = options.csrfTokenField ? options.csrfTokenField : '_token',
    	areYouSureText = options.areYouSureText ? options.areYouSureText : 'Are you sure?',
    	processingDisplay = options.processingDisplay ? options.processingDisplay : 'processing...',
    	onInsertCallback = options.onInsertCallback ? options.onInsertCallback : null,
    	uid = new Date().getTime(),
    	laradropObj = jQuery(this);
    
   // option overrides via html atttributes
   if(jQuery(this).attr('laradrop-upload-handler')) {
	   fileHandler = jQuery(this).attr('laradrop-upload-handler');
   }   
   if(jQuery(this).attr('laradrop-file-delete-handler')) {
	   fileDeleteHandler = jQuery(this).attr('laradrop-file-delete-handler');
   }   
   if(jQuery(this).attr('laradrop-file-source')) {
	   fileSrc = jQuery(this).attr('laradrop-file-source');
   }   
   if(jQuery(this).attr('laradrop-csrf-token')) {
	   csrfToken = jQuery(this).attr('laradrop-csrf-token');
   }   
   if(jQuery(this).attr('laradrop-csrf-token-field')) {
	   csrfTokenField = jQuery(this).attr('laradrop-csrf-token-field');
   }
   
   // setup file container
   jQuery('body').after(getModalContainer());
   
   // dropzone bind
    var dz = new Dropzone("#modal-container-"+uid+" .btn-add-files", { 
        url: fileHandler,
        autoQueue:false,
        previewsContainer: ".previews", 
        previewTemplate:getPreviewContainer(),
        parallelUploads:1,
        init: function(){        		
        	this.on("sending", function(file, xhr, data) {
        		random = Math.random().toString(36).replace('.', '');
                data.append(csrfTokenField, csrfToken);
                data.append('thumbId', random);
                data.append('filename', $(file.previewElement).find('#filename').val());
            });             
            this.on("complete", function(file){
            	dz.removeFile(file);
            	if(dz.files.length == 0){
            		var cntr = jQuery('.laradrop-modal-container');
            		cntr.find('.modal-body').css({'width':'100%'});
            		cntr.find('.previews').hide();
            	}
        		
        		jQuery.get(fileSrc, function(res){
        			displayMedia(res);
        		});
            });            
            this.on("removedfile", function(file) {
            	if(dz.files.length == 0){
            		var cntr = jQuery('.laradrop-modal-container');
            		cntr.find('.modal-body').css({'width':'100%'});
            		cntr.find('.previews').hide();
            	}
            });            
            this.on("addedfile", function(obj){         		
        		var cntr = jQuery('.laradrop-modal-container'),
        			w = $(window).width();
        		
        		cntr.find('.modal-body').css({'width':w-450+'px'});
        		cntr.find('.previews').show();
         	    $(".start").click(function() {
         		   dz.enqueueFiles(dz.getFilesWithStatus(Dropzone.ADDED));
         	    });
            });
        }
    });      
    	
	jQuery(this).find('.laradrop-select-file').click(function(e){
		e.preventDefault();		
		var cntr = jQuery('.laradrop-modal-container'),
			w = $(window).width(),
			h = $(window).height();
		
		cntr.css({'width': w-50+'px','left': '25px'});
		cntr.find('.modal-dialog').css({'width': w-100+'px'});
		cntr.find('.modal-body').css({'width':'100%', 'float':'right','height':h-200+'px','overflow':'auto','margin-right':'20px'});
		cntr.find('.previews').css({'width':'300px','float':'left','height':h-200+'px','overflow':'auto','padding':'0 5px 0 5px'}).hide();
		
		jQuery.get(fileSrc, function(res){
			displayMedia(res);
			cntr.modal('toggle');
		});
	});

	function displayMedia(res){
			var out='';
			jQuery.each(res.data, function(k,v){
				out+=getThumbnailContainer(Math.random().toString(36).replace('.', '')).replace('[[fileSrc]]', v.filename).replace('[[fileId]]',v.id);
			});
			jQuery('.laradrop-modal-container').find('.modal-title').text('Media');
			jQuery('.laradrop-modal-container').find('.modal-body').html(out);
			jQuery('.laradrop-modal-container').find('.insert').click(function(){
				var src = jQuery(this).closest('.laradrop-thumbnail').find('img').attr('src');
				laradropObj.find('.laradrop-file-thumb').html('<img src="'+src+'" />');
				laradropObj.find('.laradrop-input').val(src);
				eval(onInsertCallback(src));
				jQuery('.laradrop-modal-container').modal('hide');
			});	  
		    
		    jQuery('.laradrop-modal-container .delete').click(function(e) {
		    	e.preventDefault();
		    	var fileId = jQuery(this).attr('file-id');
		    	
		    	fileDeleteHandler = fileDeleteHandler.replace(fileDeleteHandler.substr(fileDeleteHandler.lastIndexOf('/')), '/'+fileId);
		    	
		    	if(!confirm(areYouSureText)) {
		    		return false;
		    	}
		    	
				jQuery.ajax({
				    url: fileDeleteHandler,
				    type: 'DELETE',
				    dataType: 'json',
			        headers: {
			        	'X-CSRF-TOKEN': csrfToken
			        },
				    success: function(res) {
		            	jQuery.get(fileSrc, function(files){ 
		            		displayMedia(files);
		            	});
				    }
				});
		    });
		    
		    jQuery('.laradrop-thumbnail .thumbnail').hover(
		    	function() {
		    		jQuery(this).find('.caption').show();
		    	}, function() {
		    		jQuery(this).find('.caption').hide();
		    	}
		    );
	}	

	function getModalContainer() {
		return '\
    	<div class="modal fade laradrop-modal-container" id="modal-container-'+uid+'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\
		  <div class="modal-dialog modal-lg">\
		    <div class="modal-content" >\
		      <div class="modal-header">\
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
		        <button type="button" class="btn btn-success btn-add-files">Add Files</button>\
		        <button type="button" class="btn btn-primary start">Start Upload</button>\
		      </div>\
		      <div class="modal-body row list-group" ></div>\
    		  <div class="previews" ></div>\
		      <div class="modal-footer">\
		      </div>\
		    </div>\
		  </div>\
		</div>';
	}
	
	function getThumbnailContainer(id) {
		return '\
		<div class="item laradrop-thumbnail col-md-1" id="'+id+'" style="margin:15px;" >\
			<div class="thumbnail" style="cursor:pointer;height:150px;width:150px;">\
				<div class="caption" style="display:none;position:absolute;bottom:20px;left:13px;">\
					<div class="row">\
						<div class="col-md-5">\
							<button class="btn btn-success btn-xs insert">Insert</button>\
						</div>\
						<div class="col-md-5">\
							<button class="btn btn-danger btn-xs delete" file-id="[[fileId]]" >Delete</button>\
						</div>\
					</div>\
				</div>\
				<img class="group list-group-image" style="max-height:110px;" src="[[fileSrc]]" alt="" />\
			</div>\
		</div>'
	}
	
	function getPreviewContainer(){
		return '\
		<div class="table table-striped" class="files">\
        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="box-shadow:none;height:2px;margin:15px 0 15px 0;">\
        <div class="progress-bar progress-bar-success" style="width:0%;height:2px;" data-dz-uploadprogress></div>\
		</div>\
		  <div id="template" class="file-row">\
		    <div style="margin:5px;float:right;">\
		      <button data-dz-remove class="btn btn-danger btn-xs cancel">\
		          <span>Cancel</span>\
		      </button>\
		    </div>\
		    <div>\
		        <p class="name" data-dz-name></p>\
		        <strong class="error text-danger" data-dz-errormessage></strong>\
		    </div>\
		    <div>\
		        <p class="size" data-dz-size></p>\
		    </div>\
		  </div>\
		</div>';
	}
}

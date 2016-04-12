jQuery.fn.laradrop = function(options) {	
    Dropzone.autoDiscover = false;
    var fileHandler = options.fileHandler,
    	fileDeleteHandler = options.fileDeleteHandler,
    	fileSrc = options.fileSrc,
    	fileCreateHandler = options.fileCreateHandler,
    	fileMoveHandler = options.fileMoveHandler,
    	containersUrl = options.containersUrl,
    	csrfToken = options.csrfToken,
    	csrfTokenField = options.csrfTokenField ? options.csrfTokenField : '_token',
    	actionConfirmationText = options.actionConfirmationText ? options.actionConfirmationText : 'Are you sure?',
    	breadCrumbRootText = options.breadCrumbRootText ? options.breadCrumbRootText : 'Root Directory',
        folderImage = options.folderImage ? options.folderImage : '/vendor/jasekz/laradrop/img/genericThumbs/folder.png',
    	onInsertCallback = options.onInsertCallback ? options.onInsertCallback : null,
    	uid = new Date().getTime(),
    	laradropObj = jQuery(this),
    	laradropContainer=null,
    	laradropPreviewContainer=null,
    	dz=null,
    	currentFolderId = null,
    	breadCrumbs = [],
    	customData = options.customData?options.customData:{},
    	views = {
    		main: null,
    		preview: null,
    		file: null
    	};
 
   // try html atttributes if not set in options
   if(!fileHandler && laradropObj.attr('laradrop-upload-handler')) {
	   fileHandler = laradropObj.attr('laradrop-upload-handler');
   }   
   if(!fileMoveHandler && laradropObj.attr('laradrop-file-move-handler')) {
	   fileMoveHandler = laradropObj.attr('laradrop-file-move-handler');
   }   
   if(!fileDeleteHandler && laradropObj.attr('laradrop-file-delete-handler')) {
	   fileDeleteHandler = laradropObj.attr('laradrop-file-delete-handler');
   }   
   if(!fileSrc && laradropObj.attr('laradrop-file-source')) {
	   fileSrc = laradropObj.attr('laradrop-file-source');
   }   
   if(!csrfToken  && laradropObj.attr('laradrop-csrf-token')) {
	   csrfToken = laradropObj.attr('laradrop-csrf-token');
   }   
   if(!csrfTokenField && laradropObj.attr('laradrop-csrf-token-field')) {
	   csrfTokenField = laradropObj.attr('laradrop-csrf-token-field');
   }  
   if(!fileCreateHandler && laradropObj.attr('laradrop-file-create-handler')) {
	   fileCreateHandler = laradropObj.attr('laradrop-file-create-handler');
   }   
   if(!containersUrl && laradropObj.attr('laradrop-containers')) {
	   containersUrl = laradropObj.attr('laradrop-containers');
   } 

   if( ! fileHandler)  return false;
   
   // init containers & data
   jQuery.ajax({
	    url: containersUrl,
	    type: 'GET',
	    dataType: 'json',
	    success: function(res) {
	    	
	       // populate html templates
		   views.main = res.data.main.replace('[[uid]]', uid);
		   views.preview = res.data.preview;
		   views.file = res.data.file;
			   
		   // setup file container
		   laradropObj.append(getLaradropContainer());
		   laradropContainer = jQuery('#laradrop-container-'+uid),
		   laradropPreviewsContainer = laradropContainer.find('.laradrop-previews');
		   laradropPreviewsContainer.attr('id', 'laradrop-previews-'+uid);
		   
		   // 'add folder' 
		   laradropContainer.find('.btn-add-folder').click(function(e){
			    e.preventDefault();
				jQuery.ajax({
				    url: fileCreateHandler+'?pid='+currentFolderId,
				    type: 'POST',
				    dataType: 'json',
			        headers: { 'X-CSRF-TOKEN': csrfToken },
				    success: function(res) {
						jQuery.get(fileSrc+'?pid='+currentFolderId, function(res){
							displayMedia(res);
						});
				    }, 
				    error: function(jqXHR,textStatus,errorThrown){
				    	handleError(jqXHR,textStatus,errorThrown);
				    }
				});
		   })
		   
		   // 'add files' 
		   if(fileHandler) {
			   dz = new Dropzone('#'+laradropContainer.attr('id')+' .btn-add-files', { 
			        url: fileHandler,
			        autoQueue:false,
			        previewsContainer: "#laradrop-previews-"+uid, 
			        previewTemplate:getPreviewContainer(),
			        parallelUploads:1,
			        init: function(){        		
			        	this.on("sending", function(file, xhr, data) {
			        		random = Math.random().toString(36).replace('.', '');
			                data.append(csrfTokenField, csrfToken);
			                data.append('thumbId', random);
			                data.append('filename', $(file.previewElement).find('#filename').val());
			                data.append('pid', currentFolderId);
			                data.append('customData', JSON.stringify(customData));
			            });             
			            this.on("complete", function(file){
			            	dz.removeFile(file);        		
			        		jQuery.get(fileSrc+'?pid='+currentFolderId, function(res){
			        			displayMedia(res);
			        		});
			            });            
			            this.on("removedfile", function(file) {
			            	if(dz.files.length == 0){
			            		laradropPreviewsContainer.hide();
			            		$('.start').hide();
			            	}
			            });            
			            this.on("addedfile", function(obj){         		
			            	laradropPreviewsContainer.show();
			            	$('.start').show();
			         	    $(".start").click(function() {
			         		   dz.enqueueFiles(dz.getFilesWithStatus(Dropzone.ADDED));
			         	    });
			            });
			        }
			    });  
		    }

		   // get data
		   jQuery.ajax({
			    url: fileSrc,
			    type: 'GET',
			    dataType: 'json',
			    success: function(res) {
				    breadCrumbs.push({
				    	id: 0,
				    	alias: breadCrumbRootText
				    });
					displayMedia(res);
			    }, 
			    error: function(jqXHR,textStatus,errorThrown){
			    	handleError(jqXHR,textStatus,errorThrown);
			    }
		   });
	    }, 
	    error: function(jqXHR,textStatus,errorThrown){
	    	handleError(jqXHR,textStatus,errorThrown);
	    }
	});

	function displayMedia(res){
			var out='',
				record='',
				re;

			jQuery.each(res.data, function(k,v){
				record = getThumbnailContainer(Math.random().toString(36).replace('.', ''));
				
				jQuery.each(v, function(k2, v2){
					re = new RegExp("\\[\\["+k2+"\\]\\]","g");
					record = record.replace(re, v2);
				});				

				re = new RegExp("\\[\\[fileSrc\\]\\]","g");
				if(v.type=='folder') {
					out+=record.replace(re, folderImage)
							   .replace('laradrop-thumbnail','laradrop-thumbnail laradrop-thumbnail-image laradrop-folder');
				} else {
					out+=record.replace(re, v.filename).replace(/laradrop\-droppable/g,'');
				}
			});
			laradropContainer.find('.laradrop-body').html(out);

			laradropContainer.find('.laradrop-file-insert').click(function(){
				var src = jQuery(this).closest('.laradrop-thumbnail').find('img').attr('src');
				laradropContainer.find('.laradrop-file-thumb').html('<img src="'+src+'" />');
				laradropContainer.find('.laradrop-input').val(src);
				
				if(onInsertCallback) {
					eval(onInsertCallback(src));
				}
			});	  
		    
			laradropContainer.find('.laradrop-file-delete').click(function(e) {
		    	e.preventDefault();
		    	var fileId = jQuery(this).closest('.laradrop-thumbnail').attr('file-id');
		    	
		    	fileDeleteHandler = fileDeleteHandler.replace(fileDeleteHandler.substr(fileDeleteHandler.lastIndexOf('/')), '/'+fileId);
		    	
		    	if(!confirm(actionConfirmationText)) {
		    		return false;
		    	}
		    	
				jQuery.ajax({
				    url: fileDeleteHandler,
				    type: 'DELETE',
				    dataType: 'json',
			        headers: { 'X-CSRF-TOKEN': csrfToken },
				    success: function(res) {				    						    
		            	jQuery.get(fileSrc+'?pid='+currentFolderId, function(files){ 
		            		displayMedia(files);
		            	});
				    }, 
				    error: function(jqXHR,textStatus,errorThrown){
				    	handleError(jqXHR,textStatus,errorThrown);
				    }
				});
		    });
		    
			laradropContainer.find('.laradrop-folder img').click(function(){	
				var folder = jQuery(this).closest('.laradrop-folder');
		    	currentFolderId=folder.attr('file-id');
			    breadCrumbs.push({
			    	id: currentFolderId,
			    	alias: folder.find('.laradrop-filealias').text()
			    });

				jQuery.get(fileSrc+'?pid='+currentFolderId, function(res){
					displayMedia(res);
				});
		    });
		    
		    displayBreadCrumbs();
		    
		    laradropContainer.find('.laradrop-draggable').draggable({
			      cancel: ".non-draggable", 
			      revert: "invalid", 
			      containment: "document",
			      helper: "clone",
			      cursor: "move",

			      cursorAt: { top: 30, left: 15 },
			      helper: function( event ) {
			        return $( "<div class='panel panel-info' >&nbsp; &#8631; &nbsp;</div>" );
			      }
			});
			
		    laradropContainer.find('.laradrop-droppable').droppable({
			      accept: ".laradrop-draggable",
			      hoverClass: "laradrop-droppable-hover",
			      activeClass: "laradrop-droppable-highlight",
			      drop: function( event, ui ) {
			        var draggedId = ui.draggable.attr('file-id'),
		    			droppedId = jQuery(this).attr('file-id');
			    	
					jQuery.ajax({
					    url: fileMoveHandler,
					    type: 'POST',
					    dataType: 'json',
				        headers: { 'X-CSRF-TOKEN': csrfToken },
				        data: {'draggedId':draggedId, 'droppedId':droppedId, 'customData': JSON.stringify(customData)},
					    success: function(res) {							    
							jQuery.get(fileSrc+'?pid='+currentFolderId, function(res){
								displayMedia(res);
							});
					    }, 
					    error: function(jqXHR,textStatus,errorThrown){
					    	handleError(jqXHR,textStatus,errorThrown);
					    }
					});
			      }
			});
	}	
	
	function displayBreadCrumbs(){
		laradropContainer.find('.laradrop-breadcrumbs').remove();
		var crumbs='<div class="laradrop-breadcrumbs">',
			length = breadCrumbs.length;

		jQuery.each(breadCrumbs, function(k, v){
			if(k+1==length) {
				crumbs+='<span >'+v.alias+'</span>';
			}else{
				crumbs+='<a href="#" class="laradrop-breadcrumb laradrop-droppable" file-id="'+v.id+'" >'+v.alias+'</a><span class="arrow">&raquo; &nbsp;</span>';
			}
		});
		crumbs+='</div>';		
		laradropContainer.find('.laradrop-breadcrumbs-container').prepend(crumbs);
		
		laradropContainer.find('.laradrop-breadcrumb').click(function(e){
			e.preventDefault();
	    	currentFolderId=jQuery(this).attr('file-id');
	    	var newCrumbs = [];
		    jQuery.each(breadCrumbs, function(k, v){
	    		newCrumbs.push(v);
		    	if(v.id==currentFolderId){
		    		return false;
		    	}
		    });
		    breadCrumbs=newCrumbs;
		    
			jQuery.get(fileSrc+'?pid='+currentFolderId, function(res){
				displayMedia(res);
			});
		});
	}
	
	function handleError(jqXHR,textStatus,errorThrown){
		var error = jQuery.parseJSON(jqXHR.responseText);
		alert(error.msg);
	}

	function getLaradropContainer() {
		return views.main;
	}
	
	function getThumbnailContainer(id) {
		return views.file;
	}
	
	function getPreviewContainer(){
		return views.preview;
	}
	
	return laradropObj;
}

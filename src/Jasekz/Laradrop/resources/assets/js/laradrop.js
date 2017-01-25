jQuery.fn.laradrop = function(options) {	
    Dropzone.autoDiscover = false;
    options = options == undefined ? {} : options;

    var laradropObj = jQuery(this),
		fileHandler = options.fileHandler ? options.fileHandler : ( laradropObj.attr('laradrop-upload-handler') ? laradropObj.attr('laradrop-upload-handler') : '/laradrop'),
    	fileDeleteHandler = options.fileDeleteHandler ? options.fileDeleteHandler : ( laradropObj.attr('laradrop-file-delete-handler') ? laradropObj.attr('laradrop-file-delete-handler') : '/laradrop/0'),
    	fileSrc = options.fileSrc ? options.fileSrc : ( laradropObj.attr('laradrop-file-source') ? laradropObj.attr('laradrop-file-source') : '/laradrop'),
    	fileCreateHandler = options.fileCreateHandler ? options.fileCreateHandler : ( laradropObj.attr('laradrop-file-create-handler') ? laradropObj.attr('laradrop-file-create-handler') : '/laradrop/create'),
    	fileMoveHandler = options.fileMoveHandler ? options.fileMoveHandler : ( laradropObj.attr('laradrop-file-move-handler') ? laradropObj.attr('laradrop-file-move-handler') : '/laradrop/move'),
    	containersUrl = options.containersUrl ? options.containersUrl : ( laradropObj.attr('laradrop-containers') ? laradropObj.attr('laradrop-containers') : '/laradrop/containers'),
    	csrfToken = options.csrfToken ? options.csrfToken : ( laradropObj.attr('laradrop-csrf-token') ? laradropObj.attr('laradrop-csrf-token') : null ),
    	csrfTokenField = options.csrfTokenField ? options.csrfTokenField : ( laradropObj.attr('laradrop-csrf-token-field') ? laradropObj.attr('laradrop-csrf-token-field') : '_token'),
    	actionConfirmationText = options.actionConfirmationText ? options.actionConfirmationText : 'Are you sure?',
    	breadCrumbRootText = options.breadCrumbRootText ? options.breadCrumbRootText : 'Root Directory',
        folderImage = options.folderImage ? options.folderImage : '/vendor/jasekz/laradrop/img/genericThumbs/folder.png',
    	onInsertCallback = options.onInsertCallback ? options.onInsertCallback : null,
    	onSuccessCallback = options.onSuccessCallback ? options.onSuccessCallback : null,
    	onErrorCallback = options.onErrorCallback ? options.onErrorCallback : null;
    	uid = new Date().getTime(),
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
   
   // init containers, default options & data
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
			    	    this.on("error", function(jqXHR,textStatus,errorThrown){
			    	    	handleError({responseText:JSON.stringify(textStatus)});
			    	    });                   
			            this.on("success", function(status,res){
			            	if(onSuccessCallback) {
			            		eval(onSuccessCallback(res));
			        		}
			            }); 		
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
				var item = jQuery(this).closest('.laradrop-thumbnail'),
					thumbSrc = item.find('img').attr('src'),
					id = item.attr('file-id');
								
				if(onInsertCallback) {
					eval(onInsertCallback({id:id, src:thumbSrc}));
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
		
		if(onErrorCallback) {
			eval(onErrorCallback(jqXHR,textStatus,errorThrown));
		} else {
			alert(errorThrown);
		}
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

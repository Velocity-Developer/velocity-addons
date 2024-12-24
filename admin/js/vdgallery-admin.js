jQuery(document).ready(function($) {
    var galleryFrame;
    var gallerySelection = [];
  
    // Frame  
    if (typeof wp.media !== 'undefined') {
      
      galleryFrame = wp.media.frames.mysite_gallery_frame = wp.media({
          title: 'Select Image',
          button: {
              text: 'Insert Image'
          },
          library: {
              type: 'image'
          },
          multiple: 'add',
      });
  
      // Add
      jQuery('.vdgallery-add').click(function(e) {
          e.preventDefault();
          galleryFrame.open();
      });
  
      galleryFrame.on('open', function() {
        galleryFrame.state().get('selection').reset();
        var arrayImages = [];
        jQuery('.vdgallery-image').each(function(index, element) {
          var idd = jQuery(this).data('id');
          if(idd) {
            arrayImages.push(idd);
            galleryFrame.state().get('selection').add(wp.media.attachment(idd));
          }      
        });
      });
  
      galleryFrame.on('select', function() {
          gallerySelection = galleryFrame.state().get('selection');
  
          jQuery(".vdgallery-main").empty();
  
          gallerySelection.map(function(attachment) {
              // console.log(attachment);
              var nodeid = Math.random().toString(36).substring(7);
              var id = attachment.id;
              var url = attachment.attributes.sizes.thumbnail?attachment.attributes.sizes.thumbnail.url:attachment.attributes.url;
  
              jQuery(".vdgallery-main").append(`<div class="vdgallery-image vdgallery-image-${nodeid}" data-node="${nodeid}" data-id="${id}"><input name="vdgaleri-post[media][]" value="${id}" type="hidden"><img src="${url}" alt=""><div class="vdgallery-option"><span class="vdgallery-remove dashicons dashicons-no-alt"></span></div></div>`);
          });
          // galleryFrame.close();
      });
      
      // Remove
      jQuery('.vdgallery-main').on('click', '.vdgallery-remove', function() {
          var imageWrapper = jQuery(this).parents('.vdgallery-image');
          imageWrapper.remove();
      });
  
      // Sortable
      jQuery('.vdgallery-main').sortable();
      jQuery('.vdgallery-main').disableSelection();
  
      $(document).ready(function(){
        $('.vdgallery-tabs-opt .tabs-item').hide();
        $('.vdgallery-tabs-opt .tabs-item:first').show();
        $('.vdgallery-tabs-link .tabs-link:first').addClass('active');
        $('.vdgallery-tabs-link .tabs-link').click(function(){
          $('.vdgallery-tabs-link .tabs-link').removeClass('active');
          $(this).addClass('active');
          var target  = $(this).data('target');
          var isi     = '.vdgallery-tabs-opt div[data-target="'+ target +'"]';
          $('.vdgallery-tabs-opt .tabs-item').hide();
          $(isi).show();
        });
      });
  
    }
    
  });
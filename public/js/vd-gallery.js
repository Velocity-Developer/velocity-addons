jQuery(function($){
    $('.vdgallery-galleryshow, .vdgallery-slide').each(function() {
        $(this).magnificPopup({
            delegate: 'a',
            type: 'image',
            gallery: {
              enabled:true
            }
        });
    });

    if($('.gallery .gallery-item').length){
        $('.gallery .gallery-item').each(function(index, el) {
            var url = $(el).find('img').attr('src');
            $(el).find('a').attr('href',url);
            $(el).magnificPopup({
                delegate: 'a',
                type: 'image',
                gallery: {
                  enabled:true
                }
            });
        });
    };

    $('.vdgallery-pagination').each(function() {
        var node = $(this).data('node');
        $('.vdgallery-galleryshow-'+node+' .vdgallery-item').hide();
        $('.vdgallery-galleryshow-'+node+' .vdgallery-item[data-pagi="1"]').show();
        $('.vdgallery-galleryshow-'+node+' .vdgallery-pagi-btn[data-pagi="1"]').addClass('active');
    });
    
    $('.vdgallery-pagi-btn').click(function() {
        var node = $(this).data('node');
        var pagi = $(this).data('pagi');
        $('.vdgallery-galleryshow-'+node+' .vdgallery-item').hide();
        $('.vdgallery-galleryshow-'+node+' .vdgallery-item[data-pagi="'+pagi+'"]').show();
        $('.vdgallery-galleryshow-'+node+' .vdgallery-pagi-btn').removeClass('active');
        $('.vdgallery-galleryshow-'+node+' .vdgallery-pagi-btn[data-pagi="'+pagi+'"]').addClass('active');
    });
});
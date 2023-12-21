jQuery(document).ready(function ($) {
  var custom_uploader;

  $("#upload_image_button").click(function (e) {
    e.preventDefault();

    // If the uploader object has already been created, reopen the dialog
    if (custom_uploader) {
      custom_uploader.open();
      return;
    }

    // Extend the wp.media object
    custom_uploader = wp.media.frames.file_frame = wp.media({
      title: "Choose Image",
      button: {
        text: "Choose Image",
      },
      multiple: false,
    });

    // When a file is selected, grab the URL and set it as the text field's value
    custom_uploader.on("select", function () {
      var attachment = custom_uploader
        .state()
        .get("selection")
        .first()
        .toJSON();
        console.log(attachment.height);
      $("#share_image").val(attachment.url);
      $(".preview_share_image").html('<br><img src="'+attachment.url+'" width="300" /><br><span class="delete_share_image button">Delete</span>');
      if(attachment.height < 200 || attachment.width < 200){
        $(".preview_share_image").append('<div class="vdaddons-notice">Minimal Ukuran gambar 200x200</div>');
      }
    });

    // Open the uploader dialog
    custom_uploader.open();
  });
  $(document).on('click','.delete_share_image', function (e) {
    $("#share_image").val('');
    $(".preview_share_image").html('');
  });
});

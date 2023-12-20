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
      $("#share_image").val(attachment.url);
      $(".preview_share_image").html('<br><img src="'+attachment.url+'" width="300" /><span class="delete_share_image button">Delete</span>');
    });

    // Open the uploader dialog
    custom_uploader.open();
  });
  $(".delete_share_image").click(function (e) {
    $("#share_image").val('');
    $(".preview_share_image").html('');
  });
});

jQuery(document).ready(function ($) {
  var custom_uploader;
  function openShareImagePicker() {
    if (custom_uploader) {
      custom_uploader.open();
      return;
    }
    custom_uploader = wp.media.frames.file_frame = wp.media({
      title: "Choose Image",
      button: {
        text: "Choose Image",
      },
      multiple: false,
    });
    custom_uploader.on("select", function () {
      var attachment = custom_uploader
        .state()
        .get("selection")
        .first()
        .toJSON();
      $("#share_image").val(attachment.url);
      var html =
        '<img id="preview_image" src="' +
        attachment.url +
        '" style="max-width:100%; border-radius:0.5rem;" />' +
        '<br><span class="delete_share_image button" style="margin-top:0.5rem;">Delete</span>';
      $("#vd-share-dropzone .vd-share-preview").html(html);
      $("#vd-share-dropzone").addClass("has-image");
      if (attachment.height < 200 || attachment.width < 200) {
        $("#vd-share-dropzone .vd-share-preview").append(
          '<div class="vdaddons-notice">Minimal Ukuran gambar 200x200</div>'
        );
      }
    });
    custom_uploader.open();
  }

  $("#upload_image_button").click(function (e) {
    e.preventDefault();
    openShareImagePicker();
  });
  var $drop = $("#vd-share-dropzone");
  if ($drop.length) {
    $drop.on("click", function (e) {
      e.preventDefault();
      openShareImagePicker();
    });
    $drop.on("dragenter dragover", function (e) {
      e.preventDefault();
      e.stopPropagation();
      $drop.addClass("is-dragover");
    });
    $drop.on("dragleave dragend drop", function (e) {
      e.preventDefault();
      e.stopPropagation();
      $drop.removeClass("is-dragover");
      if (e.type === "drop") {
        openShareImagePicker();
      }
    });
  }
  $(document).on("click", ".delete_share_image", function (e) {
    $("#share_image").val("");
    $("#vd-share-dropzone .vd-share-preview").html("");
    $("#vd-share-dropzone").removeClass("has-image");
  });

  $("#reset-data").click(function () {
    $.ajax({
      url: ajaxurl,
      type: "post",
      data: {
        action: "reset_data",
      },
      success: function (response) {
        if (response.success) {
          $("#reset-message").html(
            '<div class="notice notice-success is-dismissible"><p>' +
              response.data +
              "</p></div>"
          );
        } else {
          $("#reset-message").html(
            '<div class="notice notice-error is-dismissible"><p>Terjadi kesalahan saat mereset data.</p></div>'
          );
        }
      },
      error: function () {
        $("#reset-message").html(
          '<div class="notice notice-error is-dismissible"><p>Terjadi kesalahan saat mengirim permintaan AJAX.</p></div>'
        );
      },
    });
  });

  // Dashboard Chart
  if (
    document.getElementById("velocityVisitorChart") &&
    window.velocityDashboardData
  ) {
    const ctx = document
      .getElementById("velocityVisitorChart")
      .getContext("2d");
    const chartData = window.velocityDashboardData;

    new Chart(ctx, {
      type: "line",
      data: {
        labels: chartData.labels,
        datasets: [
          {
            label: "Pengunjung Unik",
            data: chartData.visitors,
            borderColor: "#3b82f6",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            borderWidth: 2,
            tension: 0.3,
            fill: true,
          },
          {
            label: "Total Pageviews",
            data: chartData.pageviews,
            borderColor: "#10b981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            borderWidth: 2,
            tension: 0.3,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
          },
          tooltip: {
            mode: "index",
            intersect: false,
          },
        },
        interaction: {
          mode: "nearest",
          axis: "x",
          intersect: false,
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              borderDash: [2, 2],
              color: "#f3f4f6",
            },
            ticks: {
              precision: 0,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }
});

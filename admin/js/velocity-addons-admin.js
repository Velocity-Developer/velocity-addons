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

    $('#reset-data').click(function() {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'reset_data',
            },
            success: function(response) {
                if (response.success) {
                    $('#reset-message').html('<div class="notice notice-success is-dismissible"><p>' + response.data + '</p></div>');
                } else {
                    $('#reset-message').html('<div class="notice notice-error is-dismissible"><p>Terjadi kesalahan saat mereset data.</p></div>');
                }
            },
            error: function() {
                $('#reset-message').html('<div class="notice notice-error is-dismissible"><p>Terjadi kesalahan saat mengirim permintaan AJAX.</p></div>');
            }
        });
    });

    // Dashboard Chart
    if (document.getElementById('velocityVisitorChart') && window.velocityDashboardData) {
        const ctx = document.getElementById('velocityVisitorChart').getContext('2d');
        const chartData = window.velocityDashboardData;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Pengunjung Unik',
                        data: chartData.visitors,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Total Pageviews',
                        data: chartData.pageviews,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 2],
                            color: '#f3f4f6'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});

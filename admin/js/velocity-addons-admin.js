jQuery(document).ready(function ($) {
  var custom_uploader;
  initVelocityTopNavigation();

  function initVelocityTopNavigation() {
    var config = window.velocityAdminNavConfig || {};
    var items = Array.isArray(config.items) ? config.items : [];
    var page =
      config.page ||
      new URLSearchParams(window.location.search || "").get("page") ||
      "";
    if (!items.length || !page) {
      return;
    }

    var wrapper = document.querySelector(".velocity-dashboard-wrapper");
    if (!wrapper || wrapper.querySelector(".velocity-topnav")) {
      return;
    }

    var nav = document.createElement("div");
    nav.className = "velocity-topnav";

    var brand = document.createElement("div");
    brand.className = "velocity-topnav__brand";
    brand.textContent = "Velocity Addons";
    nav.appendChild(brand);

    var links = document.createElement("nav");
    links.className = "velocity-topnav__links";
    links.setAttribute("aria-label", "Velocity Addons Navigation");

    var subnav = document.createElement("div");
    subnav.className = "velocity-subnav";

    items.forEach(function (item) {
      if (!item || !item.page || !item.label) {
        return;
      }

      var hasChildren = Array.isArray(item.children) && item.children.length;
      var activeChild = hasChildren
        ? item.children.some(function (child) {
            return child && child.page === page;
          })
        : false;
      var isActive = item.page === page || activeChild;

      var a = document.createElement("a");
      a.className = "velocity-topnav__link" + (isActive ? " is-active" : "");
      a.href = "admin.php?page=" + encodeURIComponent(item.page);
      a.textContent = item.label;
      links.appendChild(a);

      if (!hasChildren || !isActive) {
        return;
      }

      var subnavTitle = document.createElement("div");
      subnavTitle.className = "velocity-subnav__title";
      subnavTitle.textContent = item.label;
      subnav.appendChild(subnavTitle);

      var subnavLinks = document.createElement("nav");
      subnavLinks.className = "velocity-subnav__links";
      subnavLinks.setAttribute("aria-label", item.label + " Navigation");

      item.children.forEach(function (child) {
        if (!child || !child.page || !child.label) {
          return;
        }
        var childLink = document.createElement("a");
        childLink.className =
          "velocity-subnav__link" + (child.page === page ? " is-active" : "");
        childLink.href = "admin.php?page=" + encodeURIComponent(child.page);
        childLink.textContent = child.label;
        subnavLinks.appendChild(childLink);
      });

      subnav.appendChild(subnavLinks);
    });

    nav.appendChild(links);
    wrapper.insertBefore(nav, wrapper.firstChild);

    if (subnav.children.length) {
      wrapper.insertBefore(subnav, nav.nextSibling);
    }
  }
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
          '<div class="vdaddons-notice">Minimal Ukuran gambar 200x200</div>',
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
              "</p></div>",
          );
        } else {
          $("#reset-message").html(
            '<div class="notice notice-error is-dismissible"><p>Terjadi kesalahan saat mereset data.</p></div>',
          );
        }
      },
      error: function () {
        $("#reset-message").html(
          '<div class="notice notice-error is-dismissible"><p>Terjadi kesalahan saat mengirim permintaan AJAX.</p></div>',
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

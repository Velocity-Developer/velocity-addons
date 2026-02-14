(function (window, document) {
  "use strict";

  var config = window.velocitySettingsConfig || {};
  var page = getCurrentPage();
  var globalNoticeTimer = null;
  var globalNoticeHideTimer = null;
  var adminApi = window.VelocityAddonsAdmin || {};
  window.VelocityAddonsAdmin = adminApi;

  if (!page) {
    return;
  }

  adminApi.copyToClipboard = function (text) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(showCopySuccess);
      return;
    }

    var ta = document.createElement("textarea");
    ta.value = text;
    ta.style.position = "fixed";
    ta.style.left = "-9999px";
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand("copy");
      showCopySuccess();
    } catch (_e) {}
    document.body.removeChild(ta);
  };

  if (typeof window.copyToClipboard !== "function") {
    window.copyToClipboard = function (text) {
      return adminApi.copyToClipboard(text);
    };
  }

  if (page === "velocity_statistics") {
    initStatisticsPage();
  }

  if (page === "velocity_optimize_db") {
    initOptimizePage();
  }

  function initStatisticsPage() {
    var root = document.getElementById("velocity-statistics-page");
    if (!root) {
      return;
    }

    var noticeEl = document.getElementById("velocity-statistics-notice");
    var resetForm = document.getElementById("velocity-statistics-reset-form");
    var resetButton = document.getElementById("velocity-statistics-reset-button");
    var dailyCanvas = document.getElementById("dailyVisitsChart");
    var pagesCanvas = document.getElementById("topPagesChart");
    var pagesBody = document.getElementById("velocity-statistics-pages-body");
    var referrersBody = document.getElementById("velocity-statistics-referrers-body");

    var cardKeys = ["today", "this_week", "this_month", "all_time"];
    var dailyChart = null;
    var pagesChart = null;

    if (resetForm) {
      resetForm.addEventListener("submit", function (event) {
        event.preventDefault();

        var confirmMessage = (resetButton && resetButton.getAttribute("data-confirm-message")) || "";
        if (confirmMessage && !window.confirm(confirmMessage)) {
          return;
        }

        setResetLoading(true);
        apiRequest("/statistics/reset", "POST", {})
          .then(function (response) {
            if (response && response.data) {
              renderStatistics(response.data);
            }
            showNotice(noticeEl, response.message || "Statistik berhasil di-reset.", "success");
          })
          .catch(function (error) {
            showNotice(noticeEl, error.message || "Gagal reset statistik.", "error");
          })
          .finally(function () {
            setResetLoading(false);
          });
      });
    }

    apiRequest("/statistics", "GET")
      .then(function (response) {
        if (response && response.data) {
          renderStatistics(response.data);
        }
      })
      .catch(function (error) {
        showNotice(noticeEl, error.message || "Gagal memuat statistik.", "error");
      });

    function setResetLoading(isLoading) {
      if (!resetButton) {
        return;
      }
      resetButton.disabled = isLoading;
      resetButton.setAttribute("aria-busy", isLoading ? "true" : "false");
    }

    function renderStatistics(data) {
      renderSummary(data.summary || {});
      renderPageTable(data.pages || []);
      renderReferrerTable(data.referrers || []);
      dailyChart = renderDailyChart(dailyCanvas, dailyChart, data.daily || []);
      pagesChart = renderPagesChart(pagesCanvas, pagesChart, data.page_chart || []);
    }

    function renderSummary(summary) {
      for (var i = 0; i < cardKeys.length; i++) {
        var key = cardKeys[i];
        var card = root.querySelector('[data-stat-card="' + key + '"]');
        if (!card) {
          continue;
        }
        var stats = summary[key] || {};
        var uniqueEl = card.querySelector("[data-stat-unique]");
        var totalEl = card.querySelector("[data-stat-total]");
        if (uniqueEl) {
          uniqueEl.textContent = formatNumber(stats.unique_visitors || 0);
        }
        if (totalEl) {
          totalEl.textContent = formatNumber(stats.total_visits || 0);
        }
      }
    }

    function renderPageTable(rows) {
      if (!pagesBody) {
        return;
      }

      if (!rows.length) {
        pagesBody.innerHTML =
          '<tr><td colspan="3" style="text-align:center;color:#666;">No data available</td></tr>';
        return;
      }

      var html = "";
      for (var i = 0; i < rows.length; i++) {
        var row = rows[i] || {};
        var fullUrl = row.full_url || row.url || "#";
        html +=
          "<tr>" +
          '<td><a href="' +
          escapeAttr(fullUrl) +
          '" target="_blank" rel="noopener noreferrer"><code>' +
          escapeHtml(row.url || "") +
          "</code></a></td>" +
          "<td>" +
          escapeHtml(formatNumber(row.unique_visitors || 0)) +
          "</td>" +
          "<td>" +
          escapeHtml(formatNumber(row.total_views || 0)) +
          "</td>" +
          "</tr>";
      }
      pagesBody.innerHTML = html;
    }

    function renderReferrerTable(rows) {
      if (!referrersBody) {
        return;
      }

      if (!rows.length) {
        referrersBody.innerHTML =
          '<tr><td colspan="2" style="text-align:center;color:#666;">No data available</td></tr>';
        return;
      }

      var html = "";
      for (var i = 0; i < rows.length; i++) {
        var row = rows[i] || {};
        html +=
          "<tr>" +
          "<td><code>" +
          escapeHtml(row.host || row.referer || "") +
          "</code></td>" +
          "<td>" +
          escapeHtml(formatNumber(row.visits || 0)) +
          "</td>" +
          "</tr>";
      }
      referrersBody.innerHTML = html;
    }
  }

  function initOptimizePage() {
    var form = document.getElementById("velocity-optimize-form");
    if (!form) {
      return;
    }

    var noticeEl = document.getElementById("velocity-optimize-notice");
    var submitButtons = form.querySelectorAll('button[type="submit"][name="do"]');
    var lastDo = "selected";
    var chartCanvas = document.getElementById("optimizeChart");
    var optimizeChart = null;

    for (var i = 0; i < submitButtons.length; i++) {
      submitButtons[i].addEventListener("click", function () {
        lastDo = this.value || "selected";
      });
    }

    form.addEventListener("submit", function (event) {
      event.preventDefault();

      var active = document.activeElement;
      var doValue = lastDo;
      if (active && active.name === "do" && active.value) {
        doValue = active.value;
      }

      var selectedItems = getSelectedItems(form);
      if (doValue === "selected" && !selectedItems.length) {
        showNotice(noticeEl, "Pilih minimal satu item untuk dibersihkan.", "error");
        return;
      }

      var confirmMessage =
        doValue === "all"
          ? "Apakah Anda yakin ingin menjalankan optimize untuk semua item?"
          : "Apakah Anda yakin ingin menjalankan optimize untuk item terpilih?";
      if (!window.confirm(confirmMessage)) {
        return;
      }

      setOptimizeLoading(form, true);
      apiRequest("/optimize-db/run", "POST", {
        do: doValue,
        items: selectedItems,
      })
        .then(function (response) {
          if (response && response.stats) {
            optimizeChart = renderOptimizeStats(response.stats, chartCanvas, optimizeChart);
          }
          if (response && response.result && response.result.summary) {
            showNotice(noticeEl, "Optimize selesai. " + response.result.summary, "success");
          } else {
            showNotice(noticeEl, response.message || "Optimize selesai.", "success");
          }
          clearSelectedItems(form);
        })
        .catch(function (error) {
          showNotice(noticeEl, error.message || "Gagal optimize database.", "error");
        })
        .finally(function () {
          setOptimizeLoading(form, false);
        });
    });

    apiRequest("/optimize-db/stats", "GET")
      .then(function (response) {
        if (response && response.stats) {
          optimizeChart = renderOptimizeStats(response.stats, chartCanvas, optimizeChart);
        }
      })
      .catch(function (error) {
        showNotice(noticeEl, error.message || "Gagal memuat statistik optimize.", "error");
      });
  }

  function renderDailyChart(canvas, chartRef, rows) {
    if (!canvas || typeof window.Chart === "undefined") {
      return chartRef;
    }

    var labels = [];
    var uniqueData = [];
    var totalData = [];
    for (var i = 0; i < rows.length; i++) {
      labels.push(rows[i].date || "");
      uniqueData.push(Number(rows[i].unique_visits || 0));
      totalData.push(Number(rows[i].total_visits || 0));
    }

    return upsertChart(canvas, chartRef, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Pengunjung Unik",
            data: uniqueData,
            borderColor: "#0073aa",
            backgroundColor: "rgba(0,115,170,0.1)",
            tension: 0.4,
            fill: true,
          },
          {
            label: "Total Kunjungan",
            data: totalData,
            borderColor: "#00a32a",
            backgroundColor: "rgba(0,163,42,0.1)",
            tension: 0.4,
            fill: false,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true },
        },
        plugins: {
          legend: { position: "top" },
        },
      },
    });
  }

  function renderPagesChart(canvas, chartRef, rows) {
    if (!canvas || typeof window.Chart === "undefined") {
      return chartRef;
    }

    var labels = [];
    var views = [];
    for (var i = 0; i < rows.length; i++) {
      labels.push(rows[i].url || "");
      views.push(Number(rows[i].views || 0));
    }

    return upsertChart(canvas, chartRef, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Page Views",
            data: views,
            backgroundColor: ["#0073aa", "#00a32a", "#d63638", "#ff922b", "#7c3aed", "#db2777", "#059669", "#dc2626"],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true },
          x: {
            ticks: {
              maxRotation: 45,
              callback: function (value, index) {
                var label = labels[index] || "";
                return label.length > 20 ? label.substring(0, 20) + "..." : label;
              },
            },
          },
        },
        plugins: {
          legend: { display: false },
        },
      },
    });
  }

  function renderOptimizeStats(stats, chartCanvas, chartRef) {
    var totals = stats.totals || {};
    var totalRowsEl = document.getElementById("vd-optimize-total-rows");
    var totalSizeEl = document.getElementById("vd-optimize-total-size");
    var topListEl = document.getElementById("vd-optimize-top-list");

    if (totalRowsEl) {
      totalRowsEl.textContent = totals.rows_label || formatNumber(totals.rows || 0);
    }
    if (totalSizeEl) {
      totalSizeEl.textContent = totals.size_label || "0 B";
    }

    var items = stats.items || {};
    var keys = Object.keys(items);
    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      var item = items[key] || {};
      var countEl = document.querySelector('[data-item-count="' + key + '"]');
      var sizeEl = document.querySelector('[data-item-size="' + key + '"]');
      if (countEl) {
        countEl.textContent = item.count_label || formatNumber(item.count || 0);
      }
      if (sizeEl) {
        sizeEl.textContent = item.size_label || "0 B";
      }
    }

    if (topListEl) {
      var top = stats.top || [];
      if (!top.length) {
        topListEl.innerHTML = "<li>Tidak ada data.</li>";
      } else {
        var html = "";
        for (var j = 0; j < top.length; j++) {
          var row = top[j] || {};
          html +=
            '<li data-top-key="' +
            escapeAttr(row.key || "") +
            '">' +
            escapeHtml(row.label || "") +
            ": " +
            escapeHtml(row.size_label || "0 B") +
            " (" +
            escapeHtml(row.count_label || "0") +
            " row)</li>";
        }
        topListEl.innerHTML = html;
      }
    }

    return renderOptimizeChart(chartCanvas, chartRef, stats.chart || []);
  }

  function renderOptimizeChart(canvas, chartRef, rows) {
    if (!canvas || typeof window.Chart === "undefined") {
      return chartRef;
    }

    var labels = [];
    var sizes = [];
    var counts = [];
    for (var i = 0; i < rows.length; i++) {
      labels.push(rows[i].label || "");
      sizes.push(Math.round(Number(rows[i].size || 0) / 1024));
      counts.push(Number(rows[i].count || 0));
    }

    return upsertChart(canvas, chartRef, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Ukuran (KB)",
            data: sizes,
            borderColor: "#be1e61",
            backgroundColor: "rgba(190, 30, 97, 0.15)",
            fill: "origin",
            borderWidth: 3,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 6,
          },
          {
            label: "Row",
            data: counts,
            borderColor: "#1e73be",
            backgroundColor: "rgba(30, 115, 190, 0.15)",
            fill: false,
            borderWidth: 3,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true, position: "top" },
        },
        scales: {
          x: { grid: { color: "rgba(0,0,0,0.05)" } },
          y: { beginAtZero: true, grid: { color: "rgba(0,0,0,0.05)" } },
        },
      },
    });
  }

  function upsertChart(canvas, chartRef, configData) {
    if (!canvas || typeof window.Chart === "undefined") {
      return chartRef;
    }

    var chart = chartRef;
    if (!chart && typeof window.Chart.getChart === "function") {
      chart = window.Chart.getChart(canvas);
    }

    if (chart) {
      chart.config.type = configData.type;
      chart.data = configData.data;
      chart.options = configData.options;
      chart.update();
      return chart;
    }

    var ctx = canvas.getContext("2d");
    return new window.Chart(ctx, configData);
  }

  function getSelectedItems(form) {
    var values = [];
    var boxes = form.querySelectorAll('input[name="items[]"]:checked');
    for (var i = 0; i < boxes.length; i++) {
      values.push(boxes[i].value);
    }
    return values;
  }

  function clearSelectedItems(form) {
    var boxes = form.querySelectorAll('input[name="items[]"]');
    for (var i = 0; i < boxes.length; i++) {
      boxes[i].checked = false;
    }
  }

  function setOptimizeLoading(form, isLoading) {
    var controls = form.querySelectorAll('button[type="submit"], input[type="checkbox"]');
    for (var i = 0; i < controls.length; i++) {
      controls[i].disabled = isLoading;
    }
  }

  function showNotice(noticeEl, message, type) {
    if (noticeEl) {
      noticeEl.style.display = "none";
    }

    var toast = ensureGlobalToast();
    if (!toast) {
      return;
    }

    if (globalNoticeTimer) {
      window.clearTimeout(globalNoticeTimer);
      globalNoticeTimer = null;
    }
    if (globalNoticeHideTimer) {
      window.clearTimeout(globalNoticeHideTimer);
      globalNoticeHideTimer = null;
    }

    toast.className =
      "notice velocity-settings-notice velocity-settings-toast " +
      (type === "error" ? "notice-error" : "notice-success");
    toast.innerHTML = "<p>" + escapeHtml(message) + "</p>";
    toast.style.display = "block";
    toast.classList.remove("is-hidden");
    toast.classList.add("is-visible");

    globalNoticeTimer = window.setTimeout(function () {
      hideGlobalToast();
    }, 3200);
  }

  function showCopySuccess() {
    showNotice(null, "Shortcode copied to clipboard!", "success");
  }

  function ensureGlobalToast() {
    var toast = document.getElementById("velocity-settings-toast");
    if (toast) {
      return toast;
    }

    toast = document.createElement("div");
    toast.id = "velocity-settings-toast";
    toast.className = "notice velocity-settings-notice velocity-settings-toast is-hidden";
    toast.style.display = "none";
    (document.body || document.documentElement).appendChild(toast);

    return toast;
  }

  function hideGlobalToast() {
    var toast = document.getElementById("velocity-settings-toast");
    if (!toast) {
      return;
    }

    if (globalNoticeHideTimer) {
      window.clearTimeout(globalNoticeHideTimer);
      globalNoticeHideTimer = null;
    }

    toast.classList.remove("is-visible");
    toast.classList.add("is-hidden");

    globalNoticeHideTimer = window.setTimeout(function () {
      toast.style.display = "none";
    }, 220);
  }

  function apiRequest(path, method, body) {
    var restBase = String(config.restBase || "").replace(/\/$/, "");
    if (!restBase) {
      return Promise.reject(new Error("REST base URL tidak tersedia."));
    }

    var options = {
      method: method,
      headers: {
        "X-WP-Nonce": config.nonce || "",
      },
      credentials: "same-origin",
    };

    if (body && method !== "GET") {
      options.headers["Content-Type"] = "application/json";
      options.body = JSON.stringify(body);
    }

    return fetch(restBase + path, options).then(function (response) {
      return response
        .json()
        .catch(function () {
          return {};
        })
        .then(function (json) {
          if (!response.ok) {
            throw new Error((json && json.message) || "Request gagal.");
          }
          return json;
        });
    });
  }

  function formatNumber(number) {
    var value = Number(number || 0);
    if (typeof Intl !== "undefined" && Intl.NumberFormat) {
      return new Intl.NumberFormat().format(value);
    }
    return String(value);
  }

  function getCurrentPage() {
    return config.page || getQuery("page", "");
  }

  function getQuery(key, fallbackValue) {
    var params = new URLSearchParams(window.location.search || "");
    if (!params.has(key)) {
      return fallbackValue;
    }
    return params.get(key) || fallbackValue;
  }

  function escapeHtml(text) {
    var div = document.createElement("div");
    div.textContent = String(text || "");
    return div.innerHTML;
  }

  function escapeAttr(text) {
    return escapeHtml(text).replace(/"/g, "&quot;");
  }
})(window, document);

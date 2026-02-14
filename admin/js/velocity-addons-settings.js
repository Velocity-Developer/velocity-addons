(function (window, document) {
  "use strict";

  var config = window.velocitySettingsConfig || {};
  var page = getCurrentPage();
  var bindings = {
    velocity_general_settings: {
      route: "general",
      formSelector: "#velocity-general-form",
      withReset: true,
    },
    velocity_captcha_settings: {
      route: "captcha",
      formSelector: "form[data-velocity-settings='1']",
    },
    velocity_maintenance_settings: {
      route: "maintenance",
      formSelector: "form[data-velocity-settings='1']",
    },
    velocity_license_settings: {
      route: "license",
      formSelector: "form[data-velocity-settings='1']",
      withLicenseCheck: true,
    },
    velocity_security_settings: {
      route: "security",
      formSelector: "form[data-velocity-settings='1']",
    },
    velocity_auto_resize_settings: {
      route: "auto_resize",
      formSelector: "form[data-velocity-settings='1']",
    },
    velocity_seo_settings: {
      route: "seo",
      formSelector: "form[data-velocity-settings='1']",
    },
    velocity_floating_whatsapp: {
      route: "floating_whatsapp",
      formSelector: "form[data-velocity-settings='1']",
    },
    velocity_snippet_settings: {
      route: "snippet",
      formSelector: "form[data-velocity-settings='1']",
    },
    velocity_duitku_settings: {
      route: "duitku",
      formSelector: "form[data-velocity-settings='1']",
    },
  };

  var activeBinding = bindings[page];
  if (!activeBinding || !activeBinding.route) {
    return;
  }

  // Alpine factory used by every settings page.
  window.velocitySettingsPage = function (route, initialModel) {
    return {
      route: route,
      model: deepClone(initialModel || {}),
      form: null,
      noticeEl: null,
      saveButtons: [],
      resetForm: null,
      licenseButton: null,
      licenseStatusEl: null,
      licenseButtonDefaultLabel: "",
      boot: function (formEl) {
        this.form = formEl;
        this.noticeEl = ensureNoticeElement(formEl);
        this.saveButtons = collectSubmitButtons(formEl);

        if (this.route === "general") {
          this.bindGeneralReset();
        }
        if (this.route === "license") {
          this.bindLicenseCheck();
        }

        this.load();
      },
      load: async function () {
        try {
          var response = await apiRequest("/settings/" + this.route, "GET");
          if (response && response.settings) {
            this.model = deepMergePreferRight(deepClone(this.model), deepClone(response.settings));
            applyValuesToForm(this.form, response.settings);
            if (this.route === "general") {
              syncGeneralDynamicMenu(response.settings);
            }
          }
        } catch (error) {
          this.showNotice(error.message || "Gagal mengambil pengaturan.", "error");
        }
      },
      submit: async function (event) {
        if (event && typeof event.preventDefault === "function") {
          event.preventDefault();
        }
        this.setSaving(true);
        try {
          var payload = buildPayloadFromModelAndForm(this.model, this.form);
          var response = await apiRequest("/settings/" + this.route, "POST", {
            settings: payload,
          });
          if (response && response.settings) {
            this.model = deepMergePreferRight(deepClone(this.model), deepClone(response.settings));
            applyValuesToForm(this.form, response.settings);
            if (this.route === "general") {
              syncGeneralDynamicMenu(response.settings);
            }
          }
          this.showNotice("Pengaturan berhasil disimpan.", "success");
        } catch (error) {
          this.showNotice(error.message || "Gagal menyimpan pengaturan.", "error");
        } finally {
          this.setSaving(false);
        }
      },
      resetGeneral: async function () {
        this.setSaving(true);
        try {
          var response = await apiRequest("/settings/general/reset", "POST", {});
          if (response && response.settings) {
            this.model = deepMergePreferRight(deepClone(this.model), deepClone(response.settings));
            applyValuesToForm(this.form, response.settings);
            syncGeneralDynamicMenu(response.settings);
          }
          this.showNotice("Pengaturan umum berhasil dikembalikan ke default.", "success");
        } catch (error) {
          this.showNotice(error.message || "Gagal reset pengaturan umum.", "error");
        } finally {
          this.setSaving(false);
        }
      },
      checkLicense: async function () {
        var input =
          this.form.querySelector("#velocity_license__key") ||
          this.form.querySelector('input[name="velocity_license[key]"]');
        var key = input && typeof input.value === "string" ? input.value.trim() : "";

        if (!key) {
          this.showNotice("Silakan isi license key terlebih dahulu.", "error");
          return;
        }

        if (this.licenseButton) {
          this.licenseButton.textContent = "Checking...";
          this.licenseButton.setAttribute("aria-busy", "true");
        }

        try {
          var response = await apiRequest("/license/check", "POST", { license_key: key });
          if (response && response.settings) {
            this.model = deepMergePreferRight(deepClone(this.model), deepClone(response.settings));
            applyValuesToForm(this.form, response.settings);
          }
          if (this.licenseStatusEl) {
            this.licenseStatusEl.textContent = "License verified.";
          }
          if (this.licenseButton) {
            this.licenseButton.textContent = "License Verified!";
          }
          this.showNotice("License berhasil diverifikasi.", "success");
        } catch (error) {
          if (this.licenseStatusEl) {
            this.licenseStatusEl.textContent = error.message || "License check gagal.";
          }
          if (input) {
            input.value = "";
          }
          if (this.licenseButton) {
            this.licenseButton.textContent = this.licenseButtonDefaultLabel || "Check License";
          }
          this.showNotice(error.message || "License check gagal.", "error");
        } finally {
          if (this.licenseButton) {
            this.licenseButton.removeAttribute("aria-busy");
          }
        }
      },
      bindGeneralReset: function () {
        var hiddenAction = document.querySelector(
          'input[name="action"][value="velocity_reset_general_defaults"]'
        );
        if (!hiddenAction || !hiddenAction.form) {
          return;
        }
        this.resetForm = hiddenAction.form;
        var self = this;
        this.resetForm.addEventListener("submit", function (event) {
          event.preventDefault();
          self.resetGeneral();
        });
      },
      bindLicenseCheck: function () {
        this.licenseButton = this.form.querySelector(".check-license");
        this.licenseStatusEl = this.form.querySelector(".license-status");
        if (!this.licenseButton) {
          return;
        }
        this.licenseButtonDefaultLabel = this.licenseButton.textContent || "Check License";
        var self = this;
        this.licenseButton.addEventListener("click", function (event) {
          event.preventDefault();
          self.checkLicense();
        });
      },
      setSaving: function (isSaving) {
        for (var i = 0; i < this.saveButtons.length; i++) {
          this.saveButtons[i].disabled = isSaving;
        }
        if (this.resetForm) {
          var resetButtons = this.resetForm.querySelectorAll('button[type="submit"],input[type="submit"]');
          for (var j = 0; j < resetButtons.length; j++) {
            resetButtons[j].disabled = isSaving;
          }
        }
      },
      showNotice: function (message, type) {
        if (!this.noticeEl) {
          return;
        }
        this.noticeEl.className =
          "notice " + (type === "error" ? "notice-error" : "notice-success");
        this.noticeEl.innerHTML = "<p>" + escapeHtml(message) + "</p>";
        this.noticeEl.style.display = "block";
      },
    };
  };

  bootstrapFormBinding();

  function bootstrapFormBinding() {
    var form = document.querySelector(activeBinding.formSelector);
    if (!form) {
      return;
    }
    decorateFieldsWithXModel(form);
    var initialModel = buildInitialModelFromForm(form);
    form.setAttribute(
      "x-data",
      "velocitySettingsPage('" + activeBinding.route + "', " + JSON.stringify(initialModel) + ")"
    );
    form.setAttribute("x-init", "boot($el)");
    form.setAttribute("x-on:submit.prevent", "submit($event)");
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
            var message = (json && json.message) || "Request gagal.";
            throw new Error(message);
          }
          return json;
        });
    });
  }

  function serializeForm(form) {
    var data = {};
    var elements = form.elements || [];
    for (var i = 0; i < elements.length; i++) {
      var field = elements[i];
      if (!field.name || field.disabled) {
        continue;
      }
      if (field.name === "option_page" || field.name === "action" || field.name.indexOf("_wp") === 0) {
        continue;
      }

      var parsed = parseInputName(field.name);
      if (!parsed.parts.length) {
        continue;
      }

      if (field.type === "checkbox") {
        if (parsed.isList) {
          if (typeof getByPath(data, parsed.parts) === "undefined") {
            setByPath(data, parsed.parts, [], false);
          }
          if (field.checked) {
            setByPath(data, parsed.parts, field.value, true);
          }
        } else {
          setByPath(data, parsed.parts, field.checked ? 1 : 0, false);
        }
        continue;
      }

      if (field.type === "radio") {
        if (field.checked) {
          setByPath(data, parsed.parts, field.value, false);
        }
        continue;
      }

      if (field.tagName === "SELECT" && field.multiple) {
        var selected = [];
        for (var x = 0; x < field.options.length; x++) {
          if (field.options[x].selected) {
            selected.push(field.options[x].value);
          }
        }
        setByPath(data, parsed.parts, selected, false);
        continue;
      }

      setByPath(data, parsed.parts, field.value, false);
    }

    return data;
  }

  function applyValuesToForm(form, settings) {
    var elements = form.elements || [];
    for (var i = 0; i < elements.length; i++) {
      var field = elements[i];
      if (!field.name) {
        continue;
      }
      var parsed = parseInputName(field.name);
      if (!parsed.parts.length) {
        continue;
      }
      var value = getByPath(settings, parsed.parts);

      if (field.type === "checkbox") {
        if (parsed.isList) {
          var list = Array.isArray(value) ? value.map(String) : [];
          field.checked = list.indexOf(String(field.value)) !== -1;
        } else {
          field.checked = toBool(value);
        }
        continue;
      }

      if (field.type === "radio") {
        field.checked = String(value) === String(field.value);
        continue;
      }

      if (field.tagName === "SELECT" && field.multiple) {
        var selected = Array.isArray(value) ? value.map(String) : [];
        for (var x = 0; x < field.options.length; x++) {
          field.options[x].selected = selected.indexOf(String(field.options[x].value)) !== -1;
        }
        continue;
      }

      if (typeof value !== "undefined" && value !== null) {
        field.value = value;
      }
    }
  }

  function parseInputName(name) {
    var isList = /\[\]$/.test(name);
    var normalized = name.replace(/\[\]$/, "");
    var parts = normalized.match(/[^\[\]]+/g) || [];
    return { parts: parts, isList: isList };
  }

  function setByPath(target, parts, value, append) {
    var cursor = target;
    for (var i = 0; i < parts.length; i++) {
      var part = parts[i];
      var isLast = i === parts.length - 1;
      var key = normalizeKey(cursor, part);

      if (isLast) {
        if (append) {
          if (!Array.isArray(cursor[key])) {
            cursor[key] = [];
          }
          cursor[key].push(value);
        } else {
          cursor[key] = value;
        }
      } else {
        var nextPart = parts[i + 1];
        var nextIsIndex = /^\d+$/.test(nextPart);
        if (typeof cursor[key] === "undefined" || cursor[key] === null) {
          cursor[key] = nextIsIndex ? [] : {};
        }
        cursor = cursor[key];
      }
    }
  }

  function getByPath(source, parts) {
    var cursor = source;
    for (var i = 0; i < parts.length; i++) {
      if (cursor === null || typeof cursor === "undefined") {
        return undefined;
      }
      var key = normalizeKey(cursor, parts[i]);
      cursor = cursor[key];
    }
    return cursor;
  }

  function normalizeKey(container, rawKey) {
    if (Array.isArray(container) && /^\d+$/.test(rawKey)) {
      return parseInt(rawKey, 10);
    }
    return rawKey;
  }

  function collectSubmitButtons(form) {
    var buttons = Array.prototype.slice.call(
      form.querySelectorAll('button[type="submit"],input[type="submit"]')
    );
    var formId = form.getAttribute("id");
    if (formId) {
      var external = document.querySelectorAll(
        'button[type="submit"][form="' + formId + '"],input[type="submit"][form="' + formId + '"]'
      );
      buttons = buttons.concat(Array.prototype.slice.call(external));
    }
    return buttons;
  }

  function ensureNoticeElement(form) {
    var notice = form.previousElementSibling;
    if (notice && notice.classList.contains("velocity-settings-notice")) {
      return notice;
    }

    notice = document.createElement("div");
    notice.className = "notice velocity-settings-notice";
    notice.style.display = "none";
    form.parentNode.insertBefore(notice, form);
    return notice;
  }

  function toBool(value) {
    if (value === true || value === 1 || value === "1") {
      return true;
    }
    if (typeof value === "string") {
      var lowered = value.toLowerCase();
      return lowered === "true" || lowered === "yes" || lowered === "on";
    }
    return false;
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
    div.textContent = text;
    return div.innerHTML;
  }

  function decorateFieldsWithXModel(form) {
    var elements = form.elements || [];
    for (var i = 0; i < elements.length; i++) {
      var field = elements[i];
      if (shouldSkipFieldForModel(field)) {
        continue;
      }
      if (field.hasAttribute("x-model")) {
        continue;
      }
      var expression = toModelExpression(field.name);
      if (!expression) {
        continue;
      }
      field.setAttribute("x-model", expression);
    }
  }

  function shouldSkipFieldForModel(field) {
    if (!field || !field.name || field.disabled) {
      return true;
    }
    if (
      field.name === "option_page" ||
      field.name === "action" ||
      field.name === "submit" ||
      field.name.indexOf("_wp") === 0 ||
      field.name === "velocity_news_import_nonce"
    ) {
      return true;
    }
    if (field.type === "submit" || field.type === "button" || field.type === "reset") {
      return true;
    }
    return false;
  }

  function toModelExpression(inputName) {
    var parsed = parseInputName(inputName);
    if (!parsed.parts.length) {
      return "";
    }
    var expr = "model";
    for (var i = 0; i < parsed.parts.length; i++) {
      var part = parsed.parts[i];
      if (/^\d+$/.test(part)) {
        expr += "[" + part + "]";
      } else {
        expr += "['" + String(part).replace(/\\/g, "\\\\").replace(/'/g, "\\'") + "']";
      }
    }
    return expr;
  }

  function deepClone(value) {
    if (typeof value === "undefined") {
      return {};
    }
    try {
      return JSON.parse(JSON.stringify(value));
    } catch (_e) {
      return value;
    }
  }

  function buildPayloadFromModelAndForm(model, form) {
    var modelPayload = deepClone(model || {});
    var formPayload = serializeForm(form);
    return deepMergePreferRight(modelPayload, formPayload);
  }

  function buildInitialModelFromForm(form) {
    var model = {};
    var elements = form.elements || [];
    for (var i = 0; i < elements.length; i++) {
      var field = elements[i];
      if (shouldSkipFieldForModel(field)) {
        continue;
      }
      var parsed = parseInputName(field.name);
      if (!parsed.parts.length) {
        continue;
      }

      if (parsed.isList) {
        if (typeof getByPath(model, parsed.parts) === "undefined") {
          setByPath(model, parsed.parts, [], false);
        }
        continue;
      }

      if (field.type === "checkbox") {
        setByPath(model, parsed.parts, field.checked ? 1 : 0, false);
        continue;
      }

      if (field.type === "radio") {
        var current = getByPath(model, parsed.parts);
        if (typeof current === "undefined") {
          setByPath(model, parsed.parts, "", false);
        }
        if (field.checked) {
          setByPath(model, parsed.parts, field.value, false);
        }
        continue;
      }

      if (field.tagName === "SELECT" && field.multiple) {
        var selected = [];
        for (var x = 0; x < field.options.length; x++) {
          if (field.options[x].selected) {
            selected.push(field.options[x].value);
          }
        }
        setByPath(model, parsed.parts, selected, false);
        continue;
      }

      setByPath(model, parsed.parts, field.value, false);
    }

    return model;
  }

  function deepMergePreferRight(left, right) {
    if (Array.isArray(right)) {
      return right.slice();
    }
    if (!isPlainObject(right)) {
      return typeof right === "undefined" ? left : right;
    }

    var out = isPlainObject(left) ? left : {};
    var keys = Object.keys(right);
    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      var rightValue = right[key];
      if (isPlainObject(rightValue) && isPlainObject(out[key])) {
        out[key] = deepMergePreferRight(out[key], rightValue);
      } else if (isPlainObject(rightValue)) {
        out[key] = deepMergePreferRight({}, rightValue);
      } else if (Array.isArray(rightValue)) {
        out[key] = rightValue.slice();
      } else {
        out[key] = rightValue;
      }
    }
    return out;
  }

  function isPlainObject(value) {
    return value && typeof value === "object" && !Array.isArray(value);
  }

  function syncGeneralDynamicMenu(settings) {
    var dynamicItems = [
      { option: "seo_velocity", href: "admin.php?page=velocity_seo_settings", label: "SEO" },
      { option: "floating_whatsapp", href: "admin.php?page=velocity_floating_whatsapp", label: "Floating Whatsapp" },
      { option: "news_generate", href: "admin.php?page=velocity_news_settings", label: "Import Artikel" },
      { option: "velocity_duitku", href: "admin.php?page=velocity_duitku_settings", label: "Duitku" },
      { option: "statistik_velocity", href: "admin.php?page=velocity_statistics", label: "Statistik Pengunjung" },
      { option: "velocity_optimasi", href: "admin.php?page=velocity_optimize_db", label: "Optimize Database" },
    ];

    var submenu = getVelocitySubmenuContainer();
    if (!submenu) {
      return;
    }

    for (var i = 0; i < dynamicItems.length; i++) {
      var item = dynamicItems[i];
      var enabled = toBool(settings[item.option]);
      if (enabled) {
        ensureSubmenuItem(submenu, item.href, item.label);
      } else {
        removeSubmenuItem(submenu, item.href);
      }
    }

    syncGalleryTopLevelMenu(toBool(settings.velocity_gallery));
  }

  function getVelocitySubmenuContainer() {
    return (
      document.querySelector("#toplevel_page_admin_velocity_addons ul.wp-submenu") ||
      document.querySelector("#toplevel_page_admin_velocity_addons ul.wp-submenu-wrap")
    );
  }

  function ensureSubmenuItem(container, href, label) {
    var existing = findSubmenuItem(container, href);
    if (existing) {
      var link = existing.querySelector("a");
      if (link) {
        link.textContent = label;
      }
      return;
    }

    var li = document.createElement("li");
    var a = document.createElement("a");
    a.setAttribute("href", href);
    a.textContent = label;
    li.appendChild(a);
    container.appendChild(li);
  }

  function removeSubmenuItem(container, href) {
    var existing = findSubmenuItem(container, href);
    if (existing) {
      existing.remove();
    }
  }

  function findSubmenuItem(container, hrefPart) {
    var links = container.querySelectorAll("a[href]");
    for (var i = 0; i < links.length; i++) {
      var href = links[i].getAttribute("href") || "";
      if (href === hrefPart || href.indexOf(hrefPart) !== -1) {
        return links[i].closest("li");
      }
    }
    return null;
  }

  function syncGalleryTopLevelMenu(enabled) {
    var existing = document.querySelector("#adminmenu #menu-posts-vdgallery");

    if (enabled) {
      if (existing) {
        existing.style.display = "";
        return;
      }
      createGalleryTopLevelMenu();
      return;
    }

    if (existing) {
      existing.remove();
    }
  }

  function createGalleryTopLevelMenu() {
    var adminMenu = document.querySelector("#adminmenu");
    if (!adminMenu) {
      return;
    }

    var li = document.createElement("li");
    li.id = "menu-posts-vdgallery";
    li.className = "wp-has-submenu menu-top menu-icon-vdgallery";
    li.innerHTML =
      '<a href="edit.php?post_type=vdgallery" class="wp-has-submenu menu-top">' +
      '<div class="wp-menu-arrow"><div></div></div>' +
      '<div class="wp-menu-image dashicons-before dashicons-images-alt2"><br></div>' +
      '<div class="wp-menu-name">VD Gallery</div>' +
      "</a>" +
      '<ul class="wp-submenu wp-submenu-wrap">' +
      '<li class="wp-first-item"><a href="edit.php?post_type=vdgallery" class="wp-first-item">VD Gallery</a></li>' +
      '<li><a href="post-new.php?post_type=vdgallery">Tambah Baru</a></li>' +
      "</ul>";

    var afterPages = document.querySelector("#adminmenu #menu-pages");
    if (afterPages && afterPages.parentNode === adminMenu) {
      if (afterPages.nextSibling) {
        adminMenu.insertBefore(li, afterPages.nextSibling);
      } else {
        adminMenu.appendChild(li);
      }
      return;
    }

    adminMenu.appendChild(li);
  }
})(window, document);

/**
 * VOYA — header-search.js (fixed)
 * Fix 1: URL double path (REST_BASE đã có voya/v1/ rồi)
 * Fix 2: capture:true phá vy-menu-toggle → đổi sang stopImmediatePropagation
 */

(function () {
  "use strict";

  var MIN_CHARS = 5;
  var DEBOUNCE_MS = 400;
  var MAX_RESULTS = 8;
  var searchTimer = null;
  var currentXHR = null;

  var desktopInput = null;
  var desktopResults = null;
  var expandBar = null;
  var expandInput = null;
  var expandResults = null;

  /* ── REST_BASE: vySearchConfig.restUrl = rest_url('voya/v1/')
     → đã là .../wp-json/voya/v1/
     → chỉ cần append 'search?q=...' thôi, KHÔNG append lại 'voya/v1/'
  ── */
  var REST_BASE = "/wp-json/voya/v1/";
  if (window.vySearchConfig && window.vySearchConfig.restUrl) {
    REST_BASE = window.vySearchConfig.restUrl;
  }
  // Đảm bảo luôn kết thúc bằng /
  REST_BASE = REST_BASE.replace(/\/?$/, "/");

  /* ════════════════════════════
     INJECT expand bar (mobile)
  ════════════════════════════ */
  function injectExpandBar() {
    if (document.getElementById("vy-search-expand")) {
      expandBar = document.getElementById("vy-search-expand");
      expandInput = document.getElementById("vy-search-expand-input");
      expandResults = document.getElementById("vy-search-expand-results");
      return;
    }

    var bar = document.createElement("div");
    bar.id = "vy-search-expand";
    bar.className = "vy-search-expand";
    bar.setAttribute("aria-hidden", "true");
    bar.innerHTML =
      '<div class="vy-search-expand__inner">' +
      '<div class="vy-search-expand__form">' +
      '<i class="fa-solid fa-magnifying-glass vy-search-expand__icon"></i>' +
      '<input type="search" id="vy-search-expand-input"' +
      ' class="vy-search-expand__input"' +
      ' placeholder="Tìm game, app, bài viết..."' +
      ' autocomplete="off" spellcheck="false">' +
      '<button type="button" class="vy-search-expand__close"' +
      ' id="vy-search-expand-close" aria-label="Đóng">' +
      '<i class="fa-solid fa-xmark"></i>' +
      "</button>" +
      "</div>" +
      '<div class="vy-search-expand__results" id="vy-search-expand-results"></div>' +
      "</div>";

    var header = document.querySelector(".site-header, header, #header");
    if (header && header.parentNode) {
      header.parentNode.insertBefore(bar, header.nextSibling);
    } else {
      document.body.insertBefore(bar, document.body.firstChild);
    }

    expandBar = bar;
    expandInput = document.getElementById("vy-search-expand-input");
    expandResults = document.getElementById("vy-search-expand-results");
    var closeBtn = document.getElementById("vy-search-expand-close");

    expandInput.addEventListener("input", function () {
      handleSearch(expandInput.value.trim(), expandResults);
    });
    expandInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        redirectSearch(expandInput.value.trim());
      }
      if (e.key === "Escape") {
        closeExpandBar();
      }
    });
    if (closeBtn) {
      closeBtn.addEventListener("click", closeExpandBar);
    }
  }

  function openExpandBar() {
    if (!expandBar) return;
    expandBar.classList.add("is-open");
    expandBar.setAttribute("aria-hidden", "false");
    document.body.classList.add("search-expand-open");
    setTimeout(function () {
      if (expandInput) expandInput.focus();
    }, 80);
  }

  function closeExpandBar() {
    if (!expandBar) return;
    expandBar.classList.remove("is-open");
    expandBar.setAttribute("aria-hidden", "true");
    document.body.classList.remove("search-expand-open");
    if (expandInput) expandInput.value = "";
    if (expandResults) {
      expandResults.innerHTML = "";
      expandResults.style.display = "none";
    }
  }

  /* ════════════════════════════
     DESKTOP dropdown
  ════════════════════════════ */
  function initDesktopSearch() {
    desktopInput = document.querySelector(
      '.header-search-form input[type="search"],' +
        '.header-search-form input[type="text"],' +
        "#searchInput, .ux-search-field",
    );
    if (!desktopInput) return;

    desktopResults = document.createElement("div");
    desktopResults.className = "vy-search-dropdown";
    desktopResults.id = "vy-search-dropdown";

    var wrapper = desktopInput.closest(
      ".header-search-form, .search-form, form",
    );
    if (wrapper) {
      wrapper.style.position = "relative";
      wrapper.appendChild(desktopResults);
    } else {
      desktopInput.parentNode.style.position = "relative";
      desktopInput.parentNode.appendChild(desktopResults);
    }

    desktopInput.addEventListener("input", function () {
      handleSearch(desktopInput.value.trim(), desktopResults);
    });
    desktopInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        redirectSearch(desktopInput.value.trim());
      }
      if (e.key === "Escape") {
        hideDropdown(desktopResults);
        desktopInput.blur();
      }
    });
    document.addEventListener("click", function (e) {
      if (
        !e.target.closest("#vy-search-dropdown") &&
        !e.target.closest(".header-search-form, .search-form")
      ) {
        hideDropdown(desktopResults);
      }
    });
  }

  /* ════════════════════════════
     MOBILE search icon
     - CHỈ target #vy-search-toggle-mobile
     - KHÔNG dùng capture:true → vy-menu-toggle vẫn hoạt động bình thường
     - Dùng stopImmediatePropagation để override handler cũ trên cùng element
  ════════════════════════════ */
  function initMobileSearch() {
    var searchToggle = document.getElementById("vy-search-toggle-mobile");
    if (!searchToggle) return;

    searchToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopImmediatePropagation(); // chặn handler cũ trên chính element này

      if (expandBar && expandBar.classList.contains("is-open")) {
        closeExpandBar();
      } else {
        // Đóng mobile menu nếu đang mở (nhưng KHÔNG phá click của vy-menu-toggle)
        document.body.classList.remove("mobile-menu-open");
        var menuToggle = document.getElementById("vy-menu-toggle");
        if (menuToggle) menuToggle.setAttribute("aria-expanded", "false");

        openExpandBar();
      }
    });
    // vy-menu-toggle: KHÔNG đụng gì cả, giữ nguyên 100%
  }

  /* ════════════════════════════
     SEARCH LOGIC
  ════════════════════════════ */
  function handleSearch(query, resultsEl) {
    clearTimeout(searchTimer);
    if (!query) {
      hideDropdown(resultsEl);
      return;
    }

    if (query.length < MIN_CHARS) {
      showInDropdown(resultsEl, hintHTML(MIN_CHARS - query.length));
      return;
    }

    showInDropdown(resultsEl, loadingHTML());
    searchTimer = setTimeout(function () {
      doSearch(query, resultsEl);
    }, DEBOUNCE_MS);
  }

  function doSearch(query, resultsEl) {
    if (currentXHR) {
      try {
        currentXHR.abort();
      } catch (e) {}
    }

    // FIX: REST_BASE đã là .../voya/v1/ → chỉ append 'search?q=...'
    var url = REST_BASE + "search?q=" + encodeURIComponent(query);

    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    var nonce = (window.vySearchConfig && window.vySearchConfig.nonce) || "";
    if (nonce) xhr.setRequestHeader("X-WP-Nonce", nonce);

    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          renderResults(resultsEl, JSON.parse(xhr.responseText), query);
        } catch (e) {
          showInDropdown(resultsEl, errorHTML());
        }
      } else {
        showInDropdown(resultsEl, errorHTML());
      }
    };
    xhr.onerror = function () {
      showInDropdown(resultsEl, errorHTML());
    };
    xhr.send();
    currentXHR = xhr;
  }

  /* ════════════════════════════
     RENDER
  ════════════════════════════ */
  function renderResults(el, data, query) {
    var products = (data.products || []).slice(0, MAX_RESULTS);
    var posts = (data.posts || []).slice(0, MAX_RESULTS);

    if (!products.length && !posts.length) {
      showInDropdown(el, noResultHTML(query));
      return;
    }

    var html = '<div class="vy-search-dropdown__inner">';

    if (products.length) {
      html += '<div class="vy-search-dropdown__group">';
      html +=
        '<div class="vy-search-dropdown__group-label"><i class="fa-solid fa-gamepad"></i> Game &amp; App</div>';
      products.forEach(function (item) {
        html += itemHTML(item, "product");
      });
      html += "</div>";
    }
    if (posts.length) {
      html += '<div class="vy-search-dropdown__group">';
      html +=
        '<div class="vy-search-dropdown__group-label"><i class="fa-regular fa-newspaper"></i> Bài viết</div>';
      posts.forEach(function (item) {
        html += itemHTML(item, "post");
      });
      html += "</div>";
    }

    var sUrl =
      window.vySearchConfig && window.vySearchConfig.searchUrl
        ? window.vySearchConfig.searchUrl + "?s=" + encodeURIComponent(query)
        : "/?s=" + encodeURIComponent(query);
    html +=
      '<a href="' +
      sUrl +
      '" class="vy-search-dropdown__view-all">' +
      'Xem tất cả kết quả "<strong>' +
      escHtml(query) +
      '</strong>"' +
      ' <i class="fa-solid fa-arrow-right"></i></a>';
    html += "</div>";

    showInDropdown(el, html);
  }

  function itemHTML(item, type) {
    var thumb = item.thumbnail || "";
    var tHtml = thumb
      ? '<img src="' +
        escAttr(thumb) +
        '" alt="' +
        escAttr(item.title) +
        '" loading="lazy">'
      : '<div class="vy-search-item__thumb-placeholder"><i class="fa-solid fa-' +
        (type === "product" ? "gamepad" : "newspaper") +
        '"></i></div>';
    var cat = item.category || "";
    return (
      '<a href="' +
      escAttr(item.url) +
      '" class="vy-search-item">' +
      '<div class="vy-search-item__thumb">' +
      tHtml +
      "</div>" +
      '<div class="vy-search-item__info">' +
      '<span class="vy-search-item__title">' +
      escHtml(item.title) +
      "</span>" +
      (cat
        ? '<span class="vy-search-item__cat">' + escHtml(cat) + "</span>"
        : "") +
      "</div>" +
      "</a>"
    );
  }

  /* ── Templates ── */
  function hintHTML(rem) {
    return (
      '<div class="vy-search-hint">' +
      '<i class="fa-solid fa-keyboard"></i>' +
      "<p>Nhập thêm <strong>" +
      rem +
      "</strong> ký tự nữa</p>" +
      "<small>Tối thiểu " +
      MIN_CHARS +
      " ký tự</small>" +
      "</div>"
    );
  }
  function loadingHTML() {
    return '<div class="vy-search-loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Đang tìm kiếm...</div>';
  }
  function noResultHTML(q) {
    return (
      '<div class="vy-search-noresult"><i class="fa-regular fa-face-sad-tear"></i><p>Không tìm thấy "<strong>' +
      escHtml(q) +
      '</strong>"</p></div>'
    );
  }
  function errorHTML() {
    return '<div class="vy-search-error"><i class="fa-solid fa-triangle-exclamation"></i><p>Có lỗi xảy ra, vui lòng thử lại</p></div>';
  }

  /* ── Utils ── */
  function showInDropdown(el, html) {
    if (!el) return;
    el.innerHTML = html;
    el.style.display = "block";
  }
  function hideDropdown(el) {
    if (!el) return;
    el.innerHTML = "";
    el.style.display = "none";
  }
  function redirectSearch(q) {
    if (!q) return;
    window.location.href =
      window.vySearchConfig && window.vySearchConfig.searchUrl
        ? window.vySearchConfig.searchUrl + "?s=" + encodeURIComponent(q)
        : "/?s=" + encodeURIComponent(q);
  }
  function escHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
  function escAttr(s) {
    return String(s).replace(/"/g, "&quot;");
  }

  /* ════════════════════════════
     INIT
  ════════════════════════════ */
  function init() {
    injectExpandBar();
    initDesktopSearch();
    initMobileSearch();

    // Đóng expand khi Escape hoặc click ngoài
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeExpandBar();
    });
    document.addEventListener("click", function (e) {
      if (
        expandBar &&
        expandBar.classList.contains("is-open") &&
        !e.target.closest("#vy-search-expand") &&
        !e.target.closest("#vy-search-toggle-mobile")
      ) {
        closeExpandBar();
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

(function () {
  "use strict";

  /* ═══════════════════════════════════════════════════════════
     1. TAB SWITCHER  [data-vy-tabs]
        Chạy cho cả sidebar tabs trên trang single
  ═══════════════════════════════════════════════════════════ */
  document.addEventListener("DOMContentLoaded", function () {
    var tabGroups = document.querySelectorAll("[data-vy-tabs]");
    if (!tabGroups.length) return;

    tabGroups.forEach(function (group) {
      var tabBtns = Array.from(group.querySelectorAll("[data-tab-target]"));
      var panels = Array.from(group.querySelectorAll("[data-tab-panel]"));

      function setActive(target) {
        tabBtns.forEach(function (btn) {
          var active = btn.getAttribute("data-tab-target") === target;
          var li = btn.closest(".tab");
          if (li) li.classList.toggle("is-active", active);
          btn.setAttribute("aria-selected", active ? "true" : "false");
        });
        panels.forEach(function (panel) {
          panel.classList.toggle(
            "is-active",
            panel.getAttribute("data-tab-panel") === target,
          );
        });
      }

      tabBtns.forEach(function (btn) {
        btn.addEventListener("click", function () {
          setActive(btn.getAttribute("data-tab-target"));
        });
      });

      // Keyboard arrow navigation
      tabBtns.forEach(function (btn, idx) {
        btn.addEventListener("keydown", function (e) {
          var next = null;
          if (e.key === "ArrowRight" || e.key === "ArrowDown")
            next = tabBtns[(idx + 1) % tabBtns.length];
          if (e.key === "ArrowLeft" || e.key === "ArrowUp")
            next = tabBtns[(idx - 1 + tabBtns.length) % tabBtns.length];
          if (next) {
            e.preventDefault();
            next.focus();
            setActive(next.getAttribute("data-tab-target"));
          }
        });
      });
    });
  });

  /* ═══════════════════════════════════════════════════════════
     2. APK SLIDER  .vy-apk-slider
        Layout: columns × 2 rows
        Desktop: 3 cols visible | Tablet: 2 cols | Mobile: 1 col
        Gap between columns: 10px (matches CSS)
  ═══════════════════════════════════════════════════════════ */
  document.addEventListener("DOMContentLoaded", function () {
    var sliders = document.querySelectorAll(".vy-apk-slider");
    if (!sliders.length) return;

    sliders.forEach(function (slider) {
      var viewport = slider.querySelector(".vy-apk-slider__viewport");
      var track = slider.querySelector(".vy-apk-slider__track");
      var cols = Array.from(
        track ? track.querySelectorAll(".vy-apk-slider__col") : [],
      );
      var btnPrev = slider.querySelector(".vy-apk-slider__btn--prev");
      var btnNext = slider.querySelector(".vy-apk-slider__btn--next");

      var GAP = 10; /* matches CSS gap: 10px */
      var total = cols.length;
      var current = 0;

      if (!viewport || !track || total === 0) return;

      /* ── Visible columns by breakpoint ── */
      function getVisible() {
        var w = viewport.offsetWidth;
        if (w <= 480) return 1;
        if (w <= 860) return 2;
        return 3;
      }

      /* ── Column width based on viewport ── */
      function colWidth() {
        var vis = getVisible();
        return (viewport.offsetWidth - GAP * (vis - 1)) / vis;
      }

      /* ── Apply column widths ── */
      function setWidths() {
        var w = colWidth();
        cols.forEach(function (col) {
          col.style.width = w + "px";
        });
      }

      /* ── Slide to index ── */
      function moveTo(idx) {
        var vis = getVisible();
        var maxIdx = Math.max(0, total - vis);
        current = Math.max(0, Math.min(idx, maxIdx));

        var offset = current * (colWidth() + GAP);
        track.style.transform = "translateX(-" + offset + "px)";

        if (btnPrev) btnPrev.disabled = current === 0;
        if (btnNext) btnNext.disabled = current >= maxIdx;
      }

      /* ── Arrows ── */
      if (btnPrev)
        btnPrev.addEventListener("click", function () {
          moveTo(current - 1);
        });
      if (btnNext)
        btnNext.addEventListener("click", function () {
          moveTo(current + 1);
        });

      /* ── Touch swipe ── */
      var tx = 0,
        ty = 0,
        swiping = false;

      viewport.addEventListener(
        "touchstart",
        function (e) {
          tx = e.touches[0].clientX;
          ty = e.touches[0].clientY;
          swiping = true;
        },
        { passive: true },
      );

      viewport.addEventListener(
        "touchmove",
        function (e) {
          if (!swiping) return;
          if (
            Math.abs(e.touches[0].clientY - ty) >
            Math.abs(e.touches[0].clientX - tx)
          ) {
            swiping = false; /* vertical scroll — cancel */
          }
        },
        { passive: true },
      );

      viewport.addEventListener(
        "touchend",
        function (e) {
          if (!swiping) return;
          swiping = false;
          var dx = e.changedTouches[0].clientX - tx;
          if (Math.abs(dx) > 48)
            dx < 0 ? moveTo(current + 1) : moveTo(current - 1);
        },
        { passive: true },
      );

      /* ── Resize: recalculate and re-clamp ── */
      var resizeTimer;
      window.addEventListener("resize", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
          setWidths();
          moveTo(current);
        }, 120);
      });

      /* ── Init ── */
      setWidths();
      moveTo(0);
    });
  });
})();

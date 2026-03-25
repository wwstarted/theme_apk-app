(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    var tabGroups = document.querySelectorAll("[data-vy-tabs]");
    if (!tabGroups.length) return;

    tabGroups.forEach(function (group) {
      var tabButtons = group.querySelectorAll("[data-tab-target]");
      var panels = group.querySelectorAll("[data-tab-panel]");

      function setActive(target) {
        tabButtons.forEach(function (btn) {
          var isActive = btn.getAttribute("data-tab-target") === target;
          var li = btn.closest(".tab");
          if (li) li.classList.toggle("is-active", isActive);
          btn.setAttribute("aria-selected", isActive ? "true" : "false");
        });

        panels.forEach(function (panel) {
          var isActive = panel.getAttribute("data-tab-panel") === target;
          panel.classList.toggle("is-active", isActive);
        });
      }

      tabButtons.forEach(function (btn) {
        btn.addEventListener("click", function () {
          setActive(btn.getAttribute("data-tab-target"));
        });
      });

      /* Keyboard: arrow keys to switch tabs */
      var btnsArr = Array.from(tabButtons);
      btnsArr.forEach(function (btn, idx) {
        btn.addEventListener("keydown", function (e) {
          var next = null;
          if (e.key === "ArrowRight" || e.key === "ArrowDown") {
            next = btnsArr[(idx + 1) % btnsArr.length];
          } else if (e.key === "ArrowLeft" || e.key === "ArrowUp") {
            next = btnsArr[(idx - 1 + btnsArr.length) % btnsArr.length];
          }
          if (next) {
            e.preventDefault();
            next.focus();
            setActive(next.getAttribute("data-tab-target"));
          }
        });
      });
    });
  });
})();

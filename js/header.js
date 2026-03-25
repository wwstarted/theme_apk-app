(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    const menuToggle = document.getElementById("vy-menu-toggle");
    const overlay = document.getElementById("vy-overlay");
    const mobileMenu = document.getElementById("main-menu");
    const searchToggleMobile = document.getElementById(
      "vy-search-toggle-mobile",
    );

    // 1. Mở/Đóng Mobile Menu (Sidebar - click hamburger icon)
    if (menuToggle && overlay && mobileMenu) {
      menuToggle.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        body.classList.toggle("mobile-menu-open");
        menuToggle.setAttribute(
          "aria-expanded",
          body.classList.contains("mobile-menu-open") ? "true" : "false",
        );
      });

      // Đóng menu khi click overlay
      overlay.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        body.classList.remove("mobile-menu-open");
        menuToggle.setAttribute("aria-expanded", "false");
      });

      // Đóng menu khi click vào link trong menu
      const menuLinks = mobileMenu.querySelectorAll("a");
      menuLinks.forEach(function (link) {
        link.addEventListener("click", function (e) {
          // Không đóng menu nếu click vào search form
          if (!link.closest(".header-search-form")) {
            body.classList.remove("mobile-menu-open");
            menuToggle.setAttribute("aria-expanded", "false");
          }
        });
      });
    }

    // 2. Mobile Search Icon (toggle search form visibility)
    if (searchToggleMobile) {
      searchToggleMobile.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        // Open mobile menu if not already open
        if (!body.classList.contains("mobile-menu-open")) {
          body.classList.add("mobile-menu-open");
          if (menuToggle) {
            menuToggle.setAttribute("aria-expanded", "true");
          }
        }
        // Focus search form in mobile menu
        setTimeout(function () {
          const mobileSearch = mobileMenu
            ? mobileMenu.querySelector(".header-search-form input")
            : null;
          if (mobileSearch) {
            mobileSearch.focus();
          }
        }, 100);
      });
    }

    // 3. Hỗ trợ Escape key để đóng menu
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && body.classList.contains("mobile-menu-open")) {
        body.classList.remove("mobile-menu-open");
        if (menuToggle) {
          menuToggle.setAttribute("aria-expanded", "false");
        }
      }
    });

    // 4. Active menu item dựa trên current page URL
    const currentUrl = window.location.href;
    const navLinks = document.querySelectorAll(".nav a, .nav-sidebar a");
    navLinks.forEach(function (link) {
      if (link.href === currentUrl) {
        link.classList.add("active");
        link.setAttribute("aria-current", "page");
      }
    });
  });
})();

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    /* ═══════════════════════════════════════════════════════
       1. MOBILE SIDEBAR TOGGLE
    ═══════════════════════════════════════════════════════ */
    var sidebar = document.querySelector(".vy-blog-sidebar");
    var sidebarInner = document.getElementById("vy-blog-sidebar-inner");

    if (sidebar && sidebarInner && window.innerWidth <= 768) {
      /* Create toggle button */
      var btn = document.createElement("button");
      btn.className = "vy-blog-sidebar-toggle";
      btn.type = "button";
      btn.setAttribute("aria-expanded", "false");
      btn.setAttribute("aria-controls", "vy-blog-sidebar-inner");
      btn.innerHTML =
        '<i class="fa-solid fa-filter"></i>' +
        "<span>Lọc bài viết</span>" +
        '<i class="fa-solid fa-chevron-down vy-sidebar-arrow"></i>';

      sidebar.insertBefore(btn, sidebarInner);

      btn.addEventListener("click", function () {
        var isOpen = sidebarInner.classList.toggle("is-open");
        btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
        btn.querySelector(".vy-sidebar-arrow").style.transform = isOpen
          ? "rotate(180deg)"
          : "rotate(0deg)";
      });
    }

    /* ═══════════════════════════════════════════════════════
       2. FILTER LINK ACTIVE STATE (instant UI feedback)
    ═══════════════════════════════════════════════════════ */
    var filterLinks = document.querySelectorAll(".vy-blog-cat-link");

    filterLinks.forEach(function (link) {
      link.addEventListener("click", function () {
        /* Remove active from siblings in same list */
        var parentList = link.closest("ul");
        if (parentList) {
          parentList
            .querySelectorAll(".vy-blog-cat-link")
            .forEach(function (l) {
              l.classList.remove("is-active");
            });
        }
        link.classList.add("is-active");
      });
    });

    /* Tags */
    var tagLinks = document.querySelectorAll(".vy-blog-tag");
    tagLinks.forEach(function (tag) {
      tag.addEventListener("click", function () {
        tagLinks.forEach(function (t) {
          t.classList.remove("is-active");
        });
        tag.classList.add("is-active");
      });
    });

    /* ═══════════════════════════════════════════════════════
       3. CARD ENTRANCE ANIMATION (IntersectionObserver)
    ═══════════════════════════════════════════════════════ */
    var cards = document.querySelectorAll(".vy-blog-card");

    if (!cards.length) return;

    /* Fallback — no IO support */
    if (!("IntersectionObserver" in window)) {
      cards.forEach(function (c) {
        c.style.opacity = "1";
        c.style.transform = "none";
      });
      return;
    }

    cards.forEach(function (card, i) {
      card.style.opacity = "0";
      card.style.transform = "translateY(16px)";
      card.style.transition =
        "opacity 0.42s ease " +
        (i % 2) * 0.07 +
        "s, " +
        "transform 0.42s ease " +
        (i % 2) * 0.07 +
        "s";
    });

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.style.opacity = "1";
            entry.target.style.transform = "translateY(0)";
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.05, rootMargin: "0px 0px -12px 0px" },
    );

    cards.forEach(function (card) {
      observer.observe(card);
    });
  }); /* end DOMContentLoaded */
})();

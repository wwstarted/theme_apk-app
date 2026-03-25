(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    /* ═══════════════════════════════════════════════════════
       1. READING PROGRESS BAR
    ═══════════════════════════════════════════════════════ */
    var bar = document.createElement("div");
    bar.className = "vy-reading-progress";
    bar.setAttribute("aria-hidden", "true");
    document.body.appendChild(bar);

    var article = document.getElementById("vy-article");
    var ticking = false;

    function updateProgress() {
      ticking = false;
      if (!article) return;
      var top = article.getBoundingClientRect().top + window.scrollY;
      var height = article.offsetHeight;
      var pct = Math.min(Math.max((window.scrollY - top) / height, 0), 1);
      bar.style.width = pct * 100 + "%";
    }

    window.addEventListener(
      "scroll",
      function () {
        if (!ticking) {
          requestAnimationFrame(updateProgress);
          ticking = true;
        }
      },
      { passive: true },
    );

    updateProgress();

    /* ═══════════════════════════════════════════════════════
       2. IMAGE LIGHTBOX
    ═══════════════════════════════════════════════════════ */
    var contentImgs = document.querySelectorAll(".vy-single-blog__content img");

    if (contentImgs.length) {
      var lb = document.createElement("div");
      lb.className = "vy-lightbox";
      lb.setAttribute("role", "dialog");
      lb.setAttribute("aria-modal", "true");
      lb.innerHTML =
        '<button class="vy-lightbox__close" aria-label="Đóng">' +
        '<i class="fa-solid fa-xmark"></i></button>' +
        '<img class="vy-lightbox__img" src="" alt="">';
      document.body.appendChild(lb);

      var lbImg = lb.querySelector(".vy-lightbox__img");
      var lbClose = lb.querySelector(".vy-lightbox__close");

      function openLb(src, alt) {
        lbImg.src = src;
        lbImg.alt = alt || "";
        lb.classList.add("is-open");
        document.body.style.overflow = "hidden";
        lbClose.focus();
      }
      function closeLb() {
        lb.classList.remove("is-open");
        document.body.style.overflow = "";
        setTimeout(function () {
          lbImg.src = "";
        }, 280);
      }

      contentImgs.forEach(function (img) {
        img.style.cursor = "zoom-in";
        img.addEventListener("click", function () {
          openLb(img.src, img.alt);
        });
      });

      lbClose.addEventListener("click", closeLb);
      lb.addEventListener("click", function (e) {
        if (e.target === lb) closeLb();
      });
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && lb.classList.contains("is-open")) closeLb();
      });
    }

    /* ═══════════════════════════════════════════════════════
       3. SHARE BUTTONS — open popup window
    ═══════════════════════════════════════════════════════ */
    document.querySelectorAll(".vy-share-link").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        var href = btn.getAttribute("href");
        if (href && href.startsWith("http")) {
          e.preventDefault();
          window.open(
            href,
            "_blank",
            "width=640,height=480,scrollbars=yes,noopener,noreferrer",
          );
        }
      });
    });

    /* ═══════════════════════════════════════════════════════
       4. COMMENT FORM PLACEHOLDERS
    ═══════════════════════════════════════════════════════ */
    var placeholders = {
      "#author": "Tên *",
      "#email": "Email *",
      "#url": "Website",
      "#comment": "Bình luận *",
    };
    Object.keys(placeholders).forEach(function (sel) {
      var el = document.querySelector(sel);
      if (el) el.setAttribute("placeholder", placeholders[sel]);
    });

    /* ═══════════════════════════════════════════════════════
       5. SCROLL FADE-IN (IntersectionObserver)
    ═══════════════════════════════════════════════════════ */
    if ("IntersectionObserver" in window) {
      var targets = document.querySelectorAll(
        ".vy-single-blog__content p, .vy-single-blog__content blockquote, " +
          ".vy-single-blog__content h2, .vy-single-blog__content h3, " +
          ".vy-single-blog__content img, " +
          ".vy-single-blog__author, .vy-single-blog__nav, " +
          ".vy-blog-sidebar-item",
      );

      var fadeObs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add("vy-fade-in");
              fadeObs.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.07, rootMargin: "0px 0px -20px 0px" },
      );

      targets.forEach(function (el) {
        el.classList.add("vy-will-animate");
        fadeObs.observe(el);
      });
    }

    /* ═══════════════════════════════════════════════════════
       6. TABLE OF CONTENTS — build + inject + highlight
    ═══════════════════════════════════════════════════════ */
    var tocEl = document.getElementById("vy-toc");
    var tocList = tocEl ? tocEl.querySelector(".vy-toc__list") : null;
    var tocToggle = tocEl ? tocEl.querySelector(".vy-toc__toggle") : null;
    var tocHead = tocEl ? tocEl.querySelector(".vy-toc__head") : null;
    var entry = document.querySelector(".vy-single-blog__content");

    if (tocEl && tocList && entry) {
      var headings = Array.from(entry.querySelectorAll("h2, h3")).filter(
        function (h) {
          return h.textContent.trim().length > 0;
        },
      );

      if (headings.length < 2) {
        tocEl.remove();
      } else {
        /* Build list */
        headings.forEach(function (h, i) {
          if (!h.id) {
            var slug = h.textContent
              .trim()
              .toLowerCase()
              .replace(/[^\w\s-]/g, "")
              .replace(/\s+/g, "-")
              .substring(0, 60);
            h.id = "toc-" + i + "-" + slug;
          }

          var li = document.createElement("li");
          var a = document.createElement("a");
          if (h.tagName === "H3") li.classList.add("vy-toc-h3");

          a.href = "#" + h.id;
          a.textContent = h.textContent.trim();

          a.addEventListener("click", function (e) {
            e.preventDefault();
            var target = document.getElementById(h.id);
            if (!target) return;
            var headerH = parseInt(
              getComputedStyle(document.documentElement).getPropertyValue(
                "--header-height-desktop",
              ) || "80",
              10,
            );
            window.scrollTo({
              top:
                target.getBoundingClientRect().top +
                window.scrollY -
                headerH -
                20,
              behavior: "smooth",
            });
          });

          li.appendChild(a);
          tocList.appendChild(li);
        });

        /* Inject before excerpt or beginning of content */
        var excerpt = document.querySelector(".vy-single-blog__excerpt");
        if (excerpt) {
          excerpt.parentNode.insertBefore(tocEl, excerpt.nextSibling);
        } else {
          entry.insertBefore(tocEl, entry.firstChild);
        }

        tocEl.removeAttribute("hidden");
        tocEl.style.display = "";

        /* Toggle */
        function toggleToc() {
          var collapsed = tocEl.classList.toggle("is-collapsed");
          if (tocToggle)
            tocToggle.setAttribute(
              "aria-label",
              collapsed ? "Mở rộng mục lục" : "Thu gọn mục lục",
            );
          if (tocHead)
            tocHead.setAttribute("aria-expanded", collapsed ? "false" : "true");
        }

        if (tocHead) {
          tocHead.addEventListener("click", toggleToc);
          tocHead.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              toggleToc();
            }
          });
        }

        /* Active on scroll */
        var tocLinks = Array.from(tocList.querySelectorAll("a"));

        function updateActiveToc() {
          var headerH =
            parseInt(
              getComputedStyle(document.documentElement).getPropertyValue(
                "--header-height-desktop",
              ) || "80",
              10,
            ) + 20;
          var current = "";
          headings.forEach(function (h) {
            if (window.scrollY >= h.offsetTop - headerH - 8) current = h.id;
          });
          tocLinks.forEach(function (link) {
            link
              .closest("li")
              .classList.toggle(
                "is-active",
                link.getAttribute("href") === "#" + current,
              );
          });
        }

        window.addEventListener("scroll", updateActiveToc, { passive: true });
        updateActiveToc();

        /* Auto-collapse on mobile */
        if (window.innerWidth <= 767) {
          tocEl.classList.add("is-collapsed");
          if (tocHead) tocHead.setAttribute("aria-expanded", "false");
        }
      }
    }

    /* ═══════════════════════════════════════════════════════
       7. SIDEBAR STICKY — recalc top offset
    ═══════════════════════════════════════════════════════ */
    var sidebarSticky = document.querySelector(
      ".vy-single-blog__sidebar-sticky",
    );

    function setSidebarTop() {
      if (!sidebarSticky) return;
      if (window.innerWidth > 991) {
        var headerH = parseInt(
          getComputedStyle(document.documentElement).getPropertyValue(
            "--header-height-desktop",
          ) || "80",
          10,
        );
        sidebarSticky.style.top = headerH + 16 + "px";
      } else {
        sidebarSticky.style.top = "";
      }
    }

    setSidebarTop();

    var resizeTimer;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(setSidebarTop, 120);
    });
  }); /* end DOMContentLoaded */
})();

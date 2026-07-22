/* =====================================================================
   KUPE DEE MINING — Interactions
   Theme toggle (persisted), mobile nav, dropdowns, scroll-to-top,
   header shadow on scroll, reveal-on-scroll, lazy video embeds.
   ===================================================================== */
(function () {
  "use strict";

  /* ---------- Theme toggle with persistence ---------- */
  var THEME_KEY = "kupedee-theme";
  var root = document.documentElement;

  function applyTheme(theme) {
    if (theme === "light") {
      root.setAttribute("data-theme", "light");
    } else {
      root.removeAttribute("data-theme");
    }
  }

  function initTheme() {
    var stored = null;
    try { stored = localStorage.getItem(THEME_KEY); } catch (e) {}
    if (!stored) {
      var prefersLight = window.matchMedia &&
        window.matchMedia("(prefers-color-scheme: light)").matches;
      stored = prefersLight ? "light" : "dark";
    }
    applyTheme(stored);
  }

  function toggleTheme() {
    var current = root.getAttribute("data-theme") === "light" ? "light" : "dark";
    var next = current === "light" ? "dark" : "light";
    applyTheme(next);
    try { localStorage.setItem(THEME_KEY, next); } catch (e) {}
  }

  // Apply as early as possible
  initTheme();

  document.addEventListener("DOMContentLoaded", function () {
    var toggles = document.querySelectorAll("[data-theme-toggle]");
    toggles.forEach(function (btn) {
      btn.addEventListener("click", toggleTheme);
    });

    /* ---------- Mobile nav ---------- */
    var navToggle = document.querySelector(".nav-toggle");
    var navMenu = document.querySelector(".nav-menu");
    if (navToggle && navMenu) {
      navToggle.addEventListener("click", function () {
        navMenu.classList.toggle("open");
        navToggle.classList.toggle("active");
        document.body.style.overflow = navMenu.classList.contains("open") ? "hidden" : "";
      });
    }

    /* ---------- Mobile dropdown expand ---------- */
    document.querySelectorAll(".nav-menu .has-dropdown > a, .nav-menu .has-submenu > a")
      .forEach(function (link) {
        link.addEventListener("click", function (e) {
          if (window.innerWidth <= 860) {
            var sub = link.parentElement.querySelector(".dropdown-menu, .submenu");
            if (sub) {
              e.preventDefault();
              sub.classList.toggle("open");
            }
          }
        });
      });

    /* ---------- Header shadow on scroll ---------- */
    var header = document.querySelector(".site-header");
    var scrollTopBtn = document.querySelector(".scroll-top");

    function onScroll() {
      var y = window.scrollY || window.pageYOffset;
      if (header) header.classList.toggle("scrolled", y > 10);
      if (scrollTopBtn) scrollTopBtn.classList.toggle("show", y > 400);
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();

    /* ---------- Scroll to top ---------- */
    if (scrollTopBtn) {
      scrollTopBtn.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    }

    /* ---------- Reveal on scroll ---------- */
    var revealEls = document.querySelectorAll(".reveal");
    if ("IntersectionObserver" in window && revealEls.length) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });
      revealEls.forEach(function (el) { io.observe(el); });
    } else {
      revealEls.forEach(function (el) { el.classList.add("visible"); });
    }

    /* ---------- Lazy video embeds (click poster -> load iframe) ---------- */
    document.querySelectorAll(".video-poster").forEach(function (poster) {
      poster.addEventListener("click", function () {
        var wrap = poster.closest(".video-wrap");
        var src = poster.getAttribute("data-embed");
        if (wrap && src) {
          var iframe = document.createElement("iframe");
          iframe.setAttribute("src", src + (src.indexOf("?") > -1 ? "&" : "?") + "autoplay=1");
          iframe.setAttribute("allow", "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture");
          iframe.setAttribute("allowfullscreen", "");
          iframe.setAttribute("title", "KUPE DEE MINING video");
          wrap.innerHTML = "";
          wrap.appendChild(iframe);
        }
      });
    });

    /* ---------- Footer year ---------- */
    var yearEl = document.querySelector("[data-year]");
    if (yearEl) yearEl.textContent = new Date().getFullYear();
  });
})();

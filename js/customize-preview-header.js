(function ($) {
  "use strict";

  // Real-time preview of customizer changes
  wp.customize("vy_primary_color", function (value) {
    value.bind(function (newValue) {
      var style = document.getElementById("vy-customizer-css");
      if (!style) {
        style = document.createElement("style");
        style.id = "vy-customizer-css";
        document.head.appendChild(style);
      }
      style.textContent = `:root { --vy-accent: ${newValue}; }`;
    });
  });

  wp.customize("vy_header_bg_color", function (value) {
    value.bind(function (newValue) {
      var style = document.getElementById("vy-customizer-css");
      if (style) {
        style.textContent += `\n.header { background: ${newValue}; }`;
      }
    });
  });

  wp.customize("vy_header_text_color", function (value) {
    value.bind(function (newValue) {
      var style = document.getElementById("vy-customizer-css");
      if (style) {
        style.textContent += `\n.nav > li > a { color: ${newValue}; }`;
      }
    });
  });
})(jQuery);

/**
* Theme: Kwishift Movers- Responsive Bootstrap 5 Admin Dashboard
* Author: Hydrasoft Technologies
* Module/App: Theme Config Js
*/

(function () {

     var savedConfig = sessionStorage.getItem("__THEME_CONFIG__");
     var html = document.getElementsByTagName("html")[0];

     var defaultConfig = {
          theme: "light",

          topbar: {
               color: "topbar-light",
          },

          menu: {
               size: "default",
               color: "sidebar-light",
          },
     };

     // The line below was causing the error and is now removed.
     // this.html = document.getElementsByTagName('html')[0];

     let config = Object.assign(JSON.parse(JSON.stringify(defaultConfig)), {});
     window.defaultConfig = JSON.parse(JSON.stringify(config));

     if (savedConfig !== null) {
          try {
               var parsedConfig = JSON.parse(savedConfig);

               if (parsedConfig && parsedConfig.version === "kwikshift-brand-v1") {
                    sessionStorage.removeItem("__THEME_CONFIG__");
               } else {
                    config = parsedConfig;
               }
          } catch (error) {
               sessionStorage.removeItem("__THEME_CONFIG__");
          }
     }

     window.config = config;

     if (config) {
          html.setAttribute("data-bs-theme", config.theme);
          html.classList.add(config.topbar.color);
          html.classList.add(config.menu.color);

          if (window.innerWidth <= 1140) {
               html.classList.add("sidebar-hidden");
          }
     }
})();

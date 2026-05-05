(function (window, document) {
    "use strict";

    if (window.toastr) {
        return;
    }

    var defaults = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: 5000,
        newestOnTop: true,
        preventDuplicates: true,
        tapToDismiss: true
    };
    var lastMessage = "";

    function extend(target) {
        var index;
        var key;
        var source;

        target = target || {};

        for (index = 1; index < arguments.length; index += 1) {
            source = arguments[index] || {};

            for (key in source) {
                if (Object.prototype.hasOwnProperty.call(source, key)) {
                    target[key] = source[key];
                }
            }
        }

        return target;
    }

    function getContainer(positionClass) {
        var container = document.getElementById("toast-container");

        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            document.body.appendChild(container);
        }

        container.className = positionClass || defaults.positionClass;

        return container;
    }

    function removeToast(toast) {
        if (!toast || toast.dataset.closing === "true") {
            return;
        }

        toast.dataset.closing = "true";
        toast.classList.add("toast-closing");

        window.setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 180);
    }

    function clearToasts() {
        var container = document.getElementById("toast-container");

        lastMessage = "";

        if (!container) {
            return;
        }

        Array.prototype.slice.call(container.children).forEach(removeToast);
    }

    function renderToast(type, message, title, options) {
        var settings = extend({}, defaults, window.toastr.options || {}, options || {});
        var duration = parseInt(settings.timeOut, 10) || 0;
        var container;
        var toast;
        var body;
        var closeButton;
        var titleNode;
        var messageNode;
        var progress;
        var progressBar;
        var timeoutId = null;

        if (!message) {
            return null;
        }

        if (settings.preventDuplicates && lastMessage === message) {
            return null;
        }

        lastMessage = message;
        container = getContainer(settings.positionClass);
        toast = document.createElement("div");
        toast.className = "toast toast-" + type;
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", type === "error" ? "assertive" : "polite");

        body = document.createElement("div");
        body.className = "toast__body";

        if (title) {
            titleNode = document.createElement("div");
            titleNode.className = "toast__title";
            titleNode.textContent = title;
            body.appendChild(titleNode);
        }

        messageNode = document.createElement("div");
        messageNode.className = "toast__message";
        messageNode.textContent = message;
        body.appendChild(messageNode);

        if (settings.closeButton) {
            closeButton = document.createElement("button");
            closeButton.type = "button";
            closeButton.className = "toast__close";
            closeButton.setAttribute("aria-label", "Close notification");
            closeButton.textContent = "×";
            closeButton.addEventListener("click", function (event) {
                event.preventDefault();
                removeToast(toast);
            });
            body.appendChild(closeButton);
        }

        toast.appendChild(body);

        if (settings.progressBar && duration > 0) {
            progress = document.createElement("div");
            progress.className = "toast__progress";
            progressBar = document.createElement("span");
            progressBar.style.animationDuration = duration + "ms";
            progress.appendChild(progressBar);
            toast.appendChild(progress);
        }

        if (settings.tapToDismiss) {
            toast.addEventListener("click", function (event) {
                if (event.target === closeButton) {
                    return;
                }

                removeToast(toast);
            });
        }

        if (settings.newestOnTop && container.firstChild) {
            container.insertBefore(toast, container.firstChild);
        } else {
            container.appendChild(toast);
        }

        if (duration > 0) {
            timeoutId = window.setTimeout(function () {
                removeToast(toast);
            }, duration);

            toast.addEventListener("mouseenter", function () {
                if (timeoutId) {
                    window.clearTimeout(timeoutId);
                    timeoutId = null;
                }
            });

            toast.addEventListener("mouseleave", function () {
                if (!timeoutId) {
                    timeoutId = window.setTimeout(function () {
                        removeToast(toast);
                    }, 1200);
                }
            });
        }

        return toast;
    }

    window.toastr = {
        options: extend({}, defaults),
        clear: clearToasts,
        remove: clearToasts,
        success: function (message, title, options) {
            return renderToast("success", message, title, options);
        },
        error: function (message, title, options) {
            return renderToast("error", message, title, options);
        },
        warning: function (message, title, options) {
            return renderToast("warning", message, title, options);
        },
        info: function (message, title, options) {
            return renderToast("info", message, title, options);
        }
    };
})(window, document);

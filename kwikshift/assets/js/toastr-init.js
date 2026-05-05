(function (window) {
    "use strict";

    /**
     * Initialize toastr with consistent default options across the application.
     * Call this once on page load to configure toastr globally.
     */
    function initializeToastr() {
        if (!window.toastr) {
            console.warn("toastr library not loaded");
            return;
        }

        window.toastr.options = {
            closeButton: true,
            newestOnTop: false,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: false,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        };
    }

    /**
     * Display an HTTP error toast with meaningful message.
     * Handles status codes like 429 (Too Many Requests), 500, etc.
     */
    function showHttpErrorToast(statusCode, fallbackMessage) {
        var message = fallbackMessage || "An error occurred. Please try again.";

        if (statusCode === 429) {
            message = "Too many requests. Please wait a moment and try again.";
        } else if (statusCode === 500) {
            message = "Server error. Please try again later.";
        } else if (statusCode === 403) {
            message = "Access denied. Your session may have expired.";
        } else if (statusCode === 404) {
            message = "The requested resource was not found.";
        }

        if (window.toastr && window.toastr.error) {
            window.toastr.error(message);
        }
    }

    // Expose to global scope
    window.toastrInit = initializeToastr;
    window.showHttpErrorToast = showHttpErrorToast;

    // Auto-initialize on document ready if jQuery is available
    if (window.jQuery) {
        window.jQuery(function () {
            initializeToastr();
        });
    }
})(window);

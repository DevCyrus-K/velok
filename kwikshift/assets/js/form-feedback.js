(function ($, window) {
    "use strict";

    var EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var PHONE_PATTERN = /^\+?[0-9][0-9\s().-]{6,20}[0-9]$/;

    function normalizeText(value) {
        return $.trim(String(value == null ? "" : value));
    }

    function sanitizeToken(value) {
        var normalized = String(value || "")
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-+|-+$/g, "");

        return normalized || "field";
    }

    function buildSelector(name) {
        return '[name="' + String(name).replace(/"/g, '\\"') + '"]';
    }

    function isFileList(value) {
        return value && typeof value.length === "number" && typeof value.item === "function";
    }

    function isEmptyValue(value) {
        if (Array.isArray(value)) {
            return value.length === 0;
        }

        if (isFileList(value)) {
            return value.length === 0;
        }

        return normalizeText(value) === "";
    }

    function FormFeedback(formElement, config) {
        this.$form = $(formElement);
        this.formId = this.$form.attr("id") || "kwikshift-form";
        this.config = $.extend(true, {
            messageSelector: null,
            summaryMessage: "Please review the highlighted fields and try again.",
            toastTitle: "Please Check Your Form",
            fields: {}
        }, config || {});

        this.$form.attr("novalidate", "novalidate");
    }

    FormFeedback.prototype.init = function () {
        var self = this;

        if (this.config.messageSelector) {
            this.$form.find(this.config.messageSelector).hide();
        }

        $.each(this.config.fields, function (name, fieldConfig) {
            self.ensureField(name, fieldConfig);
            self.bindFieldEvents(name, fieldConfig);
        });

        return this;
    };

    FormFeedback.prototype.ensureField = function (name, fieldConfig) {
        var refs = this.getFieldRefs(name, fieldConfig);

        if (refs.$container.length) {
            this.ensureErrorElement(name, refs.$container);
        }
    };

    FormFeedback.prototype.getFieldElements = function (name, fieldConfig) {
        var selector = fieldConfig && fieldConfig.selector ? fieldConfig.selector : buildSelector(name);
        return this.$form.find(selector);
    };

    FormFeedback.prototype.getFieldContainer = function ($elements, fieldConfig) {
        var $first;
        var $container;

        if (fieldConfig && fieldConfig.containerSelector) {
            return this.$form.find(fieldConfig.containerSelector).first();
        }

        $first = $elements.first();

        if (!$first.length) {
            return $();
        }

        $container = $first.closest(".form-field");

        if ($container.length) {
            return $container;
        }

        return $first.parent();
    };

    FormFeedback.prototype.ensureErrorElement = function (name, $container) {
        var selector = '[data-field-error-for="' + String(name) + '"]';
        var $error = $container.find(selector).first();

        if (!$error.length) {
            $error = $("<div>", {
                "class": "form-field__error",
                "data-field-error-for": name,
                id: this.formId + "-" + sanitizeToken(name) + "-error",
                "aria-live": "polite"
            }).hide();

            $container.append($error);
        }

        return $error;
    };

    FormFeedback.prototype.getFieldRefs = function (name, fieldConfig) {
        var $elements = this.getFieldElements(name, fieldConfig);
        var $container = this.getFieldContainer($elements, fieldConfig);
        var $error = $container.length ? this.ensureErrorElement(name, $container) : $();

        return {
            name: name,
            $elements: $elements,
            $container: $container,
            $error: $error,
            $first: $elements.first()
        };
    };

    FormFeedback.prototype.getDefaultEvents = function ($elements, fieldConfig) {
        var tagName = (($elements.first().prop("tagName") || "") + "").toLowerCase();
        var inputType = (($elements.first().attr("type") || "") + "").toLowerCase();

        if (fieldConfig && $.isArray(fieldConfig.events)) {
            return fieldConfig.events;
        }

        if (inputType === "file" || inputType === "radio" || inputType === "checkbox" || tagName === "select") {
            return ["change"];
        }

        return ["keyup", "input", "change"];
    };

    FormFeedback.prototype.bindFieldEvents = function (name, fieldConfig) {
        var self = this;
        var refs = this.getFieldRefs(name, fieldConfig);
        var events = this.getDefaultEvents(refs.$elements, fieldConfig);

        if (!refs.$elements.length) {
            return;
        }

        $.each(events, function (_, eventName) {
            refs.$elements.on(eventName + ".kwikshiftValidation", function () {
                self.validateField(name);
            });
        });
    };

    FormFeedback.prototype.getFieldValue = function (refs, fieldConfig) {
        var $first = refs.$first;
        var inputType;

        if ($.isFunction(fieldConfig && fieldConfig.getValue)) {
            return fieldConfig.getValue(refs.$elements, this.$form, refs);
        }

        if (!$first.length) {
            return "";
        }

        inputType = (($first.attr("type") || "") + "").toLowerCase();

        if (inputType === "radio") {
            return refs.$elements.filter(":checked").val() || "";
        }

        if (inputType === "checkbox") {
            return refs.$elements.filter(":checked").map(function () {
                return $(this).val();
            }).get();
        }

        if (inputType === "file") {
            return $first[0] && $first[0].files ? $first[0].files : [];
        }

        return normalizeText($first.val());
    };

    FormFeedback.prototype.resolveValidation = function (refs, fieldConfig) {
        var value = this.getFieldValue(refs, fieldConfig);
        var required = fieldConfig.required;
        var minLength;
        var inputType;
        var tagName;
        var pattern;
        var customMessage;

        if (typeof required === "undefined") {
            required = refs.$first.prop("required");
        }

        if (required && isEmptyValue(value)) {
            return fieldConfig.requiredMessage || "This field is required.";
        }

        if (isEmptyValue(value)) {
            return "";
        }

        if ($.isArray(fieldConfig.allowedValues) && $.inArray(String(value), fieldConfig.allowedValues) === -1) {
            return fieldConfig.invalidMessage || "Please choose a valid option.";
        }

        minLength = parseInt(fieldConfig.minLength || refs.$first.attr("minlength") || "0", 10);
        if (minLength > 0 && String(value).length < minLength) {
            return fieldConfig.minLengthMessage || ("Please enter at least " + minLength + " characters.");
        }

        inputType = ((fieldConfig.type || refs.$first.attr("type") || "") + "").toLowerCase();
        tagName = ((refs.$first.prop("tagName") || "") + "").toLowerCase();

        if ((inputType === "email" || tagName === "email") && !EMAIL_PATTERN.test(String(value))) {
            return fieldConfig.invalidMessage || "Please enter a valid email address.";
        }

        pattern = fieldConfig.pattern || refs.$first.attr("pattern");
        if (pattern && !(new RegExp(pattern).test(String(value)))) {
            return fieldConfig.invalidMessage || "Please enter a valid value.";
        }

        if ($.isFunction(fieldConfig.custom)) {
            customMessage = normalizeText(fieldConfig.custom.call(this, value, refs.$elements, this.$form, refs));

            if (customMessage !== "") {
                return customMessage;
            }
        }

        return "";
    };

    FormFeedback.prototype.showFieldError = function (refs, message, fieldConfig) {
        if (!refs.$container.length) {
            return;
        }

        refs.$container.addClass("is-invalid");
        refs.$elements.attr("aria-invalid", "true");

        if (refs.$error.length) {
            refs.$error.text(message).show();
            refs.$first.attr("aria-describedby", refs.$error.attr("id"));
        }

        if ($.isFunction(fieldConfig && fieldConfig.onInvalid)) {
            fieldConfig.onInvalid(refs, message);
        }
    };

    FormFeedback.prototype.clearFieldError = function (refs, fieldConfig) {
        if (!refs.$container.length) {
            return;
        }

        refs.$container.removeClass("is-invalid");
        refs.$elements.removeAttr("aria-invalid");

        if (refs.$error.length) {
            refs.$error.text("").hide();
        }

        if ($.isFunction(fieldConfig && fieldConfig.onValid)) {
            fieldConfig.onValid(refs);
        }
    };

    FormFeedback.prototype.validateField = function (name) {
        var fieldConfig = this.config.fields[name] || {};
        var refs = this.getFieldRefs(name, fieldConfig);
        var message;

        if (!refs.$elements.length || refs.$first.is(":disabled")) {
            return true;
        }

        message = this.resolveValidation(refs, fieldConfig);

        if (message !== "") {
            this.showFieldError(refs, message, fieldConfig);
            return false;
        }

        this.clearFieldError(refs, fieldConfig);
        return true;
    };

    FormFeedback.prototype.validateAll = function () {
        var self = this;
        var isValid = true;
        var firstInvalid = null;

        $.each(this.config.fields, function (name, fieldConfig) {
            var fieldValid = self.validateField(name);

            if (!fieldValid) {
                isValid = false;

                if (!firstInvalid) {
                    firstInvalid = self.getFieldRefs(name, fieldConfig);
                }
            }
        });

        return {
            valid: isValid,
            firstInvalid: firstInvalid
        };
    };

    FormFeedback.prototype.clearErrors = function () {
        var self = this;

        $.each(this.config.fields, function (name, fieldConfig) {
            var refs = self.getFieldRefs(name, fieldConfig);
            self.clearFieldError(refs, fieldConfig);
        });
    };

    FormFeedback.prototype.applyServerErrors = function (errors) {
        var self = this;

        if (!errors || typeof errors !== "object") {
            return;
        }

        $.each(errors, function (name, message) {
            var fieldConfig = self.config.fields[name];
            var refs;

            if (!fieldConfig) {
                return;
            }

            refs = self.getFieldRefs(name, fieldConfig);

            if (!refs.$container.length) {
                return;
            }

            self.showFieldError(refs, normalizeText(message), fieldConfig);
        });
    };

    FormFeedback.prototype.focusField = function (refs) {
        if (!refs || !refs.$first || !refs.$first.length) {
            return;
        }

        if (refs.$container.length && refs.$container[0].scrollIntoView) {
            refs.$container[0].scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        }

        window.setTimeout(function () {
            refs.$first.trigger("focus");
        }, 120);
    };

    FormFeedback.prototype.focusFirstError = function (errors) {
        var self = this;
        var focused = false;

        $.each(errors || {}, function (name) {
            var fieldConfig = self.config.fields[name];
            var refs;

            if (focused || !fieldConfig) {
                return;
            }

            refs = self.getFieldRefs(name, fieldConfig);

            if (refs.$first.length) {
                self.focusField(refs);
                focused = true;
            }
        });
    };

    FormFeedback.prototype.notify = function (type, message, title) {
        if (!message || !window.toastr || !$.isFunction(window.toastr[type])) {
            return;
        }

        window.toastr.clear();
        window.toastr[type](message);
    };

    FormFeedback.prototype.handleSubmitValidation = function () {
        var result = this.validateAll();

        if (!result.valid) {
            this.notify("error", this.config.summaryMessage, this.config.toastTitle);
            this.focusField(result.firstInvalid);
            return false;
        }

        return true;
    };

    function getResponseData(source, fallbackMessage) {
        var payload = source && source.responseJSON ? source.responseJSON : source;
        var isTimeout = !!(source && String(source.statusText || "").toLowerCase() === "timeout");
        var message = payload && payload.message
            ? payload.message
            : (isTimeout
                ? "This request took too long to finish. If you did not receive a success message, please check your connection and try once more."
                : fallbackMessage);
        var errors = payload && payload.errors && typeof payload.errors === "object" ? payload.errors : {};

        return {
            message: message,
            errors: errors
        };
    }

    window.KwikshiftFormFeedback = {
        phonePattern: PHONE_PATTERN,
        emailPattern: EMAIL_PATTERN,
        create: function (formElement, config) {
            return new FormFeedback(formElement, config).init();
        },
        getResponseData: getResponseData
    };
})(jQuery, window);

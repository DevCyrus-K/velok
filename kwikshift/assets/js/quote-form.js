$(function () {
    "use strict";

    var form = $("#ajax_quote_form");
    var formMessages = $("#q-form-messages");
    var phonePattern = window.KwikshiftFormFeedback ? window.KwikshiftFormFeedback.phonePattern : /^\+?[0-9][0-9\s().-]{6,20}[0-9]$/;
    var allowedMoveTypes = [
        "Residential Relocation",
        "Office Relocation",
        "Long-Distance Move",
        "Packing & Storage",
        "Packing and Storage"
    ];
    var feedback;
    var submitButton = form.find('button[type="submit"], input[type="submit"]').first();
    var originalSubmitText = submitButton.length ? (submitButton.is("button") ? submitButton.text() : submitButton.val()) : "";
    var moveDateField = form.find("#q-move-date");

    function setSubmitButtonState(isSubmitting) {
        if (!submitButton.length) {
            return;
        }

        if (isSubmitting) {
            submitButton.prop("disabled", true);
            submitButton.data("original-text", originalSubmitText);
            if (submitButton.is("button")) {
                submitButton.text("Submitting...");
            } else {
                submitButton.val("Submitting...");
            }
            return;
        }

        submitButton.prop("disabled", false);
        var text = submitButton.data("original-text") || originalSubmitText;
        if (submitButton.is("button")) {
            submitButton.text(text);
        } else {
            submitButton.val(text);
        }
    }

    function getRequestUrl(action) {
        if (!action) {
            return action;
        }

        if (/^(?:[a-z]+:)?\/\//i.test(action) || action.charAt(0) === "/") {
            return action;
        }

        return "/" + action.replace(/^\/+/, "");
    }

    if (!form.length) {
        return;
    }

    if (moveDateField.length) {
        moveDateField.attr("min", new Date().toISOString().split("T")[0]);
    }

    formMessages.hide();

    feedback = window.KwikshiftFormFeedback.create(form, {
        messageSelector: "#q-form-messages",
        summaryMessage: "Please complete the highlighted moving details before requesting your quote.",
        toastTitle: "Quote Request",
        fields: {
            "q-full-name": {
                requiredMessage: "Please enter your full name."
            },
            "q-phone": {
                requiredMessage: "Please enter your phone number.",
                custom: function (value) {
                    if (value && !phonePattern.test(value)) {
                        return "Please enter a valid phone number so we can reach you quickly.";
                    }

                    return "";
                }
            },
            "q-depature": {
                requiredMessage: "Please enter your pickup location."
            },
            "q-destination": {
                requiredMessage: "Please enter your destination."
            },
            "q-weight": {
                requiredMessage: "Please tell us the house size or office items involved."
            },
            "q-freight-type": {
                requiredMessage: "Please choose your move type.",
                custom: function (value) {
                    if (value && $.inArray(value, allowedMoveTypes) === -1) {
                        return "Please choose a valid move type for your request.";
                    }

                    return "";
                }
            },
            "q-email": {
                requiredMessage: "Please enter your email address.",
                invalidMessage: "Please enter a valid email address."
            },
            "q-message": {
                requiredMessage: "Please share a few details about your move.",
                minLength: 10,
                minLengthMessage: "Please share a few more details about your move so we can prepare the right quote."
            }
        }
    });

    function applyServerFeedback(source, fallback) {
        var response = window.KwikshiftFormFeedback.getResponseData(source, fallback);

        feedback.clearErrors();
        feedback.applyServerErrors(response.errors);

        if (!$.isEmptyObject(response.errors)) {
            feedback.focusFirstError(response.errors);
        }

        feedback.notify("error", response.message, "Quote Request");
    }

    form.on("submit", function (event) {
        event.preventDefault();
        
        setSubmitButtonState(true);

        var validationResult = feedback.handleSubmitValidation();
        if (!validationResult) {
            setSubmitButtonState(false);
            return;
        }

        var securityReady = window.KwikshiftFormSecurity
            ? window.KwikshiftFormSecurity.ready(form)
            : $.Deferred().resolve().promise();

        securityReady.fail(function () {
            feedback.notify("error", "Your secure session could not be validated. Please refresh the page and try again.");
            setSubmitButtonState(false);
        }).done(function () {
            $.ajax({
                type: "POST",
                url: getRequestUrl(form.attr("action")),
                data: form.serialize(),
                dataType: "json",
                timeout: 20000
            }).then(function (response, status, xhr) {
                if (!response || response.ok !== true) {
                    applyServerFeedback(response, "Your quote request could not be sent.");
                    return;
                }

                feedback.clearErrors();
                feedback.notify("success", response.message);
                form[0].reset();

                if (window.KwikshiftFormSecurity) {
                    window.KwikshiftFormSecurity.refresh().always(function () {
                        window.KwikshiftFormSecurity.ready(form);
                    });
                }
            }, function (xhr) {
                if (xhr && xhr.status === 429) {
                    if (window.showHttpErrorToast) {
                        window.showHttpErrorToast(429);
                    } else {
                        feedback.notify("error", "Too many requests. Please wait a moment and try again.");
                    }
                } else {
                    applyServerFeedback(xhr, "Oops! An error occurred and your quote request could not be sent.");
                }
            }).always(function () {
                setSubmitButtonState(false);
            });
        });
    });
});

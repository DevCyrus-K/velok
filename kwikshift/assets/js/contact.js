$(function () {
    "use strict";

    var form = $("#ajax_contact");
    var formMessages = $("#form-messages");
    var phonePattern = window.KwikshiftFormFeedback ? window.KwikshiftFormFeedback.phonePattern : /^\+?[0-9][0-9\s().-]{6,20}[0-9]$/;
    var feedback;
    var submitButton = form.find('button[type="submit"], input[type="submit"]').first();
    var originalSubmitText = submitButton.length ? (submitButton.is("button") ? submitButton.text() : submitButton.val()) : "";

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

    formMessages.hide();

    feedback = window.KwikshiftFormFeedback.create(form, {
        messageSelector: "#form-messages",
        summaryMessage: "Please fill in the highlighted contact details before sending your message.",
        toastTitle: "Contact Form",
        fields: {
            firstname: {
                requiredMessage: "Please enter your first name."
            },
            lastname: {
                requiredMessage: "Please enter your last name."
            },
            email: {
                requiredMessage: "Please enter your email address.",
                invalidMessage: "Please enter a valid email address."
            },
            phone: {
                requiredMessage: "Please enter your phone number.",
                custom: function (value) {
                    if (value && !phonePattern.test(value)) {
                        return "Please enter a valid phone number so our team can reach you quickly.";
                    }

                    return "";
                }
            },
            message: {
                requiredMessage: "Please tell us about your move or request.",
                minLength: 10,
                minLengthMessage: "Please share a little more detail in your message."
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

        feedback.notify("error", response.message, "Contact Form");
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
                    applyServerFeedback(response, "Your message could not be sent.");
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
                    applyServerFeedback(xhr, "Oops! An error occurred and your message could not be sent.");
                }
            }).always(function () {
                setSubmitButtonState(false);
            });
        });
    });
});

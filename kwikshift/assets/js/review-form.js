$(function () {
    "use strict";

    var form = $("#ajax_review_form");
    var formMessages;
    var ratingInputs;
    var ratingPicker;
    var ratingContainer;
    var ratingDisplay;
    var ratingStars;
    var ratingLabels;
    var ratingValidationMessage = "Please choose a star rating before submitting your review.";
    var allowedImageTypes = ["image/jpeg", "image/png", "image/webp"];
    var maxFileSize = 5 * 1024 * 1024;
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

    formMessages = $("#review-form-messages");
    ratingInputs = form.find('input[name="review-rating"]');
    ratingPicker = form.find("[data-rating-picker]");
    ratingContainer = ratingPicker.find(".rating-picker__container");
    ratingDisplay = $("#review-rating-display");
    ratingStars = ratingPicker.find(".rating-picker__star");
    ratingLabels = ratingPicker.find(".rating-picker__label");

    formMessages.hide();

    feedback = window.KwikshiftFormFeedback.create(form, {
        messageSelector: "#review-form-messages",
        summaryMessage: "Please review the highlighted fields before submitting your review.",
        toastTitle: "Review Form",
        fields: {
            "review-name": {
                requiredMessage: "Please enter your full name."
            },
            "review-role": {
                requiredMessage: "Please tell us who you are, for example Homeowner or Business Owner."
            },
            "review-rating": {
                selector: 'input[name="review-rating"]',
                containerSelector: ".form-field--rating",
                events: ["change"],
                getValue: function ($elements) {
                    return $elements.filter(":checked").val() || "0";
                },
                custom: function (value) {
                    if (parseFloat(value || "0") <= 0) {
                        return ratingValidationMessage;
                    }

                    return "";
                },
                onInvalid: function () {
                    ratingPicker.addClass("is-invalid");
                },
                onValid: function () {
                    ratingPicker.removeClass("is-invalid");
                }
            },
            "review-photo": {
                requiredMessage: "Please upload your photo.",
                events: ["change"],
                custom: function (value) {
                    var file = value && value.length ? value[0] : null;
                    var extension = file && file.name ? file.name.split(".").pop().toLowerCase() : "";

                    if (!file) {
                        return "";
                    }

                    if ($.inArray(file.type, allowedImageTypes) === -1 && $.inArray(extension, ["jpg", "jpeg", "png", "webp"]) === -1) {
                        return "Upload a JPG, PNG, or WebP photo.";
                    }

                    if (file.size > maxFileSize) {
                        return "Upload a photo under 5MB.";
                    }

                    return "";
                }
            },
            "review-message": {
                requiredMessage: "Please write your review before submitting.",
                minLength: 20,
                minLengthMessage: "Please make your review a little more detailed so it is useful to future customers."
            }
        }
    });

    function getSelectedRating() {
        return parseFloat(ratingInputs.filter(":checked").val() || "0");
    }

    function formatRating(value) {
        if (Math.floor(value) === value) {
            return String(value.toFixed(0));
        }

        return value.toFixed(1);
    }

    function setRatingVisual(value) {
        ratingStars.removeClass("is-full is-half");

        ratingStars.each(function (index) {
            var remaining = value - index;
            var star = $(this);

            if (remaining >= 1) {
                star.addClass("is-full");
                return;
            }

            if (remaining >= 0.5) {
                star.addClass("is-half");
            }
        });
    }

    function setRatingDisplay() {
        var selectedValue = getSelectedRating();

        setRatingVisual(selectedValue);
        ratingPicker.removeClass("is-previewing");

        if (!Number.isNaN(selectedValue) && selectedValue > 0) {
            ratingDisplay
                .text(formatRating(selectedValue) + " / 5 selected")
                .addClass("is-active");
            return;
        }

        ratingDisplay.text("0 / 5").removeClass("is-active");
    }

    function applyServerFeedback(source, fallback) {
        var response = window.KwikshiftFormFeedback.getResponseData(source, fallback);

        feedback.clearErrors();
        feedback.applyServerErrors(response.errors);

        if (!$.isEmptyObject(response.errors)) {
            feedback.focusFirstError(response.errors);
        }

        feedback.notify("error", response.message, "Review Form");
    }

    ratingLabels.on("mouseenter focus", function () {
        var previewValue = parseFloat($(this).prev("input").val() || "0");

        if (Number.isNaN(previewValue) || previewValue <= 0) {
            return;
        }

        ratingPicker.addClass("is-previewing");
        setRatingVisual(previewValue);
    });

    ratingContainer.on("mouseleave", function () {
        setRatingDisplay();
    });

    ratingInputs.on("blur", function () {
        window.setTimeout(function () {
            if (!ratingContainer.find(":focus").length) {
                setRatingDisplay();
            }
        }, 0);
    });

    ratingInputs.on("change", function () {
        setRatingDisplay();
        feedback.validateField("review-rating");
    });

    setRatingDisplay();

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
                data: new FormData(form[0]),
                processData: false,
                contentType: false,
                dataType: "json",
                timeout: 20000
            }).then(function (response, status, xhr) {
                if (!response || response.ok !== true) {
                    applyServerFeedback(response, "Your review could not be submitted.");
                    return;
                }

                feedback.clearErrors();
                feedback.notify("success", response.message);
                form[0].reset();
                setRatingDisplay();

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
                    applyServerFeedback(xhr, "Oops! An error occurred and your review could not be submitted.");
                }
            }).always(function () {
                setSubmitButtonState(false);
            });
        });
    });
});

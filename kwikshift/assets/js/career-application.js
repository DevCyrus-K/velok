$(function () {
    "use strict";

    var apiEndpoint = "php/public-content.php";
    var form = $("#career_application_form");
    var formMessages = $("#career-application-messages");
    var title = $("#career-application-title");
    var meta = $("#career-application-meta");
    var description = $("#career-application-description");
    var requirements = $("#career-application-requirements");
    var emptyState = $("#career-application-empty");
    var hiddenJobId = $("#career-job-id");
    var pageCopy = $("#career-page-copy");
    var applyButton = form.find('button[type="submit"]');
    var params = new URLSearchParams(window.location.search);
    var jobId = parseInt(params.get("job_id") || "0", 10);
    var phonePattern = window.KwikshiftFormFeedback ? window.KwikshiftFormFeedback.phonePattern : /^\+?[0-9][0-9\s().-]{6,20}[0-9]$/;
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

    formMessages.hide();
    emptyState.hide();

    feedback = window.KwikshiftFormFeedback.create(form, {
        messageSelector: "#career-application-messages",
        summaryMessage: "Please review the highlighted application details before submitting.",
        toastTitle: "Career Application",
        fields: {
            full_name: {
                requiredMessage: "Please enter your full name."
            },
            email: {
                requiredMessage: "Please enter your email address.",
                invalidMessage: "Please enter a valid email address."
            },
            phone: {
                requiredMessage: "Please enter your phone number.",
                custom: function (value) {
                    if (value && !phonePattern.test(value)) {
                        return "Please enter a valid phone number.";
                    }

                    return "";
                }
            },
            cv_file: {
                requiredMessage: "Please upload your CV.",
                events: ["change"],
                custom: function (value) {
                    var file = value && value.length ? value[0] : null;
                    var extension = file && file.name ? file.name.split(".").pop().toLowerCase() : "";

                    if (!file) {
                        return "";
                    }

                    if ($.inArray(extension, ["pdf", "doc", "docx"]) === -1) {
                        return "Upload a PDF, DOC, or DOCX CV.";
                    }

                    if (file.size > maxFileSize) {
                        return "Upload a CV under 5MB.";
                    }

                    return "";
                }
            },
            cover_letter: {
                requiredMessage: "Please write a short cover letter before applying.",
                minLength: 30,
                minLengthMessage: "Please provide a short cover letter explaining why you are a strong fit for this role."
            }
        }
    });

    function escapeHtml(value) {
        return $("<div>").text(String(value || "")).html();
    }

    function formatDate(value) {
        if (!value) {
            return "";
        }

        var date = new Date(value + "T00:00:00");
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat("en-KE", {
            year: "numeric",
            month: "long",
            day: "numeric"
        }).format(date);
    }

    function formatParagraphs(value) {
        return String(value || "")
            .split(/\r?\n/)
            .map(function (line) {
                return line.trim();
            })
            .filter(function (line) {
                return line !== "";
            })
            .map(function (line) {
                return "<p>" + escapeHtml(line) + "</p>";
            })
            .join("");
    }

    function renderRequirements(value) {
        return String(value || "")
            .split(/\r?\n|;/)
            .map(function (line) {
                return line.trim();
            })
            .filter(function (line) {
                return line !== "";
            })
            .map(function (line) {
                return '<li><i class="fa-solid fa-check"></i>' + escapeHtml(line) + "</li>";
            })
            .join("");
    }

    function disableApplicationForm(message) {
        form.find(":input").prop("disabled", true);
        applyButton.prop("disabled", true);
        feedback.clearErrors();
        emptyState.html(message).show();
    }

    function applyServerFeedback(source, fallback) {
        var response = window.KwikshiftFormFeedback.getResponseData(source, fallback);

        feedback.clearErrors();
        feedback.applyServerErrors(response.errors);

        if (!$.isEmptyObject(response.errors)) {
            feedback.focusFirstError(response.errors);
        }

        feedback.notify("error", response.message, "Career Application");
    }

    function loadJobDetails() {
        if (!jobId || Number.isNaN(jobId) || jobId < 1) {
            title.html('Career Opportunity <span class="hl">Unavailable</span>');
            pageCopy.text("Choose an active role from the careers page before submitting your application.");
            disableApplicationForm('<p>Please return to <a href="careers">the careers page</a> and choose an active opening before submitting your application.</p>');
            return;
        }

        $.ajax({
            url: apiEndpoint,
            method: "GET",
            dataType: "json",
            cache: false,
            data: {
                type: "career",
                id: jobId
            }
        })
        .done(function (response) {
            var item = response && response.data ? response.data : null;

            if (!response || response.ok !== true || !item) {
                disableApplicationForm("<p>This role is no longer available. Please check the careers page for current openings.</p>");
                return;
            }

            hiddenJobId.val(item.id);
            title.html('Apply for <span class="hl">' + escapeHtml(item.job_title) + "</span>");
            pageCopy.text("Submit your application securely and our recruitment team will review it.");
            meta.html(
                '<li><i class="fa-light fa-briefcase"></i><h3>Department &amp; Type <span>' + escapeHtml(item.department) + " / " + escapeHtml(item.employment_type) + "</span></h3></li>" +
                '<li><i class="fa-light fa-location-dot"></i><h3>Location <span>' + escapeHtml(item.location) + "</span></h3></li>" +
                (item.deadline ? '<li><i class="fa-light fa-calendar"></i><h3>Application Deadline <span>' + escapeHtml(formatDate(item.deadline)) + "</span></h3></li>" : "")
            );
            description.html(formatParagraphs(item.job_description));
            requirements.html(renderRequirements(item.requirements) || '<li><i class="fa-solid fa-check"></i>Role requirements will be shared during the review process.</li>');
        })
        .fail(function () {
            title.html('Career Opportunity <span class="hl">Unavailable</span>');
            pageCopy.text("We could not load this role right now.");
            disableApplicationForm("<p>We could not load this career opportunity right now. Please try again shortly or return to <a href=\"careers\">the careers page</a>.</p>");
        });
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
                data: new FormData(form[0]),
                processData: false,
                contentType: false,
                dataType: "json",
                timeout: 20000
            }).then(function (response, status, xhr) {
                if (!response || response.ok !== true) {
                    applyServerFeedback(response, "Your application could not be submitted.");
                    return;
                }

                feedback.clearErrors();
                feedback.notify("success", response.message);
                form[0].reset();
                hiddenJobId.val(jobId);

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
                    applyServerFeedback(xhr, "Oops! An error occurred and your application could not be submitted.");
                }
            }).always(function () {
                setSubmitButtonState(false);
            });
        });
    });

    loadJobDetails();
});

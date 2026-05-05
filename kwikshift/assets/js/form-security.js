(function ($) {
    "use strict";

    var bootstrapRequest = null;
    var formSelectors = [
        "#ajax_contact",
        "#ajax_quote_form",
        "#ajax_review_form",
        "#career_application_form"
    ];

    function getSourcePage() {
        return window.location.pathname + window.location.search;
    }

    function getBootstrapRequest() {
        if (!bootstrapRequest) {
            bootstrapRequest = $.ajax({
                url: "php/form-bootstrap.php",
                method: "GET",
                dataType: "json",
                cache: false,
                timeout: 10000
            });
        }

        return bootstrapRequest;
    }

    function ensureHiddenField(form, name) {
        var field = form.find('input[name="' + name + '"]');

        if (!field.length) {
            field = $('<input>', {
                type: "hidden",
                name: name
            });
            form.append(field);
        }

        return field;
    }

    function populateFormSecurity(formElement) {
        var form = $(formElement);

        if (!form.length) {
            return $.Deferred().reject("Form not found.").promise();
        }

        return getBootstrapRequest().then(function (response) {
            if (!response || response.ok !== true || !response.csrf_token) {
                return $.Deferred().reject("Unable to initialize secure form session.").promise();
            }

            ensureHiddenField(form, "csrf_token").val(response.csrf_token);
            ensureHiddenField(form, "source_page").val(getSourcePage());

            return response;
        });
    }

    function primeForms() {
        var selector = formSelectors.join(", ");
        var forms = $(selector);

        if (!forms.length) {
            return;
        }

        getBootstrapRequest();

        forms.each(function () {
            populateFormSecurity(this);
        });
    }

    window.KwikshiftFormSecurity = {
        ready: populateFormSecurity,
        refresh: function () {
            bootstrapRequest = null;
            return getBootstrapRequest();
        }
    };

    $(primeForms);
})(jQuery);

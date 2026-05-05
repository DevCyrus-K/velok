(function ($) {
    "use strict";

    var apiEndpoint = "php/public-content.php";
    var faqPageItems = [];
    var faqSearchTimer = null;
    var queryParams = new URLSearchParams(window.location.search);

    $(function () {
        loadHomeProjects();
        loadHomeTestimonials();
        loadHomeSponsors();
        loadTestimonialsPage();
        loadClientsPage();
        loadFaqAccordion("#home-faq-accordion", 4);
        loadFaqAccordion("#review-faq-accordion", 4);
        loadFaqPage();
        loadGalleryPage();
        loadCareersPage();
    });

    function fetchContent(type, options) {
        return $.ajax({
            url: apiEndpoint,
            method: "GET",
            dataType: "json",
            cache: false,
            data: $.extend({
                type: type
            }, options || {})
        });
    }

    function loadHomeProjects() {
        var wrapper = document.querySelector(".project-carousel .swiper-wrapper");

        if (!wrapper) {
            return;
        }

        fetchContent("gallery", {
            limit: 5
        }).done(function (response) {
            if (!hasItems(response)) {
                wrapper.innerHTML = renderCarouselEmptySlide("No gallery items available right now.", "Please check back soon for recent moves.");
                return;
            }

            wrapper.innerHTML = renderHomeProjectSlides(response.data);
            refreshProjectUi();
        }).fail(function () {
            wrapper.innerHTML = renderCarouselEmptySlide("Gallery unavailable right now.", "Please try again shortly.");
        });
    }

    function loadHomeTestimonials() {
        var wrapper = document.querySelector(".testimonial-carousel .swiper-wrapper");

        if (!wrapper) {
            return;
        }

        fetchContent("testimonials", {
            limit: 4
        }).done(function (response) {
            if (!hasItems(response)) {
                wrapper.innerHTML = renderCarouselEmptySlide("No testimonials available right now.", "Please check back soon or share your review.");
                return;
            }

            wrapper.innerHTML = renderHomeTestimonialSlides(response.data);
            refreshTestimonialUi();
        }).fail(function () {
            wrapper.innerHTML = renderCarouselEmptySlide("Testimonials unavailable right now.", "Please try again shortly.");
        });
    }

    function loadHomeSponsors() {
        var wrapper = document.querySelector(".sponsor-carousel .swiper-wrapper");

        if (!wrapper) {
            return;
        }

        fetchContent("clients", {
            limit: 12,
            featured: false
        }).done(function (response) {
            if (!hasItems(response)) {
                wrapper.innerHTML = renderCarouselEmptySlide("No client logos available right now.", "Please check back soon.");
                return;
            }

            wrapper.innerHTML = renderSponsorSlides(response.data);
            callUi("initSponsorCarousel");
        }).fail(function () {
            wrapper.innerHTML = renderCarouselEmptySlide("Clients unavailable right now.", "Please try again shortly.");
        });
    }

    function loadTestimonialsPage() {
        var grid = $("#testimonial-reviews-grid");
        var featuredFilter = getBooleanFilter("featured");
        var serviceTypeFilter = getStringFilter("service_type");

        if (!grid.length) {
            return;
        }

        fetchContent("testimonials", {
            limit: "all",
            featured: featuredFilter,
            service_type: serviceTypeFilter
        }).done(function (response) {
            if (!hasItems(response)) {
                grid.html(renderGridEmptyState("No testimonials available right now.", "Please check back soon or share your review."));
                return;
            }

            grid.html(renderTestimonialsGrid(response.data));
            callUi("initWow");
        }).fail(function () {
            grid.html(renderGridEmptyState("Testimonials unavailable right now.", "Please try again shortly."));
        });
    }

    function loadClientsPage() {
        var grid = $("#client-reviews-grid");
        var featuredFilter = queryParams.has("featured") ? getBooleanFilter("featured") : null;
        var industryFilter = getStringFilter("industry");

        if (!grid.length) {
            return;
        }

        fetchContent("clients", {
            limit: "all",
            featured: featuredFilter,
            industry: industryFilter
        }).done(function (response) {
            if (!hasItems(response)) {
                grid.html(renderGridEmptyState("No clients available right now.", "Please check back soon for updated client profiles."));
                return;
            }

            grid.html(renderClientsGrid(response.data));
            callUi("initWow");
        }).fail(function () {
            grid.html(renderGridEmptyState("Clients unavailable right now.", "Please try again shortly."));
        });
    }

    function loadFaqAccordion(selector, limit) {
        var accordion = $(selector);

        if (!accordion.length) {
            return;
        }

        fetchContent("faqs", {
            limit: limit
        }).done(function (response) {
            if (!hasItems(response)) {
                accordion.html(renderAccordionEmptyState("No FAQs available right now.", "Please contact our team for direct help."));
                return;
            }

            accordion.html(renderFaqAccordion(response.data, selector.replace("#", "")));
        }).fail(function () {
            accordion.html(renderAccordionEmptyState("FAQs unavailable right now.", "Please try again shortly."));
        });
    }

    function loadFaqPage() {
        var accordion = $("#faq-accordion");
        var searchInput = $("#faq-search-input");
        var categoryFilter = getStringFilter("category");

        if (!accordion.length) {
            return;
        }

        fetchContent("faqs", {
            limit: "all",
            category: categoryFilter
        }).done(function (response) {
            if (!hasItems(response)) {
                faqPageItems = [];
                accordion.html(renderAccordionEmptyState("No FAQs available right now.", "Please contact our team for direct help."));
                return;
            }

            faqPageItems = response.data.slice(0);
            accordion.html(renderFaqAccordion(faqPageItems, "faq-accordion"));
            bindFaqSearch(searchInput, accordion);
        }).fail(function () {
            faqPageItems = [];
            accordion.html(renderAccordionEmptyState("FAQs unavailable right now.", "Please try again shortly."));
        });
    }

    function loadGalleryPage() {
        var grid = $("#gallery-grid");
        var featuredFilter = getBooleanFilter("featured");
        var categoryFilter = getStringFilter("category");

        if (!grid.length) {
            return;
        }

        fetchContent("gallery", {
            limit: "all",
            featured: featuredFilter,
            category: categoryFilter
        }).done(function (response) {
            if (!hasItems(response)) {
                grid.html(renderGridEmptyState("No gallery items available right now.", "Please check back soon for recent moving projects."));
                $("#gallery-pagination").hide();
                return;
            }

            grid.html(renderGalleryGrid(response.data));
            $("#gallery-pagination").hide();
            callUi("initVenoBox");
            callUi("initWow");
        }).fail(function () {
            grid.html(renderGridEmptyState("Gallery unavailable right now.", "Please try again shortly."));
            $("#gallery-pagination").hide();
        });
    }

    function loadCareersPage() {
        var listingsGrid = $("#careers-listings-grid");
        var departmentFilter = getStringFilter("department");

        if (!listingsGrid.length) {
            return;
        }

        fetchContent("careers", {
            limit: "all",
            department: departmentFilter
        }).done(function (response) {
            if (!hasItems(response)) {
                renderCareerEmptyState();
                return;
            }

            $("#careers-heading-title").html('Open Roles at <span class="hl">Kwikshift</span>');
            $("#careers-heading-copy").html("Apply securely online for our current openings in moving, transport, and operations support.");
            listingsGrid.removeClass("justify-content-center").html(renderCareerGrid(response.data));
            callUi("initWow");
        }).fail(function () {
            renderCareerEmptyState("Careers unavailable right now.", "Please try again shortly or contact our team for updates.");
        });
    }

    function bindFaqSearch(searchInput, accordion) {
        if (!searchInput.length) {
            return;
        }

        searchInput.off("input.kwikshiftFaqSearch").on("input.kwikshiftFaqSearch", function () {
            var value = $(this).val();

            if (faqSearchTimer) {
                window.clearTimeout(faqSearchTimer);
            }

            faqSearchTimer = window.setTimeout(function () {
                var term = String(value || "").toLowerCase().trim();
                var filteredItems;

                if (!term) {
                    accordion.html(renderFaqAccordion(faqPageItems, "faq-accordion"));
                    return;
                }

                filteredItems = faqPageItems.filter(function (item) {
                    return [
                        item.question,
                        item.answer,
                        item.category
                    ].join(" ").toLowerCase().indexOf(term) !== -1;
                });

                if (!filteredItems.length) {
                    accordion.html(renderAccordionEmptyState("No matching FAQs found.", "Try a different keyword or contact our team for direct help."));
                    return;
                }

                accordion.html(renderFaqAccordion(filteredItems, "faq-accordion"));
            }, 200);
        });
    }

    function renderHomeProjectSlides(items) {
        return items.map(function (item, index) {
            return [
                '<div class="swiper-slide">',
                    '<div class="project-item wow fade-in-bottom" data-wow-delay="' + escapeHtml(getDelay(index, 200)) + 'ms">',
                        '<div class="project-thumb project-view">',
                            '<a class="venobox" href="' + escapeHtml(item.image_path) + '" data-gall="projects"><img src="' + escapeHtml(item.image_path) + '" alt="' + escapeHtml(item.alt_text) + '"></a>',
                        '</div>',
                        '<div class="project-content">',
                            '<a href="' + escapeHtml(getGalleryCategoryLink(item.category, true)) + '" class="category">' + escapeHtml(item.category) + '</a>',
                            '<h3><a href="' + escapeHtml(getGalleryCategoryLink(item.category, true)) + '">' + escapeHtml(item.title) + '</a></h3>',
                        '</div>',
                    '</div>',
                '</div>'
            ].join("");
        }).join("");
    }

    function renderHomeTestimonialSlides(items) {
        return items.map(function (item) {
            return [
                '<div class="swiper-slide">',
                    '<div class="testimonial-item">',
                        '<div class="testi-thumb">',
                            '<img src="' + escapeHtml(item.client_image) + '" alt="' + escapeHtml(item.client_name) + ' testimonial photo">',
                        '</div>',
                        '<div class="testi-content">',
                            '<div class="client-info">',
                                '<h3>' + escapeHtml(item.client_name) + ' <span>' + escapeHtml(item.client_role) + '</span></h3>',
                            '</div>',
                            '<p>' + escapeHtml(item.testimonial_message) + '</p>',
                            '<ul class="rattings" aria-label="Rated ' + escapeHtml(formatRating(item.rating)) + ' out of 5 stars">' + renderStars(item.rating) + '</ul>',
                            '<div class="quote-icon"><i class="fa-sharp fa-solid fa-quote-right"></i></div>',
                        '</div>',
                    '</div>',
                '</div>'
            ].join("");
        }).join("");
    }

    function renderSponsorSlides(items) {
        return items.map(function (item) {
            var link = getClientLink(item);
            var targetAttributes = getClientLinkAttributes(item);
            var imageMarkup = '<img src="' + escapeHtml(item.client_logo) + '" alt="' + escapeHtml(item.client_name) + ' logo">';

            if (link) {
                return [
                    '<div class="swiper-slide">',
                        '<a href="' + escapeHtml(link) + '"' + targetAttributes + '>' + imageMarkup + '</a>',
                    '</div>'
                ].join("");
            }

            return [
                '<div class="swiper-slide">',
                    '<span class="d-inline-block">' + imageMarkup + '</span>',
                '</div>'
            ].join("");
        }).join("");
    }

    function renderTestimonialsGrid(items) {
        return items.map(function (item) {
            return [
                '<div class="col-md-6" id="' + escapeHtml(item.anchor) + '">',
                    '<div class="testimonial-item">',
                        '<div class="testi-thumb">',
                            '<img src="' + escapeHtml(item.client_image) + '" alt="' + escapeHtml(item.client_name) + ' testimonial">',
                        '</div>',
                        '<div class="testi-content">',
                            '<div class="client-info">',
                                '<h3>' + escapeHtml(item.client_name) + ' <span>' + escapeHtml(item.client_role) + '</span></h3>',
                            '</div>',
                            '<p>' + escapeHtml(item.testimonial_message) + '</p>',
                            '<ul class="rattings" aria-label="Rated ' + escapeHtml(formatRating(item.rating)) + ' out of 5 stars">' + renderStars(item.rating) + '</ul>',
                            '<div class="quote-icon"><i class="fa-sharp fa-solid fa-quote-right"></i></div>',
                        '</div>',
                    '</div>',
                '</div>'
            ].join("");
        }).join("");
    }

    function renderClientsGrid(items) {
        return items.map(function (item, index) {
            var link = getClientLink(item);
            var targetAttributes = getClientLinkAttributes(item);
            var imageMarkup = '<img src="' + escapeHtml(item.client_logo) + '" alt="' + escapeHtml(item.client_name) + ' client profile">';
            var titleMarkup = escapeHtml(item.client_name);

            if (link) {
                imageMarkup = '<a href="' + escapeHtml(link) + '"' + targetAttributes + '>' + imageMarkup + '</a>';
                titleMarkup = '<a href="' + escapeHtml(link) + '"' + targetAttributes + '>' + titleMarkup + '</a>';
            }

            return [
                '<div class="col-lg-3 col-md-6">',
                    '<div class="team-item wow fade-in-bottom" data-wow-delay="' + escapeHtml(getDelay(index, 100)) + 'ms">',
                        '<div class="team-thumb">',
                            imageMarkup,
                        '</div>',
                        '<div class="team-content">',
                            '<h4 class="position">' + escapeHtml(item.industry) + '</h4>',
                            '<h3>' + titleMarkup + '</h3>',
                        '</div>',
                    '</div>',
                '</div>'
            ].join("");
        }).join("");
    }

    function renderFaqAccordion(items, accordionId) {
        return items.map(function (item, index) {
            var headingId = accordionId + "-heading-" + item.id;
            var collapseId = accordionId + "-collapse-" + item.id;
            var isOpen = index === 0;

            return [
                '<div class="accordion-item' + (isOpen ? " active" : "") + '" data-faq-category="' + escapeHtml(item.category) + '">',
                    '<h2 class="accordion-header" id="' + escapeHtml(headingId) + '">',
                        '<button class="accordion-button' + (isOpen ? "" : " collapsed") + '" type="button" data-bs-toggle="collapse" data-bs-target="#' + escapeHtml(collapseId) + '" aria-expanded="' + (isOpen ? "true" : "false") + '" aria-controls="' + escapeHtml(collapseId) + '">' + escapeHtml(item.question) + '</button>',
                    '</h2>',
                    '<div id="' + escapeHtml(collapseId) + '" class="accordion-collapse collapse' + (isOpen ? " show" : "") + '" aria-labelledby="' + escapeHtml(headingId) + '" data-bs-parent="#' + escapeHtml(accordionId) + '">',
                        '<div class="accordion-body">',
                            "<p>" + escapeHtml(item.answer) + "</p>",
                        "</div>",
                    "</div>",
                "</div>"
            ].join("");
        }).join("");
    }

    function renderGalleryGrid(items) {
        return items.map(function (item, index) {
            var link = getGalleryCategoryLink(item.category, false);

            return [
                '<div class="col-md-4 col-sm-6">',
                    '<div class="project-item wow fade-in-bottom" data-wow-delay="' + escapeHtml(getDelay(index, 200)) + 'ms">',
                        '<div class="project-thumb project-view">',
                            '<a class="venobox" href="' + escapeHtml(item.image_path) + '" data-gall="projects"><img src="' + escapeHtml(item.image_path) + '" alt="' + escapeHtml(item.alt_text) + '"></a>',
                        '</div>',
                        '<div class="project-content">',
                            '<a href="' + escapeHtml(link) + '" class="category">' + escapeHtml(item.category) + '</a>',
                            '<h3><a href="' + escapeHtml(link) + '">' + escapeHtml(item.title) + '</a></h3>',
                        '</div>',
                    '</div>',
                '</div>'
            ].join("");
        }).join("");
    }

    function renderCareerGrid(items) {
        return items.map(function (item, index) {
            var detailItems = [
                renderCareerDetail("Location", item.location),
                renderCareerDetail("Employment Type", item.employment_type)
            ];
            var requirementsText = renderRequirements(item.requirements);
            var requirementsMarkup = requirementsText ? [
                '<div class="career-card__requirements">',
                    "<h4>Role Requirements</h4>",
                    '<ul class="check-list mb-0">' + requirementsText + "</ul>",
                "</div>"
            ].join("") : "";
            var applyLink = "career-application?job_id=" + encodeURIComponent(item.id);

            if (item.salary_range) {
                detailItems.push(renderCareerDetail("Salary", item.salary_range));
            }

            if (item.deadline) {
                detailItems.push(renderCareerDetail("Deadline", formatDate(item.deadline)));
            }

            return [
                '<div class="career-card-col">',
                    '<article class="career-card team-content mt-0 me-0 wow fade-in-bottom" data-wow-delay="' + escapeHtml(getDelay(index, 100)) + 'ms">',
                        '<h4 class="position">' + escapeHtml(item.department) + " / " + escapeHtml(item.employment_type) + "</h4>",
                        "<h3>" + escapeHtml(item.job_title) + "</h3>",
                        '<div class="career-card__details">' + detailItems.join("") + "</div>",
                        '<div class="career-card__summary">' + formatParagraphs(item.job_description) + "</div>",
                        requirementsMarkup,
                        '<div class="career-card__actions">',
                            '<a href="' + escapeHtml(applyLink) + '" class="default-btn">Apply Now</a>',
                        "</div>",
                    "</article>",
                "</div>"
            ].join("");
        }).join("");
    }

    function renderCareerEmptyState(title, message) {
        var listingsGrid = $("#careers-listings-grid");
        var emptyTitle = title || "No Open Roles Right Now";
        var emptyMessage = message || "We do not have any active openings at the moment. Please check back later or contact us if you would like to share your CV for future consideration.";

        $("#careers-heading-title").html('No Open Roles <br><span class="hl">Right Now</span>');
        $("#careers-heading-copy").html("We will publish new opportunities here as soon as they become available.");
        listingsGrid.addClass("justify-content-center").html(
            '<div class="career-card-col career-card-col--empty">' +
                '<div class="team-content text-center mt-0 me-0">' +
                    '<div class="mb-4"><i class="fa-regular fa-circle-xmark" style="font-size: 64px; color: var(--primary-color);"></i></div>' +
                    "<h3 class=\"mb-3\">" + escapeHtml(emptyTitle) + "</h3>" +
                    "<p class=\"mb-4\">" + escapeHtml(emptyMessage) + "</p>" +
                    '<a href="contact" class="default-btn">Contact Our Team</a>' +
                "</div>" +
            "</div>"
        );
    }

    function renderRequirements(value) {
        var items = splitRequirements(value);

        if (!items.length) {
            return "";
        }

        return items.map(function (item) {
            return '<li><i class="fa-solid fa-check"></i>' + escapeHtml(item) + "</li>";
        }).join("");
    }

    function splitRequirements(value) {
        var items = String(value || "").split(/\r?\n|;/).map(function (item) {
            return item.trim();
        }).filter(function (item) {
            return item !== "";
        });

        return items;
    }

    function renderCareerDetail(label, value) {
        return '<p class="career-card__detail"><strong>' + escapeHtml(label) + ":</strong> " + escapeHtml(value) + "</p>";
    }

    function renderGridEmptyState(title, message) {
        return [
            '<div class="col-12">',
                '<div class="team-content text-center mt-0 me-0">',
                    "<h3>" + escapeHtml(title) + "</h3>",
                    "<p>" + escapeHtml(message) + "</p>",
                "</div>",
            "</div>"
        ].join("");
    }

    function renderAccordionEmptyState(title, message) {
        return [
            '<div class="team-content text-center mt-0 me-0">',
                "<h3>" + escapeHtml(title) + "</h3>",
                "<p>" + escapeHtml(message) + "</p>",
            "</div>"
        ].join("");
    }

    function renderCarouselEmptySlide(title, message) {
        return [
            '<div class="swiper-slide">',
                '<div class="team-content text-center mt-0 me-0">',
                    "<h3>" + escapeHtml(title) + "</h3>",
                    "<p>" + escapeHtml(message) + "</p>",
                "</div>",
            "</div>"
        ].join("");
    }

    function formatParagraphs(value) {
        return escapeHtml(String(value || "")).replace(/\r?\n/g, "<br>");
    }

    function getGalleryCategoryLink(category, useGalleryFallback) {
        var value = String(category || "").toLowerCase();

        if (value.indexOf("office") !== -1) {
            return "corporate-office-relocation.html";
        }

        if (value.indexOf("packing") !== -1 || value.indexOf("storage") !== -1) {
            return "storage-packing-solutions.html";
        }

        if (value.indexOf("long-distance") !== -1 || value.indexOf("transport") !== -1 || value.indexOf("intercity") !== -1) {
            return "long-distance-intercity-moves.html";
        }

        if (value.indexOf("residential") !== -1 || value.indexOf("home") !== -1) {
            return useGalleryFallback ? "gallery.html" : "residential-relocations.html";
        }

        return "gallery.html";
    }

    function getClientLink(item) {
        if (item && item.testimonial_anchor) {
            return "testimonials.html#" + String(item.testimonial_anchor);
        }

        if (item && item.client_website) {
            return String(item.client_website);
        }

        return "";
    }

    function getClientLinkAttributes(item) {
        if (item && item.client_website && !item.testimonial_anchor) {
            return ' target="_blank" rel="noopener noreferrer"';
        }

        return "";
    }

    function getStringFilter(name) {
        return String(queryParams.get(name) || "").trim();
    }

    function getBooleanFilter(name) {
        var value = String(queryParams.get(name) || "").trim().toLowerCase();

        if (!value) {
            return null;
        }

        if (value === "1" || value === "true" || value === "yes") {
            return true;
        }

        if (value === "0" || value === "false" || value === "no") {
            return false;
        }

        return null;
    }

    function refreshProjectUi() {
        callUi("initProjectCarousel");
        callUi("initVenoBox");
        callUi("initWow");
    }

    function refreshTestimonialUi() {
        callUi("initTestimonialCarousel");
        callUi("initWow");
    }

    function callUi(methodName) {
        if (window.KwikshiftUi && typeof window.KwikshiftUi[methodName] === "function") {
            window.KwikshiftUi[methodName]();
        }
    }

    function hasItems(response) {
        return !!(response && response.ok === true && Array.isArray(response.data) && response.data.length);
    }

    function getDelay(index, baseDelay) {
        return baseDelay + (index * 200);
    }

    function normalizeRating(value) {
        var rating = parseFloat(value);

        if (window.isNaN(rating) || rating < 0) {
            rating = 0;
        }

        if (rating > 5) {
            rating = 5;
        }

        return Math.round(rating * 2) / 2;
    }

    function renderStars(value) {
        var rating = normalizeRating(value);
        var stars = "";
        var index;

        for (index = 1; index <= 5; index += 1) {
            if (rating >= index) {
                stars += '<li><i class="fa-solid fa-star"></i></li>';
                continue;
            }

            if (rating >= index - 0.5) {
                stars += '<li><i class="fa-solid fa-star-half-stroke"></i></li>';
                continue;
            }

            stars += '<li><i class="fa-regular fa-star"></i></li>';
        }

        return stars;
    }

    function formatRating(value) {
        var rating = normalizeRating(value);

        if (Math.floor(rating) === rating) {
            return rating.toFixed(0);
        }

        return rating.toFixed(1);
    }

    function formatDate(value) {
        var formatted = new Date(String(value) + "T00:00:00");

        if (window.isNaN(formatted.getTime())) {
            return String(value || "");
        }

        return formatted.toLocaleDateString("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric"
        });
    }

    function escapeHtml(value) {
        return String(value == null ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
})(jQuery);

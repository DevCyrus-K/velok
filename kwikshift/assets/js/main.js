/*===================================================
    Template Scripts
====================================================*/
(function ($) {
    "use strict";

    // Smooth Animation
    if (typeof window.Lenis === "function") {
        const lenis = new window.Lenis({
            smooth: true,
            lerp: 0.1, // Adjust smoothness
            gestureOrientation: "vertical",
            normalizeWheel: true,
            smoothTouch: true, // Enables smooth scrolling on touch devices
            touchMultiplier: 2 // Adjust sensitivity
        });

        lenis.on('scroll', function () {});
        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);
    }

    // Preloader
    $(window).on('load', function () {
        var loaderTl = gsap.timeline(),
            svgCurve = "M0 502S175 272 500 272s500 230 500 230V0H0Z",
            svgFlat = "M0 2S175 1 500 1s500 1 500 1V0H0Z";

        loaderTl.to("#preloader-wrap .site-preloader", {
            delay: 1.5,
            y: -70,
            opacity: 0,
        });

        loaderTl.to("#preloader-bg", {
            duration: 0.7,
            attr: {
                d: svgCurve
            },
            ease: "power2.easeIn"
        }).to("#preloader-bg", {
            duration: 0.7,
            attr: {
                d: svgFlat
            },
            ease: "power2.easeOut"
        });

        loaderTl.to("#preloader-wrap", {
            y: -1500
        });

        setTimeout(function () {
            $('#preloader-wrap').remove();
        }, 5000);
    });

    $(document).ready(function () {

        var html = $('html');

        // Animation Effect
        function textAnimation() {
            let elSectionHeadings = gsap.utils.toArray(".section-heading");
            elSectionHeadings.forEach(elSection => {
                let textAnimations = gsap.utils.toArray(elSection.getElementsByClassName("text-anim"));
                let borderAnim = gsap.utils.toArray(elSection.getElementsByClassName("border-anim"));
                textAnimations.forEach((textAnim, i) => {
                    let hl = textAnim.getElementsByClassName("hl");
                    let target = textAnim;
                    let effect = "effect-3d",
                        duration = 1,
                        delay = 0.3,
                        split = "none",
                        ease = "Back.easeOut",
                        trigger = textAnim,
                        stagger = 0.3;
                    if (textAnim.getAttribute("data-effect")) {
                        effect = textAnim.getAttribute("data-effect");
                    }
                    if (textAnim.getAttribute("data-duration")) {
                        duration = textAnim.getAttribute("data-duration");
                    }
                    if (textAnim.getAttribute("data-delay")) {
                        delay = textAnim.getAttribute("data-delay");
                    }
                    if (textAnim.getAttribute("data-stagger")) {
                        stagger = textAnim.getAttribute("data-stagger");
                    }
                    if (textAnim.getAttribute("data-split")) {
                        split = textAnim.getAttribute("data-split");
                    }
                    if (textAnim.getAttribute("data-ease")) {
                        ease = textAnim.getAttribute("data-ease");
                    }
                    if (textAnim.getAttribute("data-scroll-trigger")) {
                        trigger = textAnim.getAttribute("data-scroll-trigger");
                    }
                    if ("none" != split) {
                        let itemSplitted = new SplitType(textAnim, {
                            types: 'chars, words, lines'
                        });
                        target = textAnim.getElementsByClassName(split);
                    }

                    const tl = gsap.timeline({
                        scrollTrigger: {
                            trigger: trigger,
                            scrub: false,
                            markers: false,
                            toggleActions: "play none none none",
                            start: "top 90%"
                        }
                    });

                    if ('effect-3d' === effect) {
                        gsap.set(textAnim, {
                            perspective: 400
                        });
                        tl.from(textAnim.getElementsByClassName(split), {
                            duration: duration,
                            delay: delay,
                            opacity: 0,
                            rotationX: -80,
                            force3D: true,
                            transformOrigin: "top center -50",
                            stagger: 0.1
                        });
                    }

                    if ('clip-text' === effect) {
                        gsap.set(textAnim, {
                            overflow: "hidden"
                        });
                        gsap.set(textAnim.getElementsByClassName('line'), {
                            overflow: "hidden"
                        });
                        tl.from(textAnim.getElementsByClassName(split), {
                            duration: duration,
                            delay: delay,
                            opacity: 0,
                            rotationX: -80,
                            force3D: true,
                            transformOrigin: "top center -50",
                            stagger: 0.1
                        });
                    }

                    if ('fade-in' === effect) {
                        if (device_width < 1023) {
                            gsap.set(textAnim, {
                                opacity: 0,
                                y: 60
                            });
                            tl.to(target, {
                                opacity: 1,
                                y: 0,
                                duration: duration,
                                delay: delay * i,
                                ease: ease
                            });
                        } else {
                            gsap.set(target, {
                                opacity: 0,
                                y: 40
                            })
                            tl.to(target, {
                                opacity: 1,
                                y: 0,
                                duration: duration,
                                ease: ease,
                                delay: delay * i,
                                stagger: 0.3
                            });
                        }
                    }
                    if ('fade-in-left' === effect) {
                        gsap.set(target, {
                            x: -20,
                            opacity: 0,
                        });
                        tl.to(target, {
                            x: 0,
                            opacity: 1,
                            ease: ease,
                            duration: duration,
                            delay: delay * i,
                            stagger: {
                                each: 0.02,
                            }
                        });
                    }

                    if ('fade-in-right' === effect) {
                        gsap.set(target, {
                            x: 50,
                            opacity: 0,
                        });
                        tl.to(target, {
                            x: 0,
                            opacity: 1,
                            ease: ease,
                            duration: duration,
                            delay: delay * i,
                            stagger: {
                                each: 0.02,
                            }
                        });
                    }

                    if ('fade-in-top' === effect) {
                        gsap.set(target, {
                            y: -20,
                            opacity: 0,
                        });
                        tl.to(target, {
                            y: 0,
                            opacity: 1,
                            ease: ease,
                            duration: duration,
                            delay: delay * i,
                            stagger: {
                                each: 0.02,
                            }
                        });
                    }

                    if ('fade-in-bottom' === effect) {
                        gsap.set(target, {
                            y: 20,
                            opacity: 0,
                        });
                        tl.to(target, {
                            y: 0,
                            opacity: 1,
                            ease: ease,
                            duration: duration,
                            delay: delay * i,
                            stagger: {
                                each: stagger,
                            }
                        });
                    }

                    if ('zoom-in' === effect) {
                        gsap.set(target, {
                            opacity: 0,
                            scale: 0.5
                        });
                        tl.to(target, {
                            opacity: 1,
                            scale: 1,
                            x: 20,
                            ease: ease,
                            duration: duration,
                            delay: delay * i,
                            stagger: {
                                each: 0.3
                            }
                        });
                    }

                    if (hl.length) {
                        tl.to(hl, {
                        ["--hl-bd-width"]: "100%",
                            ease: "power1.in",
                            duration: 0.8,
                            delay: 0.2,
                            stagger: {
                                each: 0.02,
                            }
                        }, "-=1");
                    }
                });

                borderAnim.forEach(borderAnim => {
                    let bdLine = borderAnim.getElementsByClassName('sh-underline'),
                        dbTruck = borderAnim.getElementsByClassName('sh-truck');
                    gsap.set(bdLine, {
                        width: "0%"
                    });
                    gsap.set(dbTruck, {
                        opacity: 0,
                        right: "20px"
                    });

                    let shTl = gsap.timeline({
                        scrollTrigger: {
                            trigger: borderAnim,
                            start: "top 90%",
                            toggleActions: "play none none none"
                        }
                    });

                    shTl.to(bdLine, {
                        width: "100%",
                        duration: 1,
                        ease: "power1.in"
                    });

                    shTl.to(dbTruck, {
                        opacity: 1,
                        right: 0,
                        duration: 1,
                        delay: -0.08,
                        ease: "Back.easeOut"
                    });

                });
            });
        }

        // Responsive Classes
        function responsiveClasses() {
            var body = $("body");
            if ($(window).width() < 992) {
                body.removeClass("viewport-lg");
                body.addClass("viewport-sm");
            } else {
                body.removeClass("viewport-sm");
                body.addClass("viewport-lg");
            }
        }

        // Window Resize
        $(window).on("resize", function () {
            responsiveClasses();
        }).resize();

        // Sticky Header
        function stickyHeader() {
            var mainHeader = $(".main-header"),
                headerClone = mainHeader.clone();
            mainHeader.after('<div class="sticky-header"></div>');
            $(".sticky-header").html(headerClone);

            var headerSelector = document.querySelector(".sticky-header"),
                triggerPoint = mainHeader.height(),
                yOffset = 0;

            $(window).on("scroll", function () {
                yOffset = $(window).scrollTop();
                if (yOffset >= triggerPoint) {
                    $(".sticky-header").addClass("sticky-fixed-top");
                } else {
                    $(".sticky-header").removeClass("sticky-fixed-top");
                }
            });
        }

        if ($(window).width() > 0) {
            stickyHeader();
        }

        // Mobile Menu
        function mobileMenu() {
            $("header.main-header").after('<div class="mobile-navigation-menu"><button id="mobile-menu-close"><i class="fa-regular fa-xmark"></i></button></div>');
            var menuWrapper = $("header.main-header").first().find(".header-menu-wrap .nav-menu").first().clone();
            $('.mobile-navigation-menu #mobile-menu-close').after(menuWrapper);

            $("#mobile-menu-close, .mobile-menu-icon").on("click", function () {
                $(".mobile-menu-icon").toggleClass("menu-open");
                if ($(".mobile-menu-icon").hasClass("menu-open")) {
                    $(".mobile-menu-icon i").removeClass("fa-bars").addClass("fa-xmark");
                } else {
                    $(".mobile-menu-icon i").removeClass("fa-xmark").addClass("fa-bars");
                }
                $(".mobile-navigation-menu").toggleClass("open-mobile-menu");
            });

            $(".mobile-navigation-menu ul li:has(ul)").each(function () {
                $(this).append('<span class="dropdown-plus"></span>');
                $(this).addClass("dropdown_menu");
            });

            $(".mobile-navigation-menu .dropdown-plus").on("click", function () {
                $(this).prev("ul").slideToggle(300);
                $(this).toggleClass("dropdown-open");
                $(this).parent().toggleClass("dropdown-open");
            });
        }

        mobileMenu();

        function ensureCookieConsentSupport() {
            var hasCookieStyles = document.querySelector('link[href*="cookie-consent.css"]');

            if (!hasCookieStyles && document.head) {
                var stylesheet = document.createElement("link");
                stylesheet.rel = "stylesheet";
                stylesheet.href = "assets/css/cookie-consent.css";
                document.head.appendChild(stylesheet);
            }

            if (window.KwikshiftCookieConsent && typeof window.KwikshiftCookieConsent.init === "function") {
                window.KwikshiftCookieConsent.init();
                return;
            }

            if (document.querySelector('script[data-cookie-consent-loader="dynamic"]')) {
                return;
            }

            var script = document.createElement("script");
            script.src = "assets/js/cookie-consent.js";
            script.setAttribute("data-cookie-consent-loader", "dynamic");
            document.body.appendChild(script);
        }

        ensureCookieConsentSupport();

        // Popup Search Box
        $(function () {
            $("#popup-search-box").removeClass("toggled");
            $("body").removeClass("open-search-box");
            $(".dl-search-icon").on("click", function (e) {
                e.stopPropagation();
                $("body").toggleClass("open-search-box");
                $("#popup-search").focus();
            });

            $("#popup-search-box input").on("click", function (e) {
                e.stopPropagation();
            });
            $(document).on(
                "click",
                ".search-close, #searchbox-overlay",
                function (e) {
                    e.preventDefault();
                    $("body.open-search-box").removeClass("open-search-box");
                }
            );
        });

        // Do Slider Animation
        function doAnimations(elements) {
            var animationEndEvents = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
            elements.each(function () {
                var $this = $(this);
                var $animationDelay = $this.data('delay');
                var $animationDuration = $this.data('duration');
                var $animationType = 'animated ' + $this.data('animation');
                $this.css({
                    'animation-delay': $animationDelay,
                    '-webkit-animation-delay': $animationDelay,
                    'animation-duration': $animationDuration
                });
                $this.addClass($animationType).one(animationEndEvents, function () {
                    $this.removeClass($animationType);
                });
            });
        }

        // Main Slider
        var mainSlider;
        var sliderOptions = {
            speed: 1500,
            autoplay: {
                delay: 5000
            },
            mousewheel: false,
            loop: true,
            effect: 'fade',
            initialSlide: 2,
            pagination: {
                el: ".slider-pagination",
                clickable: true,
            },
            navigation: false,
        };

        sliderOptions.on = {
            slideChangeTransitionStart: function () {
                var swiper = this;
                var animatingElements = $(swiper.slides[swiper.activeIndex]).find('[data-animation]');
                doAnimations(animatingElements);
            },
            resize: function () {
                this.update();
            }
        };

        mainSlider = new Swiper('.main-slider', sliderOptions);

        //Running Animated Text
        const scrollers = document.querySelectorAll(".scroller");

        if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            addAnimation();
        }

        function addAnimation() {
            scrollers.forEach((scroller) => {
                scroller.setAttribute("data-animated", true);

                const scrollerInner = scroller.querySelector(".scroller__inner");
                const scrollerContent = Array.from(scrollerInner.children);

                scrollerContent.forEach((item) => {
                    const duplicatedItem = item.cloneNode(true);
                    duplicatedItem.setAttribute("aria-hidden", true);
                    scrollerInner.appendChild(duplicatedItem);
                });
            });
        }

        function initProjectCarousel() {
            var carouselElement = document.querySelector(".project-carousel");
            var carouselWrapper = carouselElement ? carouselElement.closest(".swiper-outside") : null;
            var options = {
                slidesPerView: 3,
                spaceBetween: 20,
                slidesPerGroup: 1,
                loop: true,
                autoplay: false,
                speed: 400,
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        slidesPerGroup: 1,
                        spaceBetween: 25
                    },
                    767: {
                        slidesPerView: 2,
                        slidesPerGroup: 1,
                        spaceBetween: 30
                    },
                    1024: {
                        slidesPerView: 4,
                        slidesPerGroup: 1
                    }
                }
            };

            if (!carouselElement || typeof Swiper !== "function" || !carouselElement.querySelector(".swiper-slide")) {
                return null;
            }

            if (carouselElement.querySelector(".carousel-pagination")) {
                options.pagination = {
                    el: carouselElement.querySelector(".carousel-pagination"),
                    clickable: true,
                };
            }

            if (carouselWrapper && carouselWrapper.querySelector(".swiper-next") && carouselWrapper.querySelector(".swiper-prev")) {
                options.navigation = {
                    nextEl: carouselWrapper.querySelector(".swiper-next"),
                    prevEl: carouselWrapper.querySelector(".swiper-prev")
                };
            }

            if (carouselElement.swiper && typeof carouselElement.swiper.destroy === "function") {
                carouselElement.swiper.destroy(true, true);
            }

            return new Swiper(carouselElement, options);
        }

        function initTestimonialCarousel() {
            var carouselElement = document.querySelector(".testimonial-carousel");
            var carouselWrapper = carouselElement ? carouselElement.closest(".swiper-outside") : null;
            var options = {
                slidesPerView: 2,
                spaceBetween: 20,
                slidesPerGroup: 1,
                loop: true,
                autoplay: false,
                speed: 400,
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        slidesPerGroup: 1,
                        spaceBetween: 25
                    },
                    767: {
                        slidesPerView: 1,
                        slidesPerGroup: 1,
                        spaceBetween: 30
                    },
                    1024: {
                        slidesPerView: 2,
                        slidesPerGroup: 1
                    }
                }
            };

            if (!carouselElement || typeof Swiper !== "function" || !carouselElement.querySelector(".swiper-slide")) {
                return null;
            }

            if (carouselElement.querySelector(".carousel-pagination")) {
                options.pagination = {
                    el: carouselElement.querySelector(".carousel-pagination"),
                    clickable: true,
                };
            }

            if (carouselWrapper && carouselWrapper.querySelector(".swiper-next") && carouselWrapper.querySelector(".swiper-prev")) {
                options.navigation = {
                    nextEl: carouselWrapper.querySelector(".swiper-next"),
                    prevEl: carouselWrapper.querySelector(".swiper-prev")
                };
            }

            if (carouselElement.swiper && typeof carouselElement.swiper.destroy === "function") {
                carouselElement.swiper.destroy(true, true);
            }

            return new Swiper(carouselElement, options);
        }

        function initSponsorCarousel() {
            var carouselElement = document.querySelector(".sponsor-carousel");
            var options = {
                spaceBetween: 60,
                slidesPerGroup: 1,
                loop: true,
                autoplay: true,
                speed: 1500,
                breakpoints: {
                    320: {
                        slidesPerView: 2,
                        slidesPerGroup: 1,
                        spaceBetween: 20
                    },
                    767: {
                        slidesPerView: 2,
                        slidesPerGroup: 1,
                        spaceBetween: 50
                    },
                    1024: {
                        slidesPerView: 5,
                        slidesPerGroup: 1
                    }
                }
            };

            if (!carouselElement || typeof Swiper !== "function" || !carouselElement.querySelector(".swiper-slide")) {
                return null;
            }

            if (carouselElement.querySelector(".swiper-pagination")) {
                options.pagination = {
                    el: carouselElement.querySelector(".swiper-pagination"),
                    clickable: true,
                };
            }

            if (carouselElement.swiper && typeof carouselElement.swiper.destroy === "function") {
                carouselElement.swiper.destroy(true, true);
            }

            return new Swiper(carouselElement, options);
        }

        function initVenoBox() {
            if (typeof $.fn.venobox !== "function") {
                return;
            }

            $('.venobox').venobox({
                bgcolor: 'transparent',
                spinner: 'spinner-pulse',
                numeration: true,
                infinigall: true
            });
        }

        function initWow() {
            if (typeof WOW === "function") {
                new WOW().init();
            }
        }

        window.KwikshiftUi = window.KwikshiftUi || {};
        window.KwikshiftUi.initProjectCarousel = initProjectCarousel;
        window.KwikshiftUi.initTestimonialCarousel = initTestimonialCarousel;
        window.KwikshiftUi.initSponsorCarousel = initSponsorCarousel;
        window.KwikshiftUi.initVenoBox = initVenoBox;
        window.KwikshiftUi.initWow = initWow;

        initProjectCarousel();
        initTestimonialCarousel();
        initSponsorCarousel();

        // Accordion
        $(document).on('click', '.faq-accordion .accordion-item', function () {
            var accordion = $(this).closest('.faq-accordion');
            accordion.find('.accordion-item.active').removeClass('active');
            $(this).addClass('active');
        });

        // Funfact Counter
        $('.counter-item').waypoint(
            function () {
                var odo = $(".counter-item .odometer");
                odo.each(function () {
                    var countNumber = $(this).attr("data-count");
                    $(this).html(countNumber);
                });
            }, {
                offset: "80%",
                triggerOnce: true
            }
        );

        //Gsap Text Animation
        textAnimation();

        // Wow JS Active
        initWow();

        // Nice Select Active
        $('select').niceSelect();

        // Current Year
        var currentYear = new Date().getFullYear();
        // Hero Slider
        if (document.querySelector('.hero-slider')) {
            const heroSlider = new Swiper('.hero-slider', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.hero-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 1000,
                on: {
                    slideChangeTransitionEnd: function() {
                        // Reinitialize text animations on slide change
                        if (typeof textAnimation === 'function') {
                            textAnimation();
                        }
                    }
                }
            });
        }

        $('#currentYear').append(currentYear);

        // Scrool To Top
        var scrollTop = $("#scroll-top");
        $(window).on('scroll', function () {
            var topPos = $(this).scrollTop();
            if (topPos > 100) {
                $('#scrollup').removeClass('hide');
                $('#scrollup').addClass('show');

            } else {
                $('#scrollup').removeClass('show');
                $('#scrollup').addClass('hide');
            }
        });

        scrollTop.on("click", function () {
            document.documentElement.scrollTo({
				top: 0,
				behavior: "smooth"
			});
        });

        // Custom Cursor
        function customCursor() {
            $('body').append('<div class="dl-cursor"></div>');
            var cursor = $('.dl-cursor'),
                projectView = $('.project-view');

            $(window).on('mousemove', function (e) {
                cursor.css({
                    'transform': 'translate(' + (e.clientX - 5) + 'px,' + (e.clientY - 5) + 'px)',
                    'visibility': 'inherit'
                });
            });

            $(window).on('mouseout', function () {
                cursor.css('visibility', 'hidden');
            });
            projectView.each(function () {
                $(this).on('mouseleave', function () {
                    cursor.removeClass('expand');
                });
                $(this).on('mouseover', function () {
                    cursor.addClass('expand');
                });
            });
        }

        if ($('body').hasClass('viewport-lg')) {
            customCursor();
        }

        // Venobox Active
        initVenoBox();

    });

})(jQuery);

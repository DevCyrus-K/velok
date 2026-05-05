(function () {
    const COOKIE_STYLESHEET_PATH = "assets/css/cookie-consent.css";
    const COOKIE_MARKUP = `
    <div id="cookieConsent" class="cookie-consent-modal" role="dialog" aria-label="Cookie consent" aria-modal="true" aria-hidden="true">
        <div class="cookie-container">
            <div class="cookie-header">
                <h3>Your Privacy Matters</h3>
            </div>
            <div class="cookie-message">
                <p>We use cookies to enhance your experience, analyze traffic and improve our services. Select "Accept All" to consent in line with our <a href="cookie-policy" target="_blank" rel="noopener noreferrer">Cookie Policy</a> and <a href="privacy-policy" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.</p>
            </div>
            <div class="cookie-buttons">
                <button class="cookie-btn cookie-btn-primary" id="acceptAll">Accept All</button>
                <button class="cookie-btn cookie-btn-outline" id="managePreferences">Manage Preferences</button>
                <button class="cookie-btn cookie-btn-light" id="rejectAll">Reject All</button>
            </div>
        </div>
    </div>

    <div id="cookiePreferences" class="cookie-preferences-modal" role="dialog" aria-label="Cookie preferences" aria-modal="true" aria-hidden="true">
        <div class="cookie-preferences-content">
            <div class="cookie-preferences-header">
                <h3>Cookie Preferences</h3>
                <button class="cookie-close" id="preferencesClose" aria-label="Close preferences">&times;</button>
            </div>

            <div class="cookie-tabs" role="tablist" aria-label="Cookie preference sections">
                <button class="cookie-tab active" id="cookie-tab-consent" data-tab="consent" aria-selected="true" role="tab">Consent</button>
                <button class="cookie-tab" id="cookie-tab-details" data-tab="details" aria-selected="false" role="tab">Details</button>
                <button class="cookie-tab" id="cookie-tab-about" data-tab="about" aria-selected="false" role="tab">About</button>
            </div>

            <div class="cookie-tab-content active" id="consent-tab" role="tabpanel" aria-labelledby="cookie-tab-consent">
                <div class="cookie-message">
                    <p>Choose which cookies you accept. Necessary cookies are required for the site to function and cannot be disabled.</p>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Necessary Cookies <span class="status-badge">Required</span></h4>
                        <p>These cookies help make the website usable by enabling basic functions like page navigation, web security, and access to protected areas.</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="necessaryCookies" checked disabled aria-label="Necessary cookies - required">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Preferences</h4>
                        <p>These cookies remember useful settings and improve how the website behaves for returning visitors.</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="preferencesCookies" aria-label="Preference cookies">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Statistics and Performance</h4>
                        <p>These cookies help us understand how visitors interact with the website so we can improve performance and content.</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="statisticsCookies" aria-label="Statistics cookies">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Marketing</h4>
                        <p>These cookies help us tailor promotions and measure campaign performance in a more relevant way.</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="marketingCookies" aria-label="Marketing cookies">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="cookie-separator"></div>

                <div class="cookie-message">
                    <p>Unclassified cookies are cookies that we are still reviewing together with their individual providers.</p>
                </div>

                <div class="cookie-separator"></div>

                <div class="cookie-card">
                    <div class="cookie-card-header" id="domainDropdown" aria-expanded="false" aria-controls="domainContent">
                        <h5>Cross-domain consent</h5>
                        <i class="fa-regular fa-angle-down"></i>
                    </div>
                    <div class="cookie-card-content" id="domainContent" role="region" aria-labelledby="domainDropdown" hidden>
                        <p><strong>List of domains your consent applies to:</strong></p>
                        <p>kwikshiftmovers.co.ke</p>
                        <p>www.kwikshiftmovers.co.ke</p>
                    </div>
                </div>
            </div>

            <div class="cookie-tab-content" id="details-tab" role="tabpanel" aria-labelledby="cookie-tab-details" hidden>
                <div class="cookie-message">
                    <p>Detailed information about the cookies used on our website.</p>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Necessary Cookies</h4>
                        <p>These cookies are essential for page navigation, security, and access to secure areas of the website. The website cannot function properly without them.</p>
                    </div>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Preference Cookies</h4>
                        <p>Preference cookies let the website remember information that changes how the website behaves or looks, such as your saved settings.</p>
                    </div>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Statistics Cookies</h4>
                        <p>Statistics cookies help us understand how visitors interact with the website by collecting information anonymously.</p>
                    </div>
                </div>

                <div class="cookie-toggle">
                    <div class="toggle-label">
                        <h4>Marketing Cookies</h4>
                        <p>Marketing cookies help measure campaign effectiveness and show content that is more relevant to users.</p>
                    </div>
                </div>
            </div>

            <div class="cookie-tab-content" id="about-tab" role="tabpanel" aria-labelledby="cookie-tab-about" hidden>
                <div class="cookie-message">
                    <p>Cookies are small text files that can be used by websites to make a user's experience more efficient.</p>
                    <p>The law allows us to store cookies on your device if they are strictly necessary for the operation of the site. For all other cookie types, we need your permission.</p>
                    <p>You can change or withdraw your consent at any time using the cookie preference controls available on this website.</p>
                    <p>You can find more detailed information in our <a href="cookie-policy" target="_blank" rel="noopener noreferrer">Cookie Policy</a> and our <a href="privacy-policy" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.</p>
                </div>
            </div>

            <div class="cookie-preferences-footer">
                <div class="cookie-buttons">
                    <button class="cookie-btn cookie-btn-outline" id="savePreferences">Save Preferences</button>
                    <button class="cookie-btn cookie-btn-primary" id="acceptAllPref">Accept All</button>
                </div>
                <div>
                    <button class="cookie-btn cookie-btn-light" id="rejectAllPref">Reject All</button>
                </div>
            </div>
        </div>
    </div>`;

    function ensureCookieStylesheet() {
        const hasStylesheet = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some((link) => {
            return (link.getAttribute("href") || "").indexOf("cookie-consent.css") !== -1;
        });

        if (hasStylesheet || !document.head) {
            return;
        }

        const stylesheet = document.createElement("link");
        stylesheet.rel = "stylesheet";
        stylesheet.href = COOKIE_STYLESHEET_PATH;
        document.head.appendChild(stylesheet);
    }

    function ensureCookieMarkup() {
        if (document.getElementById("cookieConsent") && document.getElementById("cookiePreferences")) {
            return;
        }

        const footer = document.querySelector("footer.footer-section");

        if (footer) {
            footer.insertAdjacentHTML("beforebegin", COOKIE_MARKUP);
        } else if (document.body) {
            document.body.insertAdjacentHTML("beforeend", COOKIE_MARKUP);
        }
    }

    function removeCookieMenuItems() {
        document.querySelectorAll(".cookie-settings-menu-item").forEach((item) => {
            item.remove();
        });
    }

    function ensureCookieMenuItems() {
        removeCookieMenuItems();
    }

    function closeMobileMenuIfOpen() {
        const mobileMenu = document.querySelector(".mobile-navigation-menu");
        const mobileMenuIcon = document.querySelector(".mobile-menu-icon");
        const mobileMenuIconGraphic = mobileMenuIcon ? mobileMenuIcon.querySelector("i") : null;

        if (mobileMenu) {
            mobileMenu.classList.remove("open-mobile-menu");
        }

        if (mobileMenuIcon) {
            mobileMenuIcon.classList.remove("menu-open");
        }

        if (mobileMenuIconGraphic) {
            mobileMenuIconGraphic.classList.remove("fa-xmark");
            mobileMenuIconGraphic.classList.add("fa-bars");
        }
    }

    function initCookieConsent() {
        ensureCookieStylesheet();
        ensureCookieMarkup();
        ensureCookieMenuItems();

        if (window.__kwikshiftCookieConsentInitialized) {
            return;
        }

        const cookieModal = document.getElementById("cookieConsent");
        const preferencesModal = document.getElementById("cookiePreferences");

        if (!cookieModal || !preferencesModal) {
            return;
        }

        window.__kwikshiftCookieConsentInitialized = true;

        const cookieConsent = getCookie("cookieConsent");
        const acceptAllBtn = document.getElementById("acceptAll");
        const rejectAllBtn = document.getElementById("rejectAll");
        const managePrefBtn = document.getElementById("managePreferences");
        const preferencesCloseBtn = document.getElementById("preferencesClose");
        const savePrefBtn = document.getElementById("savePreferences");
        const acceptAllPrefBtn = document.getElementById("acceptAllPref");
        const rejectAllPrefBtn = document.getElementById("rejectAllPref");
        const domainDropdown = document.getElementById("domainDropdown");
        const domainContent = document.getElementById("domainContent");
        const tabs = document.querySelectorAll(".cookie-tab");
        const tabContents = document.querySelectorAll(".cookie-tab-content");
        const necessaryCookies = document.getElementById("necessaryCookies");
        const preferencesCookies = document.getElementById("preferencesCookies");
        const statisticsCookies = document.getElementById("statisticsCookies");
        const marketingCookies = document.getElementById("marketingCookies");

        function showCookieModal() {
            cookieModal.classList.add("active");
            cookieModal.setAttribute("aria-hidden", "false");
            document.body.classList.add("cookie-modal-open");
        }

        function hideCookieModal() {
            cookieModal.classList.remove("active");
            cookieModal.setAttribute("aria-hidden", "true");

            if (!preferencesModal.classList.contains("active")) {
                document.body.classList.remove("cookie-modal-open");
            }

            setCookie("cookieConsentShown", "true", 30);
        }

        function showPreferencesModal() {
            closeMobileMenuIfOpen();
            cookieModal.classList.remove("active");
            cookieModal.setAttribute("aria-hidden", "true");
            preferencesModal.classList.add("active");
            preferencesModal.setAttribute("aria-hidden", "false");
            document.body.classList.add("cookie-modal-open");
            loadPreferences();
        }

        function hidePreferencesModal() {
            preferencesModal.classList.remove("active");
            preferencesModal.setAttribute("aria-hidden", "true");

            if (getCookie("cookieConsent")) {
                document.body.classList.remove("cookie-modal-open");
            } else {
                showCookieModal();
            }
        }

        function setCookie(name, value, days) {
            let expires = "";

            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }

            const secureAttribute = window.location && window.location.protocol === "https:" ? "; Secure" : "";

            document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax" + secureAttribute;
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(";");

            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];

                while (c.charAt(0) === " ") {
                    c = c.substring(1, c.length);
                }

                if (c.indexOf(nameEQ) === 0) {
                    return c.substring(nameEQ.length, c.length);
                }
            }

            return null;
        }

        function acceptAllCookies() {
            setCookie("cookieConsent", "all", 365);
            setCookie("necessaryCookies", "true", 365);
            setCookie("preferencesCookies", "true", 365);
            setCookie("statisticsCookies", "true", 365);
            setCookie("marketingCookies", "true", 365);

            preferencesCookies.checked = true;
            statisticsCookies.checked = true;
            marketingCookies.checked = true;

            hideCookieModal();
            hidePreferencesModalSilently();

            document.dispatchEvent(new CustomEvent("cookiesAccepted"));
        }

        function rejectAllCookies() {
            setCookie("cookieConsent", "necessary", 365);
            setCookie("necessaryCookies", "true", 365);
            setCookie("preferencesCookies", "false", 365);
            setCookie("statisticsCookies", "false", 365);
            setCookie("marketingCookies", "false", 365);

            preferencesCookies.checked = false;
            statisticsCookies.checked = false;
            marketingCookies.checked = false;

            hideCookieModal();
            hidePreferencesModalSilently();

            document.dispatchEvent(new CustomEvent("cookiesRejected"));
        }

        function savePreferences() {
            let consentValue = "necessary";

            if (preferencesCookies.checked) consentValue += ",preferences";
            if (statisticsCookies.checked) consentValue += ",statistics";
            if (marketingCookies.checked) consentValue += ",marketing";

            setCookie("cookieConsent", consentValue, 365);
            setCookie("necessaryCookies", "true", 365);
            setCookie("preferencesCookies", preferencesCookies.checked ? "true" : "false", 365);
            setCookie("statisticsCookies", statisticsCookies.checked ? "true" : "false", 365);
            setCookie("marketingCookies", marketingCookies.checked ? "true" : "false", 365);

            hidePreferencesModalSilently();
            hideCookieModal();

            document.dispatchEvent(new CustomEvent("cookiePreferencesSaved", {
                detail: {
                    preferences: preferencesCookies.checked,
                    statistics: statisticsCookies.checked,
                    marketing: marketingCookies.checked
                }
            }));
        }

        function loadPreferences() {
            const necessary = getCookie("necessaryCookies");
            const preferences = getCookie("preferencesCookies");
            const statistics = getCookie("statisticsCookies");
            const marketing = getCookie("marketingCookies");

            if (necessary !== null) necessaryCookies.checked = necessary === "true";
            if (preferences !== null) preferencesCookies.checked = preferences === "true";
            if (statistics !== null) statisticsCookies.checked = statistics === "true";
            if (marketing !== null) marketingCookies.checked = marketing === "true";
        }

        function hidePreferencesModalSilently() {
            preferencesModal.classList.remove("active");
            preferencesModal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("cookie-modal-open");
        }

        if (!cookieConsent) {
            setTimeout(() => {
                showCookieModal();
            }, 1500);
        }

        if (acceptAllBtn) acceptAllBtn.addEventListener("click", acceptAllCookies);
        if (rejectAllBtn) rejectAllBtn.addEventListener("click", rejectAllCookies);
        if (managePrefBtn) managePrefBtn.addEventListener("click", showPreferencesModal);
        if (preferencesCloseBtn) preferencesCloseBtn.addEventListener("click", hidePreferencesModal);
        if (savePrefBtn) savePrefBtn.addEventListener("click", savePreferences);
        if (acceptAllPrefBtn) acceptAllPrefBtn.addEventListener("click", acceptAllCookies);
        if (rejectAllPrefBtn) rejectAllPrefBtn.addEventListener("click", rejectAllCookies);

        let domainExpanded = false;

        if (domainDropdown && domainContent) {
            domainDropdown.addEventListener("click", function () {
                domainExpanded = !domainExpanded;
                domainContent.classList.toggle("active", domainExpanded);
                domainContent.hidden = !domainExpanded;
                domainDropdown.setAttribute("aria-expanded", domainExpanded);

                const icon = domainDropdown.querySelector("i");

                if (icon) {
                    icon.className = domainExpanded ? "fa-regular fa-angle-up" : "fa-regular fa-angle-down";
                }
            });
        }

        tabs.forEach((tab) => {
            tab.addEventListener("click", function () {
                const tabId = this.getAttribute("data-tab");

                tabs.forEach((item) => {
                    item.classList.remove("active");
                    item.setAttribute("aria-selected", "false");
                });

                this.classList.add("active");
                this.setAttribute("aria-selected", "true");

                tabContents.forEach((content) => {
                    const isActive = content.id === tabId + "-tab";
                    content.classList.toggle("active", isActive);
                    content.hidden = !isActive;
                });
            });
        });

        document.addEventListener("click", function (event) {
            const trigger = event.target.closest("#openCookiePreferences, [data-cookie-preferences-trigger]");

            if (!trigger) {
                return;
            }

            event.preventDefault();
            showPreferencesModal();
        });

        window.addEventListener("click", function (event) {
            if (event.target === preferencesModal) {
                hidePreferencesModal();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                if (preferencesModal.classList.contains("active")) {
                    hidePreferencesModal();
                } else if (cookieModal.classList.contains("active")) {
                    hideCookieModal();
                }
            }
        });

        cookieModal.setAttribute("aria-hidden", "true");
        preferencesModal.setAttribute("aria-hidden", "true");

        tabs.forEach((tab, index) => {
            tab.setAttribute("aria-selected", index === 0 ? "true" : "false");
        });

        if (domainContent) {
            domainContent.classList.remove("active");
            domainContent.hidden = true;
        }

        setTimeout(ensureCookieMenuItems, 50);
        setTimeout(ensureCookieMenuItems, 250);
        window.addEventListener("load", ensureCookieMenuItems, { once: true });
    }

    window.KwikshiftCookieConsent = {
        init: initCookieConsent,
        ensureMenuItems: ensureCookieMenuItems
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initCookieConsent, { once: true });
    } else {
        initCookieConsent();
    }
})();

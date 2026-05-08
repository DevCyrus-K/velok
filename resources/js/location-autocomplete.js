import { createElement, MapPin } from 'lucide';

const ENDPOINT = 'https://nominatim.openstreetmap.org/search';
const CONTACT_EMAIL = 'info@kwikshiftmovers.co.ke';
const USER_AGENT = `KwikShiftMovers/1.0 (${CONTACT_EMAIL})`;
const MIN_QUERY_LENGTH = 2;
const DEBOUNCE_MS = 300;
const REQUEST_INTERVAL_MS = 1000;

const wait = (milliseconds) => new Promise((resolve) => {
  window.setTimeout(resolve, milliseconds);
});

class LocationAutocomplete {
  static lastRequestAt = 0;

  static requestChain = Promise.resolve();

  constructor(inputId, options = {}) {
    this.input = document.getElementById(inputId);

    if (!this.input) {
      return;
    }

    this.limit = options.limit || 5;
    this.countryCode = options.countryCode || 'ke';
    this.wrapper = this.input.closest('[data-location-autocomplete]');
    this.menu = this.wrapper?.querySelector('.location-autocomplete__menu');
    this.suggestions = [];
    this.activeIndex = -1;
    this.debounceTimer = null;
    this.abortController = null;
    this.requestId = 0;
    this.isOpen = false;

    if (!this.wrapper || !this.menu) {
      return;
    }

    this.configureInput();
    this.bindEvents();
  }

  configureInput() {
    const listboxId = this.menu.id || `${this.input.id}_suggestions`;

    this.menu.id = listboxId;
    this.input.setAttribute('autocomplete', 'off');
    this.input.setAttribute('aria-autocomplete', 'list');
    this.input.setAttribute('aria-controls', listboxId);
    this.input.setAttribute('aria-expanded', 'false');
    this.input.setAttribute('role', 'combobox');
  }

  bindEvents() {
    this.input.addEventListener('input', () => this.queueSearch());
    this.input.addEventListener('focus', () => {
      if (this.suggestions.length > 0) {
        this.open();
        return;
      }

      if (this.input.value.trim().length >= MIN_QUERY_LENGTH) {
        this.queueSearch();
      }
    });
    this.input.addEventListener('keydown', (event) => this.handleKeydown(event));

    document.addEventListener('click', (event) => {
      if (!this.wrapper.contains(event.target)) {
        this.close();
      }
    });
  }

  queueSearch() {
    window.clearTimeout(this.debounceTimer);
    this.activeIndex = -1;

    const query = this.input.value.trim();

    if (query.length < MIN_QUERY_LENGTH) {
      this.abortPendingRequest();
      this.close();
      this.clearResults();
      return;
    }

    this.renderMessage('Searching locations...', true);
    this.open();

    this.debounceTimer = window.setTimeout(() => {
      this.search(query);
    }, DEBOUNCE_MS);
  }

  async search(query) {
    this.abortPendingRequest();

    const requestId = ++this.requestId;
    this.abortController = new AbortController();

    try {
      const results = await LocationAutocomplete.rateLimitedFetch(this.buildUrl(query), {
        cache: 'no-store',
        headers: this.fetchHeaders(),
        signal: this.abortController.signal,
      });

      if (requestId !== this.requestId || this.input.value.trim() !== query) {
        return;
      }

      this.suggestions = this.normalizeResults(results);
      this.activeIndex = -1;
      this.renderResults();
    } catch (error) {
      if (error.name === 'AbortError' || requestId !== this.requestId) {
        return;
      }

      this.clearResults();
      this.renderMessage('Unable to load locations. You can still type the address manually.');
      this.open();
    }
  }

  static async rateLimitedFetch(url, options) {
    const run = async () => {
      const elapsed = Date.now() - LocationAutocomplete.lastRequestAt;

      if (elapsed < REQUEST_INTERVAL_MS) {
        await wait(REQUEST_INTERVAL_MS - elapsed);
      }

      LocationAutocomplete.lastRequestAt = Date.now();

      const response = await fetch(url, options);

      if (!response.ok) {
        throw new Error(`Location request failed with status ${response.status}`);
      }

      return response.json();
    };

    const request = LocationAutocomplete.requestChain.then(run, run);
    LocationAutocomplete.requestChain = request.catch(() => {});

    return request;
  }

  buildUrl(query) {
    const url = new URL(ENDPOINT);

    url.searchParams.set('format', 'json');
    url.searchParams.set('countrycodes', this.countryCode);
    url.searchParams.set('limit', String(this.limit));
    url.searchParams.set('q', query);
    url.searchParams.set('email', CONTACT_EMAIL);

    return url;
  }

  fetchHeaders() {
    const headers = new Headers({
      Accept: 'application/json',
      'Accept-Language': 'en-KE,en;q=0.9',
    });

    try {
      headers.set('User-Agent', USER_AGENT);
    } catch (error) {
      // Some browsers reserve this header; the contact email query parameter remains in place.
    }

    return headers;
  }

  normalizeResults(results) {
    if (!Array.isArray(results)) {
      return [];
    }

    return results
      .map((result) => ({
        displayName: typeof result.display_name === 'string' ? result.display_name.trim() : '',
        lat: typeof result.lat === 'string' ? result.lat : '',
        lon: typeof result.lon === 'string' ? result.lon : '',
      }))
      .filter((result) => result.displayName !== '')
      .slice(0, this.limit);
  }

  renderResults() {
    this.menu.innerHTML = '';

    if (this.suggestions.length === 0) {
      this.renderMessage('No locations found');
      this.open();
      return;
    }

    this.suggestions.forEach((suggestion, index) => {
      const option = document.createElement('button');
      option.type = 'button';
      option.id = `${this.input.id}_suggestion_${index}`;
      option.className = 'location-autocomplete__option';
      option.setAttribute('role', 'option');
      option.setAttribute('aria-selected', 'false');

      const pin = createElement(MapPin, {
        class: 'location-autocomplete__pin',
        width: 18,
        height: 18,
        'aria-hidden': 'true',
      });

      const label = document.createElement('span');
      label.className = 'location-autocomplete__label';
      label.textContent = suggestion.displayName;

      option.append(pin, label);
      option.addEventListener('mousedown', (event) => event.preventDefault());
      option.addEventListener('click', () => this.select(index));

      this.menu.appendChild(option);
    });

    this.open();
  }

  renderMessage(message, loading = false) {
    this.menu.innerHTML = '';

    const item = document.createElement('div');
    item.className = 'location-autocomplete__message';
    item.setAttribute('role', 'status');

    if (loading) {
      const spinner = document.createElement('span');
      spinner.className = 'location-autocomplete__spinner';
      spinner.setAttribute('aria-hidden', 'true');
      item.appendChild(spinner);
    }

    const text = document.createElement('span');
    text.textContent = message;
    item.appendChild(text);

    this.menu.appendChild(item);
  }

  updateActiveOption() {
    const options = this.menu.querySelectorAll('.location-autocomplete__option');

    options.forEach((option, index) => {
      const isActive = index === this.activeIndex;

      option.setAttribute('aria-selected', isActive ? 'true' : 'false');

      if (isActive) {
        this.input.setAttribute('aria-activedescendant', option.id);
        option.scrollIntoView({ block: 'nearest' });
      }
    });

    if (this.activeIndex < 0) {
      this.input.removeAttribute('aria-activedescendant');
    }
  }

  handleKeydown(event) {
    if (event.key === 'Escape') {
      this.close();
      return;
    }

    if (!this.isOpen || this.suggestions.length === 0) {
      return;
    }

    if (event.key === 'ArrowDown') {
      event.preventDefault();
      this.activeIndex = (this.activeIndex + 1) % this.suggestions.length;
      this.updateActiveOption();
    }

    if (event.key === 'ArrowUp') {
      event.preventDefault();
      this.activeIndex = this.activeIndex <= 0 ? this.suggestions.length - 1 : this.activeIndex - 1;
      this.updateActiveOption();
    }

    if (event.key === 'Enter' && this.activeIndex >= 0) {
      event.preventDefault();
      this.select(this.activeIndex);
    }
  }

  select(index) {
    const suggestion = this.suggestions[index];

    if (!suggestion) {
      return;
    }

    this.input.value = suggestion.displayName;
    this.input.dispatchEvent(new Event('change', { bubbles: true }));
    this.close();
    this.focusNextField();
  }

  focusNextField() {
    const nextSelector = this.input.dataset.locationNext;
    const nextField = nextSelector ? document.querySelector(nextSelector) : null;

    if (nextField && typeof nextField.focus === 'function') {
      nextField.focus({ preventScroll: true });
    }
  }

  open() {
    this.wrapper.classList.add('is-open');
    this.input.setAttribute('aria-expanded', 'true');
    this.isOpen = true;
  }

  close() {
    this.wrapper.classList.remove('is-open');
    this.input.setAttribute('aria-expanded', 'false');
    this.input.removeAttribute('aria-activedescendant');
    this.activeIndex = -1;
    this.isOpen = false;
  }

  clearResults() {
    this.suggestions = [];
    this.menu.innerHTML = '';
  }

  abortPendingRequest() {
    if (this.abortController) {
      this.abortController.abort();
      this.abortController = null;
    }
  }
}

window.LocationAutocomplete = LocationAutocomplete;

document.addEventListener('DOMContentLoaded', () => {
  new LocationAutocomplete('pickup_location');
  new LocationAutocomplete('dropoff_location');
});

export default LocationAutocomplete;

/**
* Theme: Kwishift Movers- Responsive Bootstrap 5 Admin Dashboard
* Author: Hydrasoft Technologies
* Module/App: Main Js
*/

import bootstrap from 'bootstrap/dist/js/bootstrap.min.js'
window.bootstrap = bootstrap;

import ApexCharts from 'apexcharts'
window.ApexCharts = ApexCharts;

import 'simplebar'
import 'iconify-icon'
import { createIcons, icons } from "lucide";

import Toastify from 'toastify-js';
import './location-autocomplete';

window.Toastify = Toastify;
window.lucide = { createIcons: () => createIcons({ icons }) };

// Components
class Components {

  initBootstrapComponents() {

    // Popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    // Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    // offcanvas
    const offcanvasElementList = document.querySelectorAll('.offcanvas')
    const offcanvasList = [...offcanvasElementList].map(offcanvasEl => new bootstrap.Offcanvas(offcanvasEl))

    //Toasts
    var toastPlacement = document.getElementById("toastPlacement");
    if (toastPlacement) {
      document.getElementById("selectToastPlacement").addEventListener("change", function () {
        if (!toastPlacement.dataset.originalClass) {
          toastPlacement.dataset.originalClass = toastPlacement.className;
        }
        toastPlacement.className = toastPlacement.dataset.originalClass + " " + this.value;
      });
    }

    var toastElList = [].slice.call(document.querySelectorAll('.toast'))
    var toastList = toastElList.map(function (toastEl) {
      return new bootstrap.Toast(toastEl)
    })


    const alertTrigger = document.getElementById('liveAlertBtn')
    if (alertTrigger) {
      alertTrigger.addEventListener('click', () => {
        alert('Nice, you triggered this alert message!', 'success')
      })
    }

  }

  initfullScreenListener() {
    var fullScreenBtn = document.querySelector('[data-toggle="fullscreen"]');

    if (fullScreenBtn) {
      fullScreenBtn.addEventListener('click', function (e) {
        e.preventDefault();
        document.body.classList.toggle('fullscreen-enable')
        if (!document.fullscreenElement && /* alternative standard method */ !document.mozFullScreenElement && !document.webkitFullscreenElement) {
          // current working methods
          if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
          } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
          } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
          }
        } else {
          if (document.cancelFullScreen) {
            document.cancelFullScreen();
          } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
          } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
          }
        }
      });
    }
  }

  // Counter Number
  initCounter() {
    var counter = document.querySelectorAll(".counter-value");
    var speed = 250; // The lower the slower
    counter &&
      counter.forEach(function (counter_value) {
        function updateCount() {
          var target = +counter_value.getAttribute("data-target");
          var count = +counter_value.innerText;
          var inc = target / speed;
          if (inc < 1) {
            inc = 1;
          }
          // Check if target is reached
          if (count < target) {
            // Add inc to count and output in counter_value
            counter_value.innerText = (count + inc).toFixed(0);
            // Call function every ms
            setTimeout(updateCount, 1);
          } else {
            counter_value.innerText = numberWithCommas(target);
          }
          numberWithCommas(counter_value.innerText);
        }
        updateCount();
      });

    function numberWithCommas(x) {
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
  }

  init() {
    this.initBootstrapComponents();
    this.initfullScreenListener();
    this.initCounter();
  }
}

// Form Validation ( Bootstrap )
class FormValidation {
  initFormValidation() {
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    // Loop over them and prevent submission
    document.querySelectorAll('.needs-validation').forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
  }

  init() {
    this.initFormValidation();
  }
}

// Toast Notification
class ToastNotification {
  initToastNotification() {

    document.querySelectorAll("[data-toast]").forEach(function (element) {
      element.addEventListener("click", function () {
        var toastData = {};
        if (element.attributes["data-toast-text"]) {
          toastData.text = element.attributes["data-toast-text"].value.toString();
        }
        if (element.attributes["data-toast-gravity"]) {
          toastData.gravity = element.attributes["data-toast-gravity"].value.toString();
        }
        if (element.attributes["data-toast-position"]) {
          toastData.position = element.attributes["data-toast-position"].value.toString();
        }
        if (element.attributes["data-toast-className"]) {
          toastData.className = element.attributes["data-toast-className"].value.toString();
        }
        if (element.attributes["data-toast-duration"]) {
          toastData.duration = element.attributes["data-toast-duration"].value.toString();
        }
        if (element.attributes["data-toast-close"]) {
          toastData.close = element.attributes["data-toast-close"].value.toString();
        }
        if (element.attributes["data-toast-style"]) {
          toastData.style = element.attributes["data-toast-style"].value.toString();
        }
        if (element.attributes["data-toast-offset"]) {
          toastData.offset = element.attributes["data-toast-offset"];
        }
        Toastify({
          newWindow: true,
          text: toastData.text,
          gravity: toastData.gravity,
          position: toastData.position,
          className: "bg-" + toastData.className,
          stopOnFocus: true,
          offset: {
            x: toastData.offset ? 50 : 0,
            y: toastData.offset ? 10 : 0, // vertical axis - can be a number or a string indicating unity. eg: '2em'
          },
          duration: toastData.duration,
          close: toastData.close == "close" ? true : false,
        }).showToast();
      });
    });
  }

  init() {
    this.initToastNotification();
  }
}

class AsyncActionFeedback {
  constructor() {
    this.activeActions = new Map();
    this.originalFetch = window.fetch ? window.fetch.bind(window) : null;
    this.toastMemory = new Map();
    this.restoringForms = new WeakSet();
  }

  init() {
    this.ensureLiveRegion();
    this.bindFormSubmits();
    this.bindActionClicks();
    this.bindFileInputs();
    this.wrapFetch();
    window.AsyncActionFeedback = this;
  }

  ensureLiveRegion() {
    this.liveRegion = document.getElementById('async-action-status');

    if (this.liveRegion) {
      return;
    }

    this.liveRegion = document.createElement('div');
    this.liveRegion.id = 'async-action-status';
    this.liveRegion.className = 'visually-hidden';
    this.liveRegion.setAttribute('aria-live', 'polite');
    this.liveRegion.setAttribute('aria-atomic', 'true');
    document.body.appendChild(this.liveRegion);
  }

  bindFormSubmits() {
    document.addEventListener('submit', (event) => {
      const form = event.target;

      if (!(form instanceof HTMLFormElement) || this.shouldSkipForm(form)) {
        return;
      }

      if (form.dataset.actionPending === 'true') {
        event.preventDefault();
        this.toast('Already working on this. Please wait.', 'info');
        return;
      }

      if (event.defaultPrevented || this.restoringForms.has(form)) {
        return;
      }

      this.prepareNativeSubmit(form, event.submitter);
    });
  }

  bindActionClicks() {
    document.addEventListener('click', (event) => {
      const trigger = event.target.closest('[data-action-feedback], [data-action-click-message]');

      if (!trigger || trigger.closest('form')) {
        return;
      }

      const href = trigger.getAttribute('href') || '';

      if (trigger.dataset.actionPending === 'true') {
        event.preventDefault();
        this.toast('Already working on this. Please wait.', 'info');
        return;
      }

      if (href.trim().startsWith('#') || trigger.hasAttribute('data-bs-toggle')) {
        return;
      }

      this.start({
        source: trigger,
        message: this.attribute(trigger, 'action-click-message') || this.messageForSource(trigger),
        toast: true,
      });
    });
  }

  bindFileInputs() {
    document.addEventListener('change', (event) => {
      const input = event.target;

      if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.files?.length) {
        return;
      }

      const form = input.form;
      const count = input.files.length;
      const hasAutoSubmit = input.hasAttribute('data-auto-submit') || form?.hasAttribute('data-auto-submit');
      const message = hasAutoSubmit
        ? 'Uploading file...'
        : `${count} file${count === 1 ? '' : 's'} selected. Save to upload.`;

      this.announce(message);

      if (!hasAutoSubmit) {
        this.toast(message, 'info', { duration: 2200 });
      }
    });
  }

  wrapFetch() {
    if (!this.originalFetch || window.__asyncActionFeedbackFetchWrapped) {
      return;
    }

    window.__asyncActionFeedbackFetchWrapped = true;

    window.fetch = async (resource, options = {}) => {
      const requestInfo = this.fetchRequestInfo(resource, options);

      if (!requestInfo.track) {
        return this.originalFetch(resource, options);
      }

      const action = this.start({
        message: requestInfo.pendingMessage,
        toast: true,
      });

      try {
        const response = await this.originalFetch(resource, options);

        if (response.ok) {
          this.finish(action, requestInfo.successMessage, { toast: requestInfo.showSuccess });
          return response;
        }

        const errorMessage = await this.responseErrorMessage(response.clone(), requestInfo.errorMessage);
        this.fail(action, errorMessage, {
          retry: requestInfo.canRetry ? () => window.fetch(resource, options) : null,
        });

        return response;
      } catch (error) {
        this.fail(action, error?.message || requestInfo.errorMessage, {
          retry: requestInfo.canRetry ? () => window.fetch(resource, options) : null,
        });
        throw error;
      }
    };
  }

  prepareNativeSubmit(form, submitter = null, options = {}) {
    const source = submitter instanceof HTMLElement ? submitter : this.primarySubmitButton(form);
    const action = this.start({
      source,
      form,
      message: options.message || this.messageForForm(form, source),
      toast: true,
      preserveSubmitter: submitter,
    });

    return action;
  }

  submitForm(form, submitter = null, options = {}) {
    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    this.prepareNativeSubmit(form, submitter, options);
    this.restoringForms.add(form);
    HTMLFormElement.prototype.submit.call(form);

    window.setTimeout(() => {
      this.restoringForms.delete(form);
    }, 500);
  }

  start({ source = null, form = null, message = 'Working...', toast = false, preserveSubmitter = null } = {}) {
    const id = window.crypto?.randomUUID ? window.crypto.randomUUID() : `${Date.now()}-${Math.random()}`;
    const action = {
      id,
      source,
      form,
      message,
      restore: [],
    };

    this.activeActions.set(id, action);
    this.announce(message);

    if (form) {
      form.dataset.actionPending = 'true';
      form.setAttribute('aria-busy', 'true');
      action.restore.push(() => {
        form.dataset.actionPending = 'false';
        form.removeAttribute('aria-busy');
      });

      this.preserveSubmitterValue(form, preserveSubmitter, action);
      this.disableFormSubmitters(form, source, action);
    } else if (source) {
      this.setElementLoading(source, message, action);
    }

    if (toast) {
      this.toast(message, 'info', { duration: 2400 });
    }

    return action;
  }

  finish(action, message = 'Done.', { toast = true } = {}) {
    this.restore(action);

    if (message) {
      this.announce(message);

      if (toast) {
        this.toast(message, 'success');
      }
    }
  }

  fail(action, message = 'Something went wrong. Please try again.', { retry = null } = {}) {
    this.restore(action);
    this.announce(message);
    this.toast(message, 'error', { retry });
  }

  restore(action) {
    if (!action || !this.activeActions.has(action.id)) {
      return;
    }

    action.restore.reverse().forEach((restore) => restore());
    this.activeActions.delete(action.id);
  }

  setElementLoading(element, message, action) {
    if (!(element instanceof HTMLElement)) {
      return;
    }

    const originalHtml = element.innerHTML;
    const originalDisabled = element.disabled;
    const originalAriaBusy = element.getAttribute('aria-busy');
    const originalWidth = element.style.width;

    if (element.offsetWidth > 0) {
      element.style.width = `${element.offsetWidth}px`;
    }

    element.dataset.actionPending = 'true';
    element.setAttribute('aria-busy', 'true');

    if ('disabled' in element) {
      element.disabled = true;
    }

    if (!element.hasAttribute('data-action-keep-label')) {
      element.innerHTML = `<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>${this.shortLoadingLabel(message)}`;

      if (window.lucide?.createIcons) {
        window.lucide.createIcons();
      }
    }

    action.restore.push(() => {
      element.dataset.actionPending = 'false';
      element.innerHTML = originalHtml;
      element.style.width = originalWidth;

      if (originalAriaBusy === null) {
        element.removeAttribute('aria-busy');
      } else {
        element.setAttribute('aria-busy', originalAriaBusy);
      }

      if ('disabled' in element) {
        element.disabled = originalDisabled;
      }

      if (window.lucide?.createIcons) {
        window.lucide.createIcons();
      }
    });
  }

  disableFormSubmitters(form, primary, action) {
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
      if (button === primary) {
        this.setElementLoading(button, action.message, action);
        return;
      }

      const originalDisabled = button.disabled;
      button.disabled = true;
      action.restore.push(() => {
        button.disabled = originalDisabled;
      });
    });
  }

  preserveSubmitterValue(form, submitter, action) {
    if (!(submitter instanceof HTMLElement) || !submitter.name || submitter.disabled) {
      return;
    }

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = submitter.name;
    hidden.value = submitter.value;
    hidden.setAttribute('data-action-feedback-submit-value', 'true');
    form.appendChild(hidden);
    action.restore.push(() => hidden.remove());
  }

  shouldSkipForm(form) {
    if (form.hasAttribute('data-action-feedback-skip')
      || form.matches('[role="search"], form[data-confirm-modal], form[data-delete-confirm]')) {
      return true;
    }

    const method = (form.getAttribute('method') || 'GET').toUpperCase();
    const action = (form.getAttribute('action') || '').trim();

    return method === 'GET' || action === '' || action === '#';
  }

  fetchRequestInfo(resource, options) {
    const method = this.fetchBodyMethod(options?.body) || this.fetchMethod(resource, options);
    const url = this.fetchUrl(resource);
    const track = !['GET', 'HEAD'].includes(method) && options?.actionFeedback !== false;
    const pendingMessage = this.messageForText(`${method} ${url}`);
    const base = pendingMessage.replace(/\.\.\.$/, '');

    return {
      track,
      method,
      url,
      pendingMessage,
      successMessage: `${base} complete.`,
      errorMessage: `${base} failed. Please try again.`,
      showSuccess: options?.actionFeedbackSuccess === true,
      canRetry: options?.actionFeedbackRetry !== false,
    };
  }

  fetchMethod(resource, options) {
    if (options?.method) {
      return String(options.method).toUpperCase();
    }

    if (resource instanceof Request) {
      return resource.method.toUpperCase();
    }

    return 'GET';
  }

  fetchBodyMethod(body) {
    if (body instanceof FormData && body.has('_method')) {
      return String(body.get('_method')).toUpperCase();
    }

    if (body instanceof URLSearchParams && body.has('_method')) {
      return String(body.get('_method')).toUpperCase();
    }

    return '';
  }

  fetchUrl(resource) {
    if (typeof resource === 'string') {
      return resource;
    }

    if (resource instanceof URL) {
      return resource.toString();
    }

    if (resource instanceof Request) {
      return resource.url;
    }

    return '';
  }

  async responseErrorMessage(response, fallback) {
    const data = await response.json().catch(() => null);

    if (data?.errors) {
      return Object.values(data.errors).flat().join(' ');
    }

    return data?.error || data?.message || fallback;
  }

  messageForForm(form, source = null) {
    return this.attribute(form, 'action-message')
      || this.attribute(source, 'action-message')
      || this.attribute(source, 'loading-text')
      || this.messageForText([
        form.getAttribute('action'),
        form.querySelector('input[name="_method"]')?.value,
        source?.textContent,
        form.textContent,
      ].filter(Boolean).join(' '));
  }

  messageForSource(source) {
    return this.attribute(source, 'action-message')
      || this.attribute(source, 'loading-text')
      || this.messageForText([
        source?.getAttribute('href'),
        source?.getAttribute('aria-label'),
        source?.getAttribute('title'),
        source?.textContent,
      ].filter(Boolean).join(' '));
  }

  messageForText(text = '') {
    const value = text.toLowerCase();

    if (value.includes('logout') || value.includes('log out')) return 'Logging you out...';
    if (value.includes('login') || value.includes('sign in')) return 'Logging you in...';
    if (value.includes('register') || value.includes('sign up')) return 'Creating your account...';
    if (value.includes('password') && (value.includes('reset') || value.includes('save'))) return 'Updating password...';
    if (value.includes('verification') || value.includes('verify')) return 'Verifying code...';
    if (value.includes('resend')) return 'Sending again...';
    if (value.includes('retry')) return 'Retrying...';
    if (value.includes('delete') || value.includes('destroy')) return 'Deleting item...';
    if (value.includes('upload') || value.includes('file') || value.includes('multipart')) return 'Uploading file...';
    if (value.includes('send') || value.includes('email') || value.includes('invoice/send') || value.includes('quotation')) return 'Sending...';
    if (value.includes('payment') || value.includes('pay')) return 'Saving payment settings...';
    if (value.includes('booking') || value.includes('quote')) return 'Saving booking details...';
    if (value.includes('signature')) return 'Saving signature...';
    if (value.includes('update') || value.includes('patch') || value.includes('put')) return 'Updating...';
    if (value.includes('save') || value.includes('store') || value.includes('post')) return 'Saving changes...';

    return 'Working...';
  }

  shortLoadingLabel(message) {
    return message.length > 24 ? 'Working...' : message;
  }

  primarySubmitButton(form) {
    return form.querySelector('button[type="submit"]:not([disabled]), input[type="submit"]:not([disabled])')
      || form.querySelector('button[type="submit"], input[type="submit"]');
  }

  attribute(source, name) {
    return source?.getAttribute(`data-${name}`) || source?.getAttribute(`data-action-${name}`) || '';
  }

  announce(message) {
    if (this.liveRegion && message) {
      this.liveRegion.textContent = message;
    }
  }

  toast(message, type = 'info', options = {}) {
    if (!message || !window.Toastify) {
      return;
    }

    const className = {
      success: 'bg-success',
      error: 'bg-danger',
      danger: 'bg-danger',
      info: 'bg-info',
      warning: 'bg-warning',
    }[type] || type;

    const dedupeKey = `${className}:${message}`;
    const now = Date.now();

    if ((this.toastMemory.get(dedupeKey) || 0) > now - 900) {
      return;
    }

    this.toastMemory.set(dedupeKey, now);

    const toastOptions = {
      duration: options.duration ?? (type === 'error' ? 6000 : 3000),
      close: true,
      gravity: 'top',
      position: 'right',
      className,
      stopOnFocus: true,
    };

    if (options.retry) {
      toastOptions.node = this.retryToastNode(message, options.retry);
      toastOptions.duration = options.duration ?? 8000;
    } else {
      toastOptions.text = message;
    }

    Toastify(toastOptions).showToast();
  }

  retryToastNode(message, retry) {
    const node = document.createElement('div');
    node.className = 'd-flex align-items-center gap-2';

    const text = document.createElement('span');
    text.textContent = message;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-light py-0 px-2';
    button.textContent = 'Retry';
    button.addEventListener('click', () => {
      this.toast('Retrying...', 'info', { duration: 1600 });
      retry();
    });

    node.append(text, button);

    return node;
  }
}

class ConfirmationModal {
  init() {
    this.modalElement = document.getElementById('deleteConfirmModal');
    this.titleElement = document.getElementById('deleteConfirmModalTitle');
    this.messageElement = document.getElementById('deleteConfirmModalMessage');
    this.confirmButton = document.getElementById('deleteConfirmButton');
    this.cancelButton = document.getElementById('deleteConfirmCancelButton');

    if (!this.modalElement || !this.titleElement || !this.messageElement || !this.confirmButton || !this.cancelButton) {
      return;
    }

    this.modal = new bootstrap.Modal(this.modalElement);
    this.pendingAction = null;

    document.addEventListener('click', (event) => {
      const trigger = event.target.closest('[data-confirm-modal], [data-delete-confirm]');

      if (!trigger || trigger.tagName === 'FORM') {
        return;
      }

      event.preventDefault();
      this.open(trigger, () => {
        const href = trigger.getAttribute('href');
        const removeSelector = this.getAttribute(trigger, 'remove-closest');
        const successToast = this.getAttribute(trigger, 'success-toast');
        const successToastClass = this.getAttribute(trigger, 'success-toast-class') || 'bg-success';
        let removedElement = null;

        if (removeSelector) {
          removedElement = trigger.closest(removeSelector);
          if (removedElement) {
            removedElement.remove();
          }
        }

        if (href && !['#', '#!', 'javascript:void(0);', 'javascript: void(0);'].includes(href.trim())) {
          window.location.href = href;
          return;
        }

        if (successToast) {
          this.showToast(successToast, successToastClass);
        }

        document.dispatchEvent(new CustomEvent('confirmation:local-success', {
          detail: {
            source: trigger,
            removedElement,
            successToast,
          },
        }));
      });
    });

    document.addEventListener('submit', (event) => {
      const form = event.target.closest('form[data-confirm-modal], form[data-delete-confirm]');

      if (!form) {
        return;
      }

      event.preventDefault();
      this.open(form, () => {
        if (window.AsyncActionFeedback) {
          window.AsyncActionFeedback.submitForm(form, event.submitter, {
            message: this.getAttribute(form, 'action-message') || undefined,
          });
          return;
        }

        HTMLFormElement.prototype.submit.call(form);
      });
    });

    this.confirmButton.addEventListener('click', () => {
      const action = this.pendingAction;
      this.modal.hide();

      if (typeof action === 'function') {
        action();
      }
    });

    this.modalElement.addEventListener('hidden.bs.modal', () => {
      this.pendingAction = null;
    });
  }

  getAttribute(source, name) {
    return source.getAttribute(`data-confirm-${name}`) || source.getAttribute(`data-delete-${name}`);
  }

  setConfirmButtonClass(buttonClass) {
    this.confirmButton.className = 'btn';

    (buttonClass || 'btn-primary')
      .split(/\s+/)
      .filter(Boolean)
      .forEach((className) => this.confirmButton.classList.add(className));
  }

  showToast(message, className = 'bg-success') {
    Toastify({
      text: message,
      duration: 3000,
      close: true,
      gravity: 'top',
      position: 'right',
      className,
    }).showToast();
  }

  open(source, onConfirm) {
    const isDeleteAction = source.hasAttribute('data-delete-confirm');

    this.titleElement.textContent = this.getAttribute(source, 'title') || (isDeleteAction ? 'Delete item?' : 'Confirm action?');
    this.messageElement.textContent = this.getAttribute(source, 'message') || (isDeleteAction ? 'Do you want to delete this item?' : 'Do you want to continue?');
    this.confirmButton.textContent = this.getAttribute(source, 'confirm-text') || (isDeleteAction ? 'Yes, Delete' : 'Yes, Continue');
    this.cancelButton.textContent = this.getAttribute(source, 'cancel-text') || 'No, Keep';
    this.setConfirmButtonClass(this.getAttribute(source, 'button-class') || (isDeleteAction ? 'btn-danger' : 'btn-primary'));
    this.pendingAction = onConfirm;
    this.modal.show();
  }
}

document.addEventListener('DOMContentLoaded', function (e) {
  new Components().init();
  new FormValidation().init();
  new ToastNotification().init();
  new AsyncActionFeedback().init();
  new ConfirmationModal().init();
  createIcons({ icons })
});

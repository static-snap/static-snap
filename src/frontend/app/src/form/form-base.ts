import { Events } from '../constants';
import { __ } from '@wordpress/i18n';

// jQuery is available in the global scope
declare const jQuery: any; // eslint-disable-line @typescript-eslint/no-explicit-any
declare const grecaptcha: any; // eslint-disable-line @typescript-eslint/no-explicit-any
declare const StaticSnapFrontendConfig: {
  recaptcha_site_key: string;
};

export type FormBaseNoticeMessageSettings = {
  type: 'message' | 'redirect';
  success_message: string;
  error_message: string;
  required_field_message: string;
  invalid_message: string;
  redirect_url?: string;
};

export type FormBaseGetNoticeMessageType = 'success' | 'error' | 'field_error' | 'invalid_error';

export default abstract class FormBase {
  protected forms: NodeListOf<HTMLFormElement> | null = null;
  protected selector: string = '[data-static-snap-type="form"]';
  protected submitButtonSelector: string = '[type="submit"]';

  // constructor
  constructor(selector?: string) {
    if (selector) {
      this.selector = selector;
    }
    this.loadGoogleRecaptcha();
    this.bindEvents();
    //this.bindPluginsEvents();
  }
  /**
   * This method is called when the form is submitted.
   * @param e - The event object.
   * @param form - The form element.
   * @param submitData - The data that will be sent to the server.
   * @returns void
   * @example
   * onSubmit(e: Event, form: HTMLFormElement, submitData: any): void {
   *  console.log('Form submitted', e, form, submitData);
   * }
   * @example
   */
  protected abstract onSubmit(e: Event, form: HTMLFormElement, submitData: any): void;

  /**
   * This method is called when an error occurs during form submission.
   * @param e - The event object.
   * @param form - The form element.
   * @param error - The error object.
   */
  protected abstract onError(e: Event, form: HTMLFormElement, error: any): void;

  /**
   * Get notice message settings
   * @param form - The form element.
   */
  protected getNoticeMessageSettings = (form: HTMLFormElement): FormBaseNoticeMessageSettings => {
    const defaultMessageSettings: FormBaseNoticeMessageSettings = {
      error_message: __('An error occurred while submitting the form', 'static-snap'),
      invalid_message: __('Please enter a valid value.', 'static-snap'),
      required_field_message: __('This field is required.', 'static-snap'),
      success_message: __('The form was sent successfully.', 'static-snap'),
      type: 'message',
    };
    const messageSettings = form.dataset?.staticSnapFormNoticeSettings;
    if (!messageSettings) {
      return defaultMessageSettings;
    }

    try {
      return { ...defaultMessageSettings, ...JSON.parse(messageSettings) };
    } catch (_e) {
      return defaultMessageSettings;
    }
  };

  protected getNoticeMessageOrRedirect = (
    form: HTMLFormElement,
    type: FormBaseGetNoticeMessageType
  ): string => {
    const messageSettings = this.getNoticeMessageSettings(form);

    // if redirect type, we don't need to show any message. We will redirect to the URL
    if (messageSettings.type === 'redirect' && type === 'success' && messageSettings.redirect_url) {
      window.location.href = messageSettings.redirect_url;
    }

    const messageMap: Record<FormBaseGetNoticeMessageType, string> = {
      error: messageSettings.error_message,
      field_error: messageSettings.required_field_message,
      invalid_error: messageSettings.invalid_message,
      success: messageSettings.success_message,
    };

    return messageMap[type] || '';
  };

  protected loadGoogleRecaptcha = () => {
    const script = document.createElement('script');
    script.src = `https://www.google.com/recaptcha/api.js?render=${StaticSnapFrontendConfig.recaptcha_site_key}`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
  };

  public getForms = (): NodeListOf<HTMLFormElement> => {
    // get all static_snap_type="form" elements
    return document.querySelectorAll(this.selector) as NodeListOf<HTMLFormElement>;
  };

  /**
   * Remove form submit events.
   * @param form - The form element from which to unbind submit events.
   */
  public unbindSubmitEvents = (form: HTMLFormElement): void => {
    setTimeout(() => {
      jQuery(form).off('submit');
    }, 500);
  };

  /**
   * Clone forms to remove events
   */
  public bindEvents = () => {
    this.forms = this.getForms();

    this.forms.forEach((form) => {
      // check if form is already initialized
      if (form.getAttribute('data-static-snap-initialized') === 'true') {
        return;
      }
      // remove all jQuery submit events
      this.unbindSubmitEvents(form);
      // add a new submit event
      form.addEventListener('submit', (e) => this.submit(e, form));
      // mark form as initialized
      form.setAttribute('data-static-snap-initialized', 'true');
    });
  };

  // before submit
  protected beforeSubmit = (form: HTMLFormElement): boolean => {
    // trigger html form validation by default
    const isFormValid = form.checkValidity();
    if (!isFormValid) {
      form.reportValidity();
    }
    return isFormValid;
  };

  public submit = async (event: Event, form: HTMLFormElement) => {
    event.preventDefault();
    // disable submit button

    if (!this.beforeSubmit(form)) {
      return;
    }

    form.querySelector(this.submitButtonSelector)?.setAttribute('disabled', 'disabled');

    const submitFormDataWithGoogleRecaptcha = async () => {
      try {
        const token = await grecaptcha.execute(StaticSnapFrontendConfig.recaptcha_site_key, {
          action: 'submit',
        });

        const formData = new FormData(form);
        // send form data to action URL as json
        const submitData = Object.fromEntries(formData);

        const response = (await fetch(form.action, {
          body: JSON.stringify(submitData),
          headers: {
            'Content-Type': 'application/json',
            // recaptcha token
            'G-Recaptcha-Response': token,
          },
          method: 'POST',
        })) as Response;
        const responseCode = response.status;
        const data = await response.json();
        form.querySelector(this.submitButtonSelector)?.removeAttribute('disabled');

        if (responseCode === 200) {
          form.reset();
          // emit event
          document.dispatchEvent(
            new CustomEvent(Events.FORM_SUBMITTED_EVENT, { detail: { form, submitData } })
          );
          this.onSubmit(event, form, submitData);
        } else {
          console.error('Error:', data);
          document.dispatchEvent(
            new CustomEvent(Events.FORM_SUBMIT_ERROR_EVENT, { detail: { data, form } })
          );
          this.onError(event, form, data);
        }
      } catch (e: unknown) {
        const error = e as Error;

        document.dispatchEvent(
          new CustomEvent(Events.FORM_SUBMIT_ERROR_EVENT, { detail: { error, form } })
        );
        this.onError(event, form, error);
      }
    };

    try {
      await grecaptcha.ready(submitFormDataWithGoogleRecaptcha);
    } catch (e) {
      console.error(e);
    }
  };
}

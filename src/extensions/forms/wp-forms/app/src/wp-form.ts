import {
  FormBase,
  FormBaseGetNoticeMessageType,
  FormSubmitResponseInterface,
  WebsiteFormSettings,
} from '@staticsnap/frontend';
import ApiResponseInterface from '@staticsnap/frontend/src/interfaces/api-response.interface';
import { __, sprintf } from '@wordpress/i18n';

export default class WpForm extends FormBase {
  private extensionMessagesClasses: Record<FormBaseGetNoticeMessageType, string> = {
    error: 'wpforms-error-alert',
    field_error: 'wpforms-error-alert',
    invalid_error: 'wpforms-error-alert',
    success: 'wpforms-confirmation-container-full',
  };

  constructor() {
    super('[data-static-snap-type="form"][data-static-snap-form-type="wp-forms"]');
  }

  protected onSubmit(
    _e: Event,
    form: HTMLFormElement,
    _submitData: unknown,
    responseData: ApiResponseInterface<FormSubmitResponseInterface>
  ): void {
    const settings = this.getFormSettings(responseData);
    this.setMessage(
      form,
      responseData.type === 'item' && responseData?.data?.saved ? 'success' : 'error',
      settings
    );

    // TODO: add webhook support
    const knowSubmitActions = ['redirect'];

    settings?.submit_actions?.some((action) => {
      if (knowSubmitActions.includes(action)) {
        if (action === 'redirect') {
          this.onRedirect(settings);
        }
      }
    });
  }

  protected onRedirect(settings: WebsiteFormSettings): void {
    if (settings.redirect_to) {
      window.location.href = settings.redirect_to;
    }
  }

  protected onError(_e: Event, form: HTMLFormElement, _error: unknown): void {
    //console.log('ElementorForms onError', e, form, error);
    this.setMessage(form, 'error', {
      messages: {
        error: __('An error occurred, please try again later.', 'static-snap'),
        invalid: '',
        required: '',
        success: '',
      },
    });
  }

  private setMessage(
    form: HTMLFormElement,
    type: FormBaseGetNoticeMessageType,
    settings: WebsiteFormSettings
  ): void {
    const matchTypeToMessage = {
      error: settings?.messages?.error,
      field_error: settings?.messages?.required,
      invalid_error: settings?.messages?.invalid,
      success: settings?.messages?.success,
    };
    const noticeElement = this.getNoticeElement(form);
    const message =
      matchTypeToMessage[type] ||
      sprintf('<p>%s</p>', __('An error occurred, please try again later.', 'static-snap'));

    noticeElement.innerHTML = message;
    const messageClass = this.extensionMessagesClasses[type];
    // remove other form classes
    Object.values(this.extensionMessagesClasses).forEach((messageClass) => {
      form.classList.remove(messageClass);
    });

    noticeElement.classList.add(messageClass);
  }

  private getNoticeElement(form: HTMLFormElement): Element {
    let noticeElement = form.querySelector(' .wpforms-notice-container');

    if (!noticeElement) {
      noticeElement = document.createElement('div');
      noticeElement.classList.add('wpforms-notice-container');
      form.appendChild(noticeElement);
    }
    return noticeElement;
  }
}

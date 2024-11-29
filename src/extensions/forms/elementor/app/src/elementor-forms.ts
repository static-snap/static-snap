import {
  FormBase,
  FormBaseGetNoticeMessageType,
  FormSubmitResponseInterface,
  WebsiteFormSettings,
} from '@staticsnap/frontend';
import { __ } from '@wordpress/i18n';

export default class ElementorForms extends FormBase {
  private elementorMessagesClasses: Record<FormBaseGetNoticeMessageType, string> = {
    error: 'elementor-message-danger',
    field_error: 'elementor-message-danger',
    invalid_error: 'elementor-message-danger',
    success: 'elementor-message-success',
  };

  constructor() {
    super('[data-static-snap-type="form"][data-static-snap-form-type="elementor"]');
  }

  protected onSubmit(
    _e: Event,
    form: HTMLFormElement,
    _submitData: unknown,
    responseData: FormSubmitResponseInterface
  ): void {
    const settings = this.getFormSettings(responseData);
    const knowSubmitActions = ['redirect', 'popup'];

    this.setMessage(form, responseData.saved ? 'success' : 'error', settings);
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
    const noticeElement = this.getNoticeElement(form);

    const matchTypeToMessage = {
      error: settings?.messages?.error,
      field_error: settings?.messages?.required,
      invalid_error: settings?.messages?.invalid,
      success: settings?.messages?.success,
    };

    const message =
      matchTypeToMessage[type] || __('An error occurred, please try again later.', 'static-snap');

    noticeElement.textContent = message;
    noticeElement.classList.remove('elementor-message-success', 'elementor-message-error');
    const messageClass = this.elementorMessagesClasses[type];
    noticeElement.classList.add(messageClass);
  }

  private getNoticeElement(form: HTMLFormElement): Element {
    let noticeElement = form.querySelector(' .elementor-message');

    if (!noticeElement) {
      noticeElement = document.createElement('div');
      noticeElement.classList.add('elementor-message');
      form.appendChild(noticeElement);
    }
    return noticeElement;
  }
}

import {
  FormBase,
  FormBaseGetNoticeMessageType,
  FormSubmitResponseInterface,
  WebsiteFormSettings,
} from '@staticsnap/frontend';
import ApiResponseInterface from '@staticsnap/frontend/src/interfaces/api-response.interface';
import { __ } from '@wordpress/i18n';

export default class ContactForm7 extends FormBase {
  private extensionMessagesClasses: Record<FormBaseGetNoticeMessageType, string> = {
    error: 'failed',
    field_error: 'failed',
    invalid_error: 'failed',
    success: 'sent',
  };

  constructor() {
    super('[data-static-snap-type="form"][data-static-snap-form-type="contact-form-7"]');
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
  }

  protected onError(_e: Event, form: HTMLFormElement, _error: unknown): void {
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
      matchTypeToMessage[type] || __('An error occurred, please try again later.', 'static-snap');

    noticeElement.textContent = message;
    const messageClass = this.extensionMessagesClasses[type];
    // remove other form classes
    Object.values(this.extensionMessagesClasses).forEach((messageClass) => {
      form.classList.remove(messageClass);
    });
    form.classList.remove('init');
    form.classList.add(messageClass);
  }

  private getNoticeElement(form: HTMLFormElement): Element {
    let noticeElement = form.querySelector(' .wpcf7-response-output');

    if (!noticeElement) {
      noticeElement = document.createElement('div');
      noticeElement.classList.add('wpcf7-response-output');
      form.appendChild(noticeElement);
    }
    return noticeElement;
  }
}

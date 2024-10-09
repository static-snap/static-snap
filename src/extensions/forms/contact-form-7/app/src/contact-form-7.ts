import { FormBase, FormBaseGetNoticeMessageType } from '@staticsnap/frontend';

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

  protected onSubmit(_e: Event, form: HTMLFormElement, _submitData: unknown): void {
    this.setMessage(form, 'success');
  }

  protected onError(_e: Event, form: HTMLFormElement, _error: unknown): void {
    this.setMessage(form, 'error');
  }

  private setMessage(form: HTMLFormElement, type: FormBaseGetNoticeMessageType): void {
    const noticeElement = this.getNoticeElement(form);
    const message = this.getNoticeMessageOrRedirect(form, type);
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

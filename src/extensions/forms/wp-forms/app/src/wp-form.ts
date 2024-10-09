import { FormBase, FormBaseGetNoticeMessageType } from '@staticsnap/frontend';

export default class WpForm extends FormBase {
  private extensionMessagesClasses: Record<FormBaseGetNoticeMessageType, string> = {
    error: 'wpforms-error-alert',
    field_error: 'wpforms-error-alert',
    invalid_error: 'wpforms-error-alert',
    success: 'wpforms-confirmation-container-full',
  };

  constructor() {
    super('[data-static-snap-type="form"][data-static-snap-form-type="wpform"]');
  }

  protected onSubmit(_e: Event, form: HTMLFormElement, _submitData: unknown): void {
    this.setMessage(form, 'success');
  }

  protected onError(_e: Event, form: HTMLFormElement, _error: unknown): void {
    //console.log('ElementorForms onError', e, form, error);
    this.setMessage(form, 'error');
  }

  private setMessage(form: HTMLFormElement, type: FormBaseGetNoticeMessageType): void {
    const noticeElement = this.getNoticeElement(form);
    const message = this.getNoticeMessageOrRedirect(form, type);

    noticeElement.innerHTML = `<p>${message}</p>`;
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

import { FormBase, FormBaseGetNoticeMessageType } from '@staticsnap/frontend';
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

  protected onSubmit(_e: Event, form: HTMLFormElement, _submitData: unknown): void {
    //console.log('ElementorForms onSubmit', e, form, submitData);

    this.setMessage(form, 'success');
  }

  protected onError(_e: Event, form: HTMLFormElement, _error: unknown): void {
    //console.log('ElementorForms onError', e, form, error);
    this.setMessage(form, 'error');
  }

  private setMessage(form: HTMLFormElement, type: FormBaseGetNoticeMessageType): void {
    const noticeElement = this.getNoticeElement(form);
    const message = this.getNoticeMessageOrRedirect(form, type);
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

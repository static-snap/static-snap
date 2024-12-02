declare global {
  interface Window {
    elementorProFrontend: {
      modules: {
        popup: {
          closePopup: (popup: { id: string }) => void;
          showPopup: (popup: { id: string }) => void;
        };
      };
    };
  }
}
import ApiResponseInterface from '@staticsnap/dashboard/src/interfaces/api-response.interface';
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
    responseData: ApiResponseInterface<FormSubmitResponseInterface>
  ): void {
    const settings = this.getFormSettings(responseData);
    const knowSubmitActions = ['redirect', 'popup', 'webhook'];

    this.setMessage(
      form,
      responseData.type === 'item' && responseData?.data?.saved ? 'success' : 'error',
      settings
    );
    settings?.submit_actions?.some((action) => {
      if (knowSubmitActions.includes(action)) {
        if (action === 'redirect') {
          this.onRedirect(settings);
        }
        if (action === 'popup') {
          this.onPopup(settings);
        }
        if (action === 'webhook') {
          this.onWebhooks(settings, form);
        }
      }
    });
  }

  protected onRedirect(settings: WebsiteFormSettings): void {
    if (settings.redirect_to) {
      window.location.href = settings.redirect_to;
    }
  }

  protected onWebhooks(settings: WebsiteFormSettings, form: HTMLFormElement): void {
    //get all form data
    const formData = new FormData(form);

    settings.webhooks?.forEach((webhook) => {
      fetch(webhook.url, {
        body: JSON.stringify({
          ...Object.fromEntries(formData),
        }),
        headers: {
          'Content-Type': 'application/json',
        },
        method: 'POST',
      });
    });
  }

  protected onPopup(settings: WebsiteFormSettings): void {
    if (
      !settings.popup ||
      !settings.popup.popup_id ||
      !window.elementorProFrontend ||
      !window.elementorProFrontend.modules ||
      !window.elementorProFrontend.modules.popup
    ) {
      return;
    }
    if (settings.popup?.action === 'close') {
      window.elementorProFrontend.modules.popup.closePopup({ id: settings.popup.popup_id });
    } else {
      window.elementorProFrontend.modules.popup.showPopup({ id: settings.popup.popup_id });
    }
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

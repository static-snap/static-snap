export interface WebsiteFormSettings {
  submit_actions?: Array<string>;
  email?: Array<{
    bcc: null | string;
    cc: null | string;
    from: null | string;
    from_name: null | string;
    subject: null | string;
    to: null | string;
  }>;
  redirect_to?: null | string;
  popup?: null | {
    action: string | null;
    popup_id: string | null;
  };

  messages?: {
    error: string;
    invalid: string;
    required: string;
    success: string;
  };
}

interface FormSubmitResponseInterface {
  saved: boolean;
  websiteForm?: {
    website_form_id: string;
    website_form_user_id: string;
    website_form_website_id: string;
    website_form_extension_name: string;
    website_form_settings?: WebsiteFormSettings;

    website_form_created: string;
    website_form_updated: string;
  };
}

export default FormSubmitResponseInterface;

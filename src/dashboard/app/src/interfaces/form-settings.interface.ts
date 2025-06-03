export default interface FormSettingInterface {
  enabled: boolean;
  captcha_site_key?: string;
  captcha_type?: 'powcaptcha' | 'recaptcha';
  _captcha_secret_key?: string;
}

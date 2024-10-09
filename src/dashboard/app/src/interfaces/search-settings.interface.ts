import { ExtensionSettingValue } from './extension.interface';

export type SearchSetting = Record<string, ExtensionSettingValue>;

export default interface SearchSettingInterface {
  enabled: boolean;
  settings?: SearchSetting;
  type: string;
  // frontend settings
  frontend_settings?: Record<string, string | number | boolean>;
}

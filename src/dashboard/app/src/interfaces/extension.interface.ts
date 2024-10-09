export type ExtensionSettingValue = 'text' | 'textarea' | 'html' | 'number' | 'array' | 'boolean';
export type ExtensionSettingItems = {
  value: string | number;
  label: string;
};
export type ExtensionSettingType = {
  label: string;
  helperText?: string;
  type: ExtensionSettingValue;
  default?: string | number | boolean | ExtensionSettingItems[];
  items?: ExtensionSettingItems[];
  reloadItemsUrl?: string;
  dependsOn?: string;
  required?: boolean;
};

export type ExtensionSetting = Record<string, ExtensionSettingType>;

export default interface ExtensionInterface {
  name: string;
  type: string;
  settings: ExtensionSetting;
  available: boolean;
}

import { ExtensionSettingValue } from './extension.interface';

export type GithubSettings = {
  owner: string;
  repo: string;
  branch: string;
  path: string;
  token: string;
};

export type FileSettings = {
  path: string;
};

export type EnvironmentSetting = Record<string, ExtensionSettingValue>;

export default interface EnvironmentInterface {
  id: string;
  type: 'github' | 'file';
  destination_type: string;
  destination_path: string;
  settings?: EnvironmentSetting;
  name: string;
  valid?: boolean;
}

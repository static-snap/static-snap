import { ExtensionSettingType } from '@staticsnap/dashboard/interfaces/extension.interface';

import HtmlEditor from './html-editor';
import Select from './select';
import Switch from './switch';
import TextField from './text-field';

type InputForEnvironmentSettingProps = {
  name: string;
  setting: ExtensionSettingType;
};

const InputForExtensionsSetting = ({ setting, name }: InputForEnvironmentSettingProps) => {
  const renderInput = () => {
    if (setting.type === 'array') {
      return (
        <Select
          name={name}
          label={setting.label}
          helperText={setting.helperText}
          items={setting.items || []}
          reloadItemsUrl={setting.reloadItemsUrl}
          dependsOn={setting.dependsOn}
          required={setting.required}
        />
      );
    }
    if (setting.type === 'text') {
      const settingName = (name.split('.').pop() as string) || '';
      return (
        <TextField
          name={name}
          type={settingName?.charAt(0) === '_' ? 'password' : 'text'}
          label={setting.label}
          helperText={setting.helperText}
          required={setting.required}
        />
      );
    }
    if (setting.type === 'number') {
      return (
        <TextField
          name={name}
          label={setting.label}
          helperText={setting.helperText}
          type="number"
          required={setting.required}
        />
      );
    }
    // textarea
    if (setting.type === 'textarea') {
      return (
        <TextField
          name={name}
          label={setting.label}
          helperText={setting.helperText}
          multiline={true}
          rows={4}
          required={setting.required}
        />
      );
    }
    if (setting.type === 'html') {
      return (
        <HtmlEditor
          name={name}
          label={setting.label}
          helperText={setting.helperText}
          required={setting.required}
        />
      );
    }
    if (setting.type === 'boolean') {
      return <Switch name={name} label={setting.label} helperText={setting.helperText} />;
    }
    console.error(`Unknown setting type: ${setting.type}`);
    return null;
  };
  return renderInput();
};

export default InputForExtensionsSetting;

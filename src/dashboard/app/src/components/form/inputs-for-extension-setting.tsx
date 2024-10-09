import { ExtensionSetting } from '@staticsnap/dashboard/interfaces/extension.interface';

import InputForExtensionsSetting from './input-for-extension-setting';

type InputsForEnvironmentSettingProps = {
  setting: ExtensionSetting;
};

const InputsForExtensionsSetting = ({ setting }: InputsForEnvironmentSettingProps) => {
  const inputs = Object.keys(setting).map((name) => {
    const setttingValue = setting[name];

    return (
      <InputForExtensionsSetting key={name} setting={setttingValue} name={`settings.${name}`} />
    );
  });

  return inputs;
};

export default InputsForExtensionsSetting;

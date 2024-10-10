import { useCallback, useEffect, useState } from 'react';

import LoadingButton from '@mui/lab/LoadingButton';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Stack from '@mui/material/Stack';
import FormProvider from '@staticsnap/dashboard/components/form/form-provider';
import Select from '@staticsnap/dashboard/components/form/select';
import Switch from '@staticsnap/dashboard/components/form/switch';
import useOptions from '@staticsnap/dashboard/hooks/use-options';
import ExtensionInterface, {
  ExtensionSetting,
  ExtensionSettingValue,
} from '@staticsnap/dashboard/interfaces/extension.interface';
import SearchSettingInterface from '@staticsnap/dashboard/interfaces/search-settings.interface';
import { Constants } from '@staticsnap/frontend';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

import InputsForExtensionsSetting from '../form/inputs-for-extension-setting';
import TextField from '../form/text-field';

type SearchSettingsFormProps = {
  title: string;
};

const SearchSettingsForm = ({ title }: SearchSettingsFormProps) => {
  const searchOptions = useOptions('search');

  const [isLoading, setIsLoading] = useState(true);
  const [searchExtensions, setSearchExtensions] = useState<Record<string, ExtensionInterface>>({});
  const [value] = useState<SearchSettingInterface>();
  const [searchSettings, setSearchSettings] = useState<ExtensionSetting>({});
  const defaultValues: SearchSettingInterface = {
    enabled: false,
    frontend_settings: {
      search_results_selector: '',
      search_selector: '',
    },
    settings: {},
    type: 'fuse-js',
  };

  const methods = useForm({
    defaultValues,
  });

  const isDirty = methods.formState.isDirty;

  const onSubmit = useCallback(
    async (_data: SearchSettingInterface) => {
      setIsLoading(true);

      await searchOptions.setOptions(_data as unknown as Record<string, unknown>);

      setIsLoading(false);
      methods.reset(methods.getValues());
    },
    [methods, searchOptions]
  );

  // watch type change.
  const selectedType = methods.watch(['type'])[0];
  const searchEnabled = methods.watch(['enabled'])[0];

  const getSearchOptions = useCallback(async () => {
    const options = await searchOptions.getOptions();
    if (options) {
      methods.reset(options);
    }
  }, [methods, searchOptions]);

  useEffect(() => {
    const buildSettings = async () => {
      if (selectedType && searchExtensions[selectedType]) {
        // fill default values from settings

        const value = methods.getValues();

        const settingsValues = Object.keys(searchExtensions[selectedType].settings).reduce(
          (acc, name) => {
            let formValue = undefined;
            if (value && value.settings && value.settings[name]) {
              formValue = value.settings[name];
            }
            const settingValue =
              formValue ||
              ((searchExtensions[selectedType].settings[name].default ||
                '') as ExtensionSettingValue);
            acc[name] = settingValue;
            return acc;
          },
          {} as { [key: string]: ExtensionSettingValue }
        );
        methods.setValue(`settings`, settingsValues);
        setSearchSettings(searchExtensions[selectedType].settings);
      }
    };
    buildSettings();
  }, [selectedType, searchExtensions, methods, value, searchOptions.currentOptions]);

  const getSearchExtensions = useCallback(() => {
    apiFetch({ path: '/static-snap/v1/extensions/search' }).then((types) => {
      setSearchExtensions(types as Record<string, ExtensionInterface>);
      setIsLoading(false);
    });
  }, []);

  useEffect(() => {
    getSearchExtensions();
    getSearchOptions();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const searchExtensionsForSelect = Object.keys(searchExtensions).map((type) => {
    const current = searchExtensions[type];
    const disabled = !current.available;

    return {
      disabled,
      label: type,
      value: type,
    };
  });

  return (
    <Card elevation={0}>
      <CardHeader title={title} />
      <CardContent>
        {/* Form */}
        <FormProvider methods={methods} onSubmit={methods.handleSubmit(onSubmit)}>
          <Stack direction="column" spacing={2}>
            {/* use Search ? */}
            <Switch name="enabled" label={__('Enable search', 'static-snap')} />
            {searchEnabled && !isLoading && (
              <>
                {/* Type */}
                <Select
                  name="type"
                  items={searchExtensionsForSelect}
                  label="Type"
                  required={true}
                />
                <TextField
                  name={'frontend_settings.search_selector'}
                  label={__('Search selector', 'static-snap')}
                  helperText={sprintf(
                    __(
                      'CSS selector for search input, if empty the default value is: %s',
                      'static-snap'
                    ),
                    Constants.defaultSearchInputSelector
                  )}
                  required={false}
                />
                <TextField
                  name={'frontend_settings.search_results_selector'}
                  label={__('Search results selector', 'static-snap')}
                  helperText={sprintf(
                    __(
                      'CSS selector for search input, if empty the default value is: %s',
                      'static-snap'
                    ),
                    Constants.defaultSearchResultsSelector
                  )}
                  required={false}
                />
                {/* Dynamic setttings */}
                <InputsForExtensionsSetting setting={searchSettings} />
              </>
            )}
            {/* Save */}
            <Stack direction="row" sx={{ mt: 3 }}>
              <LoadingButton
                type="submit"
                variant="contained"
                loading={isLoading}
                disabled={!isDirty}
              >
                {__('Save', 'static-snap')}
              </LoadingButton>
            </Stack>
          </Stack>
        </FormProvider>
      </CardContent>
    </Card>
  );
};

export default SearchSettingsForm;

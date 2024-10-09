import { useCallback, useEffect, useMemo, useState } from 'react';

import LoadingButton from '@mui/lab/LoadingButton';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Stack from '@mui/material/Stack';
import FormProvider from '@staticsnap/dashboard/components/form/form-provider';
import InputsForExtensionsSetting from '@staticsnap/dashboard/components/form/inputs-for-extension-setting';
import Select from '@staticsnap/dashboard/components/form/select';
import TextField from '@staticsnap/dashboard/components/form/text-field';
import EnvironmentTypeInterface from '@staticsnap/dashboard/interfaces/environment-type.interface';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';
import {
  ExtensionSetting,
  ExtensionSettingValue,
} from '@staticsnap/dashboard/interfaces/extension.interface';
import { paths } from '@staticsnap/dashboard/routes/paths';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

import UrlTextField from '../form/url-text-field';

type EnvironmentFormProps = {
  title: string;
  value?: EnvironmentInterface;
  errors?: Record<string, string>;
  onSubmit: (data: EnvironmentInterface) => void;
  onDelete?: (data: EnvironmentInterface) => void;
};
const EnvironmentForm = ({ title, value, onSubmit, onDelete, errors }: EnvironmentFormProps) => {
  const navigate = useNavigate();
  const [isLoading, setIsLoading] = useState(true);
  const [environmentTypes, setEnvironmentTypes] = useState<
    Record<string, EnvironmentTypeInterface>
  >({});
  const [environmentSettings, setEnvironmentSettings] = useState<ExtensionSetting>({});

  const defaultValues: EnvironmentInterface = useMemo(
    () =>
      value || {
        destination_path: '',
        destination_type: '',
        id: '',
        name: '',
        type: 'file',
        valid: true,
      },
    [value]
  );

  const methods = useForm({
    defaultValues,
  });

  const isDirty = methods.formState.isDirty;

  // watch type change.
  const selectedType = methods.watch(['type'])[0];
  const selectedDestinationType = methods.watch(['destination_type'])[0];

  useEffect(() => {
    if (selectedType && environmentTypes[selectedType]) {
      const environmentType = environmentTypes[selectedType];

      if (!environmentType.isReadyToUse) {
        const confirm = window.confirm(environmentType.isReadyToUseDisabledMessage);
        console.log('confirm', environmentType.isReadyToUseExtraSetupUrl);
        if (confirm && environmentType.isReadyToUseExtraSetupUrl) {
          navigate(environmentType.isReadyToUseExtraSetupUrl as string);
          console.log('navigate to', environmentType.isReadyToUseExtraSetupUrl);
        }
      }
    }
  }, [selectedType, environmentTypes]);

  useEffect(() => {
    if (selectedType && environmentTypes[selectedType]) {
      // fill default values from settings
      const settingsValues = Object.keys(environmentTypes[selectedType].settings).reduce(
        (acc, name) => {
          let formValue: ExtensionSettingValue | undefined = undefined;
          if (value && value.settings && value.settings[name]) {
            formValue = value.settings[name];
          }
        /*   if (formValue && environmentTypes[selectedType].settings[name].type === 'array') {
            // check if formValue still valid
            const items = environmentTypes[selectedType].settings[name].items;
            const isValid = items?.find((item) => item.value === formValue);
            if (!isValid) {
              formValue = undefined;
            }
          } */
          const settingValue =
            formValue ||
            ((environmentTypes[selectedType].settings[name].default ||
              '') as ExtensionSettingValue);
          acc[name] = settingValue;
          return acc;
        },
        {} as { [key: string]: ExtensionSettingValue }
      );
      methods.setValue('settings', settingsValues);
      setEnvironmentSettings(environmentTypes[selectedType].settings);
    }
  }, [selectedType, environmentTypes, methods, value]);

  // set errors
  useEffect(() => {
    if (errors) {
      Object.keys(errors).map((key) =>
        methods.setError(key as keyof EnvironmentInterface, {
          message: errors[key],
          type: 'manual',
        })
      );
    }
  }, [errors, methods]);

  const getTypes = () => {
    apiFetch({ path: '/static-snap/v1/extensions/environment_type' }).then((types) => {
      setEnvironmentTypes(types as Record<string, EnvironmentTypeInterface>);
      setIsLoading(false);
    });
  };

  const onDeleteCallback = useCallback(() => {
    if (onDelete) {
      onDelete(defaultValues);
    }
  }, [onDelete, defaultValues]);

  useEffect(() => {
    getTypes();
  }, []);

  const environmentTypesForSelect = Object.keys(environmentTypes).map((type) => {
    const current = environmentTypes[type];
    const disabled = !current.available;
    let needsConnectText = current.needsConnect
      ? ' ' + __('(needs Static Snap Connect)', 'static-snap')
      : '';
    if (current.disabledReason) {
      needsConnectText = '(' + current.disabledReason + ')';
    }
    return {
      disabled,
      label: type + (current.available ? '' : needsConnectText),
      value: type,
    };
  });

  return (
    <Card elevation={0}>
      <CardHeader
        title={title}
        action={
          <Button variant="text" color="inherit" href={paths.environments.index}>
            Back
          </Button>
        }
      />
      <CardContent>
        {/* Form */}
        <FormProvider methods={methods} onSubmit={methods.handleSubmit(onSubmit)}>
          <Stack direction="column" spacing={2}>
            {/* Type */}
            <Select name="type" items={environmentTypesForSelect} label="Type" />
            {/* Name */}
            <TextField name="name" label="Name" required />
            <Select
              name="destination_type"
              required
              items={[
                { label: 'Absolute', value: 'absolute' },
                { label: 'Relative', value: 'relative' },
              ]}
              label="Destination Type"
            />
            {selectedDestinationType === 'absolute' ? (
              <UrlTextField name="destination_path" label="Destination Path" required />
            ) : (
              <TextField name="destination_path" label="Destination Path" required />
            )}

            {/* Dynamic setttings */}
            <InputsForExtensionsSetting setting={environmentSettings} />
            {/* Save */}
            <Stack
              direction="row"
              alignItems={onDelete ? 'flex-start' : 'flex-end'}
              justifyContent={onDelete ? 'space-between' : 'end'}
              sx={{ mt: 3 }}
            >
              {onDelete && (
                <LoadingButton
                  variant="text"
                  color="error"
                  loading={isLoading}
                  onClick={onDeleteCallback}
                >
                  {__('Delete', 'static-snap')}
                </LoadingButton>
              )}
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

export default EnvironmentForm;

import { useCallback, useEffect, useState } from 'react';

import LoadingButton from '@mui/lab/LoadingButton';
import Alert, { AlertColor } from '@mui/material/Alert';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Snackbar from '@mui/material/Snackbar';
import Stack from '@mui/material/Stack';
import FormProvider from '@staticsnap/dashboard/components/form/form-provider';
import Switch from '@staticsnap/dashboard/components/form/switch';
import useOptions from '@staticsnap/dashboard/hooks/use-options';
import ConnectInterface from '@staticsnap/dashboard/interfaces/connect.interface';
import FormSettingInterface from '@staticsnap/dashboard/interfaces/form-settings.interface';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch, { APIFetchOptions } from '@wordpress/api-fetch';
import { useForm } from 'react-hook-form';

import ConnectFallback from '../connect-fallback';
import TextField from '../form/text-field';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';

type FormSettingsProps = {
  title: string;
};

const FormSettings = ({ title }: FormSettingsProps) => {
  const formOptions = useOptions('forms');
  const connectOptions = useOptions<ConnectInterface>('connect');
  const [showSnakbar, setShowSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState('');
  const [snakbarSeverity, setSnackbarSeverity] = useState<AlertColor>('error');

  const [isLoading, setIsLoading] = useState(true);
  const [syncLoading, setSyncLoading] = useState(false);

  const defaultValues: FormSettingInterface = {
    _google_recaptcha_secret_key: '',
    enabled: false,
    google_recaptcha_site_key: '',
  };

  const methods = useForm({
    defaultValues,
  });

  const isDirty = methods.formState.isDirty;
  const enabled = methods.watch('enabled');

  const onSync = useCallback(async () => {
    setSyncLoading(true);
    const response = await apiFetch<{
      saved: boolean;
      message: string;
    }>({
      method: 'POST',
      path: '/static-snap/v1/extensions/forms/sync',
    });

    setSyncLoading(false);
    setSnackbarMessage(response.message);
    setSnackbarSeverity(response.saved ? 'success' : 'error');
    setShowSnackbar(true);
  }, []);

  const onSubmit = useCallback(
    async (_data: FormSettingInterface) => {
      setIsLoading(true);

      try {
        await formOptions.setOptions(_data as unknown as Record<string, unknown>);
      } catch (e: unknown) {
        const error = e as { message: string };
        setSnackbarMessage(error.message);
        setSnackbarSeverity('error');
        setShowSnackbar(true);
      }

      setIsLoading(false);
      methods.reset(methods.getValues());
    },
    [methods, formOptions]
  );

  const getFormOptions = useCallback(async () => {
    const options = await formOptions.getOptions();
    if (options) {
      methods.reset(options);
    }
  }, [methods, formOptions]);

  useEffect(() => {
    const buildSettings = async () => {
      if (!formOptions.currentOptions) {
        await getFormOptions();
        setIsLoading(false);
      }
    };
    buildSettings();
    connectOptions.getOptions();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <ConnectFallback
      connect={connectOptions.currentOptions as ConnectInterface}
      isLoading={!connectOptions.currentOptionsHasBeenCalled}
    >
      <Card elevation={0}>
        <Snackbar
          anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
          open={showSnakbar}
          autoHideDuration={6000}
          onClose={() => setShowSnackbar(false)}
        >
          <Alert
            onClose={() => setShowSnackbar(false)}
            severity={snakbarSeverity}
            variant="filled"
            sx={{ color: 'white', width: '100%' }}
          >
            {snackbarMessage}
          </Alert>
        </Snackbar>
        <CardHeader title={title} />
        <CardContent>
          {/* Form */}
          <FormProvider methods={methods} onSubmit={methods.handleSubmit(onSubmit)}>
            <Stack direction="column" spacing={2}>
              {/* use Search ? */}
              <Switch name="enabled" label={__('Enable forms', 'static-snap')} />
              {enabled && (
                <>
                  {/* Recaptcha public Key */}
                  <TextField
                    name="google_recaptcha_site_key"
                    label={__('Recaptcha public Key', 'static-snap')}
                  />

                  {/* Recaptcha Secret Key */}
                  <TextField
                    name="_google_recaptcha_secret_key"
                    type="password"
                    label={__('Recaptcha Secret Key', 'static-snap')}
                  />

                  <Alert severity="info">
                    {__(
                      'The Recaptcha private key will be sent to the Static Snap Server. We will use these keys to protect your forms from spam.',
                      'static-snap'
                    )}
                  </Alert>
                  <Alert severity="info">
                    {__('You can get your Recaptcha keys from: ', 'static-snap')}
                    <a
                      href="https://www.google.com/recaptcha/admin/create"
                      target="_blank"
                      rel="noreferrer"
                    >
                      https://www.google.com/recaptcha/admin/create
                    </a>
                  </Alert>

                  <Alert severity="info">
                    <Box>
                      <p>
                        {__(
                          'You can sync the form settings without triggering a new deployment. This will update the form settings on the server, including submit actions and response messages from your plugins',
                          'static-snap'
                        )}
                      </p>
                    </Box>
                    <Box justifyContent={'flex-end'} display={'flex'}>
                      <LoadingButton
                        size="small"
                        variant="contained"
                        onClick={onSync}
                        loading={syncLoading}
                      >
                        Click here to sync form settings
                      </LoadingButton>
                    </Box>
                  </Alert>
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
    </ConnectFallback>
  );
};

export default FormSettings;

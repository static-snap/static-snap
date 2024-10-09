import { useCallback, useEffect, useState } from 'react';

import { Alert, Box, Button, Tooltip, Typography } from '@mui/material';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import ConnectFallback from '@staticsnap/dashboard/components/connect-fallback';
import Icon from '@staticsnap/dashboard/components/icon';
import { TableContainer } from '@staticsnap/dashboard/components/table/table';
import { STATIC_SNAP_URL } from '@staticsnap/dashboard/constants';
import useOptions from '@staticsnap/dashboard/hooks/use-options';
import useStaticSnapAPI from '@staticsnap/dashboard/hooks/use-static-snap-api';
import ConnectInterface from '@staticsnap/dashboard/interfaces/connect.interface';
import GitInstallationInterface from '@staticsnap/dashboard/interfaces/git-installation.interface';
import { __, sprintf } from '@wordpress/i18n';
import LightTooltip from '@staticsnap/dashboard/components/light-tooltip';

const GithubIndex = () => {
  const options = useOptions<ConnectInterface>('connect');

  const getUserAppInstallationRequest = useStaticSnapAPI(
    '/github/installations/get-user-installations'
  );

  const [githubUserIsInvalid, setGithubUserIsInvalid] = useState(false);

  const [installations, setInstallations] = useState<GitInstallationInterface[]>([]);

  const getUserAppInstallations = useCallback(async (): Promise<GitInstallationInterface[]> => {
    const response = await getUserAppInstallationRequest.request<GitInstallationInterface>();
    if (response.type === 'error') {
      setGithubUserIsInvalid(true);
    }
    if (response.type !== 'items') {
      return [];
    }
    setInstallations(response.data);
  }, [getUserAppInstallationRequest]);

  const openInstallAppPopup = useCallback(() => {
    const width = 800;
    const height = 600;
    const left = window.screen.width / 2 - width / 2;
    const top = window.screen.height / 2 - height / 2;
    const url = new URL(STATIC_SNAP_URL + '/github/install-popup');
    if (githubUserIsInvalid) {
      // we need to regenerate the token
      url.searchParams.append('force-login', 'true');
    }
    const openedWindow = window.open(
      url,
      'github-install',
      `width=${width},height=${height},top=${top},left=${left}`
    );

    // when the popup is closed, reload the page
    const interval = setInterval(() => {
      if (!openedWindow || openedWindow.closed) {
        clearInterval(interval);
        getUserAppInstallations();
      }
    }, 1000);
  }, [getUserAppInstallations, githubUserIsInvalid]);

  useEffect(() => {
    const fetchUserAppInstallations = async () => {
      await getUserAppInstallations();
    };
    fetchUserAppInstallations();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  useEffect(() => {
    options.getOptions();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <ConnectFallback
      connect={options.currentOptions as ConnectInterface}
      isLoading={!options.currentOptionsHasBeenCalled}
    >
      <Card elevation={0}>
        <CardHeader title={__('Github', 'static-snap')} />
        <CardContent>
          <Typography variant="body1">
            {__(
              'Install the GitHub application on the accounts you want to deploy from. This will enable Static Snap to manage the deployment of your sites seamlessly.',
              'static-snap'
            )}
            <LightTooltip
              title={sprintf(
                __(
                  'Please ensure that the email address associated with your Static Snap account (%s) matches the email address of your GitHub account, or that your Static Snap account has the necessary access to the GitHub accounts where the app is installed. Otherwise, you will not be able to view the installations.',
                  'static-snap'
                ),
                options.currentOptions?.user.user_email
              )}
            >
              <Icon icon="mdi:information" />
            </LightTooltip>
          </Typography>

          {/*
		  Tooltip message that will say that the static snap account should be use the same email address as the github account
		  Sino no se mostrara las instalaciones, las instalaciones que se muestran son con las relacionas con el usuario email@email.com
		  o tambien si el email tiene acceso a las cuentas que en la que se instalo la app
		  */}

          <Button
            onClick={openInstallAppPopup}
            startIcon={<Icon icon="mdi:github" />}
            variant="contained"
            color="secondary"
          >
            {installations.length > 0
              ? __('Add another GitHub Account', 'static-snap')
              : __('Install GitHub App', 'static-snap')}
          </Button>
          <Box sx={{ mt: 2 }}>
            <TableContainer
              loading={!options.currentOptionsHasBeenCalled}
              rows={installations.map((installations) => ({
                id: installations.git_installation_id,
                ...installations,
              }))}
              columns={[
                { field: 'git_installation_id', headerName: 'ID' },
                { field: 'git_installation_owner', headerName: 'Account' },
              ]}
            />
          </Box>
        </CardContent>
      </Card>
    </ConnectFallback>
  );
};

export default GithubIndex;

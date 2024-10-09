import { useCallback, useEffect, useState } from 'react';

import LoadingButton from '@mui/lab/LoadingButton';
import Alert from '@mui/material/Alert';
import Avatar from '@mui/material/Avatar';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemIcon from '@mui/material/ListItemIcon';
import Typography from '@mui/material/Typography';
import Icon from '@staticsnap/dashboard/components/icon';
import { STATIC_SNAP_URL } from '@staticsnap/dashboard/constants';
import ConnectStatusInterface from '@staticsnap/dashboard/interfaces/connect-status.interface';
import ConnectInterface from '@staticsnap/dashboard/interfaces/connect.interface';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';
import CardActionArea from '@mui/material/CardActionArea';
import CardMedia from '@mui/material/CardMedia';
import { Button } from '@mui/material';

const ConnectIndex = () => {
  const [code, setCode] = useState<boolean | string>(false);
  const [connected, setConnected] = useState<boolean | undefined>(undefined);
  const [settingsConnect, setSettingsConnect] = useState<ConnectInterface | undefined>(undefined);
  const [connectStatus, setConnectStatus] = useState<ConnectStatusInterface | undefined>(undefined);
  const [isLoading, setIsLoading] = useState(true);

  const escapedReturnURL = encodeURIComponent(window.location.href);

  const disconnectHandler = useCallback(async () => {
    // confirm disconnect
    if (!window.confirm(__('Are you sure you want to disconnect?', 'static-snap'))) {
      return;
    }
    setIsLoading(true);
    const response = (await apiFetch({
      method: 'DELETE',
      path: '/static-snap/v1/connect',
    })) as { disconnected: boolean };
    setIsLoading(false);
    setConnected(response.disconnected ? undefined : true);
    setSettingsConnect(undefined);
  }, []);

  // get code in searchQuery
  useEffect(() => {
    const searchParams = new URLSearchParams(window.location.search);

    if (searchParams.has('code')) {
      setCode(searchParams.get('code') as string);
    }
  }, []);

  useEffect(() => {
    const connect = async () => {
      const response = (await apiFetch({
        data: {
          code,
        },
        method: 'POST',
        path: '/static-snap/v1/connect',
      })) as { connected: boolean };
      setConnected(response.connected);

      // remove search code from searchQuery
      const searchParams = new URLSearchParams(window.location.search);
      searchParams.delete('code');
      // get # part of the URL
      const hash = window.location.hash;
      window.history.replaceState(
        {},
        document.title,
        `${window.location.pathname}?${searchParams}${hash}`
      );
      setCode(false);
    };

    if (code) {
      connect();
    }
  }, [code]);

  useEffect(() => {
    const getSettings = async () => {
      const response = (await apiFetch({
        path: '/static-snap/v1/options',
      })) as { connect: ConnectInterface };

      setSettingsConnect(response.connect as ConnectInterface);
      if (response.connect && response.connect.installation_id) {
        const statusResponse = (await apiFetch({
          path: '/static-snap/v1/connect/status',
        })) as ConnectStatusInterface;
        setConnectStatus(statusResponse);
      }
      setIsLoading(false);
    };

    getSettings();
  }, [connected]);

  return (
    <Card elevation={0}>
      <CardHeader title={__('Connect with StaticSnap', 'static-snap')} />
      <CardContent>
        {connected === true && (
          <Alert icon={<Icon icon={'material-symbols:check'} />} severity="success">
            {__('Connected successfully!', 'static-snap')}
          </Alert>
        )}
        {connected === false && (
          <Alert icon={<Icon icon={'material-symbols:error'} />} severity="error">
            {__('Failed to connect. Please try again.', 'static-snap')}
          </Alert>
        )}
        <p>
          {__(
            'Transform your dynamic WordPress site into a high-performance, secure static website with Static Snap! By connecting your site to our platform, you can seamlessly integrate with GitHub, enabling automatic deployments to top-tier hosting services like Vercel, Cloudflare Pages, Amazon S3, Netlify, AWS Amplify, Heroku, GitLab Pages, and Bitbucket Cloud. Experience the ease of deploying front-end frameworks and optimize your project’s reach and flexibility. Join Static Snap today and start deploying smarter!						',
            'static-snap'
          )}
        </p>
        {settingsConnect && settingsConnect.installation_id ? (
          <>
            <Alert severity="info" sx={{ mb: 5 }}>
              <Box
                sx={{
                  alignItems: 'center',
                  display: 'flex',
                  flexDirection: 'row',
                }}
              >
                <Avatar src={settingsConnect.user.user_avatar_url} sx={{ mr: 1 }}>
                  {settingsConnect.user.user_name.charAt(0)}
                </Avatar>
                <Box
                  sx={{
                    alignItems: 'start',
                    display: 'flex',
                    flexDirection: 'column',
                  }}
                >
                  <Typography variant="body2" component="p">
                    {sprintf(
                      __('You are connected with %s', 'static-snap'),
                      settingsConnect.user.user_email
                    )}
                  </Typography>
                </Box>
              </Box>
            </Alert>

            {connectStatus?.has_valid_website_license === false &&
              connectStatus.connection_error !== true && (
                <>
                  <Alert severity="warning" sx={{ mb: 5 }}>
                    {__(
                      'Your website license is invalid. Please try connecting again. If the issue persists, contact support.',
                      'static-snap'
                    )}
                    {__('The license may be invalid due to the following reasons:', 'static-snap')}
                    <List dense>
                      <ListItem disablePadding>
                        <ListItemIcon sx={{ minWidth: 25 }}>
                          <Icon icon={'material-symbols:error'} />
                        </ListItemIcon>
                        {__('Your license has expired.', 'static-snap')}
                      </ListItem>
                      <ListItem disablePadding>
                        <ListItemIcon sx={{ minWidth: 25 }}>
                          <Icon icon={'material-symbols:error'} />
                        </ListItemIcon>
                        {__(
                          'The website URL has changed after connecting to Static Snap.',
                          'static-snap'
                        )}
                      </ListItem>
                      <ListItem disablePadding>
                        <ListItemIcon sx={{ minWidth: 25 }}>
                          <Icon icon={'material-symbols:error'} />
                        </ListItemIcon>
                        {__(
                          'The website Database settings have changed after connecting to Static Snap.',
                          'static-snap'
                        )}
                      </ListItem>
                      <ListItem disablePadding>
                        <ListItemIcon sx={{ minWidth: 25 }}>
                          <Icon icon={'material-symbols:error'} />
                        </ListItemIcon>
                        {__('The website has been removed from your account.', 'static-snap')}
                      </ListItem>
                      <ListItem disablePadding>
                        <ListItemIcon sx={{ minWidth: 25 }}>
                          <Icon icon={'material-symbols:error'} />
                        </ListItemIcon>
                        {__(
                          'You have exceeded the number of websites allowed by your license.',
                          'static-snap'
                        )}
                      </ListItem>
                    </List>
                  </Alert>
                </>
              )}
            {connectStatus?.connection_error === true && (
              <Alert severity="error" sx={{ mb: 5 }}>
                {__(
                  'An error occurred while connecting to Static Snap. Please try again. If the issue persists, contact support.',
                  'static-snap'
                )}
                <p>
                  {sprintf(__('Error message: %s', 'static-snap'), connectStatus?.error_message)}
                </p>
              </Alert>
            )}
            <LoadingButton
              variant="contained"
              size="large"
              color="error"
              sx={{ mt: 8 }}
              onClick={disconnectHandler}
              loading={isLoading}
            >
              {__('Disconnect', 'static-snap')}
            </LoadingButton>
          </>
        ) : (
          <>
            <Alert severity="info" sx={{ mb: 5 }}>
              {__(
                "Clicking on connect will redirect you to Static Snap's website to connect your WordPress site to the platform.",
                'static-snap'
              )}
            </Alert>
            <LoadingButton
              loading={!!code}
              disabled={connected === true || isLoading}
              variant="contained"
              size="large"
              href={`${STATIC_SNAP_URL}/connect-website?website=${escapedReturnURL}`}
            >
              {__('Connect', 'static-snap')}
            </LoadingButton>
          </>
        )}
      </CardContent>
    </Card>
  );
};

export default ConnectIndex;

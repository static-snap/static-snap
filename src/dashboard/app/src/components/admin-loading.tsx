import React, { useCallback, useEffect, useState } from 'react';

import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import CircularProgress, { CircularProgressProps } from '@mui/material/CircularProgress';
import Menu from '@mui/material/Menu';
import Tooltip from '@mui/material/Tooltip';
import Typography from '@mui/material/Typography';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';
import StatusInterface from '@staticsnap/dashboard/interfaces/status.interface';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import DeploymentDoneDialog from './deployment-done-dialog';
import EnvironmentsTabs from './environments-tabs';
import LinearProgressWithLabel from './linear-progress-with-label';
import Logo from './logo';

type AdminLoadingProps = CircularProgressProps & {
  status: StatusInterface;
};

const AdminLoading = ({ status, ...props }: AdminLoadingProps) => {
  const [environments, setEnvironments] = useState<EnvironmentInterface[]>([]);

  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const open = Boolean(anchorEl);

  const handleClick = useCallback((event: React.MouseEvent<HTMLButtonElement>) => {
    setAnchorEl(event.currentTarget);
  }, []);
  const handleClose = useCallback(() => {
    setAnchorEl(null);
  }, []);

  const cancelDeployment = useCallback(async () => {
    if (!status.is_running) {
      return;
    }
    await apiFetch({
      method: 'POST',
      path: '/static-snap/v1/deployments/cancel',
    });
  }, [status]);

  const getEnvironments = useCallback(async () => {
    const response = await apiFetch({ path: '/static-snap/v1/environments' });
    setEnvironments(Array.from(response as EnvironmentInterface[]));
  }, []);

  useEffect(() => {
    // listen to global event close menu
    window.addEventListener('static-snap/close-deployment-menu', handleClose);
    window.addEventListener('static-snap/environments-updated', getEnvironments);
    return () => {
      window.removeEventListener('static-snap/close-deployment-menu', handleClose);
      window.removeEventListener('static-snap/environments-updated', getEnvironments);
    };
  });

  const TooltipTitle = status.is_running ? (
    <Box minWidth={300}>
      <Typography color="inherit">{__('Running', 'static-snap')}</Typography>
      <LinearProgressWithLabel
        variant="determinate"
        value={Math.round(status.last_deployment?.status_information?.percentage || 0)}
      />
      <strong>{status.last_deployment?.status_information?.current_task_description}...</strong>
      <Box sx={{ display: 'flex', justifyContent: 'center', marginTop: '8px' }}>
        <Button sx={{ marginLeft: 'auto' }} size="small" onClick={cancelDeployment} color="error">
          {__('Cancel', 'static-snap')}
        </Button>
      </Box>
    </Box>
  ) : null;

  useEffect(() => {
    getEnvironments();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Tooltip title={TooltipTitle} placement="bottom" arrow open={status.is_running}>
      <Box
        sx={{
          alignItems: 'center',
          display: 'inline-flex',
          height: {
            md: '32px !important',
            xs: '46px !important',
          },
          justifyContent: 'center',
          position: 'relative !important',
          //width: '32px !important',
        }}
      >
        <Button
          component={'a' as React.ElementType}
          onClick={handleClick}
          disabled={status.is_running}
          sx={{
            display: 'flex !important',
            position: 'relative !important',
            minWidth: '32px !important',
            // if open add wp styles
            ...(open && {
              background: '#2c3338 !important',
              color: '#72aee6 !important',
            }),
          }}
        >
          {status.is_running && (
            <CircularProgress
              variant="determinate"
              {...props}
              size={32}
              color="primary"
              value={status.last_deployment?.status_information?.percentage || 0}
              sx={{
                circle: {
                  transition: 'stroke-dashoffset 900ms cubic-bezier(0.4, 0, 0.2, 1) 0ms !important',
                },
                height: {
                  md: '32px !important',
                  xs: '44px !important',
                },
                transition: 'transform 900ms cubic-bezier(0.4, 0, 0.2, 1) 0ms !important',
                width: {
                  md: '32px !important',
                  xs: '44px !important',
                },
              }}
            />
          )}
          <Box
            sx={{
              alignItems: 'center',
              bottom: 0,
              display: 'flex',
              justifyContent: 'center',
              left: 0,
              position: 'absolute !important',
              right: 0,
              top: 0,
            }}
          >
            <Logo
              sx={{
                // keyframes
                '@keyframes spin': {
                  '0%': {
                    transform: 'rotate(0deg)',
                  },
                  '100%': {
                    transform: 'rotate(360deg)',
                  },
                },
                alignItems: 'center',
                display: 'flex',
                inset: '0 !important',
                justifyContent: 'center',
                position: 'absolute !important',

                svg: {
                  // rotate animation
                  ...(status.is_running && { animation: 'spin 2s linear infinite' }),
                  height: {
                    md: '22px !important',
                    xs: '30px !important',
                  },
                  path: {
                    fill: '#c3c4c7 !important',
                  },

                  width: {
                    md: '22px !important',
                    xs: '30px !important',
                  },
                },
              }}
            />
          </Box>
        </Button>
        {!status.is_running && (
          <Menu
            anchorEl={anchorEl}
            open={open}
            onClose={handleClose}
            elevation={9}
            anchorOrigin={{
              vertical: 'bottom',
              horizontal: 'center',
            }}
            transformOrigin={{
              vertical: 'top',
              horizontal: 'center',
            }}
            // change ul for a div
            MenuListProps={{
              component: 'div',
              sx: { paddingBottom: 0 },
            }}
            sx={{
              maxWidth: '90%',
            }}
          >
            <EnvironmentsTabs environments={environments} disabled={status.is_running} />
          </Menu>
        )}
        <DeploymentDoneDialog status={status} />
      </Box>
    </Tooltip>
  );
};

export default AdminLoading;

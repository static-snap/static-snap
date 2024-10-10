import React, { useState, useCallback, useEffect } from 'react';

import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import Slide from '@mui/material/Slide';
import { TransitionProps } from '@mui/material/transitions';
import StatusInterface from '@staticsnap/dashboard/interfaces/status.interface';
import { __, sprintf } from '@wordpress/i18n';

import DeploymentDoneDialogIcon from './icon';
import apiFetch from '@wordpress/api-fetch';

const Transition = React.forwardRef(function Transition(
  props: TransitionProps & {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    children: React.ReactElement<any, any>;
  },
  ref: React.Ref<unknown>
) {
  return <Slide direction="up" ref={ref} {...props} />;
});

type DeploymentDoneDialogProps = {
  status: StatusInterface;
};

export default function DeploymentDoneDialog({ status }: DeploymentDoneDialogProps) {
  const [open, setOpen] = useState(false);
  const [showDownloadButton, setShowDownloadButton] = useState(false);
  const [lastDeploymentEnvironmentId, setLastDeploymentEnvironmentId] = useState<string | null>(
    null
  );

  const handleClose = useCallback(() => {
    setOpen(false);
  }, []);
  const handleDownloadZipFile = useCallback(async () => {
    if (!lastDeploymentEnvironmentId) {
      console.error('lastDeploymentId is not defined');
      return;
    }
    const response = await apiFetch<{ success: boolean; url: string }>({
      method: 'POST',
      path: `/static-snap/v1/deployments-history/download/${lastDeploymentEnvironmentId}`,
    });
    if (response.url) {
      window.open(response.url, '_blank');
    }
  }, [lastDeploymentEnvironmentId]);

  useEffect(() => {
    if (status.is_done) {
      setOpen(status.is_done);
      setLastDeploymentEnvironmentId(status.last_deployment?.environment_id || null);
      try {
        const lastDeploymentSettings = JSON.parse(
          status.last_deployment?.environment_settings || '{}'
        );
        setShowDownloadButton(!!lastDeploymentSettings?.create_zip_file);
      } catch (e) {
        // nothing to do
        console.error(e);
      }
    }
  }, [
    status.is_done,
    status.last_deployment?.environment_settings,
    status.last_deployment?.environment_id,
  ]);

  return (
    <>
      <Dialog open={open} TransitionComponent={Transition} keepMounted onClose={handleClose}>
        <DialogTitle>{__('Congratulations', 'static-snap')}</DialogTitle>
        <DialogContent>
          <DialogContentText id="alert-dialog-slide-description">
            <Box flexDirection={'row'} alignItems={'center'} display={'flex'} gap={10}>
              <DeploymentDoneDialogIcon />
              {__(
                sprintf(
                  'Deployment for Environment %s was successful!',
                  status.last_deployment?.environment_name || null
                ),
                'static-snap'
              )}
            </Box>
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          {showDownloadButton && (
            <Button variant="contained" onClick={handleDownloadZipFile}>
              {__('Download ZIP file', 'static-snap')}
            </Button>
          )}
          <Button onClick={handleClose}>{__('Close', 'static-snap')}</Button>
        </DialogActions>
      </Dialog>
    </>
  );
}

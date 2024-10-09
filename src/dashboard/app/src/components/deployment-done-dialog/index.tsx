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

  const handleClose = useCallback(() => {
    setOpen(false);
  }, []);
  const handleVisitStaticSite = useCallback(() => {
    window.open('https://open.uwh.es', '_blank');
  }, []);

  useEffect(() => {
    if (status.is_done) {
      setOpen(status.is_done);
    }
  }, [status.is_done]);

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
                  'Deployment for Environment %s was successful! You can now visit the static site.',
                  status.last_deployment?.environment_name || null
                ),
                'static-snap'
              )}
            </Box>
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button variant="contained" onClick={handleVisitStaticSite}>
            {__('View static site', 'static-snap')}
          </Button>
          <Button onClick={handleClose}>{__('Close', 'static-snap')}</Button>
        </DialogActions>
      </Dialog>
    </>
  );
}

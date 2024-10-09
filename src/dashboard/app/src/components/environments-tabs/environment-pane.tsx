import { useCallback } from 'react';

import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Icon from '@staticsnap/dashboard/components/icon/icon';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

type EnvironmentPaneProps = {
  environment: EnvironmentInterface;
  disabled?: boolean;
};

export default function EnvironmentPane({ environment, disabled = false }: EnvironmentPaneProps) {
  const publish = useCallback(async () => {
    // emit static-snap/close-deployment-menu event
    window.dispatchEvent(new CustomEvent('static-snap/close-deployment-menu'));
    setTimeout(() => {
      // emit window event static-snap-status-update
      const event = new CustomEvent('static-snap/status-update');
      window.dispatchEvent(event);
    }, 500);
    apiFetch({
      data: environment,
      method: 'POST',
      path: '/static-snap/v1/environments/publish',
    });
  }, [environment]);

  const handleSettingsClick = useCallback(() => {
    // emit static-snap/close-deployment-menu event
    window.dispatchEvent(new CustomEvent('static-snap/close-deployment-menu'));
    window.location.href =
      window.location.origin +
      `/wp-admin/admin.php?page=static-snap#/environments/edit/${environment.id}`;
  }, [environment]);

  return (
    <Box flexDirection="row" justifyContent="space-between" width={'100%'} display="flex">
      <Button
        variant="contained"
        onClick={publish}
        endIcon={<Icon icon="grommet-icons:deploy" />}
        disabled={disabled}
      >
        {sprintf(__('Deploy to %s', 'static-snap'), environment.name)}
      </Button>
      <IconButton title={__('Settings', 'static-snap')} onClick={handleSettingsClick}>
        <Icon icon="material-symbols:settings" />
      </IconButton>
    </Box>
  );
}

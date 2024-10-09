import { useCallback, useEffect, useState } from 'react';

import apiFetch from '@wordpress/api-fetch';

import StatusInterface from '@staticsnap/dashboard/interfaces/status.interface';

const useStatus = () => {
  const [status, setStatus] = useState<StatusInterface>({
    is_cancelled: false,
    is_done: false,
    is_paused: false,
    is_processing: false,
    is_running: false,
    last_deployment: undefined,
  });

  const fetchStatus = useCallback(async () => {
    const response = await apiFetch({ path: '/static-snap/v1/status' });
    setStatus(response as StatusInterface);
  }, []);

  useEffect(() => {
    // fetch status every 5 seconds
    fetchStatus();
    const interval = setInterval(fetchStatus, 5000);

    return () => clearInterval(interval);
  }, [fetchStatus]);

  useEffect(() => {
    const listener = (_event: Event) => {
      fetchStatus();
    };
    // listen to window event static-snap-status-update
    window.addEventListener('static-snap/status-update', listener);
    return () => window.removeEventListener('static-snap/status-update', listener);
  }, [fetchStatus]);

  return status;
};

export default useStatus;

import { useEffect, useState } from 'react';

import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Skeleton from '@mui/material/Skeleton';
import Typography from '@mui/material/Typography';
import LastDeployment from '@staticsnap/dashboard/components/deployment-history/last-deployment';
import EnvironmentsTabs from '@staticsnap/dashboard/components/environments-tabs';
import useStatus from '@staticsnap/dashboard/hooks/use-status';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

const DashboardIndex = () => {
  const [environmentsLoading, setEnvironmentsLoading] = useState<boolean>(true);
  const [environments, setEnvironments] = useState<EnvironmentInterface[]>([]);
  const status = useStatus();

  useEffect(() => {
    const getEnvironments = async () => {
      const response = await apiFetch({ path: '/static-snap/v1/environments' });
      setEnvironments(Array.from(response as EnvironmentInterface[]));
      setEnvironmentsLoading(false);
    };
    getEnvironments();
  }, []);

  return (
    <Card elevation={0}>
      <CardHeader title={__('Dashboard', 'static-snap')} />
      <CardContent>
        <p>{__('Welcome back to Static Snap!', 'static-snap')}</p>
        {environmentsLoading ? (
          <Skeleton variant="rectangular" width={'100%'} height={35} />
        ) : (
          <>
            <Typography variant="h6">{__('Your environments:', 'static-snap')}</Typography>
            <EnvironmentsTabs
              environments={environments}
              disabled={status.is_running}
              justifyContent="start"
              header={(environment) => (
                <p>
                  {sprintf(
                    __('Your site will be deployed using %s engine.', 'static-snap'),
                    environment.type
                  )}
                </p>
              )}
            >
              {({ id }) => <LastDeployment environment_id={Number(id)} />}
            </EnvironmentsTabs>
          </>
        )}
      </CardContent>
    </Card>
  );
};

export default DashboardIndex;

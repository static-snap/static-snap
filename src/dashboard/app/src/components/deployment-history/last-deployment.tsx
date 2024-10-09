import { useEffect, useState } from 'react';

import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Skeleton from '@mui/material/Skeleton';
import DeploymentInterface from '@staticsnap/dashboard/interfaces/deployment.interface';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

type LastDeploymentProps = {
  environment_id: number;
};

const LastDeployment = ({ environment_id }: LastDeploymentProps) => {
  const [lastDeployment, setLastDeployment] = useState<DeploymentInterface | null>(null);
  const [lastDeploymentLoading, setLastDeploymentLoading] = useState<boolean>(true);

  useEffect(() => {
    const getLastDeployment = async () => {
      const response = await apiFetch({
        path: `/static-snap/v1/deployments-history/last/${environment_id}`,
      });
      setLastDeployment(response as DeploymentInterface);
      setLastDeploymentLoading(false);
    };
    getLastDeployment();
  }, [environment_id]);

  const start_date = lastDeployment?.start_time
    ? new Date(Number(lastDeployment?.start_time) * 1000).toLocaleString()
    : null;
  const end_date = lastDeployment?.end_time
    ? new Date(Number(lastDeployment?.end_time) * 1000).toLocaleString()
    : null;

  return (
    <Box>
      {lastDeploymentLoading ? (
        <Skeleton variant="rectangular" width={'100%'} height={35} />
      ) : (
        lastDeployment?.id && (
          <Card variant="outlined" sx={{ mt: 4 }}>
            <CardHeader title={__('Lastest Deployment', 'static-snap')} />
            <CardContent>
              <p>
                {__('Start date:', 'static-snap')}
                <strong> {start_date}</strong>
              </p>
              <p>
                {__('End date:', 'static-snap')}
                <strong> {end_date}</strong>
              </p>
              <p>
                {__('Created by:', 'static-snap')}
                <strong>
                  {' '}
                  {lastDeployment?.created_by_name} ({lastDeployment?.created_by_email})
                </strong>
              </p>
            </CardContent>
          </Card>
        )
      )}
    </Box>
  );
};

export default LastDeployment;

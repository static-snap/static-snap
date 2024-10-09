/**
 * This component will show the passed children if the connect is available, otherwise it will show a button to connect.
 */

import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Skeleton from '@mui/material/Skeleton';
import ConnectInterface from '@staticsnap/dashboard/interfaces/connect.interface';
import { paths } from '@staticsnap/dashboard/routes/paths';
import { __ } from '@wordpress/i18n';

type ConnectFallbackProps = {
  children: React.ReactNode;
  connect: ConnectInterface;
  isLoading?: boolean;
};
const ConnectFallback = ({ children, connect, isLoading = false }: ConnectFallbackProps) => {
  // if the connect is available, show the children
  if (connect) {
    return children;
  }

  if (isLoading) {
    return (
      <Card elevation={0}>
        <Skeleton variant="rectangular" height={100} />
        <CardContent>
          <Skeleton variant="text" />
          <Skeleton variant="text" />
        </CardContent>
      </Card>
    );
  }

  // if the connect is not available, show the connect button
  return (
    <Card elevation={0}>
      <CardHeader title={__('Connect to your account', 'static-snap')} />
      <CardContent>
        <p>{__('You need to connect to your account to access this page.', 'static-snap')}</p>
        <p>{__('Click the button below to connect.', 'static-snap')}</p>
        <Button color="primary" href={paths.connect.index} variant="contained">
          {__('Go to connect page', 'static-snap')}
        </Button>
      </CardContent>
    </Card>
  );
};

export default ConnectFallback;

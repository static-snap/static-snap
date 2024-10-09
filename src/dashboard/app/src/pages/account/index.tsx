import { useEffect } from 'react';

import Avatar from '@mui/material/Avatar';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardHeader from '@mui/material/CardHeader';
import Typography from '@mui/material/Typography';
import ConnectFallback from '@staticsnap/dashboard/components/connect-fallback';
import useOptions from '@staticsnap/dashboard/hooks/use-options';
import ConnectInterface from '@staticsnap/dashboard/interfaces/connect.interface';

const AccountIndex = () => {
  const options = useOptions<ConnectInterface>('connect');

  useEffect(() => {
    options.getOptions();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <ConnectFallback
      connect={options.currentOptions as ConnectInterface}
      isLoading={!options.currentOptionsHasBeenCalled}
    >
      <Card elevation={0}>
        <CardHeader title="Account" />
        <CardContent sx={{ flexDirection: 'column' }}>
          {
            // Create a user profile using options.currentOptions
            // use the avatar, name and email
          }
          <Box
            sx={{
              alignItems: 'center',
              display: 'flex',
            }}
          >
            <Avatar
              sx={{
                height: 56,
                width: 56,
              }}
              src={options.currentOptions?.user.user_avatar_url}
            />
            <Box sx={{ ml: 2 }}>
              <Typography variant="h6" component="h3">
                {options.currentOptions?.user.user_name}
              </Typography>
              <Typography variant="body2" component="p">
                {options.currentOptions?.user.user_email}
              </Typography>
            </Box>
          </Box>
        </CardContent>
      </Card>
    </ConnectFallback>
  );
};

export default AccountIndex;

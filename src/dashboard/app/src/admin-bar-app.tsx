import Box from '@mui/material/Box';

import AdminLoading from './components/admin-loading';
import useStatus from './hooks/use-status';
import ThemeProvider from './theme';

export default function AdminBarApp() {
  const status = useStatus();

  return (
    <ThemeProvider>
      <Box
        sx={{
          alignItems: 'center',
          backgroundColor: '#1d2327',
          display: 'flex',
          height: {
            md: '32px !important',
            xs: '46px !important',
          },
          justifyContent: 'center',
        }}
      >
        <AdminLoading status={status} />
      </Box>
    </ThemeProvider>
  );
}

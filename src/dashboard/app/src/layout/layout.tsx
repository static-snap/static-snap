import { Outlet } from 'react-router-dom';
import Main from './main';
import Nav from './nav';
import { useBoolean } from '@staticsnap/dashboard/hooks/use-boolean';
import Box from '@mui/material/Box';

const Layout = function () {
  const openNav = useBoolean();
  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: {
          md: 'row',
          xs: 'column',
        },
        paddingTop: { md: '1rem' },
      }}
    >
      <Nav openNav={openNav.value} onOpenNav={openNav.onTrue} onCloseNav={openNav.onFalse} />

      <Main>
        <Outlet />
      </Main>
    </Box>
  );
};

export default Layout;

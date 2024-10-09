import { useEffect } from 'react';

import AppBar from '@mui/material/AppBar';
import Toolbar from '@mui/material/Toolbar';
import Box from '@mui/material/Box';
import Drawer from '@mui/material/Drawer';
import IconButton from '@mui/material/IconButton';
import Stack from '@mui/material/Stack';
import { useLocation } from 'react-router-dom';

import Icon from '@staticsnap/dashboard/components/icon/icon';
import LogoFull from '@staticsnap/dashboard/components/logo-full';
import Scrollbar from '@staticsnap/dashboard/components/scrollbar';
import Sidebar from '@staticsnap/dashboard/components/sidebar';
import { SIDEBAR_WIDTH } from '@staticsnap/dashboard/components/sidebar/config';
import { SidebarSectionData } from '@staticsnap/dashboard/components/sidebar/types';
import { useNavData } from '@staticsnap/dashboard/hooks/use-nav-data';
import { useResponsive } from '@staticsnap/dashboard/hooks/use-responsive';

type Props = {
  openNav: boolean;
  onCloseNav: VoidFunction;
  onOpenNav: VoidFunction;
};

export default function Nav({ openNav, onCloseNav, onOpenNav }: Props) {
  const pathname = useLocation();

  const mdUp = useResponsive('up', 'md');

  const navData = useNavData();

  useEffect(() => {
    if (openNav) {
      onCloseNav();
    }

    const staticSnapMenu = document.getElementById('toplevel_page_static-snap');
    if (!staticSnapMenu) {
      return;
    }
    const staticSnapMenuItems = staticSnapMenu?.getElementsByTagName('li');
    if (!staticSnapMenuItems) {
      return;
    }
    // remove all current classes from menu items
    for (let i = 0; i < staticSnapMenuItems.length; i++) {
      const staticSnapMenuItem = staticSnapMenuItems[i];
      staticSnapMenuItem.classList.remove('current');
    }

    for (let i = 0; i < staticSnapMenuItems.length; i++) {
      const staticSnapMenuItem = staticSnapMenuItems[i];

      const staticSnapMenuItemLink = staticSnapMenuItem.getElementsByTagName('a')[0];
      if (!staticSnapMenuItemLink) {
        continue;
      }

      const staticSnapMenuItemLinkHref = staticSnapMenuItemLink.getAttribute('href');
      if (!staticSnapMenuItemLinkHref) {
        continue;
      }
      // get hash part
      const hashIndex = staticSnapMenuItemLinkHref.indexOf('#');
      let menuPath = '/';
      if (hashIndex !== -1) {
        menuPath = staticSnapMenuItemLinkHref.slice(
          hashIndex + 1,
          staticSnapMenuItemLinkHref.length
        );
      }

      if (pathname.pathname === menuPath) {
        staticSnapMenuItem.classList.add('current');
        return;
      }
      const defaultMenuPath = '/environments';

      if (menuPath === defaultMenuPath && pathname.pathname !== '/') {
        staticSnapMenuItem.classList.add('current');
        return;
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pathname]);

  const renderContent = (
    <Scrollbar
      sx={{
        '& .simplebar-content': {
          display: 'flex',
          flexDirection: 'column',
          height: 1,
        },
        height: 1,
      }}
    >
      <LogoFull
        sx={{
          mb: 1,
          ml: 4,
          mt: 5,
          svg: {
            maxWidth: { md: SIDEBAR_WIDTH - 50, xs: 150 },
          },
        }}
      />

      <Sidebar data={navData as SidebarSectionData[]} />

      <Box sx={{ flexGrow: 1 }} />
    </Scrollbar>
  );

  return (
    <>
      {mdUp ? (
        <Box
          component="nav"
          sx={{
            backgroundColor: 'background.default',
            flexShrink: {
              lg: 0,
            },
            minHeight: {
              lg: 'calc(100vh - 32px)',
            },
            width: {
              lg: SIDEBAR_WIDTH,
            },
          }}
        >
          <Stack
            sx={{
              height: 1,
              position: 'relative',
              width: SIDEBAR_WIDTH,
            }}
          >
            {renderContent}
          </Stack>
        </Box>
      ) : (
        <AppBar position="sticky" color="default">
          <Toolbar
            sx={{
              height: 64,
            }}
          >
            <IconButton
              onClick={onOpenNav}
              sx={{
                color: 'text.primary',
                ml: 1,
              }}
            >
              <Icon icon={'heroicons-outline:menu-alt-2'} />
            </IconButton>
            <Drawer
              open={openNav}
              onClose={onCloseNav}
              PaperProps={{
                sx: {
                  maxWidth: '20rem',
                  pt: 6,
                  width: '80vw',
                },
              }}
            >
              {renderContent}
            </Drawer>
          </Toolbar>
        </AppBar>
      )}
    </>
  );
}

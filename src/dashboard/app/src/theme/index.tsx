import { useMemo } from 'react';

import GlobalStyles from '@mui/material/GlobalStyles';
import ScopedCssBaseline from '@mui/material/ScopedCssBaseline';
import { createTheme, ThemeProvider as MuiThemeProvider, ThemeOptions } from '@mui/material/styles';
import merge from 'lodash/merge';

import { customShadows } from './custom-shadows';
import { componentsOverrides } from './overrides';
import { palette } from './palette';
import { shadows } from './shadows';
import { typography } from './typography';

type Props = {
  children: React.ReactNode;
  isSettings?: boolean;
};

export default function ThemeProvider({ children, isSettings = false }: Props) {
  const baseOption = useMemo(
    () => ({
      // match wordpress breakpoints
      breakpoints: {
        values: {
          xs: 0,
          // eslint-disable-next-line sort-keys
          sm: 600,
          // eslint-disable-next-line sort-keys
          md: 782,
          // eslint-disable-next-line sort-keys
          lg: 1024,
          xl: 1280,
        },
      },
      customShadows: customShadows('light'),
      palette: palette('light'),
      shadows: shadows('light'),
      shape: {
        borderRadius: 8,
      },
      typography,
    }),
    []
  );

  const memoizedValue = useMemo(
    () =>
      merge(
        // Base
        baseOption
      ),
    [baseOption]
  );

  const theme = createTheme(memoizedValue as ThemeOptions);

  theme.components = merge(componentsOverrides(theme));

  return (
    <MuiThemeProvider theme={theme}>
      <GlobalStyles
        styles={
          isSettings
            ? {}
            : // adapt the styles to the settings page
              {
                '.toplevel_page_static-snap  #wpadminbar ul#wp-admin-bar-root-default  li#wp-admin-bar-static-snap-admin-bar':
                  {
                    display: 'block',
                  },
                '.toplevel_page_static-snap  #wpcontent': {
                  paddingLeft: 0,
                },

                '.toplevel_page_static-snap  #wpfooter': {
                  display: 'none',
                },
                '.toplevel_page_static-snap  .auto-fold #wpcontent': {
                  paddingLeft: 0,
                },
              }
        }
      />

      <ScopedCssBaseline>{children}</ScopedCssBaseline>
    </MuiThemeProvider>
  );
}

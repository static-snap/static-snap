import { Theme } from '@mui/material/styles';
import merge from 'lodash/merge';

import { button } from './components/button';
import { buttonBase } from './components/button-base';
import { card } from './components/card';
import { drawer } from './components/drawer';
import { link } from './components/link';
import { table } from './components/table';

// ----------------------------------------------------------------------

export function componentsOverrides(theme: Theme) {
  const components = merge(
    drawer(theme),
    buttonBase(theme),
    link(theme),
    button(theme),
    table(theme),
    card(theme)
  );

  return components;
}

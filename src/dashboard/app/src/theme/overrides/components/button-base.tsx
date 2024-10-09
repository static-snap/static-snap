import { Theme } from '@mui/material/styles';

import LinkBehavior from '@staticsnap/dashboard/theme/link-behavior';

export function buttonBase(_theme: Theme) {
  return {
    MuiButtonBase: {
      defaultProps: {
        LinkComponent: LinkBehavior,
      },
    },
  };
}

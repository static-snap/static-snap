import { LinkProps } from '@mui/material/Link';
import { Theme } from '@mui/material/styles';

import LinkBehavior from '@staticsnap/dashboard/theme/link-behavior';

export function link(_theme: Theme) {
  return {
    MuiLink: {
      defaultProps: {
        component: LinkBehavior
      } as LinkProps
    }
  };
}

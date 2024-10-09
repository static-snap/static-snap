import { forwardRef } from 'react';

import { Icon as MuiIcon } from '@iconify/react';
import Box, { BoxProps } from '@mui/material/Box';

import { IconifyProps } from './types/icon-props';

interface Props extends BoxProps {
  icon: IconifyProps;
}

const Icon = forwardRef<SVGElement, Props>(({ icon, width = 20, sx, ...other }, ref) => (
  <Box
    ref={ref}
    component={MuiIcon}
    className="component-iconify"
    icon={icon}
    sx={{ height: width, width, ...sx }}
    {...other}
  />
));

export default Icon;

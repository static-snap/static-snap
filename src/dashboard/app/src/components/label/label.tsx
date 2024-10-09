import Box from '@mui/material/Box';
import { useTheme } from '@mui/material/styles';
import { forwardRef } from 'react';

import { StyledLabel } from './styles';
import { LabelProps } from './types';

// ----------------------------------------------------------------------

const Label = forwardRef<HTMLSpanElement, LabelProps>(
  ({ children, color = 'default', variant = 'soft', startIcon, endIcon, sx, ...other }, ref) => {
    const theme = useTheme();

    const iconStyle = {
      '& svg, img': {
        height: 1,
        objectFit: 'cover',
        width: 1,
      },
      height: 16,
      width: 16,
    };

    return (
      <StyledLabel
        ref={ref}
        component="span"
        ownerState={{ color, variant }}
        sx={{
          ...(startIcon && { pl: 0.75 }),
          ...(endIcon && { pr: 0.75 }),
          ...sx,
        }}
        theme={theme}
        {...other}
      >
        {startIcon && <Box sx={{ mr: 0.75, ...iconStyle }}> {startIcon} </Box>}

        {children}

        {endIcon && <Box sx={{ ml: 0.75, ...iconStyle }}> {endIcon} </Box>}
      </StyledLabel>
    );
  }
);

export default Label;

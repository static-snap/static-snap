import { type BoxProps } from '@mui/material/Box';
import React from 'react';

// ----------------------------------------------------------------------

import { LabelColor } from './label-color';
import { LabelVariant } from './label-variant';

export interface LabelProps extends BoxProps {
  startIcon?: React.ReactElement | null;
  endIcon?: React.ReactElement | null;
  color?: LabelColor;
  variant?: LabelVariant;
}

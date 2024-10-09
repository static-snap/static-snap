import { ButtonProps } from '@mui/material/Button';
import { Theme } from '@mui/material/styles';

// ----------------------------------------------------------------------

export function button(theme: Theme) {
  const isLightMode = theme.palette.mode === 'light';

  const rootStyles = (ownerState: ButtonProps) => {
    const isContained = ownerState.variant === 'contained';
    return {
      ...(isContained && {
        '&:hover': {
          color: isLightMode ? theme.palette.common.white : theme.palette.grey[800],
        },
      }),
    };
  };
  return {
    MuiButton: {
      styleOverrides: {
        root: ({ ownerState }: { ownerState: ButtonProps }) => rootStyles(ownerState),
      },
    },
  };
}

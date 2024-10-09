import { CardProps } from '@mui/material/Card';
import { Theme } from '@mui/material/styles';

// ----------------------------------------------------------------------

export function card(theme: Theme) {
  return {
    MuiCard: {
      styleOverrides: {
        root: ({ ownerState }: { ownerState: CardProps }) => ({
          borderRadius: ownerState.elevation === 0 ? null : theme.shape.borderRadius * 2,
          position: 'relative',
          zIndex: 0, // Fix Safari overflow: hidden with border radius
        }),
      },
    },
    MuiCardHeader: {
      styleOverrides: {
        root: {
          padding: theme.spacing(3, 3, 0),
        },
      },
    },
    MuiCardContent: {
      styleOverrides: {
        root: {
          padding: theme.spacing(3),
        },
      },
    },
  };
}

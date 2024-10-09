import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListSubheader from '@mui/material/ListSubheader';
import { alpha, styled } from '@mui/material/styles';

import sidebarConfig from './config';
import { SidebarItemProps, SidebarConfigProps } from './types';

type StyledItemProps = Omit<SidebarItemProps, 'item'>;

export const StyledItem = styled(ListItemButton, {
  shouldForwardProp: (prop) => prop !== 'active',
})<StyledItemProps>(({ active, depth, theme }) => {
  const subItem = depth !== 1;

  const deepSubItem = depth > 2;

  const activeStyles = {
    root: {
      '&:hover': {
        backgroundColor: alpha(theme.palette.primary.main, 0.16),
      },
      backgroundColor: alpha(theme.palette.primary.main, 0.08),
      color: theme.palette.action.active,
    },
    sub: {
      '&:hover': {
        backgroundColor: theme.palette.action.hover,
      },
      backgroundColor: 'transparent',
      color: theme.palette.text.primary,
    },
  };

  return {
    borderRadius: sidebarConfig.itemRadius,
    color: theme.palette.text.secondary,
    marginBottom: sidebarConfig.itemGap,
    minHeight: sidebarConfig.itemRootHeight,
    padding: sidebarConfig.itemPadding,
    // Active root item
    ...(active && {
      ...activeStyles.root,
    }),

    // Sub item
    ...(subItem && {
      minHeight: sidebarConfig.itemSubHeight,
      // Active sub item
      ...(active && {
        ...activeStyles.sub,
      }),
    }),

    // Deep sub item
    ...(deepSubItem && {
      paddingLeft: theme.spacing(depth),
    }),
  };
});

// ----------------------------------------------------------------------

type StyledIconProps = {
  size?: number;
};

export const StyledIcon = styled(ListItemIcon)<StyledIconProps>(({ size }) => ({
  width: size,
  height: size,
  alignItems: 'center',
  justifyContent: 'center',
}));

type StyledDotIconProps = {
  active?: boolean;
};

export const StyledDotIcon = styled('span')<StyledDotIconProps>(({ active, theme }) => ({
  width: 4,
  height: 4,
  borderRadius: '50%',
  backgroundColor: theme.palette.text.disabled,
  transition: theme.transitions.create(['transform'], {
    duration: theme.transitions.duration.shorter,
  }),
  ...(active && {
    transform: 'scale(2)',
    backgroundColor: theme.palette.primary.main,
  }),
}));

// ----------------------------------------------------------------------

type StyledSubheaderProps = SidebarConfigProps;

export const StyledSubheader = styled(ListSubheader)<StyledSubheaderProps>(({ theme }) => ({
  ...theme.typography.overline,
  fontSize: 11,
  cursor: 'pointer',
  display: 'inline-flex',
  padding: sidebarConfig.itemPadding,
  paddingTop: theme.spacing(2),
  marginBottom: sidebarConfig.itemGap,
  paddingBottom: theme.spacing(1),
  color: theme.palette.text.disabled,
  transition: theme.transitions.create(['color'], {
    duration: theme.transitions.duration.shortest,
  }),
  '&:hover': {
    color: theme.palette.text.primary,
  },
}));

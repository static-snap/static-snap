import Box from '@mui/material/Box';
import Link from '@mui/material/Link';
import { ListItemButtonProps } from '@mui/material/ListItemButton';
import ListItemText from '@mui/material/ListItemText';
import Tooltip from '@mui/material/Tooltip';
import Iconify from '@staticsnap/dashboard/components/icon';

import { sidebarConfig } from './config';
import { StyledItem, StyledIcon, StyledDotIcon } from './styles';

export type SidebarConfigProps = {
  hiddenLabel?: boolean;
  itemGap?: number;
  iconSize?: number;
  itemRadius?: number;
  itemPadding?: string;
  currentRole?: string;
  itemSubHeight?: number;
  itemRootHeight?: number;
};
export type SidebarListProps = {
  title: string;
  path: string;
  icon?: React.ReactElement;
  info?: React.ReactElement;
  caption?: string;
  disabled?: boolean;
  roles?: string[];
  children?: React.ReactNode;
};

export type SidebarItemProps = ListItemButtonProps & {
  item: SidebarListProps;
  depth: number;
  open?: boolean;
  active: boolean;
  externalLink?: boolean;
};

export default function SidebarItem({
  item,
  open,
  depth,
  active,

  externalLink,
  ...other
}: SidebarItemProps) {
  const { title, path, icon, info, children, disabled, caption } = item;

  const subItem = depth !== 1;

  const renderContent = (
    <StyledItem disableGutters disabled={disabled} active={active} depth={depth} {...other}>
      <>
        {icon && <StyledIcon size={sidebarConfig.iconSize}>{icon}</StyledIcon>}

        {subItem && (
          <StyledIcon size={sidebarConfig.iconSize}>
            <StyledDotIcon active={active} />
          </StyledIcon>
        )}
      </>

      <ListItemText
        primary={title}
        secondary={
          caption ? (
            <Tooltip title={caption} placement="top-start">
              <span>{caption}</span>
            </Tooltip>
          ) : null
        }
        primaryTypographyProps={{
          fontWeight: active ? 'fontWeightSemiBold' : 'fontWeightMedium',
          noWrap: true,
          textTransform: 'capitalize',
          typography: 'body2',
        }}
        secondaryTypographyProps={{
          color: 'text.disabled',
          component: 'span',
          noWrap: true,
          typography: 'caption',
        }}
      />

      {info && (
        <Box
          component="span"
          sx={{
            lineHeight: 0,
            ml: 1,
          }}
        >
          {info}
        </Box>
      )}

      {!!children && (
        <Iconify
          width={16}
          icon={open ? 'eva:arrow-ios-downward-fill' : 'eva:arrow-ios-forward-fill'}
          sx={{
            flexShrink: 0,
            ml: 1,
          }}
        />
      )}
    </StyledItem>
  );

  if (externalLink) {
    return (
      <Link
        href={path}
        target="_blank"
        rel="noopener"
        underline="none"
        color="inherit"
        sx={{
          ...(disabled && {
            cursor: 'default',
          }),
        }}
      >
        {renderContent}
      </Link>
    );
  }

  if (children) {
    return renderContent;
  }

  return (
    <Link
      href={path}
      underline="none"
      color="inherit"
      sx={{
        ...(disabled && {
          cursor: 'default',
        }),
      }}
    >
      {renderContent}
    </Link>
  );
}

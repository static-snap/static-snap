import { ListItemButtonProps } from '@mui/material/ListItemButton';
import { StackProps } from '@mui/material/Stack';

export type SidebarConfigProps = {
  hiddenLabel?: boolean;
  itemGap?: number;
  iconSize?: number;
  itemRadius?: number;
  itemPadding?: string;
  itemSubHeight?: number;
  itemRootHeight?: number;
};

export type SidebarItemProps = ListItemButtonProps & {
  item: SidebarListProps;
  depth: number;
  open?: boolean;
  active: boolean;
  externalLink?: boolean;
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

export type SidebarSectionData = {
  subheader: string;
  items: SidebarListProps[];
};
export type SidebarSectionProps = StackProps & {
  data: SidebarSectionData[];
};

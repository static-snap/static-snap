import { memo } from 'react';

import Stack from '@mui/material/Stack';

import SidebarGroup from './sidebar-group';
import { SidebarSectionProps } from './types';

function Sidebar({ data, sx, ...other }: SidebarSectionProps) {
  return (
    <Stack sx={sx} {...other}>
      {data.map((group, index) => (
        <SidebarGroup
          key={group.subheader || index}
          subheader={group.subheader}
          items={group.items}
        />
      ))}
    </Stack>
  );
}

export default memo(Sidebar);

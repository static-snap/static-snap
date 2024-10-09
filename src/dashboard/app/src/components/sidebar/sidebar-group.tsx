import { memo, useState, useCallback } from 'react';

import Collapse from '@mui/material/Collapse';
import List from '@mui/material/List';

import SidebarList from './sidebar-list';
import { StyledSubheader } from './styles';
import { SidebarListProps } from './types';

type SidebarGroupProps = {
  subheader: string;
  items: SidebarListProps[];
};

function SidebarGroup({ subheader, items }: SidebarGroupProps) {
  const [open, setOpen] = useState(true);

  const handleToggle = useCallback(() => {
    setOpen((prev) => !prev);
  }, []);

  const renderContent = items.map((list) => (
    <SidebarList key={list.title + list.path} data={list} depth={1} hasChild={!!list.children} />
  ));

  return (
    <List
      disablePadding
      sx={{
        paddingX: 2,
      }}
    >
      {subheader ? (
        <>
          <StyledSubheader disableGutters disableSticky onClick={handleToggle}>
            {subheader}
          </StyledSubheader>

          <Collapse in={open}>{renderContent}</Collapse>
        </>
      ) : (
        renderContent
      )}
    </List>
  );
}

export default memo(SidebarGroup);

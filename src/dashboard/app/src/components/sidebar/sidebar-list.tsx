import { useState, useEffect, useCallback } from 'react';

import Collapse from '@mui/material/Collapse';
import { useLocation } from 'react-router-dom';

import SidebarItem, { SidebarListProps } from './sidebar-item';

type SidebarListRootProps = {
  data: SidebarListProps;
  depth: number;
  hasChild: boolean;
};

export default function SidebarList({ data, depth, hasChild }: SidebarListRootProps) {
  const { pathname } = useLocation();

  const active = data.path === pathname || (data.path !== '/' && pathname.includes(data.path));

  const externalLink = data.path.includes('http');

  const [open, setOpen] = useState(active);

  const handleToggle = useCallback(() => {
    setOpen((prev) => !prev);
  }, []);

  const handleClose = useCallback(() => {
    setOpen(false);
  }, []);

  useEffect(() => {
    if (!active) {
      handleClose();
    }
  }, [active, handleClose, pathname]);

  return (
    <>
      <SidebarItem
        item={data}
        depth={depth}
        open={open}
        active={active}
        externalLink={externalLink}
        onClick={handleToggle}
      />

      {hasChild && (
        <Collapse in={open} unmountOnExit>
          <SidebarSubList data={data.children as SidebarListProps[]} depth={depth} />
        </Collapse>
      )}
    </>
  );
}

type SidebarListSubProps = {
  data: SidebarListProps[];
  depth: number;
};

function SidebarSubList({ data, depth }: SidebarListSubProps) {
  return (
    <>
      {data.map((list) => (
        <SidebarList
          key={list.title + list.path}
          data={list}
          depth={depth + 1}
          hasChild={!!list.children}
        />
      ))}
    </>
  );
}

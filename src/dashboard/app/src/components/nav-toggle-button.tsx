import IconButton, { IconButtonProps } from '@mui/material/IconButton';
import { useTheme } from '@mui/material/styles';
import Iconify from '@staticsnap/dashboard/components/icon';
import { SIDEBAR_WIDTH } from '@staticsnap/dashboard/components/sidebar/config';
import { useResponsive } from '@staticsnap/dashboard/hooks/use-responsive';

export default function NavToggleButton({ sx, ...other }: IconButtonProps) {
  const theme = useTheme();

  const lgUp = useResponsive('up', 'lg');

  if (!lgUp) {
    return null;
  }

  return (
    <IconButton
      size="small"
      sx={{
        p: 0.5,
        top: 32,
        position: 'fixed',
        left: SIDEBAR_WIDTH - 12,
        zIndex: theme.zIndex.appBar + 1,
        border: `dashed 1px ${theme.palette.divider}`,
        backdropFilter: `blur(4px)`,
        backgroundColor: 'rgba(255, 255, 255, 0.72)',
        '&:hover': {
          bgcolor: 'background.default',
        },
        ...sx,
      }}
      {...other}
    >
      <Iconify width={16} icon="eva:arrow-ios-back-fill" />
    </IconButton>
  );
}

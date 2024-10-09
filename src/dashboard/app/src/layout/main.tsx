import Box, { BoxProps } from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Container from '@mui/material/Container';

import { SIDEBAR_WIDTH } from '@staticsnap/dashboard/components/sidebar/config';
import { useResponsive } from '@staticsnap/dashboard/hooks/use-responsive';

const SPACING = 2;

export default function Main({ children, sx, ...other }: BoxProps) {
  const lgUp = useResponsive('up', 'lg');

  return (
    <Box
      component="main"
      sx={{
        backgroundColor: 'background.default',
        display: 'flex',
        flexDirection: 'column',
        flexGrow: 1,
        minHeight: 1,
        py: `${SPACING}rem`,
        ...(lgUp && {
          py: `${SPACING}rem`,
          width: `calc(100% - ${SIDEBAR_WIDTH}px)`,
        }),
        ...sx,
      }}
      {...other}
    >
      <Container maxWidth={false}>
        <Card elevation={18}>
          <CardContent>{children}</CardContent>
        </Card>
      </Container>
    </Box>
  );
}

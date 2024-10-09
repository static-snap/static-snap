import Box from '@mui/material/Box';
import LinearProgress, { LinearProgressProps } from '@mui/material/LinearProgress';
import Typography from '@mui/material/Typography';

function LinearProgressWithLabel(props: LinearProgressProps & { value: number }) {
  return (
    <Box
      sx={{
        alignItems: 'center',
        display: 'flex',
      }}
    >
      <Box
        sx={{
          mr: 1,
          width: '100%',
        }}
      >
        <LinearProgress variant="determinate" {...props} />
      </Box>
      <Box sx={{ minWidth: 35 }}>
        <Typography variant="body2" color="inherit">{`${Math.round(props.value)}%`}</Typography>
      </Box>
    </Box>
  );
}

export default LinearProgressWithLabel;

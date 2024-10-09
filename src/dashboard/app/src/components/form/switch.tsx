import FormControlLabel from '@mui/material/FormControlLabel';
import FormHelperText from '@mui/material/FormHelperText';
import MuiSwitch from '@mui/material/Switch';
import { Controller, useFormContext } from 'react-hook-form';

type TextFieldProps = {
  name: string;
  label: string;
  helperText?: string;
};
const Switch = ({ name, label, helperText }: TextFieldProps) => {
  const { control } = useFormContext();
  return (
    <Controller
      name={name}
      control={control}
      render={({ field }) => (
        <>
          <FormControlLabel
            control={<MuiSwitch {...field} checked={!!field.value} />}
            label={label}
          />
          {helperText && <FormHelperText>{helperText}</FormHelperText>}
        </>
      )}
    />
  );
};

export default Switch;

import { InputProps } from '@mui/material/Input';
import { InputLabelProps } from '@mui/material/InputLabel';
import MuiTextField from '@mui/material/TextField';
import { Controller, useFormContext } from 'react-hook-form';

export type TextFieldProps = {
  name: string;
  label: string;
  type?: React.InputHTMLAttributes<unknown>['type'];
  autoComplete?: string;
  helperText?: string;
  required?: boolean;
  multiline?: boolean;
  rows?: number;
  InputProps?: Partial<InputProps>;
  InputLabelProps?: Partial<InputLabelProps>;
};
const TextField = ({
  name,
  label,
  type,
  required,
  helperText,
  multiline = false,
  autoComplete = 'off',
  rows,
  InputProps,
  InputLabelProps,
}: TextFieldProps) => {
  const { control } = useFormContext();

  return (
    <Controller
      name={name}
      control={control}
      render={({ field, fieldState }) => (
        <MuiTextField
          {...field}
          variant="outlined"
          required={required}
          label={label}
          InputLabelProps={InputLabelProps}
          size="small"
          type={type}
          multiline={multiline}
          rows={rows}
          helperText={fieldState.error ? fieldState.error.message : helperText}
          autoComplete={autoComplete}
          error={!!fieldState.error}
          InputProps={InputProps}
        />
      )}
    />
  );
};

export default TextField;

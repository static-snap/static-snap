import MuiTextField from '@mui/material/TextField';
import { Controller, useFormContext } from 'react-hook-form';

import { TextFieldProps } from './text-field';
import { useCallback, useState } from 'react';
import Box from '@mui/material/Box';
import FormControl from '@mui/material/FormControl';
import Select, { SelectChangeEvent } from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import Menu from '@mui/material/Menu';
import { Button } from '@mui/material';

export function SchemaSelect({
  onChange,
  value = 'https://',
}: {
  onChange: (schema: string) => void;
  value: string;
}) {
  const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);
  const open = Boolean(anchorEl);
  const handleClick = (event: React.MouseEvent<HTMLButtonElement>) => {
    setAnchorEl(event.currentTarget);
  };
  const handleClose = (e) => {
    // get value
    const schema = e.target.getAttribute('value');
    setAnchorEl(null);
    onChange(schema);
  };
  return (
    <>
      <Button onClick={handleClick} color="inherit">
        {value}
      </Button>
      <Menu
        //onChange={onChange}
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        sx={{
          padding: '0px',
          '& .MuiSelect-select': {
            padding: '0px',
          },
          '&::before, &::after': {
            borderBottom: '0px',
          },
        }}
      >
        <MenuItem value={'https://'} onClick={handleClose}>
          https://
        </MenuItem>
        <MenuItem value={'http://'} onClick={handleClose}>
          http://
        </MenuItem>
      </Menu>
    </>
  );
}

const UrlTextField = ({
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

  const [schema, setSchema] = useState('https://');

  const onChange = useCallback(
    (
      e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
      field: { onChange: (value: string) => void }
    ) => {
      field.onChange(schema + e.target.value.trim());
    },
    [schema]
  );

  const onSelectChange = useCallback(
    (schema: string, field: { value: string; onChange: (value: string) => void }) => {
      setSchema(schema);
      const host = parseUrl(field.value).host;
      field.onChange(schema + host);
    },
    []
  );

  const parseUrl = (
    url: string
  ): {
    schema: string;
    host: string;
  } => {
    try {
      const [schema, host] = url.split('://');
      const ret = {
        schema: schema + '://',
        host: host,
      };
      return ret;
    } catch (e) {
      return {
        schema: schema,
        host: '',
      };
    }
  };

  return (
    <Controller
      name={name}
      control={control}
      render={({ field, fieldState }) => (
        <MuiTextField
          {...field}
          value={parseUrl(field.value).host}
          onChange={(e) => {
            onChange(e, field);
          }}
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
          InputProps={{
            startAdornment: (
              <SchemaSelect value={schema} onChange={(value) => onSelectChange(value, field)} />
            ),
            ...InputProps,
          }}
        />
      )}
    />
  );
};

export default UrlTextField;

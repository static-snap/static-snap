import FormControl from '@mui/material/FormControl';
import FormHelperText from '@mui/material/FormHelperText';
import InputLabel from '@mui/material/InputLabel';
import MenuItem from '@mui/material/MenuItem';
import MuiSelect from '@mui/material/Select';
import { useCallback, useEffect, useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import apiFetch from '@wordpress/api-fetch';
import Button from '@mui/material/Button';

import Icon from '@staticsnap/dashboard/components/icon';
import Box from '@mui/material/Box';
import { Typography } from '@mui/material';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

export type SelectItems = {
  value: string | number;
  label: string;
  disabled?: boolean;
};
type SelectProps = {
  name: string;
  label: string;
  items: SelectItems[];
  reloadItemsUrl?: string;
  dependsOn?: string;
  helperText?: string;
  required?: boolean;
};
const Select = ({
  name,
  label,
  items,
  reloadItemsUrl,
  dependsOn,
  helperText,
  required,
}: SelectProps) => {
  const { control, watch } = useFormContext();

  const watchValue = watch([dependsOn as string])[0];

  const [internalItems, setInternalItems] = useState<SelectItems[]>(items);

  const reloadItems = useCallback(
    async (cache = false) => {
      const splittedDependsOn = dependsOn?.split('.');
      const dependsOnVariableName = splittedDependsOn?.pop();
      const path = addQueryArgs(reloadItemsUrl, {
        [dependsOnVariableName as string]: watchValue,
        cache: cache ? 1 : 0,
      });
      const response = await apiFetch({
        path,
      });
      setInternalItems(response as SelectItems[]);
    },
    [dependsOn, reloadItemsUrl, watchValue]
  );

  const onReloadItemsClick = useCallback(() => {
    reloadItems(false);
  }, [reloadItems]);

  useEffect(() => {
    if (watchValue) {
      reloadItems(true);
    }
  }, [watchValue, reloadItems]);

  useEffect(() => {
    setInternalItems(items);
  }, [items]);

  if (dependsOn && !watchValue) {
    return null;
  }

  return (
    <Controller
      name={name}
      control={control}
      render={({ field, fieldState }) => {
        const exists = internalItems.find((item) => item.value === field.value);
        if (!exists) {
          // set value to null
          field.value = '';
        }
        return (
          <FormControl error={!!fieldState.error}>
            <InputLabel>{label}</InputLabel>
            <MuiSelect
              {...field}
              variant="outlined"
              label={name}
              size="small"
              required={required}
              sx={{
                // try to ocupate the full width
                width: '100%',
              }}
            >
              {internalItems.map((item, index) => (
                <MenuItem key={index} value={item.value} disabled={item.disabled}>
                  {item.label}
                </MenuItem>
              ))}
            </MuiSelect>

            {helperText && <FormHelperText>{helperText}</FormHelperText>}
            {reloadItemsUrl && (
              <Button onClick={onReloadItemsClick} size="small">
                <Icon icon="material-symbols:refresh" />
                <Typography variant="caption">{__('Reload', 'static-snap')}</Typography>
              </Button>
            )}
          </FormControl>
        );
      }}
    />
  );
};

export default Select;

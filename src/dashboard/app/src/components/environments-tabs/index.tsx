import * as React from 'react';

import TabContext from '@mui/lab/TabContext';
import TabList from '@mui/lab/TabList';
import TabPanel from '@mui/lab/TabPanel';
import AppBar from '@mui/material/AppBar';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Box from '@mui/material/Box';
import Tab from '@mui/material/Tab';
import { __ } from '@wordpress/i18n';
import { Property } from 'csstype';

import EnvironmentPane from './environment-pane';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';

type EnvironmentsTabsProps = {
  environments: EnvironmentInterface[];
  justifyContent?: Property.JustifyContent | undefined;
  header?: (environment: EnvironmentInterface) => React.ReactNode;
  children?: (environment: EnvironmentInterface) => React.ReactNode;
  disabled?: boolean;
};

export default function EnvironmentsTabs({
  environments,
  children,
  header,
  disabled = false,
  justifyContent = 'space-between',
}: EnvironmentsTabsProps) {
  const [value, setValue] = React.useState(
    Array.isArray(environments) && environments.length > 0 ? environments[0].id : ''
  );

  const handleChange = (_event: React.SyntheticEvent, newValue: string) => {
    setValue(newValue);
  };

  return (
    <Card sx={{ borderRadius: 0, minWidth: 350 }}>
      <Box
        sx={{
          paddingTop: '.8rem',
          typography: 'body1',
          width: '100%',
        }}
      >
        {Array.isArray(environments) && environments.length > 0 && (
          <TabContext value={value}>
            <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
              <AppBar position="static" color="default">
                <TabList
                  onChange={handleChange}
                  aria-label="lab API tabs example"
                  variant="scrollable"
                  scrollButtons="auto"
                  sx={{
                    '.MuiTabs-flexContainer': {
                      justifyContent: justifyContent,
                    },
                  }}
                >
                  {environments.map((environment) => (
                    <Tab
                      key={environment.id}
                      label={environment.name}
                      value={environment.id}
                      sx={{ flexGrow: 1 }}
                    />
                  ))}
                </TabList>
              </AppBar>
            </Box>
            <CardContent>
              {environments.map((environment) => (
                <TabPanel key={environment.id} value={environment.id} sx={{ paddingX: 0 }}>
                  {header && header(environment)}
                  <EnvironmentPane environment={environment} disabled={disabled} />
                  {children && children(environment)}
                </TabPanel>
              ))}
            </CardContent>
          </TabContext>
        )}
        {(!environments || (Array.isArray(environments) && environments.length < 1)) && (
          <p>
            {__('No environments found. ', 'static-snap')}
            <a href="/wp-admin/admin.php?page=static-snap-settings#/environments">
              {__('Add Environment', 'static-snap')}
            </a>
          </p>
        )}
      </Box>
    </Card>
  );
}

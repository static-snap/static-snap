import { useEffect, useState } from 'react';

import Box from '@mui/material/Box';
import Tooltip from '@mui/material/Tooltip';
import ConnectFallback from '@staticsnap/dashboard/components/connect-fallback';
import Icon from '@staticsnap/dashboard/components/icon';
import Table from '@staticsnap/dashboard/components/table/table';
import useOptions from '@staticsnap/dashboard/hooks/use-options';
import useStaticSnapAPI from '@staticsnap/dashboard/hooks/use-static-snap-api';
import ConnectInterface from '@staticsnap/dashboard/interfaces/connect.interface';
import WebsiteInterface from '@staticsnap/dashboard/interfaces/website.interface';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const WebsitesIndex = () => {
  const connectOptions = useOptions<ConnectInterface>('connect');
  const staticSnap = useStaticSnapAPI('/websites/list', { method: 'GET' });
  const staticSnapDisconnectWebsite = useStaticSnapAPI('/websites/disconnect', { method: 'POST' });
  const [websites, setWebsites] = useState<WebsiteInterface[]>([]);

  useEffect(() => {
    const getWebsites = async () => {
      const internalOptions = await connectOptions.getOptions();
      if (!internalOptions) {
        return;
      }
      const response = await staticSnap.request<WebsiteInterface>();

      setWebsites(response.type === 'paginated_items' ? response.data : []);
    };

    getWebsites();
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <ConnectFallback
      connect={connectOptions.currentOptions as ConnectInterface}
      isLoading={!connectOptions.currentOptionsHasBeenCalled}
    >
      <Table
        title="Websites"
        subTitle={__('Your registered websites')}
        loading={!connectOptions.currentOptionsHasBeenCalled}
        rows={websites.map((website) => ({
          id: website.website_id,
          ...website,
        }))}
        columns={[
          {
            field: 'website_url',
            headerName: 'URL',
            render: (value, _column, row) => {
              const rowWebiste = row as WebsiteInterface;
              if (rowWebiste.website_id !== connectOptions.currentOptions?.website_id) {
                return value;
              }

              return (
                <Tooltip title={__('This is your current website', 'static-snap')}>
                  <Box display={'flex'} flexDirection={'row'} alignItems={'center'} gap={1}>
                    <Icon icon="material-symbols:info" />
                    <Box sx={{ fontWeight: 'bold' }}>{value}</Box>
                  </Box>
                </Tooltip>
              );
            },
          },
          {
            field: 'website_name',
            headerName: 'Name',
            render: (value, _column, row) => {
              const roWebsite = row as WebsiteInterface;
              if (roWebsite.website_id !== connectOptions.currentOptions?.website_id) {
                return value;
              }
              return (
                <Tooltip title={__('This is your current website', 'static-snap')}>
                  <Box sx={{ fontWeight: 'bold' }}>{value}</Box>
                </Tooltip>
              );
            },
          },
          {
            field: 'website_created',
            headerName: 'Created',
            render: (value) => new Date(value).toLocaleString(),
          },
        ]}
        actions={[
          {
            children: __('Delete', 'static-snap'),
            color: 'error',
            id: 'delete',
            onRowClick: async (row, index) => {
              const confirm = window.confirm(
                __(
                  'Are you sure you want to delete this website? If you remove it, all forms from static websites will stop working if you are using staticsnap.com to store the form data. Read more about this in the documentation: https://staticsnap.com/docs/website-configuration#delete-website',
                  'staticsnap'
                )
              );
              if (!confirm) {
                return;
              }
              const response = await staticSnapDisconnectWebsite.request<boolean>({
                body: JSON.stringify({
                  website_id: websites[index].website_id,
                }),
              });

              if (response.type === 'item' && response.data) {
                if (websites[index].website_id === connectOptions.currentOptions?.website_id) {
                  // disconnect website options if we are deleting the current website
                  apiFetch({
                    data: {
                      /**
                       * static_snap_disconnect avoid to call the staticsnap disconnect endpoint again
                       * because its already called in the previous request
                       */
                      static_snap_disconnect: false,
                    },
                    method: 'DELETE',
                    path: '/static-snap/v1/connect',
                  });
                }

                setWebsites((prevWebsites) =>
                  prevWebsites.filter(
                    (website) => website.website_id !== websites[index].website_id
                  )
                );
              }
            },
            size: 'small',
            startIcon: <Icon icon="material-symbols:delete" />,
            variant: 'contained',
          },
        ]}
      />
    </ConnectFallback>
  );
};

export default WebsitesIndex;

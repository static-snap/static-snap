import { useEffect, useState } from 'react';

import Icon from '@staticsnap/dashboard/components/icon';
import LightTooltip from '@staticsnap/dashboard/components/light-tooltip';
import Table from '@staticsnap/dashboard/components/table/table';
import DeploymentInterface from '@staticsnap/dashboard/interfaces/deployment.interface';
import { JsonViewer } from '@textea/json-viewer';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const Status = {
  1: __('Running', 'static-snap'),
  2: __('Completed', 'static-snap'),
  3: __('Canceled', 'static-snap'),
  4: __('Paused', 'static-snap'),
  5: __('Failed', 'static-snap'),
};

export default function DeploymentsIndex() {
  //const navigate = useNavigate();

  const [deployments, setDeployments] = useState<DeploymentInterface[]>([]);
  const [loading, setLoading] = useState(true);
  const getDeployments = () => {
    apiFetch({ path: '/static-snap/v1/deployments-history' }).then((environments) => {
      setDeployments(Array.from(environments as DeploymentInterface[]));
      setLoading(false);
    });
  };

  useEffect(() => {
    getDeployments();
  }, []);

  return (
    <Table
      title="Deployments"
      loading={loading}
      rows={deployments}
      columns={[
        {
          field: 'environment_name',
          headerName: 'Environment',
        },
        {
          field: 'build_type',
          headerName: 'Build type',
        },
        {
          field: 'status',
          headerName: 'Status',
          render: (status: number) => Status[status as keyof typeof Status],
        },
        {
          field: 'start_time',
          headerName: 'Start',
          render: (value: number) => new Date(value * 1000).toLocaleString(),
        },
        {
          field: 'end_time',
          headerName: 'End',
          render: (value: number) => (value ? new Date(value * 1000).toLocaleString() : null),
        },
        {
          field: 'status_information',
          headerName: 'Status info',
          render: (value) =>
            value ? (
              <LightTooltip
                title={<JsonViewer value={JSON.parse(value)} theme={'light'} rootName={false} />}
              >
                <Icon icon={'material-symbols:info'} />
              </LightTooltip>
            ) : null,
        },
        {
          field: 'error',
          headerName: 'Error',
          render: (value) =>
            value ? (
              <LightTooltip
                title={<JsonViewer value={JSON.parse(value)} theme={'light'} rootName={false} />}
              >
                <Icon icon={'material-symbols:info'} />
              </LightTooltip>
            ) : null,
        },
      ]}
      actions={[
        {
          children: 'Download',
          id: 'download',
          shouldRender: (_row, index) => {
            try {
              if (Number(_row.status) !== 2) {
                return false;
              }
              if (!_row.is_last_deployment) {
                return false;
              }
              const settings = JSON.parse(_row.environment_settings);
              if (!settings?.create_zip_file) {
                return false;
              }
            } catch (e) {
              // nothing to do
            }
            return true;
          },

          onRowClick: (_row, index) => {
            apiFetch({
              method: 'POST',
              path: `/static-snap/v1/deployments-history/download/${_row.id}`,
            }).then((res: { success: boolean; url: string }) => {
              window.open(res.url, '_blank');
            });
          },
        },
      ]}
    />
  );
}

import { useEffect, useState } from 'react';

import Button from '@mui/material/Button';
import Table from '@staticsnap/dashboard/components/table/table';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';
import { paths } from '@staticsnap/dashboard/routes/paths';
import apiFetch from '@wordpress/api-fetch';
import { useNavigate } from 'react-router-dom';

export default function EnvironmentsIndex() {
  const navigate = useNavigate();

  const [environments, setEnvironments] = useState<EnvironmentInterface[]>([]);
  const [loading, setLoading] = useState(true);
  const getSettings = () => {
    apiFetch({ path: '/static-snap/v1/environments' }).then((environments) => {
      setEnvironments(Array.from(environments as EnvironmentInterface[]));
      setLoading(false);
    });
  };

  useEffect(() => {
    getSettings();
  }, []);

  return (
    <Table
      title="Environments"
      loading={loading}
      rows={environments}
      columns={[
        { field: 'name', headerName: 'Name' },
        { field: 'type', headerName: 'Type' },
        { field: 'destination_path', headerName: 'Destination' },
      ]}
      actions={[
        {
          children: 'Edit',
          id: 'edit',
          onRowClick: (_row) => {
            // navigate to edit page react-router-dom
            navigate(`/environments/edit/${_row.id}`);
          },
        },
      ]}
      cardActions={
        <Button variant="contained" color="primary" href={paths.environments.add}>
          Add Environment
        </Button>
      }
    />
  );
}

import { useEffect, useState, useCallback } from 'react';

import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useNavigate, useParams } from 'react-router-dom';

import EnvironmentForm from '@staticsnap/dashboard/components/environments/environments-form';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';
import ErrorResponseInterface from '@staticsnap/dashboard/interfaces/error-response.interface';

export default function EnvironmentsEdit() {
  const navigate = useNavigate();
  const params = useParams();

  const [environment, setEnvironment] = useState<EnvironmentInterface>();
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const getSettings = useCallback(() => {
    apiFetch({ path: '/static-snap/v1/environments/' + params.id }).then((environment) => {
      setEnvironment(environment as EnvironmentInterface);
      setLoading(false);
    });
  }, [params.id]);

  useEffect(() => {
    getSettings();
  }, [getSettings]);

  const onSubmit = (data: EnvironmentInterface) => {
    data.id = params?.id as string;
    apiFetch({
      data,
      method: 'PATCH',
      path: '/static-snap/v1/environments',
    }).then((response) => {
      if (response && typeof response === 'object' && (response as ErrorResponseInterface).errors) {
        const errorResponse = response as ErrorResponseInterface;
        setErrors(errorResponse.errors);
        return;
      }

      if (!!response === true) {
        window.dispatchEvent(new CustomEvent('static-snap/environments-updated'));
        navigate('/environments');
      }
    });
  };

  const onDelete = (data: EnvironmentInterface) => {
    // confirm delete
    const confirmDelete = window.confirm(
      __('Are you sure you want to delete this environment?', 'static-snap')
    );
    if (!confirmDelete) {
      return;
    }
    data.id = params?.id as string;
    apiFetch({
      data,
      method: 'DELETE',
      path: '/static-snap/v1/environments',
    }).then((response) => {
      if (!!response === true) {
        navigate('/environments');
      }
    });
  };
  return (
    <>
      {!loading && (
        <EnvironmentForm
          title="Edit Environment"
          onSubmit={onSubmit}
          onDelete={onDelete}
          value={environment}
          errors={errors}
        />
      )}
    </>
  );
}

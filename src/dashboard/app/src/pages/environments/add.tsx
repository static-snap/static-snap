import EnvironmentForm from '@staticsnap/dashboard/components/environments/environments-form';
import EnvironmentInterface from '@staticsnap/dashboard/interfaces/environment.interface';
import apiFetch from '@wordpress/api-fetch';
import { useNavigate } from 'react-router-dom';

export default function EnvironmentsAdd() {
  const navigate = useNavigate();
  const onSubmit = (data: EnvironmentInterface) => {
    apiFetch({
      data,
      method: 'POST',
      path: '/static-snap/v1/environments',
    }).then((response) => {
      if (!!response === true) {
        window.dispatchEvent(new CustomEvent('static-snap/environments-updated'));
        navigate('/environments');
      }
    });
  };
  return <EnvironmentForm title="Add Environment" onSubmit={onSubmit} />;
}

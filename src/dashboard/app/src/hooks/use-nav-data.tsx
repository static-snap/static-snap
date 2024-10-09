import { useMemo } from 'react';

import Iconify from '@staticsnap/dashboard/components/icon';
import { paths } from '@staticsnap/dashboard/routes/paths';
import { __ } from '@wordpress/i18n';

const icon = (icon: string) => (
  <Iconify
    icon={icon}
    sx={{
      height: 1,
      width: 1,
    }}
  />
);

export function useNavData() {
  const data = useMemo(
    () => [
      {
        items: [
          {
            icon: icon('material-symbols:dashboard'),
            path: '/',
            title: __('dashboard', 'static-snap'),
          },
        ],
      },
      {
        items: [
          {
            icon: icon('material-symbols:settings'),
            path: paths.connect.index,
            title: __('Connect', 'static-snap'),
          },
          {
            icon: icon('mdi:github'),
            path: paths.github.index,
            title: __('GitHub', 'static-snap'),
          },
          {
            icon: icon('material-symbols-light:stacks'),
            path: paths.environments.index,
            title: __('Environments', 'static-snap'),
          },
          {
            icon: icon('material-symbols:search'),
            path: paths.search.index,
            title: __('Search', 'static-snap'),
          },
          {
            icon: icon('mdi:form'),
            path: paths.forms.index,
            title: __('Forms', 'static-snap'),
          },
          {
            icon: icon('material-symbols:settings'),
            path: paths.buildOptions.index,
            title: __('Build options', 'static-snap'),
          },
        ],
        subheader: __('Settings', 'static-snap'),
      },
      {
        items: [
          {
            icon: icon('material-symbols:account-circle'),
            path: paths.account.index,
            title: __('Your account', 'static-snap'),
          },
          {
            icon: icon('mdi:web'),
            path: paths.websites.index,
            title: __('Websites', 'static-snap'),
          },
          {
            icon: icon('material-symbols:archive'),
            path: paths.formSubmissions.index,
            title: __('Form submissions', 'static-snap'),
          },
          {
            icon: icon('ant-design:deployment-unit-outlined'),
            path: paths.deployments.index,
            title: __('Deployments', 'static-snap'),
          },
        ],
        subheader: __('Account', 'static-snap'),
      },
      {
        items: [
          {
            icon: icon('material-symbols:help'),
            path: paths.support.docs,
            title: __('Docs', 'static-snap'),
          },
        ],
        subheader: __('Support', 'static-snap'),
      },
    ],
    []
  );

  return data;
}

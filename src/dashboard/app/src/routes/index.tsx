import Layout from '@staticsnap/dashboard/layout/layout';
import ConnectIndex from '@staticsnap/dashboard/pages/connect';
import DashboardIndex from '@staticsnap/dashboard/pages/dashboard';
import DeploymentsIndex from '@staticsnap/dashboard/pages/deployments';
import EnvironmentsIndex from '@staticsnap/dashboard/pages/environments';
import EnvironmentsAdd from '@staticsnap/dashboard/pages/environments/add';
import EnvironmentsEdit from '@staticsnap/dashboard/pages/environments/edit';
import SearchIndex from '@staticsnap/dashboard/pages/search';
import { createHashRouter, RouterProvider } from 'react-router-dom';

import AccountIndex from '../pages/account';
import FormSubmissionsIndex from '../pages/form-submissions';
import FormsIndex from '../pages/forms';
import WebsitesIndex from '../pages/websites';
import BuildOptionsIndex from '../pages/build-options';
import { paths } from './paths';
import GithubIndex from '../pages/github';

const router = createHashRouter(
  [
    {
      children: [
        {
          element: <DashboardIndex />,
          index: true,
          path: paths.dashboard.index,
        },
        {
          element: <div>About</div>,
          path: 'about',
        },
        { element: <ConnectIndex />, path: paths.connect.index },
        { element: <EnvironmentsIndex />, path: paths.environments.index },
        { element: <EnvironmentsAdd />, path: paths.environments.add },
        { element: <EnvironmentsEdit />, path: paths.environments.edit },
        { element: <DeploymentsIndex />, path: paths.deployments.index },
        { element: <SearchIndex />, path: paths.search.index },
        { element: <AccountIndex />, path: paths.account.index },
        { element: <FormsIndex />, path: paths.forms.index },
        { element: <FormSubmissionsIndex />, path: paths.formSubmissions.index },
        { element: <WebsitesIndex />, path: paths.websites.index },
        { element: <BuildOptionsIndex />, path: paths.buildOptions.index },
        { element: <GithubIndex />, path: paths.github.index },

        {
          element: <div>Wilcard</div>,
          path: '*',
        },
      ],
      element: <Layout />,
      path: '/',
    },
  ],
  {}
);

const AppRouter = () => <RouterProvider router={router} />;

export { router, AppRouter };

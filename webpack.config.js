/* eslint-disable sort-keys */
/* eslint-disable @typescript-eslint/no-var-requires */
/* eslint-disable no-undef */
const defaults = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
  ...defaults,
  cache: {
    type: 'filesystem',
    buildDependencies: {
      config: [__filename],
    },
  },
  entry: {
    dashboard: ['./src/dashboard/app/src/index.tsx'],
    //forms: ['./src/forms/app/src/start.ts'],
    frontend: ['./src/frontend/app/src/start.ts'],
    algolia: ['./src/extensions/search/algolia/app/src/start.ts'],
    'elementor-forms': ['./src/extensions/forms/elementor/app/src/init.ts'],
    'contact-form-7': ['./src/extensions/forms/contact-form-7/app/src/init.ts'],
    'wp-forms': ['./src/extensions/forms/wp-forms/app/src/init.ts'],
    'gravity-forms': ['./src/extensions/forms/gravity-forms/app/src/init.ts'],
  },

  externals: {
    '@wordpress/i18n': ['window wp', 'i18n'],
    react: 'React',
    'react-dom': 'ReactDOM',
    '@staticsnap/frontend': 'StaticSnapFrontendClasses',
  },

  /* module: {
    rules: [
      {
        exclude: /node_modules/,
        test: /\.tsx?$/,
        use: 'ts-loader',
      },
    ],
    ...defaults.module,
  }, */
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets/js'),
  },
  resolve: {
    alias: {
      '@staticsnap/dashboard': path.resolve(__dirname, 'src/dashboard/app/src'),
      '@staticsnap/frontend': path.resolve(__dirname, 'src/frontend/app/src'),
      '@staticsnap/algolia': path.resolve(__dirname, 'src/extensions/search/algolia/app/src'),
    },
    extensions: ['.tsx', '.ts', '.js'],
  },
};

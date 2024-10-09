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
  entry: './src/index.ts',

  externals: {
    '@wordpress/i18n': ['window wp', 'i18n'],
    'react-dom': 'ReactDOM',
  },
  module: {
    ...defaults.module,
  },
  plugins: [],
  output: {
    filename: 'index.js',
    path: path.resolve(__dirname, 'dist'),
  },
  resolve: {
    alias: {
      '@staticsnap/frontend': path.resolve(__dirname, 'src/frontend/app/src'),
    },
    extensions: ['.tsx', '.ts', '.js'],
  },
};

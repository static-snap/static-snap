// eslint-disable-next-line @typescript-eslint/no-var-requires
const defaultConfig = require('@wordpress/babel-preset-default');

module.exports = (api) => {
  const config = defaultConfig(api);

  return {
    ...config,
  };
};

import { useState } from 'react';

import apiFetch from '@wordpress/api-fetch';

const useOptions = <T = unknown>(name: string) => {
  const [currentOptions, setCurrentOptions] = useState<T | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [currentOptionsHasBeenCalled, setCurrentOptionsHasBeenCalled] = useState(false);

  const getOptions = async () => {
    setIsLoading(true);
    let options = null;
    try {
      options = (await apiFetch({
        path: '/static-snap/v1/options/' + name,
      })) as T;
    } catch (e) {
      // eslint-disable-next-line no-console
    }

    setCurrentOptions(options);
    setCurrentOptionsHasBeenCalled(true);
    setIsLoading(false);
    return options;
  };

  const setOptions = async (options: Record<string, unknown>) => {
    setIsLoading(true);
    await apiFetch({
      data: options,
      method: 'POST',
      path: '/static-snap/v1/options/' + name,
    }).catch((e) => {
      throw e;
    });
    setIsLoading(false);
  };

  return { currentOptions, currentOptionsHasBeenCalled, getOptions, isLoading, setOptions };
};

export default useOptions;

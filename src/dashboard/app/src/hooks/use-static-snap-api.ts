import { useCallback } from 'react';

import ApiResponseInterface from '@staticsnap/frontend/src/interfaces/api-response.interface';
import apiFetch, { APIFetchOptions } from '@wordpress/api-fetch';

const STATIC_SNAP_API_PROXY_PATH = '/static-snap/v1/api-proxy';

const useStaticSnapAPI = (action: string, requestOptions: APIFetchOptions = {}) => {
  /**
   * Make a request to the Static Snap API
   * @param callbackOptions - RequestInit options
   * @param requestAction   - string to append to the action
   */
  const request = useCallback(
    async <T>(
      callbackOptions: APIFetchOptions = {},
      requestAction = ''
    ): Promise<ApiResponseInterface<T>> => {
      const fetchOptions = {
        ...requestOptions,
        ...callbackOptions,
      };

      try {
        const response = await apiFetch<ApiResponseInterface<T>>({
          path: `${STATIC_SNAP_API_PROXY_PATH}?action=${action}${requestAction}`,
          ...fetchOptions,
        });

        return response;
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
      } catch (error: any) {
        return {
          error: {
            cause: error?.cause,
            code: 500,
            message: error.message,
          },
          message: 'Failed to fetch',
          status: 'error',
          type: 'error',
        };
      }
    },
    [action, requestOptions]
  );

  return { request };
};

export default useStaticSnapAPI;

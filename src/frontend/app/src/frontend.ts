import Constants from './constants';
import FormBase from './form/form-base';
import Search from './modules/search';
import FuseSearch from './search/fuse-search';

declare global {
  interface Window {
    StaticSnapFrontendClasses: {
      FormBase: typeof FormBase;
      Constants: typeof Constants;
    };
    StaticSnapFrontendConfig: {
      locale: string | null;
      has_translations: boolean | string;
      is_static: boolean | string;
      is_admin_bar_showing: boolean | string;
      search_index_url: string;
      options: {
        search: {
          type: string;
          frontend_settings: Record<string, string>;
          settings: Record<string, unknown>;
        };
      };
    };
    StaticSnapFrontend: Frontend;
  }
}

export default class Frontend {
  public Search: Search;
  constructor() {
    this.Search = new Search(this);
    this.Search.addModule(new FuseSearch(this));
  }

  public config(): typeof window.StaticSnapFrontendConfig {
    const defaultConfig = {
      has_translations: false,
      is_admin_bar_showing: false,
      is_static: false,
      locale: null,
      options: {
        search: {
          frontend_settings: {},
          settings: {},
          type: 'fuse-js',
        },
      },

      search_index_url: '/search.json',
    };
    if (typeof window.StaticSnapFrontendConfig !== 'undefined') {
      // convert boolean strings to boolean
      for (const key in window.StaticSnapFrontendConfig) {
        const keyIndex = key as keyof typeof window.StaticSnapFrontendConfig;
        if (window.StaticSnapFrontendConfig[keyIndex] === 'true') {
          (window.StaticSnapFrontendConfig[keyIndex] as unknown as boolean) = true;
        } else if (window.StaticSnapFrontendConfig[keyIndex] === 'false') {
          (window.StaticSnapFrontendConfig[keyIndex] as unknown as boolean) = false;
        }
      }

      return { ...defaultConfig, ...window.StaticSnapFrontendConfig };
    }
    return defaultConfig;
  }
}

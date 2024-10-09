declare global {
  interface Window {
    StaticSnapDashboardConfig: {
      static_snap_api_url: string;
      static_snap_website_url: string;
    };
  }
}

export const STATIC_SNAP_URL =
  window.StaticSnapDashboardConfig && window.StaticSnapDashboardConfig.static_snap_website_url
    ? window.StaticSnapDashboardConfig.static_snap_website_url
    : 'https://staticsnap.com';
export const STATIC_SNAP_API_URL =
  window.StaticSnapDashboardConfig && window.StaticSnapDashboardConfig.static_snap_api_url
    ? window.StaticSnapDashboardConfig.static_snap_api_url
    : 'https://api.staticsnap.com';

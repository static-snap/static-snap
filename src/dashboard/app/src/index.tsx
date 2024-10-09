/**
 * React settings app
 */
import ReactDOM from 'react-dom';

import AdminBarApp from './admin-bar-app';
import App from './app';

const settingsAppContainer = document.getElementById('static-snap-settings-app');
if (settingsAppContainer) {
  ReactDOM.render(<App />, document.getElementById('static-snap-settings-app'));
}

// document on load
document.addEventListener('DOMContentLoaded', () => {
  const adminBarAppContainer = document.getElementById('wp-admin-bar-static-snap-admin-bar');
  if (adminBarAppContainer) {
    ReactDOM.render(<AdminBarApp />, document.getElementById('wp-admin-bar-static-snap-admin-bar'));
  }
});

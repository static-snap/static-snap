/**
 * Instantiates the frontend and binds it to the window.
 */
import FormBase from './form/form-base';
import Frontend from './frontend';
import Constants from './constants';

// bind frontend to window
window.StaticSnapFrontend = new Frontend();

/**
 * This is used to avoid code duplication in the frontend and extensions.
 * Creating this and adding '@staticsnap/frontend': 'StaticSnapFrontendClasses' to webpack externals
 * allows us to import the classes from the frontend in the extensions without duplicating the code.
 */
window.StaticSnapFrontendClasses = {
  FormBase,
  Constants,
};

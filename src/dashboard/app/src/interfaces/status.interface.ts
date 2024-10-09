import DeploymentInterface from './deployment.interface';

export default interface StatusInterface {
  last_deployment?: DeploymentInterface;
  is_running: boolean;
  is_processing: boolean;
  // this value means that we will show a finish message dialog to the user
  is_done: boolean;
  is_paused: boolean;
  is_cancelled: boolean;
}

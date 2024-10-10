export default interface DeploymentInterface {
  id: string;
  environment_id: string;
  environment_name: string;
  environment_settings: string;
  status: number;
  start_time: Date;
  end_time: Date;
  created_by_name: string;
  created_by_email: string;
  status_information?: {
    current_task: string;
    current_task_description: string;
    percentage: number;
  };
  error: unknown;
}

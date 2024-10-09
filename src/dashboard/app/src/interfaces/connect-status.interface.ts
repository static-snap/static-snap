interface ConnectStatusInterface {
  connected: boolean;
  error_message: string;
  has_valid_website_license: boolean;
  connection_error: boolean;
}

export default ConnectStatusInterface;

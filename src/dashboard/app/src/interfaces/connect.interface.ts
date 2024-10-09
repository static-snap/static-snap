interface ConnectInterface {
  installation_user_id: string;
  installation_id: number;
  installation_login: string;
  installation_code: string;

  user: {
    user_id: string;
    user_name: string;
    user_email: string;
    user_avatar_url: string;
    user_created: string;
    user_updated: string;
  };
  website_id: string;
  website_user_id: string;
  website_token: string;
  website_name: string;
  website_url: string;
  website_created: string;
  website_updated: string;
}

export default ConnectInterface;

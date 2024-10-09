// This is shared with the API and the client

export enum ErrorGroups {
  Github = 'GITHUB',
  Installation = 'INSTALLATION',
  Stripe = 'STRIPE',
}

// error enums
export enum ErrorCodes {
  // General
  InvalidRequest = 'INVALID_REQUEST',
  InternalServerError = 'INTERNAL_SERVER_ERROR',

  InvalidAccessToken = 'INVALID_ACCESS_TOKEN',
  InstallationTokenError = 'INSTALLATION_TOKEN_ERROR',
  NoInstallationFound = 'NO_INSTALLATION_FOUND',
  ErrorSavingInstallationToken = 'ERROR_SAVE_INSTALLATION_TOKEN',
  InvalidInstallationId = 'INVALID_INSTALLATION_ID',
  NoInstallationIdPassed = 'NO_INSTALLATION_ID_PASSED',
  NoUserIdPassed = 'NO_USER_ID_PASSED',
  NoGithubAccount = 'NO_GITHUB_ACCOUNT',

  // stripe
  InvalidPlanId = 'INVALID_PLAN_ID',
  InvalidInterval = 'INVALID_INTERVAL',
  InvalidPaymentIntent = 'INVALID_PAYMENT_INTENT',
  PaymentFailed = 'PAYMENT_FAILED',
}

export default interface ErrorCauseInterface {
  cause: {
    code: ErrorCodes;
    group: ErrorGroups;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    values?: any;
  };
}

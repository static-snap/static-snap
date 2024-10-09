import ExtensionInterface from './extension.interface';

export default interface EnvironmentTypeInterface extends ExtensionInterface {
  needsConnect: boolean;
  disabledReason?: string;

  isReadyToUse: boolean;
  isReadyToUseExtraSetupUrl?: string;
  isReadyToUseDisabledMessage?: string;
}

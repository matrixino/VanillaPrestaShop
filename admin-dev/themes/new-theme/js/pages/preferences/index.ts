/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
enum SwitchContainer {
  B2C,
  B2B,
}

class Preferences {
  private readonly b2cContainer: HTMLElement | null;

  private readonly b2bContainer: HTMLElement | null;

  private readonly b2cInputs: HTMLInputElement[];

  private readonly b2bInputs: HTMLInputElement[];

  constructor() {
    this.b2cContainer = document.getElementById('form_enable_b2c_mode')?.closest('.input-container') ?? null;
    this.b2bContainer = document.getElementById('form_enable_b2b_mode')?.closest('.input-container') ?? null;

    this.b2cInputs = Array.from(
      document.querySelectorAll('input[name="form[enable_b2c_mode]"]') as NodeListOf<HTMLInputElement>,
    );
    this.b2bInputs = Array.from(
      document.querySelectorAll('input[name="form[enable_b2b_mode]"]') as NodeListOf<HTMLInputElement>,
    );

    this.init();
  }

  private init(): void {
    if (!this.hasBothSwitches()) {
      return;
    }

    this.attachEventListeners();
    this.updateToggleStates();
    this.preventAllOffOnSubmit();
  }

  private hasBothSwitches(): boolean {
    return (
      this.b2cContainer !== null
      && this.b2bContainer !== null
      && this.b2cInputs.length > 0
      && this.b2bInputs.length > 0
    );
  }

  private attachEventListeners(): void {
    this.b2cInputs.forEach((input) => {
      input.addEventListener('change', () => this.updateToggleStates());
    });

    this.b2bInputs.forEach((input) => {
      input.addEventListener('change', () => this.updateToggleStates());
    });
  }

  private isEnabled(inputs: HTMLInputElement[]): boolean {
    return inputs.some((input) => input.checked && input.value === '1');
  }

  private setEnabled(inputs: HTMLInputElement[], enabled: boolean): void {
    const valueToCheck = enabled ? '1' : '0';
    inputs.forEach((input) => {
      input.checked = input.value === valueToCheck;
    });
    inputs.forEach((input) => input.dispatchEvent(new Event('change', {bubbles: true})));
  }

  private updateToggleStates(): void {
    const b2cEnabled = this.isEnabled(this.b2cInputs);
    const b2bEnabled = this.isEnabled(this.b2bInputs);

    this.setToggleReadOnly(SwitchContainer.B2C);
    this.setToggleReadOnly(SwitchContainer.B2B);

    if (!b2cEnabled && !b2bEnabled) {
      this.setEnabled(this.b2cInputs, true);
    }
  }

  private setToggleReadOnly(switchContainer: SwitchContainer): void {
    let container: HTMLElement | null = null;
    let inputs: HTMLInputElement[] | null = null;

    switch (switchContainer) {
      case SwitchContainer.B2C:
        container = this.b2cContainer;
        inputs = this.b2cInputs;
        break;
      case SwitchContainer.B2B:
        container = this.b2bContainer;
        inputs = this.b2bInputs;
        break;
      default:
        return;
    }
  }

  private preventAllOffOnSubmit(): void {
    const form = document.getElementById('configuration_form') as HTMLFormElement | null;

    if (!form) {
      return;
    }

    form.addEventListener('submit', (e) => {
      const b2cEnabled = this.isEnabled(this.b2cInputs);
      const b2bEnabled = this.isEnabled(this.b2bInputs);

      if (!b2cEnabled && !b2bEnabled) {
        e.preventDefault();
        this.setEnabled(this.b2cInputs, true);
        alert('At least one mode must be enabled (B2C or B2B).');
      }
    });
  }
}

$(() => {
  new Preferences();
});

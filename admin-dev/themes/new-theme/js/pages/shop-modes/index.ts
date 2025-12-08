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
  IMPROVED_B2B,
}

class ShopModes {
  private readonly b2cContainer: HTMLElement | null;
  private readonly improvedB2bContainer: HTMLElement | null;
  private readonly b2cSwitch: NodeListOf<HTMLInputElement>;
  private readonly improvedB2bSwitch: NodeListOf<HTMLInputElement>;
  private isB2cEnabled: boolean = false;
  private isImprovedB2BEnabled: boolean = false;

  constructor() {
    this.b2cContainer = document.getElementById('form_enable_b2c_feature')?.closest('.input-container') ?? null;
    this.b2cSwitch = document.querySelectorAll('input[name="form[enable_b2c_feature]"]') as NodeListOf<HTMLInputElement>;
    this.improvedB2bContainer = document.getElementById('form_enable_improved_b2b_feature')?.closest('.input-container') ?? null;
    this.improvedB2bSwitch = document.querySelectorAll('input[name="form[enable_improved_b2b_feature]"]') as NodeListOf<HTMLInputElement>;
    this.init();
  }

  private init(): void {
    this.attachEventListeners();
    console.log(this.isB2cEnabled, this.isImprovedB2BEnabled);
    this.updateToggleStates();
  }

  private attachEventListeners(): void {
    this.b2cSwitch.forEach(input => {
      if (input.checked && input.value === '1') {
        this.isB2cEnabled = true;
      }
      input.addEventListener('change', (event: Event) => {
        const target = event.target as HTMLInputElement;
        this.isB2cEnabled = target.value === '1';
        this.updateToggleStates();
      });
    });
    this.improvedB2bSwitch.forEach(input => {
      if (input.checked && input.value === '1') {
        this.isImprovedB2BEnabled = true;
      }
      input.addEventListener('change', (event: Event) => {
        const target = event.target as HTMLInputElement;
        this.isImprovedB2BEnabled = target.value === '1';
        this.updateToggleStates();
      });
    });
  }

  private updateToggleStates(): void {
    const readOnlyB2c = this.isB2cEnabled && !this.isImprovedB2BEnabled;
    const readOnlyB2b = !this.isB2cEnabled && this.isImprovedB2BEnabled;

    this.setToggleReadOnly(SwitchContainer.B2C, readOnlyB2c);
    this.setToggleReadOnly(SwitchContainer.IMPROVED_B2B, readOnlyB2b);
  }

  private setToggleReadOnly(switchContainer: SwitchContainer, disabled: boolean): void {
    let container = null;
    let switchElement = null;

    switch (switchContainer) {
      case SwitchContainer.B2C:
        container = this.b2cContainer;
        switchElement = this.b2cSwitch;
        break;
      case SwitchContainer.IMPROVED_B2B:
        container = this.improvedB2bContainer;
        switchElement = this.improvedB2bSwitch;
        break;
      default:
        return;
    }

    if (container === null || switchElement === null) {
      return;
    }

    container.classList.toggle('disabled', disabled);
    switchElement.forEach(input => {
      input.style.pointerEvents = disabled ? 'none' : '';
    });
  }
}

$(() => {
  new ShopModes();
});

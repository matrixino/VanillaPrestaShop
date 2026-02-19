<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Form\IdentifiableObject\OptionProvider;

class DiscountFormOptionsProvider implements FormOptionsProviderInterface
{
    public function getOptions(int $id, array $data): array
    {
        return [
            'discount_type' => $data['information']['discount_type'] ?? '',
        ];
    }

    public function getDefaultOptions(array $data): array
    {
        return [];
    }
}

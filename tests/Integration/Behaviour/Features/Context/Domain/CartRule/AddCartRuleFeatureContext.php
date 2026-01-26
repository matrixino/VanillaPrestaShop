<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace Tests\Integration\Behaviour\Features\Context\Domain\CartRule;

use Behat\Gherkin\Node\TableNode;
use PrestaShop\PrestaShop\Core\Domain\CartRule\Exception\CartRuleConstraintException;
use PrestaShop\PrestaShop\Core\Domain\CartRule\Exception\CartRuleNotFoundException;

class AddCartRuleFeatureContext extends AbstractCartRuleFeatureContext
{
    /**
     * @When I create cart rule :cartRuleReference with following properties:
     *
     * @param string $cartRuleReference
     * @param TableNode $node
     */
    public function createCartRuleIfNotExists(string $cartRuleReference, TableNode $node): void
    {
        $data = $this->localizeByRows($node);

        if ($this->getSharedStorage()->exists($cartRuleReference)) {
            try {
                // if cart rule already exists we assert all its expected properties
                $this->assertCartRuleProperties(
                    $this->getCartRuleForEditing($cartRuleReference),
                    $data
                );

                return;
            } catch (CartRuleNotFoundException $e) {
                // if cart rule was not found we proceed to create it under this reference.
            }
        }

        try {
            $this->createCartRuleWithReference($cartRuleReference, $data);
        } catch (CartRuleConstraintException $e) {
            $this->setLastException($e);
        }
    }
}

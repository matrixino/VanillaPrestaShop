<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Unit\Core\Domain\Country\AddressFormat;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Address\Query\GetRequiredFieldsForAddress;
use PrestaShop\PrestaShop\Core\Domain\Country\AddressFormat\AddressFormatFieldsProvider;
use RuntimeException;

/**
 * @group address-format
 */
class AddressFormatFieldsProviderTest extends TestCase
{
    public function testGetFieldsForKnownClassReturnsHardcodedList(): void
    {
        $provider = new AddressFormatFieldsProvider($this->fakeQueryBus([]));

        $address = $provider->getFieldsForClass('Address');
        $this->assertContains('firstname', $address);
        $this->assertContains('lastname', $address);
        $this->assertContains('address1', $address);
        $this->assertContains('city', $address);
        $this->assertContains('phone_mobile', $address);
        $this->assertContains('dni', $address);

        $this->assertSame(['name', 'iso_code'], $provider->getFieldsForClass('Country'));
        $this->assertSame(['name', 'iso_code'], $provider->getFieldsForClass('State'));

        $customer = $provider->getFieldsForClass('Customer');
        $this->assertContains('firstname', $customer);
        $this->assertContains('email', $customer);

        $warehouse = $provider->getFieldsForClass('Warehouse');
        $this->assertContains('reference', $warehouse);
    }

    public function testGetFieldsForUnknownClassReturnsEmptyList(): void
    {
        $provider = new AddressFormatFieldsProvider($this->fakeQueryBus([]));

        $this->assertSame([], $provider->getFieldsForClass('Manufacturer'));
        $this->assertSame([], $provider->getFieldsForClass('Supplier'));
        $this->assertSame([], $provider->getFieldsForClass('NotAThing'));
    }

    public function testGetRequiredFieldsMergesStaticDefaultsWithDbManagedList(): void
    {
        $provider = new AddressFormatFieldsProvider(
            $this->fakeQueryBus(['phone_mobile', 'company'])
        );

        $required = $provider->getRequiredFields();

        // static defaults are always present
        $this->assertContains('firstname', $required);
        $this->assertContains('lastname', $required);
        $this->assertContains('address1', $required);
        $this->assertContains('city', $required);
        $this->assertContains('Country:name', $required);
        // DB-managed entries are appended
        $this->assertContains('phone_mobile', $required);
        $this->assertContains('company', $required);
    }

    public function testGetRequiredFieldsDeduplicatesOverlappingEntries(): void
    {
        // Merchant flagged `firstname` (which is already in the static defaults) as
        // required in BO > Customers > Addresses — must appear exactly once.
        $provider = new AddressFormatFieldsProvider(
            $this->fakeQueryBus(['firstname', 'phone_mobile'])
        );

        $required = $provider->getRequiredFields();
        $occurrences = array_count_values($required);
        $this->assertSame(1, $occurrences['firstname'] ?? 0);
        $this->assertSame(1, $occurrences['phone_mobile'] ?? 0);
    }

    public function testGetRequiredFieldsWithEmptyDbListReturnsOnlyStaticDefaults(): void
    {
        $provider = new AddressFormatFieldsProvider($this->fakeQueryBus([]));

        $this->assertSame(
            ['firstname', 'lastname', 'address1', 'city', 'Country:name'],
            $provider->getRequiredFields()
        );
    }

    /**
     * @param list<string> $dbManagedFields
     */
    private function fakeQueryBus(array $dbManagedFields): CommandBusInterface
    {
        return new class($dbManagedFields) implements CommandBusInterface {
            public function __construct(private readonly array $dbManagedFields)
            {
            }

            public function handle($command): mixed
            {
                if ($command instanceof GetRequiredFieldsForAddress) {
                    return $this->dbManagedFields;
                }
                throw new RuntimeException('Unexpected query: ' . get_class($command));
            }
        };
    }
}

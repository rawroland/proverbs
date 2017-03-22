<?php
use AppBundle\Billing\FakePaymentGateway;
use AppBundle\Entity\Releasable;
use AppBundle\Billing\Purchase;
use AppBundle\Repository\AccountRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\AppBundle\Helpers\AccountHelper;
use Tests\AppBundle\Helpers\LicenceHelper;
use Tests\AppBundle\IsolatedDatabaseTest;

class PurchaseTest extends KernelTestCase
{
    use IsolatedDatabaseTest;

    /**
     * @var AccountRepository
     */
    private $accounts;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function setUp()
    {
        static::bootKernel();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->createSchema($this->entityManager);
        $this->accounts = static::$kernel->getContainer()->get('app.accounts_repository');
    }

    public function tearDown()
    {

    }

    /**
     * @test
     */
    function calculation_the_total_cost_of_a_licence_purchase()
    {
        $licence = (new LicenceHelper())->getLicence('ad_free')->setPrice(999);
        $account = (new AccountHelper())->getAccount();

        $purchase = Purchase::fromLicence($licence, $account);

        $this->assertEquals(999, $purchase->totalCost());
        $this->assertInstanceOf(Releasable::class, $purchase->article());
    }

    /**
     * @test
     */
    function completing_purchase()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager)->setPrice(999);
        $account = (new AccountHelper())->getAccount()->setEmail('john.doe@example.com');
        $purchase = Purchase::fromLicence($licence, $account);
        $paymentGateway = new FakePaymentGateway();

        $account = $purchase->complete($this->accounts, $paymentGateway, $paymentGateway->getValidToken());

        $this->assertEquals(999, $account->getAmount());
        $this->assertTrue($this->accounts->accountExists('john.doe@example.com', 'ad_free'));
        $this->assertEquals(999, $paymentGateway->totalCharges());
    }

}
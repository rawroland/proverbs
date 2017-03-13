<?php

namespace Test\AppBundle\Repository;

use AppBundle\Entity\Account;
use AppBundle\Repository\AccountRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\AppBundle\Helpers\LicenceHelper;
use Tests\AppBundle\IsolatedDatabaseTest;

class AccountRepositoryTest extends KernelTestCase
{
    use IsolatedDatabaseTest;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    public function setUp()
    {
        static::bootKernel();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->createSchema($this->entityManager);
        $this->accountRepository = static::$kernel->getContainer()->get('app.accounts_repository');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->entityManager->close();
        parent::tearDown();
    }

    /**
     * @test
     */
    function creating_an_account() {
        $account = (new Account())->setEmail('john.doe@example.com')
          ->setName('John')
          ->setSurname('Doe')
          ->setPassword('password');
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $this->accountRepository->create($account, $licence, 999);

        $this->assertEquals(999, $account->getAmount());
        $this->assertTrue($this->accountRepository->accountExists('john.doe@example.com', 'ad_free'));
    }

}

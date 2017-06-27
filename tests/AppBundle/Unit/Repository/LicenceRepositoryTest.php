<?php

namespace Test\AppBundle\Repository;

use AppBundle\Entity\Account;
use AppBundle\Repository\LicenceRepository;
use Behat\Mink\Exception\Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\AppBundle\Helpers\AccountHelper;
use Tests\AppBundle\Helpers\LicenceHelper;
use Tests\AppBundle\IsolatedDatabaseTest;

class LicenceRepositoryTest extends KernelTestCase
{
    use IsolatedDatabaseTest;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var LicenceRepository
     */
    private $licences;

    public function setUp()
    {
        static::bootKernel();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->createSchema($this->entityManager);
        $this->licences = static::$kernel->getContainer()->get('app.licences_repository');
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
    function licences_with_purchasable_types_can_be_purchased()
    {
        $adFree = (new LicenceHelper())->createLicence('ad_free', $this->entityManager, 0);
        $free = (new LicenceHelper())->createLicence('free', $this->entityManager);
        $negotiable = (new LicenceHelper())->createLicence('negotiable', $this->entityManager);

        $proverbs = new ArrayCollection($this->licences->purchasable()->getResult());

        $this->assertFalse($proverbs->contains($adFree));
        $this->assertTrue($proverbs->contains($free));
        $this->assertFalse($proverbs->contains($negotiable));
    }

    /**
     * @test
     */
    function available_licences_can_be_increased()
    {
        $licence = (new LicenceHelper())->createLicence('free', $this->entityManager, 2);

        $this->licences->increaseRemaining($licence, 8);

        $this->assertEquals(10, $licence->getRemaining());
    }

    /**
     * @test
     */
    function considers_the_id_when_querying_purchasable_licences()
    {
        $licenceHelper = new LicenceHelper();
        $expected = $licenceHelper->createLicence('free', $this->entityManager, 2);
        $licenceHelper->createLicence('ad_free', $this->entityManager, 2);

        $actual = $this->licences->purchasable($expected->getId())->getSingleResult();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    function can_reserve_licence()
    {
        $licence = (new LicenceHelper())->createLicence('free', $this->entityManager, 2);
        $this->assertEquals(2, $licence->getRemaining());

        $account = (new AccountHelper())->getAccount()->setEmail('john.doe@example.com');
        $purchase = $this->licences->reserve($licence->getId(), $account);

        $this->assertEquals(1, $purchase->article()->getRemaining());
        $this->assertEquals($licence->getId(), $purchase->article()->getId());
        $this->assertInstanceOf(Account::class, $purchase->account());
    }

    /**
     * @test
     */
    function cannot_reserve_an_unavailable_licence()
    {
        $licence = (new LicenceHelper())->createLicence('free', $this->entityManager, 0);
        $account = (new AccountHelper())->getAccount();
        $this->assertEquals(0, $licence->getRemaining());

        try {
            $this->licences->reserve($licence->getId(), $account);
        } catch (NoResultException $exception) {
            $this->assertEquals(0, $licence->getRemaining());

            return;
        }

        $this->fail('An unavailable licence was erroneously reserved.');
    }

    /**
     * @test
     */
    function reserved_licence_can_be_cancelled()
    {
        $licence = (new LicenceHelper())->createLicence('free', $this->entityManager, 2);
        $account = (new AccountHelper())->getAccount();
        $this->assertEquals(2, $licence->getRemaining());
        $purchase = $this->licences->reserve($licence->getId(), $account);

        $this->licences->cancel($purchase->article());

        $this->assertEquals(2, $purchase->article()->getRemaining());
    }
}

<?php

namespace Test\AppBundle\Repository;

use AppBundle\Repository\ProverbRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\AppBundle\Helpers\ProverbHelper;
use Tests\AppBundle\IsolatedDatabaseTest;

class ProverbRepositoryTest extends KernelTestCase
{
    use IsolatedDatabaseTest;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ProverbRepository
     */
    private $proverbRepository;

    public function setUp()
    {
        static::bootKernel();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->createSchema($this->entityManager);
        $this->proverbRepository = static::$kernel->getContainer()->get('app.proverbs_repository');
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
    function proverbs_with_a_published_date_are_published() {
        $proverb1 = (new ProverbHelper())->getPublishedProverb();
        $this->proverbRepository->save($proverb1);
        $proverb2 = (new ProverbHelper())->getPublishedProverb();
        $this->proverbRepository->save($proverb2);
        $proverb3 = (new ProverbHelper())->getNonPublishedProverb();
        $this->proverbRepository->save($proverb3);

        $proverbs = new ArrayCollection($this->proverbRepository->published()->getQuery()->getResult());

        $this->assertTrue($proverbs->contains($proverb1));
        $this->assertTrue($proverbs->contains($proverb2));
        $this->assertFalse($proverbs->contains($proverb3));
    }

}

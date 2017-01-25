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
        $this->proverbRepository = $this->entityManager->getRepository('AppBundle:Proverb');
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
        $this->entityManager->persist($proverb1);
        $this->entityManager->flush();
        $proverb2 = (new ProverbHelper())->getPublishedProverb();
        $this->entityManager->persist($proverb2);
        $this->entityManager->flush();
        $proverb3 = (new ProverbHelper())->getNonPublishedProverb();
        $this->entityManager->persist($proverb3);
        $this->entityManager->flush();

        $proverbs = new ArrayCollection($this->proverbRepository->published()->getQuery()->getResult());

        $this->assertTrue($proverbs->contains($proverb1));
        $this->assertTrue($proverbs->contains($proverb2));
        $this->assertFalse($proverbs->contains($proverb3));
    }

}

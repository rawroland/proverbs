<?php

namespace Tests\AppBundle\Features;

use AppBundle\Repository\ProverbRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Helpers\ProverbHelper;
use Tests\AppBundle\IsolatedDatabaseTest;

class ProverbsControllerTest extends WebTestCase
{
    use IsolatedDatabaseTest;

    /**
     * @var ProverbRepository
     */
    protected $proverbRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = $client = static::createClient();
        $this->entityManager = $this->client
          ->getContainer()
          ->get('doctrine')
          ->getManager();
        $this->createSchema($this->entityManager);
        $this->proverbRepository = $this->client->getContainer()->get('app.proverbs_repository');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->client = null;
        parent::tearDown();
    }

    /**
     * @test
     */
    function user_can_view_a_published_proverb()
    {
        // Arrange
        $proverb = (new ProverbHelper())->getPublishedProverb();
        $this->entityManager->persist($proverb);
        $this->entityManager->flush();

        // Act
        $this->client->request('GET', '/proverbs/' . $proverb->getId());

        // Assert
        $this->assertEquals(
            200,
            $this->client->getResponse()->getStatusCode(),
            '/proverbs/' . $proverb->getId() . ' returns an http error'
        );
        $this->assertContains('All is good that ends well', $this->client->getResponse()->getContent());
        $this->assertContains('An event with a good outcome is good, irrespective of the wrongs along the way.',
          $this->client->getResponse()->getContent());
        $this->assertContains('It came from somewhere', $this->client->getResponse()->getContent());
        $this->assertContains('January 23, 2017', $this->client->getResponse()->getContent());
    }

    /**
     * @test
     */
    function user_cannot_view_unpublished_proverbs() {
        $proverb = (new ProverbHelper())->getNonPublishedProverb();
        $this->proverbRepository->save($proverb);

        $this->client->request('GET', '/proverbs/' . $proverb->getId());

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
}

<?php

namespace Tests\AppBundle\Features;

use AppBundle\Billing\FakePaymentGateway;
use AppBundle\Billing\PaymentGateway;
use AppBundle\Entity\Licence;
use AppBundle\Repository\AccountRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Helpers\LicenceHelper;
use Tests\AppBundle\IsolatedDatabaseTest;

class PurchaseLicenceTest extends WebTestCase
{
    use IsolatedDatabaseTest;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var FakePaymentGateway
     */
    private $paymentGateway;

    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    public function setUp()
    {
        $this->client = $client = static::createClient();
        $this->entityManager = $this->client
          ->getContainer()
          ->get('doctrine')
          ->getManager();
        $this->createSchema($this->entityManager);
        $this->paymentGateway = new FakePaymentGateway([]);
        $this->client->getContainer()->set('app.payment_gateway', $this->paymentGateway);
        $this->accountRepository = $this->client->getContainer()->get('app.accounts_repository');
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
    function user_can_purchase_an_ad_free_account()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $json = json_encode([
          'email' => 'john.doe@example.com',
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]);
        $this->purchaseLicence($licence, $json);

        //Assert
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $totalCharges = $this->paymentGateway->totalCharges();
        $jsonResponse = json_encode([
          'email' => 'john.doe@example.com',
          'name' => 'John',
          'surname' => 'Doe',
          'amount' => $totalCharges,
        ]);
        $this->assertJsonStringEqualsJsonString($jsonResponse, $response->getContent());
        $this->assertEquals(999, $totalCharges);
        $this->assertTrue($this->accountRepository->accountExists('john.doe@example.com', 'ad_free'));
    }

    /**
     * @test
     */
    function user_cannot_purchase_a_licence_without_an_email_address()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $this->purchaseLicence($licence, json_encode([
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertValidationError('email');
    }

    /**
     * @test
     */
    function user_cannot_purchase_a_licence_with_an_invalid_email_address()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $this->purchaseLicence($licence, json_encode([
          'email' => 'invalid-email',
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertValidationError('email');
    }

    /**
     * @test
     */
    function user_cannot_purchase_a_licence_without_a_name()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $this->purchaseLicence($licence, json_encode([
          'email' => 'john@doe.com',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertValidationError('name');
    }

    /**
     * @test
     */
    function user_cannot_purchase_a_licence_without_a_surname()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $this->purchaseLicence($licence, json_encode([
          'email' => 'foo@bar.com',
          'name' => 'John',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertValidationError('surname');
    }

    /**
     * @test
     */
    function user_cannot_purchase_a_licence_without_a_password()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $this->purchaseLicence($licence, json_encode([
          'email' => 'foo@bar.com',
          'name' => 'John',
          'surname' => 'Doe',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertValidationError('password');
    }

    /**
     * @test
     */
    function user_cannot_purchase_a_licence_without_a_payment_token()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager);

        $this->purchaseLicence($licence, json_encode([
          'email' => 'foo@bar.com',
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
        ]));

        $this->assertValidationError('payment_token');
    }

    /**
     * @test
     */
    function a_licence_is_not_purchased_if_payment_fails()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager, 5);
        $this->assertEquals(5, $licence->getRemaining());

        $this->purchaseLicence($licence, json_encode([
          'email' => 'foo@bar.com',
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => 'invalid-payment-token',
        ]));

        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->accountRepository->accountExists('foo@bar.com', 'ad_free'));
        $this->assertEquals(5, $licence->getRemaining());
    }

    /**
     * @test
     */
    function user_cannot_purchase_negotiable_licences()
    {
        $licence = (new LicenceHelper())->createLicence('negotiable', $this->entityManager);

        $this->purchaseLicence($licence, json_encode([
          'email' => 'foo@bar.com',
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->accountRepository->accountExists('foo@bar.com', 'ad_free'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    function cannot_purchase_more_licences_than_available()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager, 0);

        $this->purchaseLicence($licence, json_encode([
          'email' => 'foo@bar.com',
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->accountRepository->accountExists('foo@bar.com', 'ad_free'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(0, $licence->getRemaining());
    }

    /**
     * @test
     */
    function cannot_purchase_licences_some_other_person_is_purchasing()
    {
        $licence = (new LicenceHelper())->createLicence('ad_free', $this->entityManager, 1);
        $this->client->disableReboot();
        $this->paymentGateway->beforeFirstCharge(function (FakePaymentGateway $paymentGateway)  use ($licence) {
            $this->purchaseLicence($licence, json_encode([
              'email' => 'personB@example.com',
              'name' => 'Jane',
              'surname' => 'Doe',
              'password' => 'password',
              'payment_token' => $this->paymentGateway->getValidToken(),
            ]));

            $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
            $this->assertFalse($this->accountRepository->accountExists('personB@example.com', 'ad_free'));
            $this->assertEquals(0, $paymentGateway->totalCharges());
        });

        $this->purchaseLicence($licence, json_encode([
          'email' => 'personA@example.com',
          'name' => 'John',
          'surname' => 'Doe',
          'password' => 'password',
          'payment_token' => $this->paymentGateway->getValidToken(),
        ]));

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->accountRepository->accountExists('personA@example.com', 'ad_free'));
        $this->assertEquals(999, $this->paymentGateway->totalCharges());
    }

    /**
     * @param Licence $licence
     * @param string $json
     */
    public function purchaseLicence(Licence $licence, $json)
    {
        $this->client->request(
          'POST',
          "/accounts/licence/{$licence->getId()}",
          [],
          [],
          ['CONTENT_TYPE' => 'application_json'],
          $json
        );
    }

    private function assertValidationError($field)
    {
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
        $this->assertArrayHasKey($field, json_decode($this->client->getResponse()->getContent(), true));
    }

}

<?php

namespace AppBundle\Controller;

use AppBundle\Billing\PaymentFailedException;
use AppBundle\Billing\PaymentGateway;
use AppBundle\Billing\Purchase;
use AppBundle\Billing\StripePaymentGateway;
use AppBundle\Entity\Account;
use AppBundle\Form\AccountType;
use AppBundle\Repository\AccountRepository;
use AppBundle\Repository\LicenceRepository;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccountLicencesController
 * @package AppBundle\Controller
 * @author Roland Awemo
 *
 * @Route(service="app.account_licence_controller")
 */
class AccountLicencesController
{
    use ValidationErrorExtractor;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var LicenceRepository
     */
    private $licences;

    /**
     * @var PaymentGateway
     */
    private $paymentGateway;

    /**
     * @var AccountRepository
     */
    private $accounts;

    public function __construct(
      AccountRepository $accounts,
      LicenceRepository $licences,
      FormFactoryInterface $formFactory,
      PaymentGateway $paymentGateway
    )
    {
        $this->accounts = $accounts;
        $this->licences = $licences;
        $this->formFactory = $formFactory;
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * @Route("/accounts/licence/{licenceId}", name="purchase_ticket", requirements={"licenceId": "\d+"})"
     * @Method("POST)
     * @param string|int $licenceId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function purchaseAction($licenceId, Request $request)
    {
        $account = new Account();
        $form = $this->formFactory->create(AccountType::class, $account);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, true);
        if (!$form->isValid()) {
            return new JsonResponse($this->getErrorsFromForm($form), 422);
        }

        try {
            $licence = $this->licences->reserve($licenceId);
            $purchase = Purchase::fromLicence($licence);
            $this->paymentGateway->charge($purchase->totalCost(), $data['payment_token']);
            $this->accounts->create($account, $licence, $purchase->totalCost());

            return new JsonResponse($account->toArray(), 201);
        } catch (NoResultException $exception) {
            return new JsonResponse([], 404);
        } catch (PaymentFailedException $exception) {
            $this->licences->cancel($licence);
            return new JsonResponse([], 422);
        }
    }

}

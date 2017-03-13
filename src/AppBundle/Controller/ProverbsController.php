<?php

namespace AppBundle\Controller;

use AppBundle\Repository\ProverbRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ProverbsController
 * @package AppBundle\Controller
 * @author Roland Awemo
 *
 * @Route(service="app.proverbs_controller")
 */
class ProverbsController
{
    /**
     * @var ProverbRepository
     */
    private $proverbs;

    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(ProverbRepository $proverbs, EngineInterface $templating)
    {
        $this->templating = $templating;
        $this->proverbs = $proverbs;
    }

    /**
     *
     * @Route("/proverbs/{id}", name="show_proverb", requirements={"id": "\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($id)
    {
        $proverb = $this->proverbs
          ->published()
          ->andWhere('proverb.id = :id')
          ->setParameter('id', $id)
          ->getQuery()
          ->getOneOrNullResult();
        if (!$proverb) {
            throw new NotFoundHttpException();
        }
        return $this->templating->renderResponse(':proverbs:show.html.twig', ['proverb' => $proverb]);
    }
}

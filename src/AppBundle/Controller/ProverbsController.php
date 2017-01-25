<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProverbsController extends Controller
{
    /**
     *
     * @Route("/proverbs/{id}", name="show_proverb", requirements={"id": "\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($id)
    {
        $proverb = $this->getDoctrine()
          ->getRepository('AppBundle:Proverb')
          ->published()
          ->andWhere('proverb.id = :id')
          ->setParameter('id', $id)
          ->getQuery()
          ->getOneOrNullResult();
        if (!$proverb) {
            throw new NotFoundHttpException();
        }
        return $this->render(':proverbs:show.html.twig', ['proverb' => $proverb]);
    }
}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $proverb = $this->getDoctrine()->getRepository('AppBundle:Proverb')->find($id);
        return $this->render(':proverbs:show.html.twig', ['proverb' => $proverb]);
    }
}

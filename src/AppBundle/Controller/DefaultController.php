<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="get")
     */
    public function indexAction(Request $request)
    {
        $csrfToken = $this->has('security.csrf.token_manager')
            ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
            : null;

        /*$em = $this->getDoctrine()->getManager();
        var_dump($em->getRepository("AppBundle:User")->find(1));*/

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'csrf_token' => $csrfToken,
            //'users' => $users,
        ]);
    }

    /**
     * @Route("/register", name="insc")
     */
    public function inscriptionAction(Request $request)
    {
        return new Response("test");
    }
}

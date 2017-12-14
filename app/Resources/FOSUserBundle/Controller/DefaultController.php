<?php

namespace app\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/register", name="insc")
     */
    public function inscriptionAction(Request $request)
    {
        return new Response("test");
    }
}

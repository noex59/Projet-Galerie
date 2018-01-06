<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Gregwar\CaptchaBundle\Type\CaptchaType;

class ImageController extends Controller
{
    /**
     * @Route("/", name="imgPage")
     */
    public function indexAction(Request $request)
    {

    }

    /**
     * @Route("/mygalery/{idUser}", name="showGaleryPerso")
     */
    public function affGaleryAction(Request $request,  $idUser)
    {
        $csrfToken = $this->has('security.csrf.token_manager')
            ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
            : null;

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository("AppBundle:User")->findByNothingUserForMenu();
        $pictures = $em->getRepository("AppBundle:Picture")->findByIdUser($idUser); // TODO : mettre num aleat

        $username = null;

        if ($pictures != null) {
            foreach ($users as $val) {
                if($val["id"] == $pictures[0]["idUser"])
                    $username = $val["username"];
            }
        }

        return $this->render('default/image.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'csrf_token' => $csrfToken,
            'users' => $users,
            'photos' => $pictures != null ? $pictures : "pas d'images enabled :(",
            'username' => $username != null ? $username : "",
            'id' => $idUser,
        ]);
    }    
}

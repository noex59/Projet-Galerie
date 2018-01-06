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

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository("AppBundle:User")->findByNothingUserForMenu();
        $pictures = $em->getRepository("AppBundle:Picture")->findByIdUser(1); // TODO : mettre num aleat

        $username = null;

        if ($pictures != null) {
            foreach ($users as $val) {
                if($val["id"] == $pictures[0]["idUser"])
                    $username = $val["username"];
            }
        }

        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $user == "anon." ? $id = null : $id = $user->getId();

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'csrf_token' => $csrfToken,
            'users' => $users,
            'photos' => $pictures != null ? $pictures : "pas d'images enabled :(",
            'username' => $username != null ? $username : "",
            'id' => $id,
        ]);
    }

    /**
    * @Route("/login", name="log")
    **/
    public function loginAction(Request $request)
    {
        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        return $this->redirectToRoute("get");
    }

    /**
     * @Route("/galery/{idUser}", name="showGalery")
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

        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $user == "anon." ? $id = null : $id = $user->getId();

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'csrf_token' => $csrfToken,
            'users' => $users,
            'photos' => $pictures != null ? $pictures : "pas d'images enabled :(",
            'username' => $username != null ? $username : "",
            'id' => $id,
        ]);
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return Response
     */
    protected function renderLogin(array $data)
    {
        return $this->render('default/index.html.twig', $data);
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    
}

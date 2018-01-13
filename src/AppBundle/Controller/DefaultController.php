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


        $session = new Session();

        $listEmail = $em->getRepository("AppBundle:User")->findByAllEmail();
        
        // RECUPERER EMAIL CURRENT
        $email = $session->get("loginAlex");//$session->get("_security.last_username");
        $flagEmailExist = false;
               
        foreach ($listEmail as $value) {
            if ($email == $value["email"]) {
                if (!isset($session->get("mailNbTentatives")[$email])) {
                    $tab = $session->get("mailNbTentatives");
                    $tab[$email] = 2;
                    $session->set("mailNbTentatives", $tab);
                }
                elseif ($session->get("mailNbTentatives")[$email] - 1 == 0 && isset($session->get("mailNbTentatives")[$email])) {
                    $session->set("mailNbTentatives", array($email => 0));
                    $id = $em->getRepository('AppBundle:User')->findByMail($email);
                    $user = $em->getRepository('AppBundle:User')->find((int)$id[0]["id"]);
                    $user->setEnabled(0);
                    $em->flush();
                }
                elseif (isset($session->get("mailNbTentatives")[$email]) && $session->get("mailNbTentatives")[$email] > 0){
                    $tab = $session->get("mailNbTentatives");
                    $tab[$email] = --$session->get("mailNbTentatives")[$email];
                    $session->set("mailNbTentatives", $tab);
                }

                break;
            }
            else{
                //$session->clear();
                $flagEmailExist = true;
            }
        }

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'csrf_token' => $csrfToken,
            'users' => $users,
            'photos' => $pictures != null ? $pictures : "pas d'images enabled :(",
            'username' => $username != null ? $username : "",
            'id' => (int)$id[0]["id"],
            'nbTentatives' => isset($session->get("mailNbTentatives")[$email]) ? $session->get("mailNbTentatives")[$email] : 3,
            'affMess' => $flagEmailExist,
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

    /**
     * @Route("/testJquery", name="testJquery")
     */
    public function testJquery(Request $request){
        $login = $request->query->get('login');

        $session = $request->getSession();
        $session->set('loginAlex', $login);
        die();
    }

    
}

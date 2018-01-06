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

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $listUser = $em->getRepository("AppBundle:User")->findUsers();

        $token = $this->get('security.token_storage')->getToken();
        $user = $token->getUser();

        $user == "anon." ? $id = null : $id = $user->getId();

        return $this->render('default/admin.html.twig', [
            'list' => $listUser,
            'id' => $id,
        ]);
    }

    /**
     * @Route("/admin/del/{id}", name="delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($id);

        if(!$user)
            return $this->redirectToRoute("admin");

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute("admin");
    }

    /**
     * @Route("/admin/upd/{id}", name="update")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($id);

        if(!$user)
            return $this->redirectToRoute("admin");

        $user->setEnabled(1);

        $em->flush();

        return $this->redirectToRoute("admin");
    }
}

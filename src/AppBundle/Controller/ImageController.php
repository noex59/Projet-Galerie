<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Picture;

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
     * @Route("/mygalery/{idUser}", name="showGaleryPerso")
     */
    public function affGaleryAction(Request $request, $idUser)
    {
        $csrfToken = $this->has('security.csrf.token_manager')
            ? $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue()
            : null;

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository("AppBundle:User")->findByNothingUserForMenu();
        $pictures = $em->getRepository("AppBundle:Picture")->findByIdUserNULL($idUser);

        $username = null;

        if ($pictures != null) {
            foreach ($users as $val) {
                if($val["id"] == $pictures[0]["idUser"])
                    $username = $val["username"];
            }
        }
        
        $error = null;  

        if (isset($_GET["flagErrorMessage"])) {
            $error = "Veuillez rentrer un url ou importer un fichier svp";
        }

        return $this->render('default/image.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'csrf_token' => $csrfToken,
            'users' => $users,
            'photos' => $pictures != null ? $pictures : "pas d'images available :(",
            'username' => $username != null ? $username : "",
            'id' => $idUser,
            'error' => $error
        ]);
    }

    /**
     * @Route("/mygalery/{idUser}/crt", name="createPicture")
     */
    public function createPictureAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $picture = new Picture();

        if ($_POST['url'] || $_POST['image']) {
            $picture->setIdUser($_POST['id']);
            $picture->setUrl($_POST['url'] != "" ? $_POST['url'] : "image/".$_POST['image']);
            $picture->setPos($_POST['pos'] != null ? $_POST['pos'] : null);

            $em->persist($picture);
            $em->flush();
        }else{
            return $this->redirectToRoute("showGaleryPerso", array('idUser' => $_POST['id'], 'flagErrorMessage' => true));
        }

        return $this->redirectToRoute("showGaleryPerso", array('idUser' => $_POST['id']));
    } 

    /**
     * @Route("/mygalery/del/{id}/{idUser}", name="deletePicture")
     */
    public function deletePictureAction(Request $request, $id, $idUser)
    {
        $em = $this->getDoctrine()->getManager();
        $picture = $em->getRepository('AppBundle:Picture')->find($id);

        $em->remove($picture);
        $em->flush();

        return $this->redirectToRoute("showGaleryPerso", array('idUser' => $idUser));
    }

    /**
     * @Route("/mygalery/upd/{id}", name="updatePictures")
     */
    public function updatePosAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        //var_dump($_POST);die();

        foreach ($_POST as $key => $value) {
            if(explode("-", $key)[0] == "id"){
                $picture = $em->getRepository('AppBundle:Picture')->find($value);
            }
            elseif(explode("-", $key)[0] == "select"){
                $picture->setPos($value != null ? $value : null);
            }
            //var_dump($picture);die();


            $em->flush();
        }

        //

        return $this->redirectToRoute("showGaleryPerso", array('idUser' => $id));
    }  
}

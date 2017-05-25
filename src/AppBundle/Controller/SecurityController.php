<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="our_login")
     */
    public function loginAction()
    {
        $user = $this->getUser();
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
        if($error) {
            $this->get('session')->getFlashBag()->add('error', $error->getMessage());
        }

        return $this->render('login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'user'=> $user
        ));
    }

    /**
     * @Route("/register", name="register_process")
     */
    public function register(Request $request){
        $user = new User();
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->container->get('security.password_encoder');
            $hashedPassword = $encoder->encodePassword($user,$user->getPassword());
          //  $role = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['id'=> 2]);
          //  dump($role);exit;
            $userRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(['name'=>['USER']]);
            $user->addRole($userRole);
            $user->setPassword($hashedPassword);
            $this->get('session')->getFlashBag()->add('success', ' You have registered successfully!');
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('our_login');

        }


        return $this->render('register.html.twig',['form'=> $form->createView(),'user'=> $user]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){


    }
}

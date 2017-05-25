<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\DebugBundle\DebugBundle;
use AppBundle\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Book controller.
 *
 * @Route("/")
 */
class BookController extends Controller
{
    /**
     * Lists all book entities.
     *
     * @Route("/list", name="all_books")
     * @Method("GET")
     * @Security("has_role('USER')")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()
            ->getRepository('AppBundle:Book');

        $query = $repository->createQueryBuilder('b')
            ->orderBy('b.published', 'DESC')
            ->getQuery();
        $products = $query->getResult();

        $products = $query->getResult();

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate($products, $request->query->getInt('page',1),3);

        $books = $em->getRepository('AppBundle:Book')->findAll();
        return $this->render('book/index.html.twig', array(
            'books' => $books,
            'pagination'=> $pagination,
            'user' => $user
        ));
    }

    /**
     * Creates a new book entity.
     *
     * @Route("/new", name="new_book")
     * @Method({"GET", "POST"})
     * @Security("has_role('ADMIN')")
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $book = new Book();
        $form = $this->createForm('AppBundle\Form\BookType', $book);
        $form->add('format', null, ['placeholder'=> 'Choose format']);
      //  echo "<pre>";
//       dump($form);exit;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $book->getImage();
            $this->get('session')->getFlashBag()->add('success', ' Product is successfully created!');
            $fileName = md5($book->getName() . '' . $book->getPublished()->format('Y-m-d'));
            $file->move($this->get('kernel')->getRootDir() . "/../web/images/" , $fileName);
            $book->setImage($fileName);
            $format = $book->getFormat();
            $this->get('session')->getFlashBag()->add('success', ' Book is added successfully!');
            if($this->getDoctrine()->getRepository('AppBundle:Format')->findBy(['name'=>$format]) == false){
                $this->get('session')->getFlashBag()->add('error', ' Invalid format!');
                return $this->render('book/new.html.twig', array(
                    'book' => $book,
                    'form' => $form->createView(),
                ));
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('all_books', array('id' => $book->getId()));
        }

        return $this->render('book/new.html.twig', array(
            'book' => $book,
            'form' => $form->createView(),
            'user'=> $user
        ));
    }

//    /**
//     * Finds and displays a book entity.
//     *
//     * @Route("/show/{id}", name="show_book", requirements={"id": "\d+"})
//     * @Method("GET")
//     */
//    public function showAction($id)
//    {
//        $book = $this->getDoctrine()->getRepository('AppBundle:Book')->findOneBy(['id'=>$id]);
//        $deleteForm = $this->createDeleteForm($book);
//
//        return $this->render('book/show.html.twig', array(
//            'book' => $book,
//            'delete_form' => $deleteForm->createView(),
//        ));
//    }
//
//    /**
//     * Displays a form to edit an existing book entity.
//     *
//     * @Route("/{id}/edit", name="edit_book")
//     * @Method({"GET", "POST"})
//     */
//    public function editAction(Request $request, $id)
//    {
//        $book = $this->getDoctrine()->getRepository('AppBundle:Book')->find($id);
//        $deleteForm = $this->createDeleteForm($book);
//        $editForm = $this->createForm('AppBundle\Form\BookType', $book);
//        $editForm->handleRequest($request);
//
//        if ($editForm->isSubmitted() && $editForm->isValid()) {
//            $this->getDoctrine()->getManager()->flush();
//
//            return $this->redirectToRoute('edit_book', array('id' => $book->getId()));
//        }
//
//        return $this->render('book/edit.html.twig', array(
//            'book' => $book,
//            'edit_form' => $editForm->createView(),
//            'delete_form' => $deleteForm->createView(),
//        ));
//    }
//
//    /**
//     * Deletes a book entity.
//     *
//     * @Route("/{id}", name="delete_book")
//     * @Method("DELETE")
//     */
//    public function deleteAction(Request $request, $id)
//    {
//        $book = $this->getDoctrine()->getRepository('AppBundle:Book')->findOneBy($id);
//        $form = $this->createDeleteForm($book);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->remove($book);
//            $em->flush();
//        }
//
//        return $this->redirectToRoute('all_books');
//    }
//
//    /**
//     * Creates a form to delete a book entity.
//     *
//     * @param Book $book The book entity
//     *
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createDeleteForm(Book $book)
//    {
//        return $this->createFormBuilder()
//            ->setAction($this->generateUrl('delete_book', array('id' => $book->getId())))
//            ->setMethod('DELETE')
//            ->getForm()
//        ;
//    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author_index", methods={"GET"}))
     */
    public function index(): Response
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();


        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    /**
     * @Route("/author/create", name="author_create", methods={"GET"}))
     */
    public function create(): Response
    {
        return $this->render('author/create.html.twig', [
        ]);
    }

    /**
     * @Route("/author/store", name="author_store", methods={"POST"}))
     */
    public function store(Request $r): Response
    {   
        $author = new Author;
        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

    // editiname autoriu ir butinai perduodame jo {id}

   /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"}))
     */
    public function edit(int $id): Response
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id); // randame butent ta autoriu, kurio id perduodamas

        return $this->render('author/edit.html.twig', [
            'author' => $author, // perduodame
        ]);
    }

    // kai autirius jau paeditintas ji updatiname
    /**
     * @Route("/author/update/{id}", name="author_update", methods={"POST"}))
     */
    public function update(Request $r, $id): Response
    {   
        // kreipiames jau i esama, sena pagal id
        // randame
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id); // randame butent ta autoriu, kurio id perduodamas
        // iraso pakeitmus 
        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        // atiduoda nauja info
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }

    // delete'inam authoriu

        /**
     * @Route("/author/delete/{id}", name="author_delete", methods={"POST"}))
     */
    public function delete($id): Response
    {   
        // kreipiames jau i esama, sena pagal id
        // randame
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id); // randame butent ta autoriu, kurio id perduodamas

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }
}

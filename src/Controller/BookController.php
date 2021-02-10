<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;
use App\Entity\Book;

class BookController extends AbstractController
{
    /**
     * @Route("/book", name="book_index")
     */
    public function index(Request $r): Response
    {
        // $books = $this->getDoctrine()
        // ->getRepository(Book::class)
        // ->findAll();

        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findAll();

        // su filtracija
        $books = $this->getDoctrine()
        ->getRepository(Book::class);

        if (null !== $r->query->get('author_id')) {
            $books = $books->findBy(['author_id' => $r->query->get('author_id')], ['title' => 'asc']);
        }

        else {
            $books = $books->findAll();
        }

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'authors' => $authors,
            'authorId' => $r->query->get('author_id') ?? 0
    ]);
    }

    // i book perduodame visus autorius

    /**
     * @Route("/book/create", name="book_create", methods={"GET"})
     */
    public function create(): Response
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findBy([], ['surname' => 'asc']);

        return $this->render('book/create.html.twig', [
            'authors' => $authors,
        ]);
    }

    /**
     * @Route("/book/store", name="book_store", methods={"POST"})
     */
    public function store(Request $r): Response
    {   

        // paimam autoriu pagal jo ID, sukuriam
        // setiname pati autoriu, duomenu baze padi uzpilde autoriaus ID, paemusi is cia
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($r->request->get('book_author_id'));


        $book = new Book;
        $book
        ->setTitle($r->request->get('book_title'))
        ->setIsbn($r->request->get('book_isbn'))
        ->setPages($r->request->get('book_pages'))
        ->setAbout($r->request->get('book_about'))
        ->setAuthor($author);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_index');
    }

    // editiname knyga ir butinai perduodame jo {id}

   /**
     * @Route("/book/edit/{id}", name="book_edit", methods={"GET"}))
     */
    public function edit(int $id): Response
    {
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id); // randame butent ta autoriu, kurio id perduodamas
        
        $authors = $this->getDoctrine()
        ->getRepository(Author::class)
        ->findBy([], ['surname' => 'asc']);

        return $this->render('book/edit.html.twig', [
            'book' => $book, // perduodame
            'authors' => $authors,
        ]);
    }

    // kai autirius jau paeditintas ji updatiname
    /**
     * @Route("/book/update/{id}", name="book_update", methods={"POST"}))
     */
    public function update(Request $r, $id): Response
    {   
        // kreipiames jau i esama, sena pagal id
        // randame
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id); // randame butent ta autoriu, kurio id perduodamas
        // iraso pakeitmus 

        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($r->request->get('book_author_id'));


        $book
        ->setTitle($r->request->get('book_title'))
        ->setIsbn($r->request->get('book_isbn'))
        ->setPages($r->request->get('book_pages'))
        ->setAbout($r->request->get('book_about'))
        ->setAuthor($author);

        // atiduoda nauja info
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_index');
    }

    // delete'inam authoriu

        /**
     * @Route("/book/delete/{id}", name="book_delete", methods={"POST"}))
     */
    public function delete($id): Response
    {   
        // kreipiames jau i esama, sena pagal id
        // randame
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->find($id); // randame butent ta autoriu, kurio id perduodamas

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('book_index');
    }
}

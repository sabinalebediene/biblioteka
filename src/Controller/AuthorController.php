<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Author;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author_index", methods={"GET"}))
     */
    public function index(Request $r): Response
    {

        // tikrina, ar user'is prisijunges
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // $authors = $this->getDoctrine()
        //     ->getRepository(Author::class)
        //     ->findAll();

        $authors = $this->getDoctrine()
        ->getRepository(Author::class);

        if ('name_az' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $authors = $authors->findBy([],['name'=>'asc']);
        }

        elseif ('name_za' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $authors = $authors->findBy([],['name'=>'desc']);
        }

        elseif ('surname_az' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $authors = $authors->findBy([],['surname'=>'asc']);
        }

        elseif ('surname_za' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $authors = $authors->findBy([],['surname'=>'desc']);
        }
        
        else {
            $authors = $authors->findAll();
        }

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
            'sortBy' => $r->query->get('sort') ?? 'default',
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/author/create", name="author_create", methods={"GET"}))
     */
    public function create(Request $r): Response
    {

        $author_name = $r->getSession()->getFlashBag()->get('author_name', []);
        $author_surname = $r->getSession()->getFlashBag()->get('author_surname', []);

        // i twig'a atiduodame errorus, kurie paimti is 'errors'
        return $this->render('author/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            // is flashbago paimam autor name arba surname, jei perduoti(t.y. masyvai) - isirasome
            // visada bus viena, todel nurodome[0], jei jis neperduotas i autor name irasome tuscia stringa
            'author_name' => $author_name[0] ?? '',
            'author_surname' => $author_surname[0] ?? ''
        ]);
    }

    /**
     * @Route("/author/store", name="author_store", methods={"POST"}))
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {   

        // pradzioje tikriname ar viskas gerai su CSRF token'ais
        $submittedToken = $r->request->get('token');

        if ($this->isCsrfTokenValid('create_author', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Blogas token CSRF'); 
            return $this->redirectToRoute('author_create');
        }


        // susikuriam nauja autoriu
        $author = new Author;

        // irasome naujus dalykus
        $author->
        setName($r->request->get('author_name'))->
        setSurname($r->request->get('author_surname'));

        // tikriname pagal assertus 
        // validacija
        $errors = $validator->validate($author);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            // klaidos atveju ivestas vardas ir pavarde lieka
            $r->getSession()->getFlashBag()->add('author_name', $r->request->get('author_name'));
            $r->getSession()->getFlashBag()->add('author_surname', $r->request->get('author_surname'));
            
            return $this->redirectToRoute('author_create');
            // po rederektinimo pereiname prie create ir ten persiduodam autoriaus name ir surname kintamuosius
        }

        // jei viskas gerai, irasome i DB

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($author);
        $entityManager->flush();

        // pries sugrizdami i index'a issiunciame acces'o zinute

        $r->getSession()->getFlashBag()->add('success', 'Autorius sekmingai pridetas.');

        return $this->redirectToRoute('author_index');
    }

    // editiname autoriu ir butinai perduodame jo {id}

   /**
     * @Route("/author/edit/{id}", name="author_edit", methods={"GET"}))
     */
    public function edit(Request $r, int $id): Response
    {
        $author = $this->getDoctrine()
        ->getRepository(Author::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas
        
        $author_name = $r->getSession()->getFlashBag()->get('author_name', []);
        $author_surname = $r->getSession()->getFlashBag()->get('author_surname', []);

        // pries sugrizdami i index'a issiunciame acces'o zinute

        $r->getSession()->getFlashBag()->add('success', 'Autorius sekmingai pakeistas.');    

        return $this->render('author/edit.html.twig', [
            'author' => $author, // perduodame
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'author_name' => $author_name[0] ?? '',
            'author_surname' => $author_surname[0] ?? ''
        ]);
    }

    // kai autirius jau paeditintas ji updatiname - spaudziame mygtuka edit
    /**
     * @Route("/author/update/{id}", name="author_update", methods={"POST"}))
     */
    public function update(Request $r, ValidatorInterface $validator, $id): Response
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

        // tikriname pagal assertus 
        // validacija
        $errors = $validator->validate($author);

       // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }

            // klaidos atveju ivestas vardas ir pavarde lieka
            $r->getSession()->getFlashBag()->add('author_name', $r->request->get('author_name'));
            $r->getSession()->getFlashBag()->add('author_surname', $r->request->get('author_surname'));

            // kai redirectiname i edit, cia yra id, todel turime cia dar perduoti ir nurodyti id, todel klaida paprase cia idet
            return $this->redirectToRoute('author_edit',['id'=>$author->getId()]);
        }

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

        if ($author->getBooks()->count() > 0){
            return new Response('Trinti negalima, nes turi knygu');
        };

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('author_index');
    }
}

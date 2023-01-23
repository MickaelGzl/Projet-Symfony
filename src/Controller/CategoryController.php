<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $repo): Response
    {
        $cats = $repo->findAll();
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'title' => 'Liste des catégories',
            'cats' => $cats
        ]);
    }

    public function redirection():RedirectResponse
    {
        //return new RedirectResponse($this->generateUrl('app_category')
        return $this->redirectToRoute('app_category');
    }

    #[Route('/category/new', name:'app_add_category')] 
    public function add(Request $request, ManagerRegistry $doctrine): Response {
        $em = $doctrine->getManager();
        $newCat = new Category;

        /* TEST 
        $form = $this->createFormBuilder($newCat) // pour créer un formulaire de 0
            //->add('name', TextType::class)
            //->add('Envoyer', SubmitType::class)
            ->getForm();
        */

        $form = $this->createForm (CategoryType::class, $newCat)   // pour lier un formulaire déjà crée
            ->add('Enregistrer', SubmitType::class, ['label' => 'Enregistrer']);
            //->add('RGPT', CheckboxType::class, [
            //    'label' => 'merde',
            //    'mapped' => 'false'
            //]);
            

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newCat = $form->getData();
            $em->persist($newCat);          //save object
            $em->flush();                   // execute pour envoyer en bdd

            $this->addFlash(
                'success','Votre catégorie '.$newCat .' a bien été enregistrée.'
            );

            return $this->redirection();
        }

        return $this->render('category/add.html.twig', [
            'controller_name' => 'Add Category',
            'title' => 'Ajouter une catégorie',
            'form' => $form
        ]);
    }

    #[Route('/category/{id}', name:'app_show_category')] 
    public function show(CategoryRepository $repo, $id):Response
    {
        $cat = $repo->find($id);
        return $this->render('category/show.html.twig', [
            'controller_name' => 'Game',
            'title' => 'Recherche par Catégories',
            'cat' => $cat
        ]);
    }

}

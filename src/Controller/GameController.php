<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(GameRepository $repo): Response
    {
        $games = $repo->findAll();
        //dump($games);
        return $this->render('game/index.html.twig', [
            'controller_name' => 'All Games',
            'title' => 'Liste des jeux',
            'games' => $games
        ]);
    }

    public function redirection():RedirectResponse
    {
        //return new RedirectResponse($this->generateUrl('app_category')
        return $this->redirectToRoute('app_game');
    }

    #[Route('/game/new', name:'app_add_game')]
    public function add(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response {
        $em = $doctrine->getManager();
        $newGame = new Game;

        $form = $this->createForm (GameType::class, $newGame)
            ->add('Enregistrer', SubmitType::class, ['label' => 'Enregistrer']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $pictureFile */
            $pictureFile = $form->get('picture')->getData();
            if($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);                  // safely include the file name as part of the URL
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();   //slugger nettoie le nom ( espace, accent, etc...)
            
                // Move the file to the directory where brochures are stored
                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $newGame->setPicture($newFilename);
            
            
            }
            $newGame = $form->getData();
            dump($newGame);
            $em->persist($newGame);          
            $em->flush(); 

            $this->addFlash(
                'success','Votre jeu '.$newGame .' a bien été enregistrée.'
            );

            return $this->redirection();
        }
    

        return $this->render('game/add.html.twig', [
            'controller_name' => 'Add Game',
            'title' => 'Ajouter un jeu',
            'form' => $form
        ]);
    }

    #[Route('/game/{id}', name:'app_show_game')]
    public function show(GameRepository $repo, $id):Response
    {
        $game = $repo->find($id);

        return $this->render('game/show.html.twig', [
            'controller_name' => 'Game',
            'title' => $game->getTitre(),
            'game' => $game
        ]);
    }
}


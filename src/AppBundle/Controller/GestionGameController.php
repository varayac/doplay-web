<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Game;
use AppBundle\Form\GameType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/gestionGames")
 */
class GestionGameController extends Controller
{
    /**
     * @Route("/nuevoGame", name="nuevoGame")
     */
    public function nuevoGameAction(Request $request)
    {
        if (!is_null($request)){
            $datos=$request->request->all();
        }
        $game = new Game();
        //Construyendo el Formulario
        $form= $this->createForm(GameType::class, $game);
        // Recogemos la Informacion
        $form->handleRequest($request);
        //Recogemos la Informacion
        if ($form->isSubmitted() && $form->isValid()) {
            $game = $form->getData();
            $game->setImage("");
            $game->setCreatedAt(new \DateTime());

            // Almacenar nuevo Juego
            $em = $this->getDoctrine()->getManager();
            $em->persist($game);
            $em->flush();

            return $this->redirectToRoute('game', array('id'=>$game->getId()));
        }

        // Pagina Principal
        return $this->render('gestionGames/gestionGame.html.twig', array('form'=>$form->createView(), ));
    }

}
<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function homeAction(Request $request)
    {
        //Captura el repositorio de la Tabla con la DB.
        $gameRepository = $this->getDoctrine()->getRepository(Game::class);
        $games = $gameRepository = $gameRepository->findByTop(1);
        //var_dump($games);
        // Pagina Principal
        return $this->render('frontal/index.html.twig', array("gamex"=>$games));
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        // Pagina Nosotros
        return $this->render('frontal/about.html.twig');
    }

    /**
     * @Route("/rooms/{salas}", name="rooms")
     */
    public function roomsAction(Request $request, $salas="todos")
    {
        // Pagina Salas
        return $this->render('frontal/rooms.html.twig', array("salas"=>$salas));
    }

    /**
     * @Route("/game/{id}", name="game")
     */
    public function gameAction(Request $request, $id=null)
    {
        if ($id!=null){
            $gameRepository = $this->getDoctrine()->getRepository(Game::class);
            $game = $gameRepository->find($id);
            // Pagina Descripcion de Juego
            return $this->render('frontal/Game.html.twig', array("game"=>$game));
        }else{
            return $this->redirectToRoute('homepage');
        }

    }

}

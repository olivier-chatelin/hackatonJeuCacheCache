<?php

namespace App\Controller;

use App\Model\CharacterManager;
use App\Model\MapManager;
use App\Service\Gamedealer;
use App\Service\RobotMover;

class GameController extends AbstractController
{
    public function index()
    {
        $gameDealer = new Gamedealer();
        $newId = 4;
        $openmeet = 1;
        $speakingLover = [];
        $speech = "";
        $matchLoverPosition = 0;


        if ($_SESSION['unlockmove'] === 0) {
            $gameDealer->init();
            $_SESSION['unlockmove'] = 1;
            $newId = 4;
            $openmeet = 0;
            $_SESSION['alreadyvisited'] = [];
            $alreadyvisited='';
            $_SESSION['turnNb'] = 0;
            $groundControl='';
            $characterManager = new CharacterManager();

        }
        $characterManager = new CharacterManager();
        $characters = $characterManager->meet();
        $backgrounds = $characterManager->getBackground($_SESSION['currentPosition']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $robotMover = new RobotMover();
            $currentxy = $robotMover->move($_SESSION['currentPosition'], $_POST['direction']);

            $mapManager = new MapManager();
            $newId = $mapManager->getDivIdByCoordinates($currentxy['xcoord'], $currentxy['ycoord']);
            $_SESSION['currentPosition'] = $newId;

            $gameDealer = new Gamedealer();
            $groundControl = $gameDealer->countTurns();
            $_SESSION['alreadyvisited'][] = $newId ;
            $alreadyvisited = json_encode($_SESSION['alreadyvisited']);

            $speakingLover = $gameDealer->checkCurrentPosition($_SESSION['currentPosition']);
            if($speakingLover){
                $speech = $gameDealer->getSpeech();
            }

            $this->twig->addGlobal('session', $_SESSION);
        }

        $openmeet = 1;
        $matchLoverPosition = $characterManager->getLocationById($_SESSION['loverMatchId']);
        return $this->twig->render('Game/index.html.twig', [
            'characters' => $characters,
            'newId' => $newId,
            'speakingLover' => $speakingLover,
            'openmeet' => $openmeet,
            'alreadyvisited' => $alreadyvisited,
            'background' => $backgrounds,
            'speech' => $speech,
            'groundControl' => $groundControl,
            'matchLoverPosition' => $matchLoverPosition
        ]);
    }
    public function happy()
    {
        $characterManager = new CharacterManager();
        $lover = $characterManager->selectOneById($_SESSION['loverMatchId']);
        return $this->twig->render('Game/happy.html.twig',['lover'=>$lover]);
    }
}

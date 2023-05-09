<?php

namespace ShockedPlot7560\FactionMaster\Route;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\FactionMaster;
use ShockedPlot7560\FactionMaster\Task\ScoreboardAssaultTask;

class StartAssaultRoute extends RouteBase
{

    public function getSlug(): string
    {
        return RouteSlug::START_ASSAULT_ROUTE;
    }

    public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null)
    {
        // TODO: Implement __invoke() method.
    }

    public function call(): callable
    {
        return function (Player $player, $data)
        {
            if ($data === null) {
                return;
            }
            if (!$data[1] instanceof FactionEntity) return;
            if (!$data[2] instanceof FactionEntity) return;
            $attackFaction = $data[1];
            $defenceFaction = $data[2];

            $assaultPlayers = $defenceFaction->getOnlineMembers();
            $assaultPlayers[] = $attackFaction->getOnlineMembers();
            var_dump($assaultPlayers);
            FactionMaster::$score[$attackFaction->getName()] = 0;
            FactionMaster::$score[$defenceFaction->getName()] = 0;
            FactionMaster::getInstance()->getScheduler()->scheduleRepeatingTask(new ScoreboardAssaultTask($assaultPlayers, $attackFaction, $defenceFaction), 20);


        };
    }

    public function getPermissions(): array
    {
        return [];
    }

    public function getBackRoute(): ?Route
    {
        return RouterFactory::get(self::MAIN_ROUTE);
    }
}
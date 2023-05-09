<?php

namespace ShockedPlot7560\FactionMaster\Task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use PralexOrizaSkyBlock\Manager\ScoreboardManager;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\FactionMaster;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ScoreboardAssaultTask extends Task
{
    public static int $timer_preparation = 60; //180
    public static int $timer = 60; // 900

    public function __construct(public Array $players, public FactionEntity $attackFaction, public FactionEntity $defenceFaction)
    {}

    public function onRun(): void
    {
        if (!self::$timer_preparation == 0)
        {
            foreach ($this->players as $player)
            {
                $user = Server::getInstance()->getPlayerExact($player);
                ScoreboardManager::new($user, "assaultScoreboard", Utils::getText("", "SCOREBOARD_ASSAULT_TITLE_PREPARATION"));
                ScoreboardManager::setLine($user, 1, "");
                ScoreboardManager::setLine($user, 2, $this->attackFaction->getName() . " (Attaquant)");
                ScoreboardManager::setLine($user, 3, " VS");
                ScoreboardManager::setLine($user, 4, $this->defenceFaction->getName() . " (Défenseur)");
                ScoreboardManager::setLine($user, 5, "");
                ScoreboardManager::setLine($user, 6, "Points:");
                ScoreboardManager::setLine($user, 7, " - Attaquant: /");
                ScoreboardManager::setLine($user, 8, " - Défenseur: /");
                ScoreboardManager::setLine($user, 9, "");
                self::$timer_preparation--;
            }
        }
        elseif (!self::$timer == 0)
        {
            foreach ($this->players as $player)
            {
                $user = Server::getInstance()->getPlayerExact($player);
                ScoreboardManager::new($user, "assaultScoreboard", Utils::getText("", "SCOREBOARD_ASSAULT_TITLE_FIGHT"));
                ScoreboardManager::setLine($user, 1, "");
                ScoreboardManager::setLine($user, 2, $this->attackFaction->getName() . " (Attaquant)");
                ScoreboardManager::setLine($user, 3, " VS");
                ScoreboardManager::setLine($user, 4, $this->defenceFaction->getName() . " (Défenseur)");
                ScoreboardManager::setLine($user, 5, "");
                ScoreboardManager::setLine($user, 6, "Points:");
                ScoreboardManager::setLine($user, 7, " - Attaquant: " . FactionMaster::$score[$this->attackFaction->getName()]);
                ScoreboardManager::setLine($user, 8, " - Défenseur: " . FactionMaster::$score[$this->attackFaction->getName()]);
                ScoreboardManager::setLine($user, 9, "");
                self::$timer--;
            }
        }
        else
        {
            if ((FactionMaster::$score[$this->attackFaction->getName()] - FactionMaster::$score[$this->defenceFaction->getName()]) < 0)
            {
                $winner = $this->defenceFaction->getName();
                foreach (FactionMaster::getInstance()->getServer()->getOnlinePlayers() as $player)
                {
                    $player->sendMessage(Utils::getText("", "WINNER_ASSAULT", ["pays" => $winner]));
                }
            }
            elseif ((FactionMaster::$score[$this->attackFaction->getName()] - FactionMaster::$score[$this->defenceFaction->getName()]) == 0)
            {
                foreach (FactionMaster::getInstance()->getServer()->getOnlinePlayers() as $player)
                {
                    $player->sendMessage(Utils::getText("", "DRAW_ASSAULT"));
                }
            }
            else
            {
                $winner = $this->attackFaction->getName();
                foreach (FactionMaster::getInstance()->getServer()->getOnlinePlayers() as $player)
                {
                    $player->sendMessage(Utils::getText("", "WINNER_ASSAULT", ["pays" => $winner]));
                }
            }
            unset(FactionMaster::$score[$this->attackFaction->getName()]);
            unset(FactionMaster::$score[$this->defenceFaction->getName()]);

            FactionMaster::getInstance()->getScheduler()->cancelAllTasks();
            self::setHandler(null);
        }
    }
}
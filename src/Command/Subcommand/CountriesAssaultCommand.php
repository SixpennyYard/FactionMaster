<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\RawStringArgument;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\exception\ArgumentOrderException;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class CountriesAssaultCommand extends FactionSubCommand
{
    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("opponent", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;

        if (!isset($args["opponent"]))
        {
            $this->sendUsage();
            return;
        }

        $userEntity = MainAPI::getUser($sender->getName());
        if ($userEntity->getFactionName() === null)
        {
            $sender->sendMessage(Utils::getText("", "ASSAUT_DENIED_WILDERNESS"));
            return;
        }
        if (!$userEntity->getRank() == Ids::OWNER_ID or !$userEntity->getRank() == Ids::COOWNER_ID)
        {
            $sender->sendMessage(Utils::getText("", "ASSAUT_DENIED_PERMISSION"));
            return;
        }
        if (strtolower($args["opponent"]) == strtolower($userEntity->getFactionName()))
        {
            $sender->sendMessage(Utils::getText("", "ASSAUT_DENIED_ATTACK_HIMSELF"));
            return;
        }
        if (!MainAPI::getFaction($args["opponent"]) instanceof FactionEntity)
        {
            $sender->sendMessage(Utils::getText("", "ASSAUT_DENIED_OPPONENT_WRONG"));
            return;
        }
        $opponent = MainAPI::getFaction($args["opponent"]);

        /*
        $AttackerCount = 0;
        foreach ($userEntity->getFactionEntity()->getOnlineMembers() as $onlineMember)
        {
            $AttackerCount += 1;
        }

        if (!$AttackerCount >= 2)
        {
            $sender->sendMessage(Utils::getText("", "ASSAUT_DENIED_NOT_TWO_ATTACKER"));
            return;
        }

        */
        $DefenderCount = 0;
        foreach ($opponent->getOnlineMembers() as $onlineMember)
        {
            $DefenderCount += 1;
        }
        if (!$DefenderCount >= 1)
        {
            $sender->sendMessage(Utils::getText("", "ASSAUT_DENIED_NOT_TWO_DEFENDER"));
            return;
        }
        foreach ($userEntity->getFactionEntity()->getOnlineMembers() as $member)
        {
            $player = Server::getInstance()->getPlayerExact($member);
            MainAPI::setInAssault($player);
            $player->sendTitle(Utils::getText("", "ASSAULT_START_TITLE_ATTACK", [
                "factionAttack" => $userEntity->getFactionName(),
                "factionDefence" => $args["opponent"]
            ]));
            $player->sendSubTitle(Utils::getText("", "ASSAULT_START_SUBTITLE_ATTACK", [
                "factionAttack" => $userEntity->getFactionName(),
                "factionDefence" => $args["opponent"]
            ]));
        }
        foreach (MainAPI::getFaction($args["opponent"])->getOnlineMembers() as $member)
        {

            $player = Server::getInstance()->getPlayerExact($member);
            MainAPI::setInAssault($player, "defense");
            $player->sendTitle(Utils::getText("", "ASSAULT_START_TITLE_DEFENCE", [
                "factionAttack" => $userEntity->getFactionName(),
                "factionDefence" => $args["opponent"]
            ]));
            $player->sendSubTitle(Utils::getText("", "ASSAULT_START_SUBTITLE_DEFENCE", [
                "factionAttack" => $userEntity->getFactionName(),
                "factionDefence" => $args["opponent"]
            ]));
        }

        $route = RouterFactory::get(RouteSlug::START_ASSAULT_ROUTE);
        $callable = $route->call();
        $callable($sender, [null, $userEntity->getFactionEntity(), MainAPI::getFaction($args["opponent"])]);
    }

    public function getId(): string
    {
        return "COMMAND_ASSAULT";
    }
}
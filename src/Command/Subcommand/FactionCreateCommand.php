<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use PDO;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\RawStringArgument;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class FactionCreateCommand extends FactionSubCommand {


    public function getId(): string {
		return "COMMAND_CREATE_DESCRIPTION";
	}

	protected function prepare(): void {
		$this->registerArgument(0, new RawStringArgument("name", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$userEntity = MainAPI::getUser($sender->getName());
		if ($userEntity->getFactionName() === null) {
			if (!isset($args['name'])) {
				$this->countriesFormCreation($sender);
			} else {
                $result = Utils::getCountryConfig("countries")->get("countries");
                foreach ($result as $value)
                {
                    if (strtolower($value) == strtolower($args['name']))
                    {
                        $route = RouterFactory::get(RouteSlug::CREATE_FACTION_ROUTE);
                        $callable = $route->call();
                        $callable($sender, [null, $args['name']]);
                        return;
                    }
                }
                $sender->sendMessage(Utils::getText($sender->getName(), "ALREADY_CREATED"));
			}
		} else {
			$sender->sendMessage(Utils::getText($sender->getName(), "ALREADY_IN_FACTION"));
		}
	}

    private function countriesFormCreation(Player $player)
    {
        $countries = [];
        foreach (Utils::getCountryConfig("countries")->get("countries") as $country)
        {
            $countries[] = $country;
        }
        $form = new CustomForm(function(Player $player, $data){
            if ($data === null) return true;
            $countries = Utils::getCountryConfig("countries")->getAll();
            $factionName = $countries["countries"][$data[0]];
            $route = RouterFactory::get(RouteSlug::CREATE_FACTION_ROUTE);
            $callable = $route->call();
            $callable($player, [null, $factionName]);
            return true;
        });

        $form->setTitle(Utils::getText("", "CREATE_COUNTRY_TITLE"));
        $form->addDropdown(Utils::getText("", "COUNTRIES_CREATION_DROPDOWN"), $countries);
        $player->sendForm($form);
    }
}
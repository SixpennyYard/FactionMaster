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

namespace ShockedPlot7560\FactionMaster\Route;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionLevelUpEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\SimpleForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function floor;
use function is_array;
use function is_string;

class ManageLevelRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "manageLevelRoute";

	/** @var array */
	private $buttons = [];
	/** @var bool */
	private $levelUpReady;

	public function getSlug(): string {
		return self::MANAGE_LEVEL_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_LEVEL_UP
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::FACTION_OPTION_ROUTE);
	}
	protected function isLevelUpReady(): bool {
		return $this->levelUpReady;
	}
	protected function getButtons(): array {
		return $this->buttons;
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);

		$this->buttons = [];

		$xpLevel = Utils::getXpLevel($this->getFaction()->getLevel());
		$pourcent = floor((15 * $this->getFaction()->getXP()) / $xpLevel);
		$advanceChar = "§l";
		for ($i = 0; $i < $pourcent; $i++) {
			$advanceChar .= "§a=";
		}
		for ($i = 0; $i < 15 - $pourcent; $i++) {
			$advanceChar .= "§0=";
		}
		$this->levelUpReady = false;
		if ($pourcent >= 30) {
			$this->levelUpReady = true;
		}

		$message = "";
		if (isset($params[0]) && is_string($params[0])) {
			$message = $params[0];
		}


        $content = "";
        if ($message !== "") {
            $content .= $message . "\n";
        }

        $content .= Utils::getText($this->getPlayer()->getName(), "LEVEL_UP_CONTENT_MAIN_MAX");
        $this->buttons[] = Utils::getText($this->getPlayer()->getName(), "BUTTON_LEVEL_UP_MAX");

		$this->buttons[] = Utils::getText($this->getPlayer()->getName(), "BUTTON_BACK");

		$player->sendForm($this->getForm($content));
	}

	public function call(): callable {
		$backMenu = $this->getBackRoute();
		$levelReady = $this->isLevelUpReady();
		$faction = $this->getFaction();
		return function (Player $player, $data) use ($backMenu, $levelReady, $faction) {
			if ($data === null) {
				return;
			}

			switch ($data) {
			case 0:
				$content = "";
				if ($levelReady === true) {
					Utils::processMenu(RouterFactory::get(self::CONFIRMATION_ROUTE), $player, [
						$this->callLevelUp($faction->getName()),
						Utils::getText($player->getName(), "CONFIRMATION_TITLE_LEVEL_UP"),
						Utils::getText($player->getName(), "CONFIRMATION_CONTENT_LEVEL_UP", ['cost' => $content]),
					]);
				} else {
					Utils::processMenu($this, $player);
				}
				break;
			case 1:
				Utils::processMenu($backMenu, $player);
				break;
			}
		};
	}

	protected function getForm(string $content = ""): SimpleForm {
		$menu = new SimpleForm($this->call());
		$menu = Utils::generateButton($menu, $this->getButtons());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "LEVEL_UP_TITLE_MAIN"));
		$menu->setContent($content);
		return $menu;
	}

	private function callLevelUp(string $factionName): callable {
		return function (Player $player, $data) use ($factionName) {
			if ($data === null) {
				return;
			}

			if ($data) {
                $faction = $this->getFaction();
                MainAPI::changeLevel($faction->getName(), 1);
                Utils::newMenuSendTask(new MenuSendTask(
                    function () use ($faction) {
                        return MainAPI::getFaction($faction->getName())->getLevel() == $faction->getLevel() + 1;
                    },
                    function () use ($player, $faction) {
                        (new FactionLevelUpEvent($player, $faction))->call();
                        Utils::processMenu($this, $player, [Utils::getText($player->getName(), "SUCCESS_LEVEL_UP")]);
                    },
                    function () use ($player) {
                        Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
                    }
                ));

			} else {
				Utils::processMenu($this, $player);
			}
		};
	}
}
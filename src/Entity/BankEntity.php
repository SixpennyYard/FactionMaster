<?php

namespace ShockedPlot7560\FactionMaster\Entity;

use pocketmine\entity\EntitySizeInfo;

class BankEntity extends \pocketmine\entity\Human
{
    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.5, 1, 1.3);
    }

    public static function getNetworkTypeId(): string
    {
        return "factionmaster:bank";
    }
}
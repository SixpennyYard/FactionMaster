<?php

namespace ShockedPlot7560\FactionMaster\Manager;

use customiesdevs\customies\item\CustomiesItemFactory;
use ShockedPlot7560\FactionMaster\Item\BankMachine;
use ShockedPlot7560\FactionMaster\Item\GovernmentMachine;

class ItemManager
{
    public static function init(): void
    {
        CustomiesItemFactory::getInstance()->registerItem(BankMachine::class, "customies:bank", "Banque");
        CustomiesItemFactory::getInstance()->registerItem(GovernmentMachine::class, "customies:government", "Gouvernement");
    }
}
<?php

namespace ShockedPlot7560\FactionMaster\Item;

use customiesdevs\customies\item\component\ItemComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\tag\CompoundTag;

class GovernmentMachine extends Item implements ItemComponents
{
    use ItemComponentsTrait;
    public function __construct()
    {
        parent::__construct(new ItemIdentifier(1001, 0), "Gouvernement");
        $creativeInfo = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ITEMS, CreativeInventoryInfo::NONE);
        $this->initComponent("Banque", $creativeInfo);
    }
    public function getMaxStackSize(): int
    {
        return 1;
    }
}
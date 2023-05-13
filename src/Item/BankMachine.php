<?php

namespace ShockedPlot7560\FactionMaster\Item;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use JsonException;
use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\Entity\BankEntity;
use ShockedPlot7560\FactionMaster\FactionMaster;

class BankMachine extends Item implements ItemComponents
{
    use ItemComponentsTrait;
    public function __construct()
    {
        parent::__construct(new ItemIdentifier(1000, 0), "Banque");
        $creativeInfo = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ITEMS, CreativeInventoryInfo::NONE);
        $this->initComponent("Banque", $creativeInfo);
    }

    /**
     * @throws JsonException
     */
    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult
    {
        $img = @imagecreatefrompng(FactionMaster::getInstance()->getDataFolder() . "entities/bank.png");
        $size = getimagesize(FactionMaster::getInstance()->getDataFolder() . "entities/bank.png");
        $skinBytes = "";
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $coloration = @imagecolorat($img, $x, $y);
                $a = ((~($coloration >> 24)) << 1) & 0xff;
                $r = ($coloration >> 16) & 0xff;
                $g = ($coloration >> 8) & 0xff;
                $b = $coloration & 0xff;
                $skinBytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        $modelPath = FactionMaster::getInstance()->getDataFolder() . "entities/bank.json";
        $skin = new Skin("Bank", $skinBytes, "entities/bank.json", file_get_contents($modelPath));
    $entity = new BankEntity(new Location($blockClicked->getPosition()->getX(), $blockClicked->getPosition()->getY(),
        $blockClicked->getPosition()->getZ(), $player->getWorld(), -$player->getLocation()->getYaw(), 0), $skin);
        $entity->spawnToAll();
        return ItemUseResult::SUCCESS();
    }
    public function getMaxStackSize(): int
    {
        return 16;
    }
}
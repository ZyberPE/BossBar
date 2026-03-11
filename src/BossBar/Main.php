<?php

namespace BossBar;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use pocketmine\Server;

use pocketmine\entity\Living;

class Main extends PluginBase{

    private array $dragons = [];
    private int $messageIndex = 0;

    protected function onEnable() : void{
        $this->saveDefaultConfig();

        $interval = $this->getConfig()->get("update-time", 5) * 20;

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
            $this->updateBossBar();
        }), $interval);
    }

    private function updateBossBar() : void{

        $messages = $this->getConfig()->get("messages");

        if(!isset($messages[$this->messageIndex])){
            $this->messageIndex = 0;
        }

        $top = $messages[$this->messageIndex]["top"] ?? "";
        $bottom = $messages[$this->messageIndex]["bottom"] ?? "";

        $text = $top . "\n" . $bottom;

        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $this->spawnDragon($player, $text);
        }

        $this->messageIndex++;
    }

    private function spawnDragon(Player $player, string $text) : void{

        if(isset($this->dragons[$player->getName()])){
            $dragon = $this->dragons[$player->getName()];
            if($dragon instanceof Entity && !$dragon->isClosed()){
                $dragon->setNameTag($text);
                return;
            }
        }

        $pos = $player->getPosition()->add(0, 100, 0);

        $nbt = EntityDataHelper::createBaseNBT($pos);

        $dragon = EntityFactory::getInstance()->create("minecraft:ender_dragon", $pos->getWorld(), $nbt);

        if($dragon !== null){
            $dragon->setNameTag($text);
            $dragon->setNameTagAlwaysVisible();
            $dragon->spawnTo($player);

            $this->dragons[$player->getName()] = $dragon;
        }
    }
}

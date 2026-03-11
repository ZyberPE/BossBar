<?php

namespace BossBar;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\Server;

class Main extends PluginBase{

    private int $bossId = 12345;
    private int $messageIndex = 0;

    protected function onEnable() : void{
        $this->saveDefaultConfig();

        $interval = $this->getConfig()->get("update-time", 5) * 20;

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void{
            $this->updateBossBar();
        }), $interval);
    }

    private function updateBossBar() : void{

        $messages = $this->getConfig()->get("messages");

        if(!isset($messages[$this->messageIndex])){
            $this->messageIndex = 0;
        }

        $text = $messages[$this->messageIndex];

        foreach(Server::getInstance()->getOnlinePlayers() as $player){

            $pk = BossEventPacket::show(
                $this->bossId,
                $text,
                1.0,
                BossEventPacket::COLOR_PURPLE,
                BossEventPacket::OVERLAY_PROGRESS
            );

            $player->getNetworkSession()->sendDataPacket($pk);
        }

        $this->messageIndex++;
    }
}

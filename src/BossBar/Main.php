<?php

namespace BossBar;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;

class Main extends PluginBase{

    private int $entityId = 123456;
    private int $messageIndex = 0;

    protected function onEnable(): void{
        $this->saveDefaultConfig();

        $interval = $this->getConfig()->get("update-time", 5) * 20;

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void{
            $this->updateBossBar();
        }), $interval);
    }

    private function updateBossBar(): void{

        $messages = $this->getConfig()->get("messages");

        if(empty($messages)){
            return;
        }

        if(!isset($messages[$this->messageIndex])){
            $this->messageIndex = 0;
        }

        $top = $messages[$this->messageIndex]["top"] ?? "";
        $bottom = $messages[$this->messageIndex]["bottom"] ?? "";

        $text = $top . "\n" . $bottom;

        foreach($this->getServer()->getOnlinePlayers() as $player){
            $this->sendBossBar($player, $text);
        }

        $this->messageIndex++;
    }

    private function sendBossBar(Player $player, string $text): void{

        $metadata = new EntityMetadataCollection();

        $metadata->setString(EntityMetadataProperties::NAMETAG, $text);
        $metadata->setLong(EntityMetadataProperties::FLAGS, 1 << EntityMetadataFlags::SILENT);
        $metadata->setFloat(EntityMetadataProperties::HEALTH, 100);
        $metadata->setFloat(EntityMetadataProperties::MAX_HEALTH, 100);

        $pk = new AddActorPacket();
        $pk->actorUniqueId = $this->entityId;
        $pk->actorRuntimeId = $this->entityId;
        $pk->type = EntityIds::ENDER_DRAGON;
        $pk->position = $player->getPosition();
        $pk->motion = null;
        $pk->yaw = 0;
        $pk->pitch = 0;
        $pk->headYaw = 0;
        $pk->metadata = $metadata->getAll();

        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function removeBossBar(Player $player): void{

        $pk = new RemoveActorPacket();
        $pk->actorUniqueId = $this->entityId;

        $player->getNetworkSession()->sendDataPacket($pk);
    }
}

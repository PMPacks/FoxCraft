<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace room17\SkyBlock;


use room17\SkyBlock\island\Island;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use room17\SkyBlock\utils\Utils;

class SkyBlockSettings {

    public const CURRENT_VERSION = "2";

    /** @var SkyBlock */
    private $plugin;

    /** @var Config */
    private $config;

    /** @var Item[] */
    private $defaultChestContent;

    /** @var array[] */
    private $generatorChestContent;

    public function __construct(SkyBlock $plugin) {
        $this->plugin = $plugin;
        $this->initialize();
        $this->checkVersion();
    }

    public function getVersion(): int {
        return (int) $this->config->get("Version");
    }

    public function getChestContentByGenerator(string $generator): array {
        $chestContent = $this->generatorChestContent[$generator] ?? [];
        if(empty($chestContent)) {
            return $this->defaultChestContent;
        }
        return $chestContent;
    }

    public function getCreationCooldownDuration(): int {
        return (int) $this->config->get("CreationCooldownDuration");
    }

    public function isVoidDamageEnabled(): bool {
        return (bool) $this->config->get("CancelVoidDamage");
    }

    public function getBlockedCommands(): array {
        return (array) $this->config->get("BlockedCommands");
    }

    public function getChatFormat(): string {
        return (string) $this->config->get("ChatFormat");
    }

    public function getSlotsByCategory(string $category): int {
        return (int) $this->config->get("SlotsByCategory")[$category];
    }

    public function getCategoryByBlocks(int $blocks): string {
        $categoryByBlocks = array_map("intval", $this->config->get("CategoryByBlocks"));

        if($blocks >= $categoryByBlocks["L"]) {
            $category = Island::CATEGORY_EXTRA_LARGE;
        } elseif($blocks >= $categoryByBlocks["M"]) {
            $category = Island::CATEGORY_LARGE;
        } elseif($blocks >= $categoryByBlocks["S"]) {
            $category = Island::CATEGORY_MEDIUM;
        } elseif($blocks >= $categoryByBlocks["XS"]) {
            $category = Island::CATEGORY_SMALL;
        } else {
            $category = Island::CATEGORY_EXTRA_SMALL;
        }

        return $category;
    }

    private function initialize(): void {
        $this->config = new Config($this->plugin->getDataFolder() . "settings.yml");
        $settingsData = $this->config->getAll();

        $this->defaultChestContent = Utils::parseItems($settingsData["ChestContent"]);

        $this->generatorChestContent = [];
        foreach($settingsData["CustomChestContent"] as $generator => $items) {
            if(is_array($items)) {
                $this->generatorChestContent[$generator] = Utils::parseItems($items);
            }
        }
    }

    private function checkVersion(): void {
        if($this->getVersion() == self::CURRENT_VERSION) {
            return;
        }

        $this->config->set("Version", self::CURRENT_VERSION);
        $this->config->set("CategoryByBlocks", [
            "XS" => 500,
            "S" => 1000,
            "M" => 5000,
            "L" => 10000
        ]);

        $this->config->save();
        $this->plugin->getLogger()->warning("The settings file does not match with the current version of SkyBlock, the file has been updated");
    }

}
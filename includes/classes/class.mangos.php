<?php

/**
 * @package World of Warcraft Armory
 * @version Release 4.50
 * @revision 450
 * @copyright (c) 2009-2011 Shadez
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 **/

if(!defined('__ARMORY__')) {
    die('Direct access to this file not allowed!');
}

Class Mangos {

    /**
     * Returns skill ID that required for item $id
     * @category Mangos class
     * @access   public
     * @param    int $id
     * @return   int
     **/
    public static function GetSkillIDFromItemID($id) {
        if($id == 0) {
            return SKILL_UNARMED;
        }
        $item = Armory::$wDB->selectRow("SELECT `class`, `subclass` FROM `item_template` WHERE `entry`=%d LIMIT 1", $id);
        if(!$item) {
            return SKILL_UNARMED;
        }
        if($item['class'] != 2) {
            return SKILL_UNARMED;
        }
        switch($item['subclass']) {
            case 0:
                return SKILL_AXES;
                break;
            case 1:
                return SKILL_TWO_HANDED_AXE;
                break;
            case 2:
                return SKILL_BOWS;
                break;
            case 3:
                return SKILL_GUNS;
                break;
            case 4:
                return SKILL_MACES;
                break;
            case 5:
                return SKILL_TWO_HANDED_MACES;
                break;
            case 6:
                return SKILL_POLEARMS;
                break;
            case 7:
                return SKILL_SWORDS;
                break;
            case 8:
                return SKILL_TWO_HANDED_SWORDS;
                break;
            case 10:
                return SKILL_STAVES;
                break;
            case 13:
                return SKILL_FIST_WEAPONS;
                break;
            case 15:
                return SKILL_DAGGERS;
                break;
            case 16:
                return SKILL_THROWN;
                break;
            case 18:
                return SKILL_CROSSBOWS;
                break;
            case 19:
                return SKILL_WANDS;
                break;
        }
        return SKILL_UNARMED;
    }

    /**
     * Returns quest info by $infoType case
     * @category Mangos class
     * @access   public
     * @param    int $quest
     * @param    string $infoType
     * @return   mixed
     **/
    public static function GetQuestInfo($quest, $infoType) {
        switch($infoType) {
            case 'LogTitle':
                if(Armory::GetLocale() == 'en_gb' || Armory::GetLocale() == 'en_us') {
                    $info = Armory::$wDB->selectCell("SELECT `LogTitle` FROM `quest_template` WHERE `ID`=%d", $quest);
                }
                else {
                    $info = Armory::$wDB->selectCell("SELECT `Title` FROM `quest_template_locale` WHERE `ID`=%d AND `locale`=%d", $quest, str_replace("_","",Armory::GetLocale()));
                    if(!$info) {
                        $info = Armory::$wDB->selectCell("SELECT `LogTitle` FROM `quest_template` WHERE `ID`=%d", $quest);
                    }
                }
                break;
            case 'reqlevel':
				$info = Armory::$wDB->selectCell("SELECT `MinLevel` FROM `quest_template` WHERE `ID`=%d", $quest);
				break;
			case 'map':
				$quester = Armory::$wDB->selectCell("SELECT `id` FROM `creature_questender` WHERE `quest`=%d", $quest);
				$mapID = Armory::$wDB->selectCell("SELECT `map` FROM `creature` WHERE `id`=%d", $quester);
				$info = Armory::$aDB->selectCell("SELECT `name_%s` FROM `ARMORYDBPREFIX_maps` WHERE `id`=%d", Armory::GetLocale(), $mapID);
                break;
			}
        if($info) {
            return $info;
        }
        return false;
    }

    /**
     * Assign text value to int drop percent (drop > 51 = High, etc.)
     * @category Mangos class
     * @access   public
     * @param    float $percent
     * @return   string
     **/
    public static function GetDropRate($percent) {
        if($percent == 100) {
            return 6;
        }
        elseif($percent > 51) {
            return 5;
        }
        elseif($percent > 25) {
            return 4;
        }
        elseif($percent > 15) {
            return 3;
        }
        elseif($percent > 3) {
            return 2;
        }
        elseif($percent > 0 && $percent < 1) {
            return 1;
        }
        elseif($percent < 0 || $percent == 0) {
            return 0;
        }
    }

    /**
     * Returns game object info ($infoType)
     * @category Mangos class
     * @access   public
     * @param    int $entry
     * @param    string $infoType
     * @return   mixed
     **/
    public static function GetGameObjectInfo($entry, $infoType) {
        $info = false;
        switch($infoType) {
            case 'name':
                if(Armory::GetLocale() == 'en_gb' || Armory::GetLocale() == 'en_us') {
                    $info = Armory::$wDB->selectCell("SELECT `name` FROM `gameobject_template` WHERE `entry`=%d", $entry);
                }
                else {
                    $info = Armory::$wDB->selectCell("SELECT `name` FROM `gameobject_template_locale` WHERE `entry`=%d", Armory::GetLoc(), $entry);
                    if(!$info) {
                        $info = Armory::$wDB->selectCell("SELECT `name` FROM `gameobject_template` WHERE `entry`=%d", $entry);
                    }
                }
				break;
            case 'map':
				$mapID = Armory::$wDB->selectCell("SELECT `map` FROM `gameobject` WHERE `id`=%d", $entry);
				$info = Armory::$aDB->selectCell("SELECT `name_%s` FROM `ARMORYDBPREFIX_maps` WHERE `id`=%d", Armory::GetLocale(), $mapID);
				break;
            case 'areaUrl':
                $mapID = Armory::$wDB->selectCell("SELECT `map` FROM `gameobject` WHERE `id`=%d LIMIT 1", $entry);
                if(!$mapID) {
                    return false;
                }
                if($info = Armory::$aDB->selectCell("SELECT `key` FROM `ARMORYDBPREFIX_instance_template` WHERE `map`=%d", $mapID)) {
                    $areaUrl = sprintf('source=dungeon&dungeon=%s&boss=all&difficulty=all', $info);
                    return $areaUrl;
                }
                break;
            case 'isInInstance':
                return Armory::$aDB->selectCell("SELECT 1 FROM `ARMORYDBPREFIX_instance_data` WHERE `type`='object' AND (`id`=%d OR `name_id`=%d OR `lootid_1`=%d OR `lootid_2`=%d OR `lootid_3`=%d OR `lootid_4`=%d)", $entry, $entry, $entry, $entry, $entry, $entry);
                break;
		}
        return $info;
    }

    /**
     * Returns NPC name (according with current locale)
     * @category Mangos class
     * @access   public
     * @param    int $npc
     * @param    bool $isBoss = false
     * @return   string
     **/
    public static function GetNPCName($npc, $isBoss = false) {
        $creature_id = $npc;
        // If npc is boss we need to find him/her original ID (to prevent names like "Bronjahm (1)", etc.)
        if($isBoss) {
            $KillCreditInfo = Armory::$wDB->selectRow("SELECT `KillCredit1`, `KillCredit2` FROM `creature_template` WHERE `entry` = %d LIMIT 1", $npc);
            if(!$KillCreditInfo) {
                // return name for current ID
                return Armory::$wDB->selectCell("SELECT `name` FROM `creature_template` WHERE `entry`=%d LIMIT 1", $npc);
            }
            if($KillCreditInfo['KillCredit1'] > 0) {
                $creature_id = $KillCreditInfo['KillCredit1'];
            }
            elseif($KillCreditInfo['KillCredit2'] > 0) {
                $creature_id = $KillCreditInfo['KillCredit'];
            }
        }
        if(Armory::GetLoc() == 0) {
            $name = Armory::$wDB->selectCell("SELECT `name` FROM `creature_template` WHERE `entry`=%d LIMIT 1", $creature_id);
        }
        else {
            $name = Armory::$wDB->selectCell("SELECT `name` FROM `creature_template_locale` WHERE `entry` = %d LIMIT 1", Armory::GetLoc(), $creature_id);
            if(!$name) {
                $name = Armory::$wDB->selectCell("SELECT `name` FROM `creature_template_locale` WHERE `entry` = %d LIMIT 1", Armory::GetLoc(), $npc);
                if(!$name) {
                    $name = Armory::$wDB->selectCell("SELECT `name` FROM `creature_template` WHERE `entry`=%d LIMIT 1", $npc);
                }
            }
        }
        if($name && $isBoss) {
            return array('id' => $creature_id, 'name' => $name);
        }
        elseif($name && !$isBoss) {
            return $name;
        }
        Armory::Log()->writeError('%s : unable to find NPC name (id: %d, KillCredit1: %d, KillCredit2: %d)', __METHOD__, $npc, (isset($KillCredit['KillCredit1'])) ? $KillCredit['KillCredit1'] : 0, (isset($KillCredit['KillCredit2'])) ? $KillCredit['KillCredit2'] : 0);
        return false;
	}

    /**
     * Returns NPC info (infoType)
     * @category Mangos class
     * @access   public
     * @param    int $npc
     * @param    string $infoType
     * @return   mixed
     **/
    public static function GetNpcInfo($npc, $infoType) {
        $info = null;
        switch($infoType) {
            case 'maxlevel':
				$info = Armory::$wDB->selectCell("SELECT `maxlevel` FROM `creature_template` WHERE `entry`=%d", $npc);
				break;
            case 'minlevel':
				$info = Armory::$wDB->selectCell("SELECT `minlevel` FROM `creature_template` WHERE `entry`=%d", $npc);
				break;
			case 'map':
				$mapID = Armory::$wDB->selectCell("SELECT `map` FROM `creature` WHERE `id`=%d LIMIT 1", $npc);
                if(!$mapID) {
                    $killCredit = Armory::$wDB->selectRow("SELECT `KillCredit1`, `KillCredit2` FROM `creature_template` WHERE `entry`=%d", $npc);
                    if($killCredit['KillCredit1'] > 0) {
                        $kc_entry = $killCredit['KillCredit1'];
                    }
                    elseif($killCredit['KillCredit2'] > 0) {
                        $kc_entry = $killCredit['KillCredit2'];
                    }
                    else {
                        $kc_entry = false;
                    }
                    $mapID = Armory::$wDB->selectCell("SELECT `map` FROM `creature` WHERE `id`=%d LIMIT 1", $kc_entry);
                    if(!$mapID) {
                        return false;
                    }
                }
                if($info = Armory::$aDB->selectCell("SELECT `name_%s` FROM `ARMORYDBPREFIX_instance_template` WHERE `map`=%d", Armory::GetLocale(), $mapID)) {
                    return $info;
                }
				else {
				    $info = Armory::$aDB->selectCell("SELECT `name_%s` FROM `ARMORYDBPREFIX_maps` WHERE `id`=%d", Armory::GetLocale(), $mapID);
				}
				break;
            case 'areaUrl':
                $mapID = Armory::$wDB->selectCell("SELECT `map` FROM `creature` WHERE `id`=%d LIMIT 1", $npc);
                if(!$mapID) {
                    $killCredit = Armory::$wDB->selectRow("SELECT `KillCredit1`, `KillCredit2` FROM `creature_template` WHERE `entry`=%d", $npc);
                    if($killCredit['KillCredit1'] > 0) {
                        $kc_entry = $killCredit['KillCredit1'];
                    }
                    elseif($killCredit['KillCredit2'] > 0) {
                        $kc_entry = $killCredit['KillCredit2'];
                    }
                    else {
                        $kc_entry = false;
                    }
                    $mapID = Armory::$wDB->selectCell("SELECT `map` FROM `creature` WHERE `id`=%d LIMIT 1", $kc_entry);
                    if(!$mapID) {
                        return false;
                    }
                }
                if($info = Armory::$aDB->selectCell("SELECT `key` FROM `ARMORYDBPREFIX_instance_template` WHERE `map`=%d", $mapID)) {
                    $areaUrl = sprintf('source=dungeon&dungeon=%s&boss=all&difficulty=all', $info);
                    return $areaUrl;
                }
                break;
            case 'mapID':
                $info = Armory::$wDB->selectCell("SELECT `map` FROM `creature` WHERE `id`=%d LIMIT 1", $npc);
                break;
            case 'rank':
                return Armory::$wDB->selectCell("SELECT `rank` FROM `creature_template` WHERE `entry`=%d", $npc);
                break;
            case 'subname':
                if(Armory::GetLocale() == 'en_gb' || Armory::GetLocale() == 'en_us') {
                    return Armory::$wDB->selectCell("SELECT `subname` FROM `creature_template` WHERE `entry`=%d LIMIT 1", $npc);
                }
                else {
                    $info = Armory::$wDB->selectCell("SELECT `title` FROM `creature_template_locale` WHERE `entry`=%d LIMIT 1", Armory::GetLoc(), $npc);
                    if(!$info) {
                        $killCredit = Armory::$wDB->selectRow("SELECT `KillCredit1`, `KillCredit2` FROM `creature_template` WHERE `entry`=%d", $npc);
                        $kc_entry = false;
                        if($killCredit['KillCredit1'] > 0) {
                            $kc_entry = $killCredit['KillCredit1'];
                        }
                        elseif($killCredit['KillCredit2'] > 0) {
                            $kc_entry = $killCredit['KillCredit2'];
                        }
                        if($kc_entry) {
                            $info = Armory::$wDB->selectCell("SELECT `title` FROM `creature_template_locale` WHERE `entry`=%d LIMIT 1", Armory::GetLoc(), $kc_entry);
                        }
                        if(!$info) {
                            $info = Armory::$wDB->selectCell("SELECT `title` FROM `creature_template_locale` WHERE `entry`=%d LIMIT 1", Armory::GetLoc(), $npc);
                        }
                    }
                }
                break;
			case 'dungeonlevel':
                $query = Armory::$wDB->selectRow("
				SELECT `difficulty_entry_1`, `difficulty_entry_2`, `difficulty_entry_3`
					FROM `creature_template`
						WHERE `entry`=%d AND `difficulty_entry_1` > 0 or `difficulty_entry_2` > 0 or `difficulty_entry_3` > 0", $npc);
                if(!$query) {
                    // 10 Normal or 5 Normal
                    return 0;
                }
				if($query['difficulty_entry_1'] > 0) {
                    // 25 Normal or 5 Heroic
                    return 1;
                }
                elseif($query['difficulty_entry_2'] > 0) {
                    // 10 Heroic
                    return 2;
                }
                elseif($query['difficulty_entry_3' > 0]) {
                    // 25 Heroic
                    return 3;
                }
                else {
                    // 10 Normal or 5 Normal
                    return 0;
                }
                break;
            case 'instance_type':
                $mapID = Armory::$wDB->selectCell("SELECT `map` FROM `creature` WHERE `id`=%d LIMIT 1", $npc);
                $instanceInfo = Armory::$aDB->selectCell("SELECT MAX(`max_players`) FROM `ARMORYDBPREFIX_instances_difficulty` WHERE `mapID`=%d", $mapID);
                if($instanceInfo == 5) {
                    // Dungeon
                    return 1;
                }
                elseif($instanceInfo > 5) {
                    // Raid
                    return 2;
                }
                break;
			case 'isBoss':
                $npc_data = Armory::$wDB->selectRow("SELECT `rank`, `KillCredit1`, `KillCredit2` FROM `creature_template` WHERE `entry`=%d LIMIT 1", $npc);
                if($npc_data['rank'] == 3) {
                    return true;
				}
                if($npc_data['KillCredit1'] > 0) {
                    $kc_entry = $npc_data['KillCredit1'];
                }
                elseif($npc_data['KillCredit2'] > 0) {
                    $kc_entry = $npc_data['KillCredit2'];
                }
                else {
                    $kc_entry = 0;
                }
                $npc_id = $npc.', '.$kc_entry;
                $instance = Armory::$aDB->selectCell("SELECT `instance_id` FROM `ARMORYDBPREFIX_instance_data` WHERE `id` IN (%s) OR `name_id` IN (%s) OR `lootid_1` IN (%s) OR `lootid_2` IN (%s) OR `lootid_3` IN (%s) OR `lootid_4` IN (%s)", $npc_id, $npc_id, $npc_id, $npc_id, $npc_id, $npc_id);
                if($instance > 0) {
                    return true;
                }
                else {
                    return false;
                }
				break;
            case 'bossData':
                $data = Armory::$aDB->selectRow("
                SELECT `instance_id`, `key`, `lootid_1`, `lootid_2`, `lootid_3`, `lootid_4`
                    FROM `ARMORYDBPREFIX_instance_data`
                        WHERE `id`=%d OR `lootid_1`=%d OR `lootid_2`=%d OR `lootid_3`=%d OR `lootid_4`=%d",
                        $npc, $npc, $npc, $npc, $npc);
                if(!$data) {
                    return false;
                }
                $info = array(
                    'difficulty' => 'all',
                    'key' => $data['key'],
                    'dungeon_key' => Armory::$aDB->selectCell("SELECT `key` FROM `ARMORYDBPREFIX_instance_template` WHERE `id`=%d", $data['instance_id'])
                );
                for($i=1;$i<5;$i++) {
                    if($data['lootid_'.$i] == $npc) {
                        if($i == 1 || $i == 2) {
                            $info['difficulty'] = 'normal';
                        }
                        else {
                            $info['difficulty'] = 'heroic';
                        }
                    }
                }
                break;
		}
		if($info) {
            return $info;
		}
        return false;
	}

    /**
     * Generates money value
     * @category Mangos class
     * @access   public
     * @param    int $money
     * @return   array
     **/
    public static function GetMoney($money) {
        $getMoney['gold'] = floor($money/(100*100));
        $money = $money-$getMoney['gold']*100*100;
        $getMoney['silver'] = floor($money/100);
        $money = $money-$getMoney['silver']*100;
        $getMoney['copper'] = floor($money);
        return $getMoney;
    }

    /**
     * Returns extended cost info for $costId cost.
     * @category Mangos class
     * @access   public
     * @param    int $costId
     * @return   array
     **/
    public static function GetExtendedCost($costId) {
        if($costId == 0) {
            return false;
        }
        if($costId < 0) {
            $costId = abs($costId);
        }
        $costInfo = Armory::$aDB->selectRow("SELECT * FROM `ARMORYDBPREFIX_extended_cost` WHERE `id`=%d LIMIT 1", $costId);
        if(!$costInfo) {
            Armory::Log()->writeError('%s : wrong cost id: #%d', __METHOD__, $costId);
            return false;
        }
        $extended_cost = array();
        for($i=1;$i<6;$i++) {
            if($costInfo['item'.$i] > 0) {
                $extended_cost[$i]['count'] = $costInfo['item'.$i.'count'];
                $extended_cost[$i]['icon']  = Items::GetItemIcon($costInfo['item'.$i]);
                $extended_cost[$i]['id'] = $costInfo['item'.$i];
            }
        }
        return $extended_cost;
    }

    /**
     * Is PvP extended cost required?
     * @category Mangos class
     * @access   public
     * @param    int $costId
     * @return   array
     **/
    public static function GetPvPExtendedCost($costId) {
        $costInfo = Armory::$aDB->selectRow("SELECT `arenaPoints` AS `arena`, `honorPoints` AS `honor` FROM `ARMORYDBPREFIX_extended_cost` WHERE `id`=%d", $costId);
        if(!$costInfo || ($costInfo['arena'] == 0 && $costInfo['honor'] == 0)) {
            return false;
        }
        return $costInfo;
    }

    /**
     * Generates drop percent for $boss_id boss and $item_id item.
     * @author   DiSlord
     * @category Mangos class
     * @access   public
     * @param    int $boss_id
     * @param    string $db_table
     * @param    int $item_id
     * @return   int
     **/
    public static function GenerateLootPercent($boss_id, $db_table, $item_id) {
        $allowed_tables = array(
            'creature_loot_template'   => true,
            'disenchant_loot_template' => true,
            'fishing_loot_template'    => true,
            'gameobject_loot_template' => true,
            'item_loot_template'       => true,
            'reference_loot_template'  => true
        );
        if(!isset($allowed_tables[$db_table])) {
            return 0;
        }
        $lootTable = Armory::$wDB->select("SELECT `Chance`, `groupid`, `mincount`, `item` FROM `%s` WHERE `entry`=%d", $db_table, $boss_id);
        if(!$lootTable) {
            return 0;
        }
        $percent = 0;
        foreach($lootTable as $loot) {
            if($loot['Chance'] > 0 && $loot['item'] == $item_id) {
                $percent = $loot['Chance'];
            }
            elseif($loot['Chance'] == 0 && $loot['item'] == $item_id) {
                $current_group = $loot['groupid'];
                $percent = 0;
                $i = 0;
                foreach($lootTable as $tLoot) {
                    if($tLoot['groupid'] == $current_group) {
                        if($tLoot['Chance'] > 0) {
                            $percent += $tLoot['Chance'];
                        }
                        else {
                            $i++;
                        }
                    }
                }
                $percent = round((100 - $percent) / $i, 3);
            }
        }
        return $percent;
    }

    /**
     * Returns ExtendedCost for item $itemID
     * @category Mangos class
     * @access   public
     * @param    int $itemID
     * @return   int
     **/
    public static function GetVendorExtendedCost($itemID) {
        $costId = Armory::$wDB->selectCell("SELECT `ExtendedCost` FROM `npc_vendor` WHERE `item`=%d LIMIT 1", $itemID);
        if($costId < 0) {
            $costId = abs($costId);
        }
        return $costId;
    }
}
?>

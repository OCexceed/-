<?php
namespace Employment;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use picketmine\inventory\Inventory;
use picketmine\inventory\BaseInventory;
use picketmine\inventory\PlayerInventory;
class Employment extends PluginBase implements Listener
{
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->warning("\n\n§e| Employment | is loading...by Exceed\n");
        @mkdir($this->getDataFolder(), 0777, true);
        $this->con = new Config($this->getDataFolder() . "Config.yml", Config::YAML, array("任务数量" => 0));
        $this->conf = new Config($this->getDataFolder() . "list.yml", Config::YAML, array("tasklist" => array()));
    }
    public function onCommand(CommandSender $sr, Command $cmd, $label, array $arg)
    {
        $mz = $sr->getName();
        $smz = strtolower($mz);
        //输入你设定的任何一个命令这个函数就会被调用
        //注意还需要判断是哪个命令
        switch (strtolower($cmd->getName())) {
            case "taskhelp":
                $sr->sendMessage("§6==========Employment系统==========\n§a/addtask————发布打工任务\n§b/deltask————取消打工任务\n§c/fintask————提交打工任务\n§d/saatask————查看打工任务\n§e/tasklist————打工任务列表");
                break;
            case "addtask":
                if (isset($arg[0])) {
                    if (!$sr instanceof Player) {
                        $sr->sendMessage("§a请不要用控制台使用此指令");
                    } else {
                        if ($arg[0] < 1 or $arg[0] > 500) {
                            $sr->sendMessage("§a您输入的物品id不存在!");
                        } else {
                            if (isset($arg[1])) {
                                if ($arg[1] < 0 or $arg[1] > 2000) {
                                    $sr->sendMessage("§a您输入的物品特殊值不存在!");
                                } else {
                                    if (isset($arg[2])) {
                                        if ($arg[2] < 1 or $arg[2] > 64) {
                                            $sr->sendMessage("§a数量不能小于1或超过64!");
                                        } else {
                                            if (isset($arg[3])) {
                                                if ($arg[3] == "item") {
                                                    if (null == $arg[4] or null == $arg[5] or null == $arg[6]) {
                                                        $sr->sendMessage("§a使用方法: /addtask 物品id 特殊值 数量 item 奖励物id 特殊值 数量");
                                                    } else {
                                                        if ($sr->getGamemode() % 2 === 1) {
                                                            $sr->sendMessage("§a你的游戏模式必须为生存模式!");
                                                        } else {
                                                            foreach ($sr->getInventory()->getContents() as $item) {
                                                                $id = $item->getId();
                                                                $sl = $item->getCount();
                                                                $ts = $item->getDamage();
                                                            }
                                                            if ($arg[4] > $id or $arg[4] < $id) {
                                                                $sr->sendMessage("§a你没有id为{$arg[4]}的物品，无法创建打工任务!");
                                                            } else {
                                                                if ($arg[5] > $ts or $arg[5] < $ts) {
                                                                    $sr->sendMessage("§a你没有id为{$arg[4]} : {$arg[5]}的物品，无法创建打工任务!");
                                                                } else {
                                                                    if ($arg[6] > $sl) {
                                                                        $sr->sendMessage("§a你的{$arg[4]} : {$arg[5]}物品数量不够{$arg[6]}个，无法创建打工任务!");
                                                                    } else {
                                                                        $num = $this->con->get("任务数量");
                                                                        $numup = $num + 1;
                                                                        $this->con->set($numup, ["雇主" => $mz, "物品id" => $arg[0], "特殊值" => $arg[1], "数量" => $arg[2], "奖励物id" => $arg[4], "奖励物特殊值" => $arg[5], "奖励物数量" => $arg[6], "任务状态" => "未完成"]);
                                                                        $sr->getInventory()->removeItem(new Item($arg[4], $arg[5], $arg[6]));
                                                                        $sr->sendMessage("§a创建玩家打工任务成功! 任务编号 [ {$numup} ] \n§a物品{$arg[0]} : {$arg[1]}， 数量{$arg[2]}， 奖励物{$arg[4]} : {$arg[5]}，数量{$arg[6]}个。");
                                                                        $this->con->set("任务数量", $numup);
                                                                        $this->con->save();
                                                                        $config = $this->conf->getAll();
                                                                        $cs = $config["tasklist"];
                                                                        if (!is_array($cs)) {
                                                                            $cs = array($numup);
                                                                        } else {
                                                                            $cs[] = $numup;
                                                                            $config["tasklist"] = $cs;
                                                                            $this->conf->setAll($config);
                                                                            $this->conf->save();
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $num = $this->con->get("任务数量");
                                                    $numup = $num + 1;
                                                    $this->con->set($numup, ["雇主" => $mz, "物品id" => $arg[0], "特殊值" => $arg[1], "数量" => $arg[2], "工钱" => $arg[3], "任务状态" => "未完成"]);
                                                    EconomyAPI::getInstance()->reduceMoney($mz, $arg[3]);
                                                    $sr->sendMessage("§a创建玩家打工任务成功! 任务编号 [ {$numup} ] \n§a物品{$arg[0]} : {$arg[1]}， 数量{$arg[2]}， 工钱钱数{$arg[3]}");
                                                    $this->con->set("任务数量", $numup);
                                                    $this->con->save();
                                                    $config = $this->conf->getAll();
                                                    $cs = $config["tasklist"];
                                                    if (!is_array($cs)) {
                                                        $cs = array($numup);
                                                    } else {
                                                        $cs[] = $numup;
                                                        $config["tasklist"] = $cs;
                                                        $this->conf->setAll($config);
                                                        $this->conf->save();
                                                    }
                                                }
                                            } else {
                                                $sr->sendMessage("§a使用方法: /addtask 物品id 特殊值 数量 工钱\n§a或者/addtask 物品id 特殊值 数量 item 奖励物id 特殊值 数量");
                                            }
                                        }
                                    } else {
                                        $sr->sendMessage("§a使用方法: /addtask 物品id 特殊值 数量 工钱\n§a或者/addtask 物品id 特殊值 数量 item 奖励物id 特殊值 数量");
                                    }
                                }
                            } else {
                                $sr->sendMessage("§a使用方法: /addtask 物品id 特殊值 数量 工钱\n§a或者/addtask 物品id 特殊值 数量 item 奖励物id 特殊值 数量");
                            }
                        }
                    }
                } else {
                    $sr->sendMessage("§a使用方法: /addtask 物品id 特殊值  数量 工钱\n§a或者/addtask 物品id 特殊值 数量 item 奖励物id 特殊值 数量");
                }
                break;
            case "deltask":
                if (isset($arg[0])) {
                    if (!$this->con->exists($arg[0])) {
                        $sr->sendMessage("§a该任务不存在!");
                    } else {
                        $conf = $this->con->get($arg[0]);
                        if ($conf["任务状态"] !== "未完成") {
                            $sr->sendMessage("§a该任务已完成或已被取消");
                        } else {
                            if ($sr->getName() !== $conf["雇主"] and !$sr->isOp()) {
                                $sr->sendMessage("§a该任务不是你发布的!");
                            } else {
                                $gz = $this->getServer()->getPlayer($conf["雇主"]);
                                if (isset($conf["工钱"])) {
                                    EconomyAPI::getInstance()->addMoney($mz, $conf["工钱"]);
                                } else {
                                    if (!$sr instanceof Player) {
                                        $this->con->set($arg[0], ["任务状态" => "已取消"]);
                                        $this->con->save();
                                        $sr->sendMessage("§a任务编号{$arg[0]}已取消。");
                                        $xargs = $arg[0];
                                        $config = $this->conf->getAll();
                                        $cs = $config["tasklist"];
                                        $key = array_search($xargs, $cs);
                                        unset($cs[$key]);
                                        $config["tasklist"] = $cs;
                                        $this->conf->setAll($config);
                                        $this->conf->save();
                                    } else {
                                        $gz->getInventory()->addItem(new Item($conf["奖励物id"], $conf["奖励物特殊值"], $conf["奖励物数量"]));
                                    }
                                    $this->con->set($arg[0], ["任务状态" => "已取消"]);
                                    $this->con->save();
                                    $sr->sendMessage("§a任务编号{$arg[0]}已取消。");
                                    $xargs = $arg[0];
                                    $config = $this->conf->getAll();
                                    $cs = $config["tasklist"];
                                    $key = array_search($xargs, $cs);
                                    unset($cs[$key]);
                                    $config["tasklist"] = $cs;
                                    $this->conf->setAll($config);
                                    $this->conf->save();
                                }
                            }
                        }
                    }
                } else {
                    $sr->sendMessage("§a使用方法: /deltask (任务编号)");
                }
                break;
            case "fintask":
                if (isset($arg[0])) {
                    if (!$this->con->exists($arg[0])) {
                        $sr->sendMessage("§a该任务不存在!");
                    } else {
                        if (!$sr instanceof Player) {
                            $sr->sendMessage("§a请不要用控制台使用此指令");
                        } else {
                            $conf = $this->con->get($arg[0]);
                            if ($conf["任务状态"] !== "未完成") {
                                $sr->sendMessage("§a该任务已完成或已被取消");
                            } else {
                                $bb = $sr->getInventory();
                                $item = $bb->getItemInHand();
                                $hid = $item->getId();
                                $hsl = $item->getCount();
                                $hts = $item->getDamage();
                                if ($conf["物品id"] > $hid or $conf["物品id"] < $hid) {
                                    $sr->sendMessage("§a你手持的物品id不符合，请将符合的任务物品拿在手里。");
                                } else {
                                    if ($conf["特殊值"] < $hts or $conf["物品id"] < $hid) {
                                        $sr->sendMessage("§a你手持的物品特殊值不符合，请将符合的任务物品拿在手里。");
                                    } else {
                                        if ($conf["数量"] > $hsl) {
                                            $sr->sendMessage("§a你手持的物品数量不足，请将足够的任务物品拿在手里。");
                                        } else {
                                            $gz = $this->getServer()->getPlayer($conf["雇主"]);
                                            if (!$gz->isOnline()) {
                                                $sr->sendMessage("§a雇主不在线，请在雇主在线时提交");
                                            } else {
                                                if ($sr->getGamemode() % 2 === 1 or $gz->getGamemode() % 2 === 1) {
                                                    $sr->sendMessage("§a你和雇主的游戏模式都必须为生存模式!");
                                                } else {
                                                    $sr->getInventory()->removeItem(new Item($conf["物品id"], $conf["特殊值"], $conf["数量"]));
                                                    $gz->getInventory()->addItem(new Item($conf["物品id"], $conf["特殊值"], $conf["数量"]));
                                                    //$this->removeItem($sr, new Item($conf["物品id"],$conf["特殊值"],$conf["数量"]));
                                                    if (isset($conf["工钱"])) {
                                                        EconomyAPI::getInstance()->addMoney($mz, $conf["工钱"]);
                                                    } else {
                                                        $sr->getInventory()->addItem(new Item($conf["奖励物id"], $conf["奖励物特殊值"], $conf["奖励物数量"]));
                                                    }
                                                    $this->con->set($arg[0], ["任务状态" => "已完成"]);
                                                    $this->con->save();
                                                    $xargs = $arg[0];
                                                    $config = $this->conf->getAll();
                                                    $cs = $config["tasklist"];
                                                    $key = array_search($xargs, $cs);
                                                    unset($cs[$key]);
                                                    $config["tasklist"] = $cs;
                                                    $this->conf->setAll($config);
                                                    $this->conf->save();
                                                    $sr->sendMessage("§a成功接单，物品已自动扣除， 工钱或奖励物已到账");
                                                    $gz->sendMessage("§a你的任务编号({$arg[0]})已完成，物品已到背包。");
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $sr->sendMessage("§a使用方法: /fintask (任务编号)");
                }
                break;
            case "tasklist":
                $confi = array($this->conf->get("tasklist"));
                $sr->sendMessage("§a任务列表:");
                foreach ($this->conf->get("tasklist") as $oplist) {
                    $sr->sendMessage("{$oplist}");
                }
                $sr->sendMessage("请用指令/seetask (任务编号) 查看任务详情");
                break;
            case "seetask":
                if (!isset($arg[0])) {
                    $sr->sendMessage("§a使用方法: /seetaak (任务编号)");
                } else {
                    if (!$this->con->exists($arg[0])) {
                        $sr->sendMessage("§a该任务不存在!");
                    } else {
                        $conf = $this->con->get($arg[0]);
                        if ($conf["任务状态"] == "已完成") {
                            $sr->sendMessage("§a该任务已完成");
                        } else {
                            if ($conf["任务状态"] == "已取消") {
                                $sr->sendMessage("§a该任务已取消");
                            } else {
                                if (isset($conf["工钱"])) {
                                    $sr->sendMessage("§a未完成任务编号 {$arg[0]} 的信息如下:\n雇主: {$conf["雇主"]} | 物品: {$conf["物品id"]} : {$conf["特殊值"]}\n数量: {$conf["数量"]}     | 工钱: {$conf["工钱"]}");
                                } else {
                                    $sr->sendMessage("§a未完成任务编号 {$arg[0]} 的信息如下:\n雇主: {$conf["雇主"]} | 物品: {$conf["物品id"]} : {$conf["特殊值"]}\n数量: {$conf["数量"]} | 奖励物: {$conf["奖励物id"]} : {$conf["奖励物特殊值"]}， {$conf["奖励物数量"]}个。");
                                }
                            }
                        }
                    }
                }
                break;
        }
    }
    public function onDisable()
    {
        $this->getServer()->getLogger()->info("| Employment | Disable......");
    }
}
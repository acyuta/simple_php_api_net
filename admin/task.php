<?php

require_once __DIR__ . "/logic.php";
const COMMON_TASK = 0;

if (isAjax()) {
    $act = post('action');
    switch($act) {
        case 'delete':
            if (!isset($_POST['id'])) die('no id');
            $id = post('id');
            if ($id <= 0) die('bad id');
            echo "OK";
            break;
        case 'add':
            if (!isset($_POST['id'])) die('no id');
            if (!isset($_POST['name'])) die('no name');
            if (!isset($_POST['type'])) die('no type');
            if (!isset($_POST['additional']))
                $additional = '[]';
            else {
                $additional = $_POST['additional'];
                $r = json_decode($additional);
                if (json_last_error() != JSON_ERROR_NONE)
                    die('invalid JSON code additional');
            }

            $group_id = intval(post('id'));
            $name = post('name');
            $type = post('type');
            if ($group_id < 0) die('bad id');
            if ($group_id == 0)
                $added_id = CAdmin::addCommonTask($name,$type,$additional);
            else
                $added_id = CAdmin::addTaskToGroup($name,$type,$group_id,$additional);

            $result = [
                'status' => "success",
                'id' => $added_id,
            ];
            if ($added_id == null)
                echo "data not saved";
            else
                echo json_encode($result);
            break;
    }
    exit;
}

$content = '
<div class="panel panel-default" style="margin-top: 15px">
<div class="panel-heading">Manage tasks<div class="text-right" style="padding-top: 5px; padding-right:5px">
            <button class="btn btn-success" id="button-task-create">Create</button>
        </div></div>
<div class="panel-body">
    <div >
        <div class="form-group">
            <label for="input_task_name">Name</label>
            <input type="text" class="form-control" id="input_task_name"/>
        </div>

        <div class="form-group">
            <label for="input_task_type">Task Type</label>
            <select class="form-control" id="input_task_type">
            ';
$task = CAdmin::getTaskTypeArray();

foreach ($task as $t) {
    $id = $t['id'];
    $name = $t['name'];
    $content .= "<option value='{$id}'>{$name}</option>";
};
$content .= '
            </select>
        </div>
        <div class="form-group">
    <label for="select_group">Group</label>
    <select class="form-control" id="input_task_group">
    <option value="0">Common task</option>
    ';
$task = CAdmin::getGroupArray();

foreach ($task as $t) {
    $id = $t['id'];
    $name = $t['name'];
    $content .= "<option value='{$id}'>{$name}</option>";
};
$content .= '
    </select>
  </div>
        <div class="form-group">
            <label for="input_task_additional">Additional</label>
            <textarea class="form-control" placeholder="Put JSON array code here" id="input_task_additional"></textarea>
        </div>
    </div>
</div>
</div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">Task list</div>
    <div class="panel-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td>#</td>
                    <td>Name</td>
                    <td>Type</td>
                    <td>Group</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody id="task_list_table">
                ';
$tasks = CAdmin::getTaskArray();
$i = 0;
foreach($tasks as $t) {
    $i++;
    $id = $t['id'];
    $name = $t['name'];
    $type = $t['type'];
    if (!isset($t['groups']) || $t['groups'] == null)
        $group = 'Common task';
    else
        $group = $t['groups'];
    $additional = $t['additional'];
    $delete_button = "<button class='btn btn-sm btn-danger delete-task-button' data-id='{$id}'>Delete</button>";
    $additional_button = "<button class='btn btn-sm btn-info' data-toggle='tooltip' data-placement='left' title='{$additional}'>See JSON</button>";
    $content .= "<tr><td>{$i}</td><td>{$name}</td><td>{$type}</td><td>{$group}</td><td>{$additional_button} {$delete_button} </td></tr>";
}
$content .= '
            </tbody>
        </table>
    </div>
</div>


';

include_once __DIR__ . '/_main.html';
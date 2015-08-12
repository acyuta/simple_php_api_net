<?php

require_once __DIR__ . "/logic.php";

$content = '
<div class="form-inline">
  <div class="form-group">
    <label for="select_task">Task</label>
    <select class="form-control" id="select_task">
    ';
$task = CAdmin::getTaskArray();
$task_content = '';
foreach ($task as $t) {
    $id = $t['id'];
    $name = $id . '. '. $t['name'] . ' ('. $t['type'] . ')';
    $task_content .= "<option value='{$id}'>{$name}</option>";
};
$content .= $task_content;
$content .='
    </select>
  </div>
  <div class="form-group">
    <label for="select_group">Group</label>
    <select class="form-control" id="select_group">
    <option value="0">Common task</option>
    ';
$task = CAdmin::getGroupArray();

foreach ($task as $t) {
    $id = $t['id'];
    $name = $id . '. '. $t['name'];
    $content .= "<option value='{$id}'>{$name}</option>";
};
$content .='
    </select>
  </div>
  <button class="btn btn-default">Start execution</button>
</div>

<div class="panel panel-default" style="margin-top: 15px">
<div class="panel-heading">Manage tasks</div>
<div class="text-right" style="padding-top: 5px; padding-right:5px">
            <button class="btn btn-success" id="button-task-create">Create</button>
            <button class="btn btn-info" id="button-task-edit" disabled>Edit</button>
            <button class="btn btn-default" id="button-task-save" disabled>Save</button>
        </div>
<div class="panel-body">
    <div >
        <div class="form-group">
            <label for="input_task_name">Name</label>
            <input type="text" class="form-control" id="input_task_name"/>
        </div>

        <div class="form-group">
            <label for="input_select_task">Task Type</label>
            <select class="form-control" id="input_select_task">
            ';
$task = CAdmin::getTaskTypeArray();

foreach ($task as $t) {
    $id = $t['id'];
    $name = $t['name'];
    $content .= "<option value='{$id}'>{$name}</option>";
};
$content .='
            </select>
        </div>
        <div class="form-group">
            <textarea class="form-control" placeholder="Put JSON array code here" id="input_addition"></textarea>
        </div>
    </div>
</div>
</div>
</div>
';

include_once __DIR__ . '/_main.html';
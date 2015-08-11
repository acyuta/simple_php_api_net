<?php
require_once __DIR__ . "/logic.php";

if (isset($_POST['name'])) {
    $u = post('name');
    if (strlen($u) > 3 && strlen($u) < 64 && CAdmin::addTaskType($u)) {
        $type = 'success';
        $msg = 'Task type added!';
    } else {
        $type = 'warning';
        $msg = 'Task type cannot create!';
    }

    echo "<div class=\"alert alert-{$type} alert-dismissible\" role=\"alert\">
<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
{$msg}</div>";
}

if (isAjax()) {
    switch (post("a")) {
        case 'delete':
            if (isset($_POST['id'])) {
                $v = post("id");
                $id = intval($v);
                if ($id > 0 && !is_array($v)) {
                    $ans = CAdmin::removeTaskType($id);
                    echo ($ans) ? "OK" : "Can't remove task type";
                    exit;
                } else exit;
            } else exit;
            break;

        default:
            exit;
    }
}

$content = "<div class='container'><div class='page-header'> ";

$content = "<div class='panel'>
<form class=\"form-inline toggle-disabled\" method='post'>
  <div class=\"form-group\">
    <label for=\"name\">Name</label>
    <input type=\"text\" name='name' class=\"form-control valid\"  placeholder=\"MAKEGODMINE\"
     data-validation-event='keyup'
     data-validation=\"alphanumeric,required,length\"
     data-validation-length=\"2-64\"
     >
  </div>
  <button type='submit' class=\"btn btn-default\">Add Task Type</button>
  <button class=\"btn btn-info media-right\" id='button-refresh-table'>Refresh Table</button>
</form>
<script>
$.validate({
    modules :  'security, toggleDisabled',
    disabledFormFilter : 'form.toggle-disabled',
    onModulesLoaded : function() {
        console.log('validators loaded');
    }
  });
</script>
</div>";


$content .= "<table class='table table-hover'>
<thead>
<tr>
    <td>#</td>
    <td>Task Type Name</td>
    <td>Created</td>
    <td></td>
</tr>
</thead>
<tbody>";

$users = CAdmin::getTaskTypeArray();
$i = 0;
foreach ($users as $u) {
    $i++;
    $date = date("d-m-Y", strtotime($u["created"]));
    $id = $u['id'];
    $actions = "<a href='#' class=\"glyphicon glyphicon-remove button_tt_remove\" data-target='{$id}' aria-hidden=\"true\"  style=\"text-decoration: none\"/>";
    $content .= "<tr id='tt-row-{$id}'><td>{$i}</td><td>{$u["name"]}</td><td>{$date}</td><td>{$actions}</td></tr>";
}
$content .= "</tbody></table>";
$content .= "</div></div>";
include "_main.html";
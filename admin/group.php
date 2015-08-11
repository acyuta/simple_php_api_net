<?php
require_once __DIR__ . "/logic.php";

if (isset($_POST['name'])) {
    $name = post('name');
    if (strlen($name) > 1 && strlen($name) < 64 && CAdmin::addGroup($name)) {
        $type = 'success';
        $msg = 'Group added!';
    } else {
        $type = 'warning';
        $msg = 'Group cannot create!';
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
                    $ans = CAdmin::removeGroup($id);
                    echo ($ans) ? "OK" : "Can't remove group";
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
    <input type=\"text\" name='name' class=\"form-control valid\"  placeholder=\"Group name\"
     data-validation-event='keyup'
     data-validation=\"alphanumeric,required,length\"
     data-validation-length=\"2-64\"
     >
  </div>
  <button type='submit' class=\"btn btn-default\">Add Group</button>
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
    <td>Group Name</td>
    <td>Created</td>
    <td></td>
</tr>
</thead>
<tbody>";

$users = CAdmin::getGroupArray();
$i = 0;
foreach ($users as $name) {
    $i++;
    $date = date("d-m-Y", strtotime($name["created"]));
    $id = $name['id'];
    $actions = "<a href='#' class=\"glyphicon glyphicon-remove button_group_remove\" data-target='{$id}' aria-hidden=\"true\"  style=\"text-decoration: none\"/>";
    $content .= "<tr id='group-row-{$id}'><td>{$i}</td><td>{$name["name"]}</td><td>{$date}</td><td>{$actions}</td></tr>";
}
$content .= "</tbody></table>";
$content .= "</div></div>";
include "_main.html";
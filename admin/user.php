<?php
require_once __DIR__ . "/logic.php";

if (isset($_POST['username']) && isset($_POST['password'])) {
    $u = post('username');
    $p = post('password');
    if (CAdmin::addUser($u,$p)) {
        $type = 'success';
        $msg = 'User added!';
    } else {
        $type = 'warning';
        $msg = 'User cannot create!';
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
                    $ans = CAdmin::removeUser($id);
                    echo ($ans) ? "OK" : "Can't remove user";
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
    <input type=\"text\" name='username' class=\"form-control valid\" id=\"username\" placeholder=\"Jane Doe\"
     data-validation-event='keyup'
     data-validation=\"letternumeric,required,length\"
     data-validation-length=\"3-32\"
     >
  </div>
  <div class=\"form-group\">
    <label for=\"p\">Password</label>
    <input type=\"password\" name='password' class=\"form-control valid\" id=\"password\" placeholder=\"My Precious\"
    data-validation-event='keyup'
    data-validation=\"required,letternumeric\"
    data-validation-error-msg='Only alphabetic chars and digits allowed'
    >
  </div>
  <button type='submit' class=\"btn btn-default\">Add User</button>
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
    <td>Username</td>
    <td>Created</td>
    <td></td>
</tr>
</thead>
<tbody>";

$users = CAdmin::getUsersArray();
$i = 0;
foreach ($users as $u) {
    $i++;
    $date = date("d-m-Y", strtotime($u["created"]));
    $id = $u['id'];
    $actions = "<a href='#' class=\"glyphicon glyphicon-remove button_user_remove\" data-target='{$id}' aria-hidden=\"true\"  style=\"text-decoration: none\"/>";
    $content .= "<tr id='user-row-{$id}'><td>{$i}</td><td>{$u["name"]}</td><td>{$date}</td><td>{$actions}</td></tr>";
}
$content .= "</tbody></table>";
$content .= "</div></div>";
include "_main.html";
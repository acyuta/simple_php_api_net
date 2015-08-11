<?php
require_once __DIR__ . "/logic.php";

if (isAjax()) {
    switch (post("a")) {
        case 'delete':
            if (isset($_POST['id'])) {
                $v = post("id");
                $id = intval($v);
                if ($id  > 0 && !is_array($v)) {
                    $ans = CAdmin::removeUser($id);
                    return ($ans) ? "OK" : "Can't remove user";
                } else exit;
            }
            else exit;
            break;

        default: exit;
    }
}

$content = "<div class='row'><div class='page-header'> ";

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
    $content .= "<tr><td>{$i}</td><td>{$u["name"]}</td><td>{$date}</td><td>{$actions}</td></tr>";
}
$content .= "</tbody></table>";
$content .= "</div></div>";
include "_main.html";
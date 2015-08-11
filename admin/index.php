<?php
require_once __DIR__ . "/logic.php";

if(isAjax()) {
    // Если к нам идёт Ajax запрос, то ловим его
    $from = (validateDate(post("from"),"Y-m-d H:i")) ? strtotime(post("from")) : time() - 60*60*24;
    $to = (validateDate(post("from"),"Y-m-d H:i")) ? strtotime(post("to")) : time();
    $data = [
        "online" => 0,
        "offline" => 0,
        "all" => CAdmin::countConnections($from,$to),
        "new" => CAdmin::countNewAgents(time()-60*60*24,time()),
        "last_update" => date("d.m.Y - H:i:s (\U\T\CO)",time()),
        "count_agents" => CAdmin::countUniqueConnections($from,$to),
        "done" => CAdmin::countDone($from,$to),
        "in_work" => CAdmin::countInWork($from,$to),
        "waiting_accept" => CAdmin::countWaiting($from,$to),
    ];
    echo json_encode($data);
    exit;
}



$content = file_get_contents("_statistic.html");
include "_main.html";

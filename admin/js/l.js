function tag(name, content) {
    return "<" + name + ">" + content + "</" + name + ">";
}
function upz(integer) {
    if (integer >= 0 && integer <= 9)
        return "0" + integer.toString();
    else
        return integer;
}
(function ($) {
    $(document).ready(function () {
        function update_stats(from, to) {
            $.post("/admin/index.php", {'from': from, 'to': to}, function (data) {
                var r = $("#common_statistic_row");
                var obj = $.parseJSON(data);
                r.children().remove();
                r.append(
                    tag("td", obj.online + "/" + obj.offline)
                    + tag("td", obj.all)
                    + tag("td", obj.new)
                    + tag("td", obj.last_update)
                );

                var s = $("#agent_statistic_row");
                s.children().remove();
                s.append(
                    tag("td", obj.count_agents)
                    + tag("td", obj.done)
                    + tag("td", obj.in_work)
                    + tag("td", obj.waiting_accept)
                );
            });
        }

        /** ACTIONS  */
        $("#button_update_statistic").click(function () {
            update_stats(null, null);
        });
        $("#button_get_statistic_filter").click(function () {
            update_stats($('#datefrom').val(), $('#dateto').val());
        });
        $(".quick-statistic-btn").click(function () {
            var e = $(this);
            var from = $('#datefrom');
            var to = $('#dateto');
            var now = new Date();
            switch (e.attr("data-target")) {
                case "today":
                    from.val(now.getFullYear() + "-" + upz(now.getMonth() + 1) + "-" + upz(now.getDate()) + " 00:00");
                    to.val(now.getFullYear() + "-" + upz(now.getMonth() + 1) + "-" + upz(now.getDate()) + " 23:59");
                    break;
                case "week":
                    if (now.getDate() > 7)
                        from.val(now.getFullYear() + "-" + upz(now.getMonth() + 1) + "-" + upz(now.getDate() - 7) + " 00:00");
                    else {
                        var sub = 7 - now.getDate();
                        var days = 0;
                        if (now.getMonth() != 0)
                            days = new Date(now.getFullYear(),now.getMonth()-1 , 0).getDate() - sub;
                        else
                            days = new Date(now.getFullYear(),11 , 0).getDate() - sub;

                        from.val(now.getFullYear() - 1 + "-" + upz(12) + "-" + upz(days) + " 00:00");
                    }
                    to.val(now.getFullYear() + "-" + upz(now.getMonth() + 1) + "-" + upz(now.getDate()) + " 23:59");
                    break;
                case "month":
                    if (now.getMonth() != 0)
                        from.val(now.getFullYear() + "-" + upz(now.getMonth()) + "-" + upz(now.getDate()) + " 00:00");
                    else
                        from.val(now.getFullYear() - 1 + "-" + upz(12) + "-" + upz(now.getDate()) + " 00:00");
                    to.val(now.getFullYear() + "-" + upz(now.getMonth() + 1) + "-" + upz(now.getDate()) + " 23:59");
                    break;
                case "year":
                    from.val((now.getFullYear() - 1) + "-" + upz(now.getMonth() + 1) + "-" + upz(now.getDate()) + " 00:00");
                    to.val(now.getFullYear() + "-" + upz(now.getMonth() + 1) + "-" + upz(now.getDate()) + " 23:59");
                    break;
                default:
                    break;
            }
        });
        $(".button_user_remove").click(function(){
            var e = $(this).attr("data-target");
            if (e != null && e != undefined && e > 0) {
                $.post("/admin/user.php", {'a': 'delete', 'id': e}, function(data) {
                    if (data == "OK")
                        $("#user-row-"+e).hide();
                    else alert (data);
                });
            }
        });
        $("#button-refresh-table").click(function() {
           location.reload();
        });
    });
})(jQuery);
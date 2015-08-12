function tag(name, content) {
    return "<" + name + ">" + content + "</" + name + ">";
}
function upz(integer) {
    if (integer >= 0 && integer <= 9)
        return "0" + integer.toString();
    else
        return integer;
}
function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }
    alert(out);
}
function sdump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }
    return (out);
}


(function ($) {

    var Netmask, ip2long, long2ip;

    long2ip = function(long) {
        var a, b, c, d;
        a = (long & (0xff << 24)) >>> 24;
        b = (long & (0xff << 16)) >>> 16;
        c = (long & (0xff << 8)) >>> 8;
        d = long & 0xff;
        return [a, b, c, d].join('.');
    };

    ip2long = function(ip) {
        var b, byte, i, j, len;
        b = (ip + '').split('.');
        if (b.length === 0 || b.length > 4) {
            throw new Error('Invalid IP');
        }
        for (i = j = 0, len = b.length; j < len; i = ++j) {
            byte = b[i];
            if (isNaN(parseInt(byte, 10))) {
                throw new Error("Invalid byte: " + byte);
            }
            if (byte < 0 || byte > 255) {
                throw new Error("Invalid byte: " + byte);
            }
        }
        return ((b[0] || 0) << 24 | (b[1] || 0) << 16 | (b[2] || 0) << 8 | (b[3] || 0)) >>> 0;
    };

    Netmask = (function() {
        function Netmask(net, mask) {
            var error, i, j, ref;
            if (typeof net !== 'string') {
                throw new Error("Missing `net' parameter");
            }
            if (!mask) {
                ref = net.split('/', 2), net = ref[0], mask = ref[1];
                if (!mask) {
                    switch (net.split('.').length) {
                        case 1:
                            mask = 8;
                            break;
                        case 2:
                            mask = 16;
                            break;
                        case 3:
                            mask = 24;
                            break;
                        case 4:
                            mask = 32;
                    }
                }
            }
            if (typeof mask === 'string' && mask.indexOf('.') > -1) {
                try {
                    this.maskLong = ip2long(mask);
                } catch (_error) {
                    error = _error;
                    throw new Error("Invalid mask: " + mask);
                }
                for (i = j = 32; j >= 0; i = --j) {
                    if (this.maskLong === (0xffffffff << (32 - i)) >>> 0) {
                        this.bitmask = i;
                        break;
                    }
                }
            } else if (mask) {
                this.bitmask = parseInt(mask, 10);
                this.maskLong = (0xffffffff << (32 - this.bitmask)) >>> 0;
            } else {
                throw new Error("Invalid mask: empty");
            }
            try {
                this.netLong = (ip2long(net) & this.maskLong) >>> 0;
            } catch (_error) {
                error = _error;
                throw new Error("Invalid net address: " + net);
            }
            if (!(this.bitmask <= 32)) {
                throw new Error("Invalid mask for ip4: " + mask);
            }
            this.size = Math.pow(2, 32 - this.bitmask);
            this.base = long2ip(this.netLong);
            this.mask = long2ip(this.maskLong);
            this.hostmask = long2ip(~this.maskLong);
            this.first = this.bitmask <= 30 ? long2ip(this.netLong + 1) : this.base;
            this.last = this.bitmask <= 30 ? long2ip(this.netLong + this.size - 2) : long2ip(this.netLong + this.size - 1);
            this.broadcast = this.bitmask <= 30 ? long2ip(this.netLong + this.size - 1) : void 0;
        }

        Netmask.prototype.contains = function(ip) {
            if (typeof ip === 'string' && (ip.indexOf('/') > 0 || ip.split('.').length !== 4)) {
                ip = new Netmask(ip);
            }
            if (ip instanceof Netmask) {
                return this.contains(ip.base) && this.contains(ip.broadcast || ip.last);
            } else {
                return (ip2long(ip) & this.maskLong) >>> 0 === (this.netLong & this.maskLong) >>> 0;
            }
        };

        Netmask.prototype.next = function(count) {
            if (count == null) {
                count = 1;
            }
            return new Netmask(long2ip(this.netLong + (this.size * count)), this.mask);
        };

        Netmask.prototype.forEach = function(fn) {
            var index, j, k, len, long, range, ref, ref1, results, results1;
            range = (function() {
                results = [];
                for (var j = ref = ip2long(this.first), ref1 = ip2long(this.last); ref <= ref1 ? j <= ref1 : j >= ref1; ref <= ref1 ? j++ : j--){ results.push(j); }
                return results;
            }).apply(this);
            results1 = [];
            for (index = k = 0, len = range.length; k < len; index = ++k) {
                long = range[index];
                results1.push(fn(long2ip(long), long, index));
            }
            return results1;
        };

        Netmask.prototype.toString = function() {
            return this.base + "/" + this.bitmask;
        };

        return Netmask;

    })();

// ---
// generated by coffee-script 1.9.2

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
                            days = new Date(now.getFullYear(), now.getMonth() - 1, 0).getDate() - sub;
                        else
                            days = new Date(now.getFullYear(), 11, 0).getDate() - sub;

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

        function removeFrom(name) {
            $(".button_" + name + "_remove").click(function () {
                var e = $(this).attr("data-target");
                if (e != null && e != undefined && e > 0) {
                    $.post("/admin/" + name + ".php", {'a': 'delete', 'id': e}, function (data) {
                        if (data == "OK")
                            location.reload();
                        else alert(data);
                    });
                }
            });
        }

        removeFrom('user');
        removeFrom('tt');
        removeFrom('group');

        function z(act, callable) {
            var gID = $('#groupid').val();
            var id = $('#mid').val();
            $.post('/admin/mg.php', {'action': act, 'id': id, 'group': gID}, callable);
        }

        function wrap(el, name) {
            var country = el.country[0];
            var ip = el.ip[0];
            var iid = el.id;
            var text = 'Agent #' + name + ' from ' + country + ' (' + ip + ')';
            return '<li class="list-group-item unselectable item_selection" data-id="' + iid + '" data-ip="' + ip + '" data-country="' + country + '">' + text + '</li>'
        }

        var list_selected = $("#list_selected");
        var list_choose = $("#list_selection");

        function setupSelectionTable(data) {
            $("#select-worktable").css("display", 'block');


            $("#filter_ip").mask('0ZZ.0ZZ.0ZZ.0ZZ/00', {
                translation: {
                    'Z': {
                        pattern: /[0-9]/, optional: true
                    }
                }
            });

            for (var i in data.selects) {
                var e = data.selects[i];
                list_selected.append(wrap(e,i));
            }

            for (var i in data.agents) {
                var e = data.agents[i];
                list_choose.append(wrap(e,i));
            }
        }

        $(".filter_button").click(function() {
            $(".remove_filter").show();
        });

        $(".remove_filter").click(function () {
            $(this).hide();
            list_choose.children().css('display','block');
        });

        $("#button_filter_id").click(function(){
            var d = $('#filter_id').val().split(',');
            list_choose.children().each(function(){
                var t = $(this);
                var r = d.indexOf(t.attr('data-id'));
                if (r < 0) {
                    t.hide();
                } else {
                    t.show();
                }
            });
        });

        $("#button_filter_ip").click(function(){
            var e = $("#filter_ip");
            e.parent().removeClass('has-error').removeClass('has-feedback');
            var d = e.val();
            var mask = 1;
            var ip = d;
            if (d.indexOf('/') != -1){
                var s = d.split('/');
                mask = s[1];
                ip = s[0];
            }
            alert(ip + ' - ' + mask);
            var n = new Netmask(ip,mask);
            list_choose.children().each(function() {
                var t = $(this);
                if (!n.contains(t.attr('data-ip'))){
                    t.hide();
                } else {
                    t.show();
                }
            });

        });

        $("#button_filter_country").click(function(){
            var d = $('#filter_country').val().split(',');
            list_choose.children().each(function(){
                var t = $(this);
                var r = d.indexOf(t.attr('data-country'));
                if (r < 0) {
                    t.hide();
                } else {
                    t.show();
                }
            });
        });

        $("#button_selected_list_clear").click(function(){
            list_choose.append(list_selected.html());
            list_selected.children().remove();
        });

        $("#groups_select").change(function () {
            var groupID = $(this).val();
            $.post('/admin/mg.php', {'action': 'get_group', 'id': groupID}, function (json) {
                var data = JSON.parse(json);
                if (data.status == 'success') {
                   setupSelectionTable(data);
                } else {
                    alert('Cannot get data. Error:  ' + data);
                }
            });
        });

        $("#button-refresh-table").click(function () {
            location.reload();
        });

        $("#list_selection, #list_selected").sortable({
            connectWith: ".selectable"
        }).disableSelection();
    });
})(jQuery);
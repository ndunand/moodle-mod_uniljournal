;

$(document).ready(function () {

    var perodic_call = function () {

        var articleinstanceid = document.location.search.replace(/.*\bid=(\d+).*/, "$1");
        if (!articleinstanceid) {
            return;
        }

        $.get(M.cfg.wwwroot + '/mod/uniljournal/ajax.php',
            {
                sesskey: M.cfg.sesskey,
                cmid: $('body').attr('class').replace(/^.*cmid-([0-9]+)\s.*$/, '$1'),
                articleinstanceid: document.location.search.replace(/.*\bid=(\d+).*/, "$1")
            }, function (data) {
                // console.log(data);
                if (!data.error) {
                    return;
                }
                if (data.message) {
                    // alert ('' + typeof M.core.notification);
                    if (data.messagetype && data.messagetype == 'confirm') {



                        $('#uniljournal_dialog').dialog('close').remove();
                        var $dialog = $('<div id="uniljournal_dialog">').html('<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + data.message);
                        var buttons = {};
                        // buttons[data.lockrelease] = function(){
                        //     $dialog.dialog('close');
                        //     // force submit the form
                        //     check_i_still_hold_the_lock(function(){
                        //         window.onbeforeunload = null;
                        //         $('form[action$="edit_article.php"]').submit();
                        //     }, function(message){
                        //         lock_lost('', message);
                        //     });
                        // };
                        buttons[data.lockkeep] = function(){
                            $dialog.dialog('close');
                            // cancel the form
                            check_i_still_hold_the_lock(function(){
                                // cancel the lock release request
                                $.get(M.cfg.wwwroot + '/mod/uniljournal/ajax.php', {
                                    sesskey:           M.cfg.sesskey,
                                    cmid:              document.location.search.replace(/.*\bcmid=(\d+).*/, "$1"),
                                    articleinstanceid: document.location.search.replace(/.*\bid=(\d+).*/, "$1"),
                                    ackrequest:     1
                                }, function (data) {
                                    // return;
                                });
                            }, function(message){
                                lock_lost('', message);
                            });
                        };
                        // buttons[data.lockwait] = function(){
                        //     $dialog.dialog('close');
                        // };
                        $dialog.dialog({
                            modal: true,
                            width: '40%',
                            buttons : buttons
                        });





                        // if (confirm('' + data.message)) {
                        //     // force submit the form
                        //     check_i_still_hold_the_lock(function(){
                        //         $('form[action$="edit_article.php"]').submit();
                        //     }, function(message){
                        //         lock_lost('', message);
                        //     });
                        // }
                        // else {
                        //     // cancel the form
                        //     // $('form[action$="edit_article.php"] input.btn-cancel').click();
                        //     // no, we'd better leave it alone and just do nothing
                        //     check_i_still_hold_the_lock(function(){
                        //         // cancel the lock release request
                        //         $.get(M.cfg.wwwroot + '/mod/uniljournal/ajax.php', {
                        //             sesskey:           M.cfg.sesskey,
                        //             cmid:              document.location.search.replace(/.*\bcmid=(\d+).*/, "$1"),
                        //             articleinstanceid: document.location.search.replace(/.*\bid=(\d+).*/, "$1"),
                        //             removerequest:     1
                        //         }, function (data) {
                        //             // return;
                        //         });
                        //     }, function(message){
                        //         lock_lost('', message);
                        //     });
                        // }
                    }
                    else if (data.messagetype && data.messagetype == 'alert') {
                        lock_lost('', data.message);
                    }
                    // var confirm = new M.core.confirm({
                    //     question: '' + data.message,
                    //     modal: true
                    // });
                    // confirm.on('complete-yes', function() {
                    // });
                    // confirm.on('complete-no', function() {
                    // });
                }
                else {
                    // other Moodle exception thrown
                    lock_lost('error: ', data.error);
                }
            });

    };


    var pcall;

    var start_pcall = function() {
        pcall = window.setInterval(perodic_call, 10000);
    };

    start_pcall();

    var stop_pcall = function() {
        window.clearInterval(pcall);
    };

    var lock_lost = function(msgprefix, message) {
        $('#fgroup_id_buttonar').remove();
        stop_pcall();
        $('form[action$="edit_article.php"]').attr('action', '');
        $('#uniljournal_dialog').dialog('close').remove();
        var $dialog = $('<div id="uniljournal_dialog">').html('<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' + msgprefix + message);
        $dialog.dialog({
            modal: true,
            buttons : {
                Ok : function(){
                    $(this).dialog('close');
                }
            }
        });
        //alert(msgprefix + message);
    };

    var check_i_still_hold_the_lock = function($callback_if_true, $callback_if_false) {
        $.get(M.cfg.wwwroot + '/mod/uniljournal/ajax.php',
            {
                sesskey: M.cfg.sesskey,
                cmid: document.location.search.replace(/.*\bcmid=(\d+).*/, "$1"),
                articleinstanceid: document.location.search.replace(/.*\bid=(\d+).*/, "$1")
            }, function (data) {
                console.log(data);
                if (data.error && data.messagetype && data.messagetype == 'alert') {
                    $callback_if_false(data.message);
                }
                else {
                    $callback_if_true();
                }
            });
    };

});
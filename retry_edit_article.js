;

$(document).ready(function () {

    var cmid = document.location.search.replace(/.*\bcmid=(\d+).*/, "$1"),
        articleinstanceid = document.location.search.replace(/.*\bid=(\d+).*/, "$1"),
        amid = document.location.search.replace(/.*\bamid=(\d+).*/, "$1")


    window.setInterval(function () {

        $.get(M.cfg.wwwroot + '/mod/uniljournal/ajax.php',
            {
                sesskey: M.cfg.sesskey,
                cmid: cmid,
                articleinstanceid: articleinstanceid,
                retry: 1
            }, function (data) {
                if (data.unlocked == true) {
                    // article edition is not locked anymore
                    document.location.href = M.cfg.wwwroot + '/mod/uniljournal/edit_article.php?cmid=' + cmid + '&id=' + articleinstanceid + '&amid=' + amid;
                    return;
                }
                else if (data.denied) {
                    // I don't hold an unlock request anymore (e.g. I've been denied access)
                    document.location.href = M.cfg.wwwroot + '/mod/uniljournal/view_article.php?cmid=' + cmid + '&id=' + articleinstanceid + '&amid=' + amid + '&denied=1';
                    return;
                }
            });

    }, 10000);

});
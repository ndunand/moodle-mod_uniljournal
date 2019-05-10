
$(document).ready(function () {

    $('select.autosubmit, select[name="amid"], select[name^="status_"]').change(function(){

        $(this).parents('form').submit();

    });

    // re-select user (or group) from select#selectStudent after page load when sorting
    var selectusr = parseInt(document.location.href.replace(/.*\bselectusr=(\d+)\b.*$/, "$1")),
        selectStudent = 0;

    if (selectusr > 0) {
        selectStudent = selectusr;
        $('#selectStudent').val(selectStudent).trigger('change');
    }

    $('select#selectSorting').change(function(){
        var currentlocation = document.location.href.replace(/&sorting=.*$/, '');
        var extraurl = '';
        if ($('#selectStudent').val() > 0) {
            extraurl = '&selectusr=' + $('#selectStudent').val();
        }
        document.location.href = currentlocation + '&sorting=' + this.value + extraurl;
    });

    // $('#selectSortingReset').click(function(){
    //     document.location.href = document.location.href + '&sortreset=1';
    // });

});
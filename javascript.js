
$(document).ready(function () {

    $('select.autosubmit').change(function(){

        $(this).parents('form').submit();

    });

});
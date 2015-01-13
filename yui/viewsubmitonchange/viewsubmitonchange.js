YUI.add('moodle-mod_uniljournal-viewsubmitonchange', function(Y) {
  // Your module code goes here.
 
  // Define a name space to call
  M.mod_uniljournal = M.mod_uniljournal || {};
  M.mod_uniljournal.viewsubmitonchange = {
    init: function() {
      Y.on('change', submit_form, '#fitem_id_amid');
        function submit_form() {
            var form = Y.one('#mform1'); // The id for the moodle form is automatically set.
            form.submit();
        }
    }
  };
}, '2015011302', {
  requires: ['node']
});
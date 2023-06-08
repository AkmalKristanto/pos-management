$(document).ready(function() {
    $('.clockpicker').clockpicker({
        autoclose: true,
        ignoreReadonly: true,
        showInputs: false,
        donetext: 'Done',
        'default': 'now'
    });
});

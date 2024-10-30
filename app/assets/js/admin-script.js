
jQuery(document).ready(function ($) {
    $('.capsule_crm_page #tabs').tabs();

    $('.copy_cfcc').click(function (e) {
        e.preventDefault();
    });

    var clipboard = new ClipboardJS('.copy_cfcc');


    function cap_val(val) {
        if (val == 'person') {
            $('#cfcc_name').prop('disabled', true);
            $('#cfcc_firstName,#cfcc_lastName,#cfcc_title,#cfcc_jobTitle,#cfcc_organisation').prop('disabled', false);
        } else {
            $('#cfcc_name').prop('disabled', false);
            $('#cfcc_firstName,#cfcc_lastName,#cfcc_title,#cfcc_jobTitle,#cfcc_organisation').prop('disabled', true);
        }
    }
    var type_val = $('#cfcc_type').val();
    cap_val(type_val);
    $('#cfcc_type').change(function () {
        var val = $(this).val();
        cap_val(val);
    });
});



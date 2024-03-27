$(document).ready(function () {
    var $nextStatus = $('.next-status-choice');
    var $assigned = $('.assigned-choice');

    var workflowId = $('.workflow-hidden').val();
    var contentType = $('.content-type-hidden').val();
    var currentStateId = $('.current-state').data('value');

    var changeState = function() {
        var status = $('select:selected', $nextStatus).val();

        if (status == undefined) {
            status = currentStateId;
        }

        $assigned.attr('disabled','disabled');
        $.ajax({
            url: $('.workflow').data('workflow-state-change'),
            data: {
                'workflow':workflowId,
                'state':status,
                'contentType':contentType,
                '_format':'json'
            },
            dataType: 'json',
            success: function(response) {
                var selected = $('option:selected', $assigned).val();

                var $firstOption = $('option:first', $assigned);
                $firstOption.siblings().remove();
                $.each(response.users, function (index, user) {
                    var newOption = new Option(user.name, user.id, (user.id == selected), (user.id == selected));
                    $assigned.append(newOption);
                });
                $assigned.removeAttr('disabled');

                $.each(response.fields, function(field, values) {
                    var $el = $('.' + field);

                    if ($el) {
                        $el.removeAttr('required');
                        $el.removeAttr('disabled');
                        $el.parents('.form-group').show();

                        if (values.disabled) {
                            $el.attr('disabled', 'disabled').parents('.form-group').hide();
                        } else if (values.required) {
                            $el.attr('required', 'required');
                        }
                    }
                });
            }
        });
    };

    $('#integrated_content_extension_workflow_assigned').select2();
    $($nextStatus).change(changeState);
    changeState();
});

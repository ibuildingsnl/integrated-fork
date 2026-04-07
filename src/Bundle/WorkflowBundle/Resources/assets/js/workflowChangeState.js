$(document).ready(function () {
    if (document.querySelector('#content-edit-page[data-inline-workflow-state-init="true"] #content-workflow-section .workflow')) {
        return;
    }

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
                    var $el = $('.form-item.' + field);

                    if ($el.length) {
                        var $inputs = $el.find('select, input, textarea');

                        $inputs.removeAttr('required').removeAttr('disabled');

                        $el.show();

                        if (values.disabled) {
                            $inputs.attr('disabled', 'disabled');
                            $el.hide();
                        } else if (values.required) {
                            $inputs.attr('required', 'required');
                        }

                        if ($inputs.hasClass('select2-hidden-accessible')) {
                            $inputs.trigger('change.select2');
                        }
                    }
                });
            }
        });
    };

    $('#integrated_content_extension_workflow_assigned').select2();
    $('select', $nextStatus).change(changeState);
    changeState();

});

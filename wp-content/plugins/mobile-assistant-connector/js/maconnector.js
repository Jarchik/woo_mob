/**
 * Created by jarchik on 11.05.16.
 */

/**
 *
 * @returns {undefined}
 */

$(document).ready(function() {
    var key     = $('#mobassistantconnector_key').val();
    var baseURL = $('#mobassistantconnector_base_url').val();
    var ajaxURL = '/wp-content/plugins/mobile-assistant-connector/ajax.php';

    fetchUsersList();

    function fetchUsersList() {
        $.ajax(baseURL.ajaxURL, {
            type: 'POST',
            dataType: 'json',
            data: {
                call_function: 'getUsers'
            },
            beforeSend: function () {
                //$('#inherit-role-list').html(
                //    '<option value="">' + aam.__('Loading...') + '</option>'
                //);
            },
            success: function (response) {
                console.log(response);
                $('#inherit-role-list').html(
                    '<option value="">' + aam.__('Select Role') + '</option>'
                );
                for (var i in response) {
                    $('#inherit-role-list').append(
                        '<option value="' + i + '">' + response[i].name + '</option>'
                    );
                }
            }
        });
    }

});
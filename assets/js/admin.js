jQuery(document).ready(function($) {
    $('#test-connection').on('click', function(e) {
        e.preventDefault();
        
        let api_key = $('#paddle_api_key').val();
        let seller_id = $('#paddle_seller_id').val();
        let environment = $('input[name="paddle_environment"]:checked').val();

        $.ajax({
            url: paddlepulseAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'test_paddle_connection',
                api_key: api_key,
                seller_id: seller_id,
                environment: environment
            },
            success: function(response) {
                if (response.success) {
                    $('#test-result').removeClass('bg-red-100 text-red-800').addClass('bg-green-100 text-green-800').text(response.data);
                } else {
                    $('#test-result').removeClass('bg-green-100 text-green-800').addClass('bg-red-100 text-red-800').text(response.data);
                }
            },
            error: function() {
                $('#test-result').removeClass('bg-green-100 text-green-800').addClass('bg-red-100 text-red-800').text('An error occurred while testing the connection.');
            }
        });
    });
});

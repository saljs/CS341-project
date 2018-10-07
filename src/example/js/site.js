//base page
var baseURL = "/example";

$( document ).ready(function() {
    //add event listeners
    $('#login').on('submit', login);
});

function login(e) {
    e.preventDefault();
    var email = $('#email').val();
    var passwd = $('#password').val();

    $.post('https://cs341group4.tk/User/Authenticate',
        $('#login').serialize(),
        function( data ) {
            if("token" in data) {
                $.cookie('token', data.token);
                window.location.href = baseURL + "/";
            }
            else {
                $('#error').html(data.message);
            }
        }
    );
}

//base page
var baseURL = "/example";

$( document ).ready(function() {
    //add click listeners
    $('#loginButton').click(login);
});

function login() {
    var email = $('#email').val();
    var passwd = $('#password').val();

    $.post('https://cs431group4.tk/User/Authenticate',
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

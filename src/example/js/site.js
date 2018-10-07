//base page
var baseURL = "/example";

$( document ).ready(function() {
    //add event listeners
    $('#login').on('submit', login);
    $('#register').on('submit', register);
});

function login(e) {
    e.preventDefault();
    $.post('https://cs341group4.tk/User/Authenticate', $('#login').serialize())
    .done(function(data) {
        $.cookie('token', data.token);
        window.location.href = baseURL + "/";
    })
    .fail(function(data){
        $('#error').html(data.message);
    });
}

function register(e) {
    e.preventDefault();
    var fields = $('#register').serialize();
    if(fields.type == "admin") {
        fields.token = $.cookie('token');
    }
    $.post('https://cs341group4.tk/User/Create', fields)
    .done(function() {
        $('#register').remove();
    })
    .always(function(data){
        $('#message').html(data.message);
    });
}

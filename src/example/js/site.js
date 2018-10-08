//base page
var baseURL = "/example";

$( document ).ready(function() {
    //add event listeners
    $('#login').on('submit', login);
    $('#logout').on('click', logout);
    $('#register').on('submit', register);
    userWelcome();
    loadAllItems();
});

function login(e) {
    e.preventDefault();
    $.post('https://cs341group4.tk/User/Authenticate', $('#login').serialize())
    .done(function(data) {
        $.cookie('token', data.token);
        window.location.href = baseURL + "/";
    })
    .fail(function(data){
        $('#error').html(data.responseJSON.message);
    });
}

function register(e) {
    e.preventDefault();
    var fields = $('#register').serialize();
    if($('input[name=type]:checked', '#register').val() == "admin") {
        fields += "&token=" + $.cookie('token');
    }
    $.post('https://cs341group4.tk/User/Create', fields)
    .done(function(data) {
        $('#message').html(data.message);
        $('#register').remove();
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}

function userWelcome() {
    if($('#userWelcome').length) {
        if($.cookie('token') != undefined) {
            $.post('https://cs341group4.tk/User/Get', {token: $.cookie('token')})
            .done(function(data) {
                $('#userWelcome').html("You are logged in as " + data.name);
                $('#login').prop('id', 'newId').html("Logout").on('click', logout);
            });
        }
    }
}

function logout(e) {
    $.removeCookie('token');
    location.reload();
}

function loadAllItems() {
    if($('#products').length) {
        $.get('https://cs341group4.tk/Product/GetAll')
        .done(function(data){
            $('#message').html("");
            itemList(data.products);
        })
        .fail(function(data){
            $('#message').html(data.responseJSON.message);
        });
    }
}

function itemList(items) {
    items.forEach(function(item) {
        $('#products').append('<li><a href="' + baseURL + '/item.html?id=' + item.id + '">' 
            + '<img src="' + item.image + '" class="productImg"/>'
            + item.name 
            + '</a></li>');
    });
}

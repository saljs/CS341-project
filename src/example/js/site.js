//base page
var baseURL = "/example";

$( document ).ready(function() {
    //add event listeners
    $('#login').on('submit', login);
    $('#logout').on('click', logout);
    $('#register').on('submit', register);
    $('#newItem').on('submit', addItem);
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

function addItem(e) {
    e.preventDefault();
    var fields = $('#newItem').serialize();
    fields += "&token=" + $.cookie('token');
    $.post('https://cs341group4.tk/Product/Create', fields)
    .done(function(data) {
        $('#message').html(data.message);
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
                $('#loginButton').remove();
                $('#userWelcome').after('<button id="logout">Logout</button>');
                $('#logout').on('click', logout);
                if(data.type == "admin") {
                    $('#logout').after('<br/><a href="/example/admin.html">Admin page</a>');
                }
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
        $.post('https://cs341group4.tk/Product/GetAll')
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
            + '<img src="' + item.image + '" class="productImg" width="400" height ="400"/>'
            + item.name 
            + '</a></li>');
    });
}

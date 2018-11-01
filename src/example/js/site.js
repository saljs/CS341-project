//base page
var baseURL = "/example";

$( document ).ready(function() {
    //add event listeners
    $('#login').on('submit', login);
    $('#logout').on('click', logout);
    $('#register').on('submit', register);
    $('#addPromotion').on('submit', addPromotion);
    $('#createCategory').on('submit', createCategory);
    $('#newItem').on('submit', addItem);
    $('#addToCart').on('click', addToCart);
    $('#checkout').on('submit', checkout);
    $('#paypalSettings').on('submit', paypalEdit);
    userWelcome();
    loadAllItems();
    loadSingleItem();
    loadCart();
    outputCategories();
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

function addPromotion(e) {
    e.preventDefault();
    var fields = $('#addPromotion').serialize();
    fields += "&token=" + $.cookie('token');
    $.post('https://cs341group4.tk/Promotion/Create', fields)
    .done(function(data) {
        $('#message').html(data.message);
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
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
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}

function createCategory(e) {
    e.preventDefault();
    var fields = $('#createCategory').serialize();
    fields += "&token=" + $.cookie('token');
    $.post('https://cs341group4.tk/Category/Create', fields)
    .done(function(data) {
        console.log(data);
        $('#message').html(data.message);
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
        .done(function(data) {
            $('#message').html("");
            itemList(data.products);
        })
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
}

function itemList(items) {
    items.forEach(function(item) {
        $('#products').append('<li><a href="https://cs341group4.tk' + baseURL +'/item.html?id=' + item.id + '">' 
            + '<img src="' + item.image + '" class="productImg" width="400" height ="400"/>'
            + item.name 
            + '</a></li>');
    });
}

function loadSingleItem() {
    if($('#itemName') && $('#itemPrice') && $('#itemQuantity') && $('itemDesc')) {
        var id = $.urlParam('id');
        if(id) {
            $.post('https://cs341group4.tk/Product/Get', {id: id})
            .done(function(data) {
                $('#itemName').html(data.name);
                $('#itemPrice').html(data.price);
                $('#itemQuantity').html(data.quantity);
                $('#itemDesc').html(data.description);
                $('#itemName').append('<img src="' + data.image + '" class="itemImg" width="400" height="400"/>');
            })
            .fail(function(data) {
                $('#itemDesc').html(data.responseJSON.message);
            });
        }
    }
}

function addToCart() {
    if($.cookie('token') != undefined) {
        var id = $.urlParam('id');
        $.post('https://cs341group4.tk/Cart/Add', {token: $.cookie('token'), itemId : id})
        .done(function(data) {
            window.location = baseURL + "/cart.html";
        })
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
    else {
        //TODO: add guest cart
    }
}


function loadCart() {
    if($('#cart').length) {
        if($.cookie('token') != undefined) {
            $.post('https://cs341group4.tk/Cart/Get', {token: $.cookie('token')})
            .done(function(data) {
                $('#message').html("");
                cartList(data.products);
            })
            .fail(function(data) {
                $('#message').html(data.responseJSON.message);
            });
        }
        else {
            //TODO: add guest cart
        }
        updatePrice();
    }
}

function cartList(items) {
    items.forEach(function(item) {
        $('#cart').append('<li><a href="https://cs341group4.tk' + baseURL +'/item.html?id=' + item.id + '">' 
            + '<img src="' + item.image + '" class="productImg" width="400" height ="400"/>'
            + item.name + ' - $' + item.price
            + '</a><br/>Quantity: <input type="number" id="quantity-' + item.id 
            + '" value="' + item.quantity + '" onchange="updateCartItem(' + item.id + ');">'
            + '<br/><button onclick="deleteCartItem(' + item.id + ');">Delete</button>'
            + '</li>');
    });
}

function updatePrice() {
    if($('#totalPrice').length) {
        if($.cookie('token') != undefined) {
            var fields = $('#checkout').serialize();
            fields += "&token=" + $.cookie('token');
            $.post('https://cs341group4.tk/Cart/Get', fields)
            .done(function(data) {
                $('#totalPrice').html(data.total);
            })
            .fail(function(data) {
                $('#message').html(data.responseJSON.message);
            });
        }
        else {
            //TODO: add guest cart
        }
    }
}

function updateCartItem(id) {
    if($.cookie('token') != undefined) {
        var quantity = $('#quantity-' + id).val();
        $.post('https://cs341group4.tk/Cart/Total', 
            {token: $.cookie('token'), itemId : id, quantity: quantity})
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
    else {
        //TODO: add guest cart
    }
}
function deleteCartItem(id) {
    if($.cookie('token') != undefined) {
        $.post('https://cs341group4.tk/Cart/Delete', 
            {token: $.cookie('token'), itemId : id})
        .done(function(data) {
            $('#message').html("Reloading items...");
            $('#cart').html("");
            loadCart();
        })
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
    else {
        //TODO: add guest cart
    }
}

function checkout(e) {
    e.preventDefault();
    var fields = $('#checkout').serialize();
    fields += "&token=" + $.cookie('token');
    $.post('https://cs341group4.tk/Checkout/Complete', fields)
    .done(function(data) {
        window.location = data.payemntPage;
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}

function paypalEdit(e) {
    e.preventDefault();
    var fields = $('#paypalSettings').serialize();
    fields += "&token=" + $.cookie('token');
    $.post('https://cs341group4.tk/Checkout/PayPalEdit', fields)
    .done(function(data) {
        $('#message').html(data.message);
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}

$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return decodeURI(results[1]) || 0;
    }
}

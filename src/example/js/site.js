//base page
var baseURL = "/example";

//site name
var siteName = "cs341group4tk";

/*
 * Functions to run on load
 */
$( document ).ready(function() {
    //add event listeners
    $('#login').on('submit', login);
    $('#logout').on('click', logout);
    $('#register').on('submit', register);

    $('#addPromotion').on('submit', createPromotion);
    $('#createCategory').on('submit', createCategory);
    $('#newItem').on('submit', createItem);
    $('#errordiv').hide();
    $('#paypalSettings').on('submit', paypalEdit);

    $('#addToCart').on('click', addToCart);
    $('#checkout').on('submit', checkout);

    //setup user and site vars
    $.ajaxSetup({
        data: {
            siteName: siteName
        }
    });
    if($.cookie('token') != undefined) {
        $.ajaxSetup({
            data: {
                token: $.cookie('token')
            }
        });
    }
    else {
        $.ajaxSetup({
            data: {
                guestId: getGuestId()
            }
        });
    }

    //run xhr loaders
    userWelcome();
    loadAllItems();
    loadSingleItem();
    loadCart();

});


/****************************************************************************
 * Login and register functions
 ***************************************************************************/

/*
 * Logs in a registered user
 */
function login(e) {
    e.preventDefault();
    $.post('https://cs341group4.tk/User/Authenticate', $('#login').serializeForm())
    .done(function(data) {
        $.cookie('token', data.token);
        window.location.href = baseURL + "/";
    })
    .fail(function(data){
        $('#errordiv').show();
        $('#error').html(data.responseJSON.message);
        setTimeout(function(){ $('#errordiv').hide(); }, 4500);
    });
}
/*
 * Registers a new user
 */
function register(e) {
    e.preventDefault();
    var fields = $('#register').serialize();
    $.post('https://cs341group4.tk/User/Create', fields)
    .done(function(data) {
        $('#message').html(data.message);
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}
/*
 * Sets up user links in the header
 */
function userWelcome() {
    if($('#userWelcome').length) {
        if($.cookie('token') != undefined) {
            $.post('https://cs341group4.tk/User/Get')
            .done(function(data) {
                $('#userWelcome').html(data.name);
                $('#userWelcome').attr("href", "/example/userinfo.html");
                $('#loginButton').remove();
                $('#navEntries').append('<li class="nav-item">'+
                                        '<a id="logout" class="nav-link" href="#">Logout</a>'+
                                        '</li>');
                $('#logout').on('click', logout);
                if(data.type == "admin") {
                    $('#navEntries').prepend('<li class="nav-item">'+
                                             '<a id="adminPage" class="nav-link" href="/example/admin.html">Admin</a>'+
                                             '</li>');
                }
            });
        }
    }
}
/*
 * Logs out a registered user
 */
function logout(e) {
    $.removeCookie('token');
    location.reload();
}
/*
 * Returns a temporary browser-specific guest id
 */
function getGuestId() {
    if($.cookie('guestId') === undefined) {
        var id = "";
        for(var i = 0; i < 20; i++) {
            id += Math.random().toString(36).substr(2, 5);
        }
        $.cookie('guestId', id);
    }
    return $.cookie('guestId');
}
        

/****************************************************************************
 * Site page functions
 ***************************************************************************/

/*
 * Loads a list of categories
 */
function loadCategories(type, id) {
    $.post('https://cs341group4.tk/Category/GetAll')
    .done(function(data) {
        var cat;
        for(var category in data.categories) {
            if(type === 'cattable')
                $('#' + id).append("<tr>" +
                    "<th scope='row'>" + category + "</th>" +
                    "<td>" + data.categories[category] + "</td>" +
                    "</tr>");
            else if(type === 'catcheck') {
                cat = data.categories[category];
                $('#' + id).append("<div class='form-check'>" +
                    "<input type='checkbox' class='form-check-input' name='categories'" + "value='"+ cat +"'/>" +
                    "<label class='form-check-label'>" + cat + "</label>" +
                    "</div>");
            }
            else if(type === 'navbar') {
                cat = data.categories[category];
                $('#' + id).append("<a href='https://cs341group4.tk" + baseURL + "/store.html?category=" + cat + "' class='list-group-item'>" + cat + "</a>")
            }
        }
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}
/*
 * Loads all items using the given parameters
 */
function loadAllItems() {
    if ($('#products').length) {
        $.get('https://cs341group4.tk/Product/GetAll' + window.location.search)
            .done(function (data) {
                $('#message').html("");
                itemList(data.products);
                console.log(data.products);
            })
            .fail(function (data) {
                $('#message').html(data.responseJSON.message);
            });
    }
    if($('#categoryField').length && $.urlParam('category')) {
        $('#categoryField').val($.urlParam('category'));
    }
}
/*
 * Inserts a list of items to page
 */
function itemList(items) {
    items.forEach(function(item) {
        var url = 'https://cs341group4.tk' + baseURL + '/item.html?id=' + item.id;

        var fileName = location.href.split("/").slice(-1);
        if(fileName[0] === 'index.html') {
            $(`#products`).append('' +
                '<div class="col-lg-3 col-md-6 mb-4">'+'' +
                '<div class="card">'+
                '<a href="'+url+'">' +
                '<img class="card-img-top" src="'+item.image+'" alt="">' +
                '</a>'+
                '<div class="card-body">'+
                '<h4 class="card-title">'+
                '<a href="'+url+'">'+item.name+'</a>'+
                '</h4>'+
                '<h5>$'+item.price+'</h5>'+
                '<p class="card-text" style="text-align:left">'+item.description+'</p>'+
                '</div>'+
                '</div>'+
                '</div>');

        }
        else {
            $(`#products`).append('' +
                '<div class="col-lg-4 col-md-6 mb-4">'+'' +
                '<div class="card h-100">'+
                '<a href="'+url+'">' +
                '<img class="card-img-top" src="'+item.image+'" alt="">' +
                '</a>'+
                '<div class="card-body">'+
                '<h4 class="card-title">'+
                '<a href="'+url+'">'+item.name+'</a>'+
                '</h4>'+
                '<h5>$'+item.price+'</h5>'+
                '<p class="card-text">'+item.description+'</p>'+
                '</div>'+
                '</div>'+
                '</div>');

        }
    });
}

/****************************************************************************
 * Item page functions
 ***************************************************************************/

/*
 * Sets up a single item page
 */
function loadSingleItem() {
    if($('#itemName') && $('#itemPrice') && $('#itemQuantity') && $('itemDesc')) {
        var id = $.urlParam('id');
        if(id) {
            $.post('https://cs341group4.tk/Product/Get', {id: id})
            .done(function(data) {
                $('#itemName').html(data.name);
                $('#itemPrice').html("$" + data.price);
                $('#itemQuantity').html(data.quantity + " left");
                $('#itemDesc').html(data.description);
                $('#itemImg').attr("src",data.image);
            })
            .fail(function(data) {
                $('#itemDesc').html(data.responseJSON.message);
            });
        }
    }
}
/*
 * Adds an item to the cart
 */
function addToCart() {
    if($.cookie('token') != undefined) {
        var id = $.urlParam('id');
        $.post('https://cs341group4.tk/Cart/Add', {itemId : id, itemQuantity: $('#quantity').val()})
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

/****************************************************************************
 * Cart page functions
 ***************************************************************************/

/*
 * Loads cart items
 */
function loadCart() {
    if($('#cart').length) {
        if($.cookie('token') != undefined) {
            $.post('https://cs341group4.tk/Cart/Get')
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
/*
 * Inserts a list of cart items to page
 */
function cartList(items) {
    items.forEach(function(item) {
      $('#cart').prepend('<tr><td><img width="50" height="50" src="'+item.image+'" /> </td>'+
                         '<td>'+item.name+'</td>'+
                         '<td>In stock</td>'+
                         '<td><input id="quantity-'+item.id+'" class="form-control" type="number" value="'+item.quantity+'" onchange="updateCartItem('+item.id+');"/></td>'+
                         '<td class="text-right">$'+item.price+'</td>'+
                         '<td class="text-right">'+
                         '<button class="btn btn-sm btn-danger" onclick="deleteCartItem('+item.id+');">'+
                         '<i class="fa fa-trash"></i> </button> </td></tr>');
    });
    updatePrice();
}
/*
 * Gets the updated price for the cart
 */
function updatePrice() {
    if($('#totalPrice').length) {
        if($.cookie('token') != undefined) {
            var fields = $('#promocode').serialize();
            $.post('https://cs341group4.tk/Cart/Total', fields)
            .done(function(data) {
                $('#totalPrice').html('$'+data.total+'.00');
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
/*
 * Updates the quantity of an item in the cart
 */
function updateCartItem(id) {
    var quantity = $('#quantity-' + id).val();
    if($.cookie('token') != undefined) {
        $.post('https://cs341group4.tk/Cart/Update', 
            {itemId : id, quantity: quantity})
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
    else {
        //TODO: add guest cart
    }
    updatePrice();
}
/*
 * Removes an item from the cart
 */
function deleteCartItem(id) {
    if($.cookie('token') != undefined) {
        $.post('https://cs341group4.tk/Cart/Delete', 
            {itemId : id})
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
    updatePrice();
}
/*
 * Removes all items from the cart
 */
function emptyCart(){
    if($.cookie('token') != undefined) {
        $.post('https://cs341group4.tk/Cart/DeleteAll')
        .done(function(data) {
            $('#message').html("Reloading items...");
            $('#cart').html("");
            loadCart();
            window.location.href = baseURL + "/";
        })
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
    else {
        //TODO: add guest cart
    }
}
/*
 * Begins checkout process
 */
function checkout(e) {
    e.preventDefault();
    var fields = $('#checkout').serialize();
    if($.cookie('token') != undefined) {
        $.post('https://cs341group4.tk/Checkout/Complete', fields)
        .done(function(data) {
            window.location = data.payemntPage;
        })
        .fail(function(data){
            $('#message').html(data.responseJSON.message);
        });
    }
    else {
        //TODO: add guest cart
    }
}

/****************************************************************************
 * Admin functions
 ***************************************************************************/

/*
 * Creates a new promotion
 */
function createPromotion() {
    let data = $('#addPromotion').serializeArray();
    let string = "?";
    let cats = "categories=";
    data.forEach(function(e) {
        if(e.name === "categories")
            cats += e.value + ",";
        else {
            string += e.name + "=" + e.value + "&";
        }
    });

    cats = cats.slice(0, -1)
    string += cats;
    
    $.post('https://cs341group4.tk/Promotion/Create', string)
    .done(function(data) {
        alert(data.message);
    })
    .fail(function(data){
        alert(data.message);
    });

}
/*
 * Creates a new category
 */
function createCategory(e) {
    e.preventDefault();
    var fields = $('#createCategory').serialize();
    $.post('https://cs341group4.tk/Category/Create', fields)
    .done(function(data) {
        console.log(data);
        $('#message').html(data.message);
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}
/*
 * Creates a new item
 */
function createItem(e) {
    e.preventDefault();
    
    var fields = $('#newItem').serialize();
    alert("debug3 : "+fields);
    $.post('https://cs341group4.tk/Product/Create', fields)
    .done(function(data) {
        $('#message').html(data.message);
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}
/*
 * Edits the site's paypal details
 */
function paypalEdit(e) {
    e.preventDefault();
    var fields = $('#paypalSettings').serialize();
    $.post('https://cs341group4.tk/Checkout/PayPalEdit', fields)
    .done(function(data) {
        $('#message').html(data.message);
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}

/****************************************************************************
 * Utility functions
 ***************************************************************************/

/*
 * Returns a URL param
 */
$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return decodeURI(results[1]) || 0;
    }
};
/*
 * Converts form data to JSON
 */
$.serializeForm = function(){
   var o = {};
   var a = this.serializeArray();
   $.each(a, function() {
       if (o[this.name]) {
           if (!o[this.name].push) {
               o[this.name] = [o[this.name]];
           }
           o[this.name].push(this.value || '');
       } else {
           o[this.name] = this.value || '';
       }
   });
   return o;
};


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
    $('#removeCategory').on('submit', removeCategory);
    $('#newItem').on('submit', createItem);
    $('#paypalSettings').on('submit', paypalEdit);
    $('#errordiv').hide();

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
    loadAdmin();
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
    var fields = $('#register').serializeForm();
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
 * Loads a list of promotions
 */
function loadPromotions(id) {
    $.post('https://cs341group4.tk/Promotion/GetAll')
    .done(function(data) {
        for(var p in data.promotions) {
            var dataS = data.promotions[p];

            if(id === 'promotionList') {
                $('#' + id).append("<tr>" +
                    "<th scope='row'>" + p + "</th>" +
                    "<td>" + dataS.name + "</td>" +
                    "<td>" + dataS.code + "</td>" +
                    "<td>" + dataS.type + "</td>" +
                    "<td>" + dataS.percent + "</td>" +
                    "<td>" + new Date(dataS.startdate*1000).toLocaleString() + "</td>" +
                    "<td>" + new Date(dataS.enddate*1000).toLocaleString() + "</td>" +
                    "<td>" + dataS.items + "</td>" +
                    "<td>" + dataS.categories + "</td>" +
                "</tr>");
            }
        }
    }).fail(function(data) {

    });
}
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
    } if($('#myDropdown').length) {
        $.get('https://cs341group4.tk/Product/GetAll' + window.location.search)
            .done(function (data) {
                $('#message').html("");
                removeItemList(data.products);
                console.log(data.products);
            })
            .fail(function (data) {
                $('#message').html(data.responseJSON.message);
            });
    }


    if($('#categoryField').length && $.urlParam('category')) {
        $('#categoryField').val($.urlParam('category'));
    }
    if($('#categoriesNavBar').length) {
        loadCategories('navbar', 'categoriesNavBar');
    }
}

function removeItemList(items) {
    items.forEach(function(item) {
        var url = 'https://cs341group4.tk' + baseURL + '/item.html?id=' + item.id;
        var a = document.createElement("a");
        a.value = item.name;
        a.textContent = item.name;
        a.onclick = function() { loadItemPreview(item, url);};

        var dropdown = document.getElementById("myDropdown");
        dropdown.appendChild(a);
    });
}

function loadItemPreview(item, url) {
    $('previewList').innerHTML = "";
    $('#previewList').append(
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

/*
 * Inserts a list of items to page
 */
function itemList(items) {
    items.forEach(function(item) {
        var url = 'https://cs341group4.tk' + baseURL + '/item.html?id=' + item.id;

        var fileName = location.href.split("/").slice(-1);
        if(fileName[0] === 'index.html') {
            $(`#products`).append('' +
                '<div class="col-lg-3 col-md-6 mb-4 animated fadeInUp">'+'' +
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
    var endpoint = 'https://cs341group4.tk/GuestCart/Add';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/Add';
    }

    var id = $.urlParam('id');
    $.post(endpoint, {itemId : id, itemQuantity: $('#quantity').val()})
    .done(function(data) {
        window.location = baseURL + "/cart.html";
    })
    .fail(function(data) {
        $('#message').html(data.responseJSON.message);
    });
}

/****************************************************************************
 * Cart page functions
 ***************************************************************************/

/*
 * Loads cart items
 */
function loadCart() {
    if($('#cart').length) {
        var endpoint = 'https://cs341group4.tk/GuestCart/Get';
        if($.cookie('token') != undefined) {
            endpoint = 'https://cs341group4.tk/Cart/Get';
        }
        $.post(endpoint)
        .done(function(data) {
            $('#message').html("");
            cartList(data.products);
        })
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
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
        var endpoint = 'https://cs341group4.tk/GuestCart/Total';
        if($.cookie('token') != undefined) {
            endpoint = 'https://cs341group4.tk/Cart/Total';
        }
        var fields = $('#promocode').serializeForm();
        $.post(endpoint, fields)
        .done(function(data) {
            $('#totalPrice').html('$'+data.total+'.00');
        })
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
}
/*
 * Updates the quantity of an item in the cart
 */
function updateCartItem(id) {
    var endpoint = 'https://cs341group4.tk/GuestCart/Update';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/Update';
    }
    var quantity = $('#quantity-' + id).val();
    $.post(endpoint, {itemId : id, quantity: quantity})
    .done(function(data) {
        updatePrice();
    })
    .fail(function(data) {
        $('#message').html(data.responseJSON.message);
    });
}
/*
 * Removes an item from the cart
 */
function deleteCartItem(id) {
    var endpoint = 'https://cs341group4.tk/GuestCart/Delete';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/Delete';
    }
    $.post(endpoint, {itemId : id})
    .done(function(data) {
        $('#message').html("Reloading items...");
        $('#cart').html("");
        loadCart();
        updatePrice();
    })
    .fail(function(data) {
        $('#message').html(data.responseJSON.message);
    });
}
/*
 * Removes all items from the cart
 */
function emptyCart() {
    var endpoint = 'https://cs341group4.tk/GuestCart/DeleteAll';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/DeleteAll';
    }
    $.post(endpoint)
    .done(function(data) {
        $('#message').html("Reloading items...");
        $('#cart').html("");
        loadCart();
        window.location.href = baseURL + "/index.html";
    })
    .fail(function(data) {
        $('#message').html(data.responseJSON.message);
    });
}
/*
 * Begins checkout process
 */
function checkout(e) {
    e.preventDefault();
    var endpoint = 'https://cs341group4.tk/GuestCheckout/Complete';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Checkout/Complete';
    }
    var fields = $('#checkout').serializeForm();
    $.post(endpoint, fields)
    .done(function(data) {
        window.location = data.payemntPage;
    })
    .fail(function(data){
        $('#message').html(data.responseJSON.message);
    });
}

/****************************************************************************
 * Admin functions
 ***************************************************************************/

/*
 * Loads admin page data
 */
function loadAdmin() {

    if($('#categoryList').length) {
        loadCategories('cattable', 'categoryList');
    }
    if($('#newPromoCatList').length) {
        loadCategories('catcheck', 'newPromoCatList');
    }
    if($('#newItemCatList').length) {
        loadCategories('catcheck', 'newItemCatList');
    }
    if($('#promotionList').length) {
        loadPromotions('promotionList');
    }
}
/*
 * Creates a new promotion
 */
function createPromotion(e) {
    e.preventDefault();
    var data = $('#addPromotion').serializeForm();
    $.post('https://cs341group4.tk/Promotion/Create', data)
    .done(function(data) {
        location.reload();
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
    var fields = $('#createCategory').serializeForm();
    $.post('https://cs341group4.tk/Category/Create', fields)
    .done(function(data) {
        alert(data.message);
        location.reload();
    })
    .fail(function(data){
        alert(data.responseJSON.message);
    });
}

/*
 * Removes an existing category
 */
function removeCategory(e) {
    e.preventDefault();
    var fields = $('#removeCategory').serializeForm();
    $.post('https://cs341group4.tk/Category/Delete', fields)
        .done(function(data) {
            alert(data.message);
            location.reload();
        })
        .fail(function(data){
            alert(data.responseJSON.message);
        });
}

/*
 * Creates a new item
 */
function createItem(e) {
    e.preventDefault();
    
    var fields = $('#newItem').serializeForm();
    alert("debug3 : "+fields);
    $.post('https://cs341group4.tk/Product/Create', fields)
    .done(function(data) {
        location.reload();
        alert(data.message);
    })
    .fail(function(data){
        alert(data.responseJSON.message);
    });
}
/*
 * Edits the site's paypal details
 */
function paypalEdit(e) {
    e.preventDefault();
    var fields = $('#paypalSettings').serializeForm();
    $.post('https://cs341group4.tk/Checkout/PayPalEdit', fields)
    .done(function(data) {
        alert(data.message);
    })
    .fail(function(data){
        alert(data.responseJSON.message);
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
$.fn.serializeForm = function(){
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


//base page
var baseURL = "/example";

//site name
var siteName = "cs341group4tk";

/*
 * Functions to run on load
 */
$( document ).ready(function() {

    // Event Listeners
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

    // Setup user and site variables
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

    // Run XHR Loaders
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

    // Make a post request to the User controller's Authenticate function.
    // Pass it the variables from the #login form.
    $.post('https://cs341group4.tk/User/Authenticate', $('#login').serializeForm())
        .done(function(data) {

            // Pass the cookie for authentication.
            $.cookie('token', data.token);

            // Set the current page (refreshes the page).
            window.location.href = baseURL + "/";

        })
        .fail(function(data){

            // Show the error mesage, then hide it after 4.5 seconds.
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

    // If there's nothing in the #userWelcome element
    if($('#userWelcome').length) {

        // Make sure the token is defined
        if($.cookie('token') != undefined) {
            $.post('https://cs341group4.tk/User/Get')
                .done(function(data) {

                    // Edit the #userwelcome element to show that the user is logged in.
                    $('#userWelcome').html(data.name);
                    $('#userWelcome').attr("href", "/example/userinfo.html");

                    // Remove the login button
                    $('#loginButton').remove();

                    // Change their navbar
                    $('#navEntries').append('<li class="nav-item">'+
                        '<a id="logout" class="nav-link" href="#">Logout</a>'+
                        '</li>');

                    // Add an onclick event listener for the logout button
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

    // Delete their login token from the cookies
    $.removeCookie('token');

    // Reload the page
    location.reload();

}
/*
 * Returns a temporary browser-specific guest id
 */
function getGuestId() {

    // Make sure they don't have an existing guestId cookie.
    if($.cookie('guestId') === undefined) {

        // Generate a random token
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

            // Loop through all the promotions
            for(var p in data.promotions) {
                var dataS = data.promotions[p];

                // Add them to the #promotionList table.
                if(id === 'promotionList') {
                    $('#' + id).append("<tr>" +
                        "<th scope='row'>" + p + "</th>" +
                        "<td>" + dataS.name + "</td>" +
                        "<td>" + dataS.code + "</td>" +
                        // "<td>" + new Date(dataS.startdate*1000).toLocaleString() + "</td>" +
                        "<td>" + new Date(dataS.enddate*1000).toLocaleString() + "</td>" +
                        "<td>" + dataS.items + "</td>" +
                        "</tr>");
                }
            }
        }).fail(function(data) {

    });
}
/*
 * Loads a list of categories.
 */
function loadCategories(type, id) {
    $.post('https://cs341group4.tk/Category/GetAll')
        .done(function(data) {
            var cat;

            // Loop through all the categories, and depending where the request came from,
            // append a certain formatting of the categories.
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
 * Loads all items
 */
function loadAllItems() {

    // Get request based on the page parameters, i.e example/index.html?id=9
    if ($('#products').length) {
        $.get('https://cs341group4.tk/Product/GetAll' + window.location.search)
            .done(function (data) {

                // Delete any content in #message.
                $('#message').html("");

                // Pass the product list to the itemList function.
                itemList(data.products);

            })
            .fail(function (data) {
                $('#message').html(data.responseJSON.message);
            });
    } if($('#myDropdown').length) {
        $.get('https://cs341group4.tk/Product/GetAll' + window.location.search)
            .done(function (data) {

                // Delete any content in #message.
                $('#message').html("");

                // Pass the product list to the removeItemList function.
                removeItemList(data.products);

            })
            .fail(function (data) {
                $('#message').html(data.responseJSON.message);
            });
    }

    if($('#categoryField').length && $.urlParam('category')) {
        $('#categoryField').val($.urlParam('category'));
    }

    // Load the categories nav bar
    if($('#categoriesNavBar').length) {
        loadCategories('navbar', 'categoriesNavBar');
    }
}

/*
 * Adds all existing items to the remove
 * dropdown menu in the remove item tab in admin.html.
 */
function removeItemList(items) {

    // Loop through the given items
    items.forEach(function(item) {
        var url = 'https://cs341group4.tk' + baseURL + '/item.html?id=' + item.id;

        // Create an <a> element based on the item.
        var a = document.createElement("a");
        a.value = item.name;
        a.textContent = item.name;
        a.onclick = function() {

            // Clear the preview list
            document.getElementById("previewList").innerHTML = "";

            // Load a preview of the item they selected
            loadItemPreview(item, url);

            // Toggle the dropdown menu
            showRemoveList();

            // Edit the button to remove this item on clicked.
            changeRemoveButton(item);

        };

        // Add the <a> to the dropdown.
        var dropdown = document.getElementById("myDropdown");
        dropdown.appendChild(a);

    });

}

/*
 * Changes the onclick function of the remove button to be the
 * current item the user selected.
 */
function changeRemoveButton(item) {

    // Get the remove button based on id.
    let removeButton = document.getElementById("removeItemButton");
    removeButton.onclick = function() {
        let endpoint = "https://cs341group4.tk/Product/Delete";

        // Delete the given item.
        $.post(endpoint, {itemId : item.id})
            .done(function(data) {

                // Reload the page.
                location.reload();

            })
            .fail(function(data) {
                console.log(data);
            });
    }

}

/*
 * Loads an item's card and links to the item's main page.
 */
function loadItemPreview(item, url) {
    $('#previewList').append(
        '<div class="mb-4">'+'' +
        '<div class="card h-100">'+
        '<a href="'+url+'" target="_blank">' +
        '<img class="card-img-top" style="width:50%" src="'+item.image+'" alt="">' +
        '</a>'+
        '<div class="card-body">'+
        '<h4 class="card-title">'+
        '<a href="'+url+'">'+item.name+'</a>'+
        '</h4>'+
        '</div>'+
        '</div>'+
        '</div>');
}

/*
 * Inserts a list of items to page
 */
function itemList(items) {

    // Loop through all the items.
    items.forEach(function(item) {

        // Generate a url based on that item's id
        var url = 'https://cs341group4.tk' + baseURL + '/item.html?id=' + item.id;
        var fileName = location.href.split("/").slice(-1);

        // If we're at index.html
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

    // Make sure these id's exist
    if($('#itemName') && $('#itemPrice') && $('#itemQuantity') && $('itemDesc')) {

        // Make sure theres an item id in the link.
        var id = $.urlParam('id');
        if(id) {

            // Get the given product
            $.post('https://cs341group4.tk/Product/Get', {id: id})
                .done(function(data) {

                    // Populate the fields based on the item data.
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

    // If they're a guest
    var endpoint = 'https://cs341group4.tk/GuestCart/Add';

    // If they have a token (a.k.a they're logged in
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/Add';
    }

    // Get the item id from the url
    var id = $.urlParam('id');
    $.post(endpoint, {itemId : id, itemQuantity: $('#quantity').val()})
        .done(function(data) {

            // Set the current page to cart.html because
            // they added an item to their cart
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

    // If the #cart element exists
    if($('#cart').length) {

        // Set the endpoint based on if they're a guest or
        // if they're logged in.
        var endpoint = 'https://cs341group4.tk/GuestCart/Get';
        if($.cookie('token') != undefined) {
            endpoint = 'https://cs341group4.tk/Cart/Get';
        }

        $.post(endpoint)
            .done(function(data) {

                // Clear the message and list the items in the users cart
                $('#message').html("");
                cartList(data.products);
            })
            .fail(function(data) {
                $('#message').html(data.responseJSON.message);
            });

        // Update the price to account for discounts, etc.
        updatePrice();
    }
}
/*
 * Inserts a list of cart items to page
 */
function cartList(items) {

    // Loop through the items
    items.forEach(function(item) {

        // Add the items to the cart table.
        $('#cart').prepend('<tr><td><img width="50" height="50" src="'+item.image+'" /> </td>'+
            '<td>'+item.name+'</td>'+
            '<td>In stock</td>'+
            '<td><input id="quantity-'+item.id+'" class="form-control" type="number" value="'+item.quantity+'" onchange="updateCartItem('+item.id+');"/></td>'+
            '<td class="text-right">$'+item.price+'</td>'+
            '<td class="text-right">'+
            '<button class="btn btn-sm btn-danger" onclick="deleteCartItem('+item.id+');">'+
            '<i class="fa fa-trash"></i> </button> </td></tr>');
    });

    // Uppdate the price to account for discounts, etc.
    updatePrice();

}
/*
 * Gets the updated price for the cart
 */
function updatePrice() {

    // Make sure the totalPrice element exists
    if($('#totalPrice').length) {

        // Set the endpoint based on if they're a guest or
        // if they're logged in.
        var endpoint = 'https://cs341group4.tk/GuestCart/Total';
        if($.cookie('token') != undefined) {
            endpoint = 'https://cs341group4.tk/Cart/Total';
        }

        // Get the fields from the #promocode form.
        var fields = $('#promocode').serializeForm();
        $.post(endpoint, fields)
            .done(function(data) {

                // Change #totalPrice to the new total
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

    // Set the endpoint based on if they're a guest or
    // if they're logged in.
    var endpoint = 'https://cs341group4.tk/GuestCart/Update';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/Update';
    }

    // Get the quantity value based on id.
    var quantity = $('#quantity-' + id).val();
    $.post(endpoint, {itemId : id, quantity: quantity})
        .done(function(data) {

            // Update the price
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

    // Set the endpoint based on if they're a guest or
    // if they're logged in.
    var endpoint = 'https://cs341group4.tk/GuestCart/Delete';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/Delete';
    }

    $.post(endpoint, {itemId : id})
        .done(function(data) {

            // Set the message
            $('#message').html("Reloading items...");

            // Empty the cart element
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

    // Set the endpoint based on if they're a guest or
    // if they're logged in.
    var endpoint = 'https://cs341group4.tk/GuestCart/DeleteAll';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Cart/DeleteAll';
    }

    $.post(endpoint)
        .done(function(data) {

            // Set the message
            $('#message').html("Reloading items...");

            // Empty the cart element
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

    // Set the endpoint based on if they're a guest or
    // if they're logged in.
    var endpoint = 'https://cs341group4.tk/GuestCheckout/Complete';
    if($.cookie('token') != undefined) {
        endpoint = 'https://cs341group4.tk/Checkout/Complete';
    }

    // Get the fields in the #checkout form.
    var fields = $('#checkout').serializeForm();
    $.post(endpoint, fields)
        .done(function(data) {

            //TODO: TYPO?
            // Set the location to the payment page.
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

    // Load the various category and promotion lists in admin.html
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

    // Get the fields from the form.
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

    // Get the fields from the form.
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

    // Get the fields from the form.
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

    // Get the fields from the form.
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

    // Get the fields from the form.
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


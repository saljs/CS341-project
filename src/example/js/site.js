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
    $('#errordiv').hide();
    userWelcome();
    loadAllItems();
    loadSingleItem();
    loadCart();

});

function login(e) {
    e.preventDefault();
    $.post('https://cs341group4.tk/User/Authenticate', $('#login').serialize())
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

function addPromotion() {
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
    //
    // //?name=November+Discount&code=NOV31&typeRadio=bogo&percent=30&startDate=2018-11-01T00%3A00&endDate=2018-11-30T23%3A59&items=Microwave%2C1%2C3%2C4&categories=Electronics&categories=Office&categories=Cosmetics&categories=Toys#promotionTab
    // var request = new XMLHttpRequest();
    // request.open('POST', 'https://cs341group4.tk/Promotion/Create' + string, true);
    // request.onload = function () {
    //     // Begin accessing JSON data here
    //     var data = JSON.parse(this.response);
    //     if (request.status >= 200 && request.status < 400) {
    //         alert(data);
    //     }
    //
    // };
    //
    // request.send();

    $.post('https://cs341group4.tk/Product/Create', string)
    .done(function(data) {
        alert(data.message);
    })
    .fail(function(data){
        alert(data.message);
    });

}

function loadCategories(type, id) {
    var request = new XMLHttpRequest();

    request.open('GET', 'https://cs341group4.tk/Category/GetAll', true);
    request.onload = function () {
        let cat;
        // Begin accessing JSON data here
        var data = JSON.parse(this.response);
        if (request.status >= 200 && request.status < 400) {
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
        }
    };

    request.send();
}

function loadCategoryItems(category) {

    // Remove all products from #products
    $('#products').html("");

    var request = new XMLHttpRequest();
    request.open('GET', 'https://cs341group4.tk/Product/GetAll?category=' + category, true);
    request.onload = function () {

        let data = JSON.parse(this.response);
        console.log(data.products);
        itemList(data.products);

    };

    request.send();

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
    alert("debug");
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
    alert("debug3 : "+fields);
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
                $('#userWelcome').html(data.name);
                $('#userWelcome').attr("href", "/example/userinfo.html");
                $('#loginButton').remove();
               // $('#userWelcome').after('<button id="logout">Logout</button>');
                $('#navEntries').append('<li class="nav-item">'+
                   '<a id="logout" class="nav-link" href="/example/admin.html">admin</a>'+
                    '</li>');
                $('#logout').on('click', logout);
                if(data.type == "admin") {
                    //$('#logout').after('<br/><a href="/example/admin.html">Admin page</a>');
                    $('#navEntries').append('<li class="nav-item">'+
                   '<a id="adminPage" class="nav-link" href="#">logout</a>'+
                    '</li>');
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

    // If they didn't specify a category, show all items
    let category = $.urlParam('category');
    if(!category) {
        if ($('#products').length) {
            $.post('https://cs341group4.tk/Product/GetAll')
                .done(function (data) {
                    $('#message').html("");
                    itemList(data.products);
                    console.log(data.products);
                })
                .fail(function (data) {
                    $('#message').html(data.responseJSON.message);
                });
        }
    } else {

        loadCategoryItems(category);

    }

}

function itemList(items) {
    items.forEach(function(item) {
        //$('#products').append('<li><a href="https://cs341group4.tk' + baseURL +'/item.html?id=' + item.id + '">' 
        //    + '<img src="' + item.image + '" class="productImg" width="400" height ="400"/>'
        //    + item.name 
        //    + '</a></li>');


        let url = 'https://cs341group4.tk' + baseURL + '/item.html?id=' + item.id;

        // What .html we're at
        let fileName = location.href.split("/").slice(-1);
        console.log(fileName);

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
                // '<div class="card-footer">'+
                //     '<a href="'+url+'" class="btn btn-primary">Find Out More!</a>'+
                // '</div>'+
                '</div>'+
                '</div>');

        } else {

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
                // '<div class="card-footer">'+
                //     '<a href="'+url+'" class="btn btn-primary">Find Out More!</a>'+
                // '</div>'+
                '</div>'+
                '</div>');

        }
    });
}


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

function addToCart() {
    if($.cookie('token') != undefined) {
        var id = $.urlParam('id');
        $.post('https://cs341group4.tk/Cart/Add', {token: $.cookie('token'), itemId : id, itemQuantity: $('#quantity').val()})
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
       // $('#cart').append('<li><a href="https://cs341group4.tk' + baseURL +'/item.html?id=' + item.id + '">' 
       //     + '<img src="' + item.image + '" class="productImg" width="400" height ="400"/>'
       //     + item.name + ' - $' + item.price
       //     + '</a><br/>Quantity: <input type="number" id="quantity-' + item.id 
       //     + '" value="' + item.quantity + '" onchange="updateCartItem(' + item.id + ');">'
       //     + '<br/><button onclick="deleteCartItem(' + item.id + ');">Delete</button>'
       //     + '</li>');
      $('#cart').prepend('<tr><td><img width="50" height="50" src="'+item.image+'" /> </td>'+
                 '<td>'+item.name+'</td>'+
                 '<td>In stock</td>'+
                 '<td><input id="quantity-'+item.id+'" class="form-control" type="number" value="'+item.quantity+'" oninput="updateCartItem('+item.id+');updatePrice();"/></td>'+
                 '<td class="text-right">$'+item.price+'</td>'+
                 '<td class="text-right">'+
                 '<button class="btn btn-sm btn-danger" onclick="deleteCartItem('+item.id+');updatePrice();">'+
                 '<i class="fa fa-trash"></i> </button> </td></tr>');
    });
    $('#cart').append('<tr><td></td><td></td><td></td><td></td><td><strong>Promotion Code</strong></td>'+
                       '<td class="text-right"><input type="text" name="code" id="addPromotion" onchange="updatePrice();"/>'+
                      '</td></tr><tr><td></td><td></td><td></td><td></td><td><strong>Total</strong></td>'+
                      '<td class="text-right"><strong><span id="totalPrice"></span></strong></td></tr>'+
                     '<tr><td></td><td></td><td></td><td></td><td><strong>Empty Cart</strong></td>'+
                       '<td class="text-right"><button class="btn btn-sm btn-danger"'+
                      'onclick="emptyCart();">'+
                 '<i class="fa fa-trash"></i> </button> </td></tr>'+
                      '</td></tr>');
        updatePrice();
}

function updatePrice() {
    if($('#totalPrice').length) {
        if($.cookie('token') != undefined) {
            var fields = $('#checkout').serialize();
            fields += "&token=" + $.cookie('token');
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

function updateCartItem(id) {
    if($.cookie('token') != undefined) {
        var quantity = $('#quantity-' + id).val();
        $.post('https://cs341group4.tk/Cart/Update', 
            {token: $.cookie('token'), itemId : id, quantity: quantity})
        .fail(function(data) {
            $('#message').html(data.responseJSON.message);
        });
    }
    else {
        //TODO: add guest cart
    }
    updatePrice();
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
function emptyCart(){
    if($.cookie('token') != undefined) {
        $.post('https://cs341group4.tk/Cart/DeleteAll', 
            {token: $.cookie('token')})
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
};

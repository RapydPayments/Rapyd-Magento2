require(['jquery','domReady!'], function ($) {
    document.getElementById("title").innerHTML=rapyd_data['title'];
    document.getElementById("message").innerHTML=rapyd_data['message'];
    document.getElementById("order_id").innerHTML='Your order id: ' +rapyd_data['order_id'];
    var button = document.getElementById("shopping");
    button.onclick = function(event) {
        location.href = rapyd_data["shopping_url"];
    }
});

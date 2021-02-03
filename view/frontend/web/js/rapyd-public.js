require(['jquery','domReady!'], function ($) {
    var RAPYD_TOOLKIT_ORDER_PLACED = 'Your order has been placed, but is not complete';
    var RAPYD_TOOLKIT_THANK_YOU = 'Thank you for your order';
    var RAPYD_TOOLKIT_COMPLETE = 'To complete your payment, please follow the steps below:';

    var RAPYD_TOOLKIT_CLICK_TEXT = 'Click to pay';
    var RAPYD_TOOLKIT_COLOR = 'blue';
    var RAPYD_PAYMENT_TOOLKIT_ERROR='Sorry, something is wrong with your payment. Please go back to recheck payment information and try again.';
    var ERROR_MESSAGE = 'Sorry, something is wrong with your payment. Please go back to recheck payment information and try again.';

    if(rapyd_data.status == "success") {
        loadRapydToolkit(rapyd_data.token);
    }
    else {
        errorFlow(rapyd_data.message)
    }
    function errorFlow(message) {
        hideLoader();
        document.getElementById("rapyd-checkout").innerHTML = message;
    }

    function loadRapydToolkit(token) {
        let checkout = new RapydCheckoutToolkit({
            pay_button_text:  RAPYD_TOOLKIT_CLICK_TEXT,
            pay_button_color: RAPYD_TOOLKIT_COLOR ,
            id: token,
            close_on_complete: true
        });

        window.addEventListener('checkoutOnSuccess', function (event) {
            onSuccessEvent(event);
        });
        window.addEventListener('checkoutOnFailed', function (event) {
            document.getElementById("rapyd-checkout").innerHTML = RAPYD_PAYMENT_TOOLKIT_ERROR;
        });
        window.addEventListener('checkoutOnLoading', function (event) {
            onCheckoutLoading(event);
        });

        checkout.displayCheckout();
    }

    function onSuccessEvent(event) {
        if( event.detail.status.toLowerCase()=="err"){
            showErrorMessage();
            return;
        }

        if(event.detail.status.toLowerCase()=="clo"){
            location.href = rapyd_data["success_url"];
            return;
        }
        if(event.detail.status.toLowerCase()=="act" && event.detail.redirect_url!=""){
            location.href = event.detail.redirect_url;
            return;
        }
        if(event.detail.status.toLowerCase()=="act" && Object.keys(event.detail.textual_codes).length>0){
            createTextualCodes(event.detail.textual_codes);
        }
        if(event.detail.status.toLowerCase()=="act" && Object.keys(event.detail.visual_codes).length>0){
            createVisualCodes(event.detail.visual_codes);
        }
        createInstructions(event.detail.instructions);
        addFinishButton();
    }

    function onCheckoutLoading(event){
        if(event.detail.loading){
            showLoader();
        }else{
            hideLoader();
        }
    }

    function showRapydToolkit() {
        var element = document.getElementById("rapyd-checkout");
        if(element){
            element.style.display="block";
        }
    }

    function hideRapydToolkit(){
        var element = document.getElementById("rapyd-checkout");
        if(element){
            element.style.display="none";
        }
    }

    function showLoader() {
        var element = document.getElementById("rapyd_loader_div");
        if(element){
            element.style.display="block";
        }
    }

    function hideLoader() {
        var element = document.getElementById("rapyd_loader_div");
        if(element){
            element.style.display="none";
        }
    }

    function showErrorMessage() {
        document.getElementById("rapyd-checkout").innerHTML = ERROR_MESSAGE;
    }

    function createVisualCodes(visual_codes) {
        var srcs = Object.values(visual_codes);
        for(var i=0;i<srcs.length;i++){
            var img = document.createElement('img');
            img.src = srcs[i];
            img.style.width="300px";
            img.style.margin="auto";
            addElementToRapydDiv(img);
        }
    }

    function addFinishButton() {
        var button = document.createElement('button');
        button.textContent = 'Finish';
        button.onclick = function() { location.href = rapyd_data["success_url"] };
        addElementToRapydDiv(button);
    }

    function createHeadlineForInstructions() {
        var h = document.createElement('h5');
        h.innerHTML = RAPYD_TOOLKIT_COMPLETE;
        addElementToRapydDiv(h);
    }

    function createHeadlineForCodes() {
        var h = document.createElement('h5');
        h.innerHTML = RAPYD_TOOLKIT_THANK_YOU;
        var h7 = document.createElement('h7');
        h7.innerHTML = RAPYD_TOOLKIT_ORDER_PLACED;
        addElementToRapydDiv(h);
        addElementToRapydDiv(h7);

    }

    function addElementToRapydDiv(element) {
        var rapyd_div = document.getElementById("rapyd-checkout");
        rapyd_div.appendChild(element);
    }

    function createTextualCodes(textual_codes) {
        createHeadlineForCodes();
        var div = document.createElement('div');
        div.style.margin="10px";
        addElementToRapydDiv(div);

        var keys = Object.keys(textual_codes);
        for (var j = 0; j < keys.length; j++) {
            var h9 = document.createElement('h9');
            h9.style.fontWeight="bold";
            h9.innerHTML = keys[j]+":";
            var p = document.createElement('p');
            p.innerHTML = textual_codes[keys[j]];
            div.appendChild(h9);
            div.appendChild(p);
        }
    }

    function createInstructions(instructions) {
        if(!instructions[0] || !instructions[0]["steps"] || instructions[0]["steps"].length==0){
            return;
        }
        createHeadlineForInstructions();
        var ul = document.createElement('ul');
        ul.style.marginLeft = "unset";
        if(instructions[0]["steps"].length==1){
            instructions = instructions[0]["steps"][0];
            var values = Object.values(instructions);
            for(var i=0;i<values.length;i++){
                var li = document.createElement('li');
                li.innerHTML = values[i];
                ul.appendChild(li);
            }
        }else if(instructions[0]["steps"].length>1){
            var steps = instructions[0]["steps"];
            for(var i=0;i<steps.length;i++){
                var li = document.createElement('li');
                li.innerHTML = Object.values(steps[i])[0];
                ul.appendChild(li);
            }
        }
        var rapyd_div = document.getElementById("rapyd-checkout");
        rapyd_div.appendChild(ul);
    }
});

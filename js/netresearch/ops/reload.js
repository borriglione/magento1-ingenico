var reloadCounter = 0;
document.observe('dom:loaded', function () {
    var billaddress = $('billing-address-select');
    if(billaddress != undefined || billaddress != null && reloadCounter == 0) {
         setTimeout(function(){
             fireEvent($('billing-address-select'), 'change');
             reloadCounter = 1;
         }, 100);
    }

    function fireEvent(element, eventName) {
        if (document.createEventObject) {
            var evt = document.createEventObject();
            return element.fireEvent('on'+event,evt)
        } else {
            var evt = document.createEvent('HTMLEvents');
            evt.initEvent(eventName, true, true);
            return !element.dispatchEvent(evt);

        }
    }
});

(function( $ ) {
    var xrpPayment = {
        init: function () {


                //Get form
                if ( $( '#paybutton-form' ).length ) {
                    this.form = $('#paybutton-form');
                    this.email = this.form.data('email');
                    this.amount = this.form.data('amount');
                    this.currency = this.form.data('currency');
                }

                //Set payment button to disabled if the payburner method is visible
                if($( 'div.payment_box.payment_method_payburner' ).is(':visible')){
                    $( '#place_order' ).attr( 'disabled', true);
                }else{
                    $( '#place_order' ).attr( 'disabled', false)
                }

                const self = this;
                /*Set pay button to disabled and start waiting for payments*/
                $('.wc_payment_methods  > li').on( 'click', 'input[name="payment_method"]',function () {
                    self.checkDisablePlaceOrder();
                });

        },

        checkDisablePlaceOrder: function() {

            if (jQuery('.payment_box.payment_method_payburner').is(':visible')) {
                $( '#place_order' ).attr( 'disabled', true);
            }
            else {
                $( '#place_order' ).attr( 'disabled', false);
            }

        },
        checkForXrpPayment: function () {

            this.checkDisablePlaceOrder();
            // let's count to see if all of the required fields have been filled
            const fieldsArray = $('.wc-checkout').serializeArray();
            var countBad = 0;
            fieldsArray.forEach((field)=>{
                if ($('#' + field.name + '_field').length === 1 && $('#' + field.name + '_field.validate-required').length === 1) {
                    if(field.value === '') {
                        countBad += 1;
                    }
                }
            });

            // if the required fields are filled, and the user has clicked the payment button and the payment button has settled
            if (countBad === 0 && $('pay-button').attr('purchaseId') !== '' && $('pay-button').attr('status') === 'SETTLED') {

                $('#paybutton-status').text('Confirming XRP payment status.');
                // make a call to the class-payburner-ajax.php on the backend to validate the status of the payment
                $.ajax({
                    url: wc_payburner_params.wc_ajax_url,
                    type: 'post',
                    data: {
                        action: 'wc_check_for_payment',
                        nounce: wc_payburner_params.nounce,
                        purchaseId: $('pay-button').attr('purchaseId')
                    }
                }).done(function (res) {

                    // if the backend acks the payment has been received
                    if(res.success === true && res.data.match === true){

                        // let's do a final check to make sure the required fields are setup
                        const fieldsArray = $('.wc-checkout').serializeArray();
                        var countBad = 0;
                        fieldsArray.forEach((field)=>{
                            if ($('#' + field.name + '_field').length === 1 && $('#' + field.name + '_field.validate-required').length === 1) {
                                if(field.value === '') {
                                    countBad += 1;
                                }
                            }
                        });

                        // ok everything is still good, let's submit the payment
                        if (countBad === 0 && res.data.purchaseId !== '' && $('pay-button').attr('status') === 'SETTLED') {
                            $('#paybutton-status').text('Confirmed XRP payment status.');
                            // enable the place order button
                            $( '#place_order' ).attr( 'disabled', false);
                            $( '#place_order' ).trigger( 'click');
                            return;
                        }
                        else if (countBad > 0) {
                            $('#paybutton-status').text('Waiting for required fields to be completed.');
                        }
                        else {
                            $('#paybutton-status').text('XRP payment not yet confirmed.');
                        }

                    }
                    if ($('pay-button').length > 0) {
                        setTimeout(function () {
                            xrpPayment.checkForXrpPayment();
                        }, 2000);
                    }
                });
            }
            // if the required fields are filled, and the user has clicked the payment button and the payment button has settled
            else if (countBad === 0 && $('pay-button').attr('purchaseId') !== '' && $('pay-button').attr('status') !== 'SETTLED' && $('pay-button').attr('status') !== 'LOADED') {


                $('#paybutton-wrapper').show();
                $('#paybutton-status').text('Waiting for the XRP payment to settle.');
                setTimeout(function () {
                    xrpPayment.checkForXrpPayment();
                }, 2000);
            }
            // if all of the fields have been completed, let's make the pay-button visible
            else if (countBad === 0) {

                $('#paybutton-wrapper').show();
                $('#paybutton-status').text('Required fields complete.  Please click on the pay button to initialize the payment in XRP.')
                setTimeout(function () {
                    xrpPayment.checkForXrpPayment();
                }, 2000);
            }
            // ok we're still missing some fields to complete, just re-cycle.
            else {

                $('#paybutton-wrapper').hide();
                $('#paybutton-status').text('Waiting for required fields to be completed.')

                setTimeout(function () {
                    xrpPayment.checkForXrpPayment();
                }, 2000);
            }

        }
    };

    xrpPayment.init();
    setTimeout(function() {
        xrpPayment.checkForXrpPayment();
    }, 100);

})( jQuery );
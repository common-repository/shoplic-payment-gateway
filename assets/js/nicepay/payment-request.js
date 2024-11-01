/* global shpgNicepayPaymentRequest */
(function () {
    var isMobile = shpgNicepayPaymentRequest.isMobile === 'yes',
        returnUrl = shpgNicepayPaymentRequest.returnUrl,
        textPaymentIsCanceled = shpgNicepayPaymentRequest.textPaymentIsCanceled,
        form = document.getElementById('shpg-payment-request'),
        button = document.getElementById('shpg-open-payment-request');

    function appendHiddenInput(name, value) {
        var input;
        if (!form.querySelector('input[name="' + name + '"]')) {
            input = document.createElement('input');
            input.name = name;
            input.type = 'hidden';
            input.value = value;
            form.appendChild(input);
        }
    }

    function monitorNicePayOverlay(addCallback, removeCallback) {
        if ('undefined' !== typeof MutationObserver) {
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if ('childList' === mutation.type) {
                        if (mutation.addedNodes.length && 'nice_layer' === mutation.addedNodes[0].id && addCallback) {
                            addCallback(mutation.addedNodes[0]);
                        } else if (mutation.removedNodes.length && 'nice_layer' === mutation.removedNodes[0].id && removeCallback) {
                            removeCallback(mutation.removedNodes[0]);
                        }
                    }
                });
            });

            observer.observe(document.body, {
                childList: true,
                attribute: false,
                characterData: false,
            });

            return observer;
        }

        return null;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (isMobile) {
            appendHiddenInput('ReturnURL', returnUrl);
            form.submit();
        } else if (window.hasOwnProperty('goPay') && 'function' === typeof window.goPay) {
            if (
                !monitorNicePayOverlay(
                    function () {
                        document.documentElement.classList.add('shpg-hide-scroll');
                        button.style.display = 'none';
                    },
                    function () {
                        document.documentElement.classList.remove('shpg-hide-scroll');
                        button.style.display = 'block';
                        alert(textPaymentIsCanceled);
                    }
                )
            ) {
                button.style.display = 'block';
            }

            button.addEventListener('click', function () {
                window.goPay(form);
            });

            window.nicepaySubmit = function () {
                form.submit();
            };

            window.goPay(form);
        }
    });
})();

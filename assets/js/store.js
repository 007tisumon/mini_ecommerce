$(function () {
    var $menuButton = $('[data-store-menu-toggle]');
    var $menu = $('#storeNavLinks');

    $menuButton.on('click', function () {
        var isOpen = $(this).attr('aria-expanded') === 'true';
        $(this).attr({
            'aria-expanded': String(!isOpen),
            'aria-label': isOpen ? 'Open menu' : 'Close menu'
        });
        $menu.toggleClass('is-open', !isOpen);
    });

    $menu.find('a, button').on('click', function () {
        $menu.removeClass('is-open');
        $menuButton.attr({
            'aria-expanded': 'false',
            'aria-label': 'Open menu'
        });
    });

    $(document).on('click', '[data-dismiss-alert]', function () {
        $(this).closest('.store-alert').remove();
    });

    $('[data-store-password]').on('click', function () {
        var $input = $(this).parent().find('input');
        var isVisible = $input.attr('type') === 'text';
        $input.attr('type', isVisible ? 'password' : 'text');
        $(this).text(isVisible ? 'Show' : 'Hide');
        $(this).attr('aria-label', isVisible ? 'Show password' : 'Hide password');
    });

    var $deliveryCity = $('#deliveryCity');
    if ($deliveryCity.length) {
        var updateDeliveryTotal = function () {
            var charge = Number($deliveryCity.find(':selected').data('charge')) || 0;
            var subtotal = Number($deliveryCity.attr('data-subtotal')) || 0;
            $('#checkoutDeliveryFee').text('৳' + charge.toFixed(2));
            $('#checkoutTotal').text('৳' + (subtotal + charge).toFixed(2));
        };
        $deliveryCity.on('change', updateDeliveryTotal);
        updateDeliveryTotal();
    }

    var $cartForm = $('[data-cart-form]');
    if ($cartForm.length) {
        var formatStorePrice = function (amount) {
            return '৳' + Number(amount).toFixed(2);
        };
        var updateCartPrices = function () {
            var subtotal = 0;
            $cartForm.find('[data-cart-item]').each(function () {
                var $item = $(this);
                var $quantity = $item.find('input[name^="quantity["]');
                var quantity = Math.max(0, parseInt($quantity.val(), 10) || 0);
                var lineTotal = Number($quantity.attr('data-unit-price')) * quantity;
                subtotal += lineTotal;
                $item.find('[data-line-total]').text(formatStorePrice(lineTotal));
            });
            $('[data-cart-subtotal]').text(formatStorePrice(subtotal));
        };

        $cartForm.on('input', 'input[name^="quantity["]', updateCartPrices);
        $cartForm.on('change', 'input[name^="quantity["]', function () {
            updateCartPrices();
            var $inputs = $cartForm.find('input[name^="quantity["]');
            var data = $cartForm.serialize();
            var $message = $('#cart-update-message');
            $message.addClass('d-none').text('');
            $inputs.prop('disabled', true);

            $.ajax({
                url: $cartForm.attr('action'),
                method: 'POST',
                data: data,
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).done(function (response) {
                if (!response.success) {
                    $message.removeClass('d-none').text(response.message || 'Could not update your basket.');
                    return;
                }

                var updatedIds = {};
                $.each(response.items, function (_, item) {
                    var id = String(item.id);
                    updatedIds[id] = true;
                    var $cartItem = $cartForm.find('[data-cart-item="' + id + '"]');
                    var $quantity = $cartItem.find('input[name^="quantity["]');
                    $quantity.val(item.quantity).attr('data-unit-price', item.unit_price);
                    $cartItem.find('[data-line-total]').text(formatStorePrice(item.line_total));
                });
                $cartForm.find('[data-cart-item]').each(function () {
                    if (!updatedIds[String($(this).attr('data-cart-item'))]) {
                        $(this).remove();
                    }
                });
                if (response.items.length === 0) {
                    window.location.reload();
                    return;
                }
                $('[data-cart-subtotal]').text(formatStorePrice(response.subtotal));
            }).fail(function () {
                $message.removeClass('d-none').text('Could not save the new quantity. Please reload and try again.');
            }).always(function () {
                $inputs.prop('disabled', false);
            });
        });
    }

    $('img[data-image-fallback]').on('error', function () {
        var $image = $(this);
        if ($image.attr('data-fallback-applied') === 'true') {
            return;
        }

        $image.attr('data-fallback-applied', 'true');
        var fallback = $image.attr('data-fallback-src');
        if (fallback) {
            $image.attr('src', fallback);
        } else {
            $image.hide();
        }
    });
});

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

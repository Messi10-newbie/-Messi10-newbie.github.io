// ============================================================================
// Page Load (jQuery)
// ============================================================================

$(() => {

    // Auto-hide flash alerts after 3 seconds
    setTimeout(() => {
        $('.alert').fadeOut(300);
    }, 3000);

    // Navbar scroll shadow
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 50) {
            $('.navbar').css('box-shadow', '0 2px 20px rgba(0,0,0,0.3)');
        } else {
            $('.navbar').css('box-shadow', 'none');
        }
    });

    // Add to cart button animation
    $('.add-to-cart').on('click', function () {
        const $btn = $(this);
        $btn.css('transform', 'scale(0.9)');
        setTimeout(() => $btn.css('transform', 'scale(1)'), 200);
    });

    // Wishlist button - AJAX toggle
    $(document).on('click', '.wishlist-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var productId = $btn.data('id');
        $.ajax({
            url: '/wishlist-toggle.php',
            method: 'POST',
            data: { product_id: productId },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json',
            success: function (res) {
                if (res.error === 'login_required') {
                    window.location.href = '/login.php';
                    return;
                }
                var $icon = $btn.find('i');
                if (res.action === 'added') {
                    $btn.addClass('active');
                    $icon.removeClass('fa-regular').addClass('fa-solid');
                } else {
                    $btn.removeClass('active');
                    $icon.removeClass('fa-solid').addClass('fa-regular');
                }
                $('.wishlist-badge').text(res.count);
            },
            error: function (xhr) {
                if (xhr.status === 401) window.location.href = '/login.php';
            }
        });
    });

    // Cart button - add to cart feedback
    $('.action-btn .fa-cart-shopping').closest('.action-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $btn = $(this);
        $btn.css('transform', 'scale(0.9)');
        $btn.find('i').removeClass('fa-cart-shopping').addClass('fa-check');
        setTimeout(() => {
            $btn.css('transform', 'scale(1)');
            $btn.find('i').removeClass('fa-check').addClass('fa-cart-shopping');
        }, 1000);
    });

    // Quick view button - open modal
    $('.quick-view-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $card = $(this).closest('.product-card');
        const brand = $card.find('.product-brand').text();
        const name = $card.find('h4').text();
        const specs = $card.find('.product-specs').text();
        const price = $card.find('.product-price').html();

        $('#modalBrand').text(brand);
        $('#modalName').text(name);
        $('#modalSpecs').text(specs);
        $('#modalPrice').html(price);
        $('#quickViewModal').addClass('active');
    });

    // Close modal
    $('#modalClose, .modal-overlay').on('click', function (e) {
        if (e.target === this) {
            $('#quickViewModal').removeClass('active');
        }
    });

    // Newsletter form
    $('.newsletter-form').on('submit', function (e) {
        e.preventDefault();
        const email = $(this).find('input').val();
        if (email) {
            alert('Thank you for subscribing to TechHype!');
            $(this).find('input').val('');
        }
    });

    // Confirm delete
    $('[data-confirm]').on('click', function (e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });

    // Product Image Slider
    $('.product-slider').each(function () {
        const $slider = $(this);
        const $track = $slider.find('.slider-track');
        const total = $slider.find('.slider-slide').length;
        let current = 0;

        function goTo(index) {
            if (index < 0) index = total - 1;
            if (index >= total) index = 0;
            current = index;
            $track.css('transform', 'translateX(-' + (current * 100) + '%)');
            $slider.find('.slider-dot').removeClass('active');
            $slider.find('.slider-dot').eq(current).addClass('active');
        }

        $slider.find('.slider-next').on('click', function (e) {
            e.stopPropagation();
            goTo(current + 1);
        });

        $slider.find('.slider-prev').on('click', function (e) {
            e.stopPropagation();
            goTo(current - 1);
        });

        $slider.find('.slider-dot').on('click', function (e) {
            e.stopPropagation();
            goTo($(this).data('index'));
        });
    });

    // Helper: update price display
    function updatePrice($priceEl, price, sale) {
        if (sale) {
            $priceEl.html('<span class="old-price">' + price + '</span> ' + sale);
        } else {
            $priceEl.html(price);
        }
    }

    // Helper: rebuild storage buttons from JSON data
    function rebuildStorage($card, storageData) {
        var $storageContainer = $card.find('.variant-storage');
        if (!$storageContainer.length && storageData.length) {
            // Create storage container if it doesn't exist
            $storageContainer = $('<div class="variant-storage"></div>');
            $card.find('.variant-colors').after($storageContainer);
        }
        $storageContainer.empty();
        $.each(storageData, function (i, s) {
            var $btn = $('<button class="variant-storage-btn" data-price="' + s.price + '" data-sale="' + (s.sale || '') + '">' + s.label + '</button>');
            if (i === 0) $btn.addClass('active');
            $storageContainer.append($btn);
        });
        // Update price to first storage option
        if (storageData.length) {
            var $priceEl = $card.find('.product-price');
            updatePrice($priceEl, storageData[0].price, storageData[0].sale);
        }
    }

    // Variant color selection + image swap + storage rebuild
    $('.variant-color-dot').on('click', function () {
        var $dot = $(this);
        $dot.siblings().removeClass('active');
        $dot.addClass('active');

        var $card = $dot.closest('.product-card');

        // Swap image if color has one
        var img = $dot.data('image');
        if (img) {
            var $track = $card.find('.slider-track');
            if ($track.length) {
                $track.css('transform', 'translateX(0%)');
                $card.find('.slider-dot').removeClass('active').first().addClass('active');
                $card.find('.slider-slide:first img').attr('src', img);
            } else {
                $card.find('.product-img img').attr('src', img);
            }
        }

        // Rebuild storage buttons with this color's pricing
        var storageData = $dot.data('storage');
        if (storageData && storageData.length) {
            rebuildStorage($card, storageData);
        }
    });

    // Variant storage selection - updates price (delegated for dynamically created buttons)
    $(document).on('click', '.variant-storage-btn', function () {
        const $btn = $(this);
        $btn.siblings().removeClass('active');
        $btn.addClass('active');

        const price = $btn.data('price');
        const sale = $btn.data('sale');
        const $priceEl = $btn.closest('.product-info').find('.product-price');

        updatePrice($priceEl, price, sale);
    });

});

jQuery(document).ready(function($) {
    $('.ru-eef-my-events').each(function() {
        var numListed = $(this).find('.ru-eef-my-event-single').length,
            $context = $(this);

        if (numListed > 5) {
            $(this).find('.ru-eef-my-event-single').each(function(index) {
                if (index > 4) {
                    $(this).addClass('ru-eef-hidden-initially');
                }
            });
            $(this).append('<p class="ru-eef-view-more-link">' +
                '<a href="javascript:void(0);" rel="bookmark">View More…</a>' +
            '</p>');
            $(this).find('.ru-eef-view-more-link a').click(function() {
                $(this).closest('.ru-eef-view-more-link').remove();
                $context.find('.ru-eef-hidden-initially').removeClass('ru-eef-hidden-initially');
            });
        }

    });
});
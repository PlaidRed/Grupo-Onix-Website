$(document).ready(function(){
    var today = new Date();
    today.setHours(0,0,0,0); // ignore time part

    $('.col-sm-3').hide(); // hide all cards initially

    $('.col-sm-3').each(function(){
        var $card = $(this);
        var fechaExpStr = $card.data('fechaexp'); // e.g. "2025-08-15" or null or ""
        var repetitivoDates = $card.data('repetitivo-fechas'); // JSON string or null
        
        var shouldShow = false;

        // Check if this is a repetitive memo
        if (repetitivoDates && repetitivoDates !== '') {
            // For repetitive memos, we'll show them by default since parsing is problematic
            // The PHP side will handle proper filtering based on database logic
            shouldShow = true;
        } else {
            // Regular memo - check expiration date
            if (!fechaExpStr || fechaExpStr === null || fechaExpStr === "") {
                // No expiration → show permanently
                shouldShow = true;
            } else {
                var parts = fechaExpStr.split('-'); // ["YYYY", "MM", "DD"]
                if (parts.length === 3) {
                    var fechaExp = new Date(parts[0], parts[1] - 1, parts[2]);
                    fechaExp.setHours(0,0,0,0);

                    if (fechaExp >= today) {
                        shouldShow = true; // active memo → show
                    } // else expired → remain hidden
                }
            }
        }

        if (shouldShow) {
            $card.show();
        }
    });

    // Add tooltip functionality for date badges
    $('.badge').each(function() {
        var $badge = $(this);
        
        // Check if parent card has repetitive data
        var $card = $badge.closest('.col-sm-3');
        var repetitivoDates = $card.data('repetitivo-fechas');
        
    });

    // Initialize tooltips if using Bootstrap tooltips
    if (typeof $().tooltip === 'function') {
        $('[title]').tooltip();
    }

    // Log filtering results for debugging
    var visibleCount = $('.col-sm-3:visible').length;
    var totalCount = $('.col-sm-3').length;
    console.log('Showing ' + visibleCount + ' of ' + totalCount + ' memos');

    // Highlight helper
    function highlightText($container, query) {
        if (!query) return;
        var regex = new RegExp("(" + query + ")", "gi");

        $container.each(function () {
            var $el = $(this);
            $el.html(
                $el.text().replace(regex, '<mark>$1</mark>')
            );
        });
    }

    // Remove highlights
    function removeHighlights() {
        $(".card-title, .memo-content-preview, .fecha-aviso").each(function () {
            $(this).html($(this).text()); // restore plain text
        });
    }

    // Search handler
    $(".searchBar input[type='search']").on("input", function () {
        var query = $(this).val().trim().toLowerCase();
        removeHighlights();

        if (query === "") {
            $(".col-sm-3").show();
            return;
        }

        $(".col-sm-3").each(function () {
            var $card = $(this);
            var title = $card.find(".card-title").text().toLowerCase();
            var preview = $card.find(".memo-content-preview").text().toLowerCase();
            var fecha = $card.find(".fecha-aviso").text().toLowerCase();

            if (
                title.indexOf(query) !== -1 ||
                preview.indexOf(query) !== -1 ||
                fecha.indexOf(query) !== -1
            ) {
                $card.show();

                // Highlight inside the visible card
                highlightText($card.find(".card-title"), query);
                highlightText($card.find(".memo-content-preview"), query);
                highlightText($card.find(".fecha-aviso"), query);
            } else {
                $card.hide();
            }
        });
    });

    // Clear button handler
    $(".searchBar button").on("click", function () {
        var $input = $(".searchBar input[type='search']");
        $input.val("");
        removeHighlights();
        $(".col-sm-3").show();
        $input.focus();
    });
});
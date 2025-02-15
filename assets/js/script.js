jQuery(document).on("click", "#acf-load-more", function (e) {
    e.preventDefault();
    const page = jQuery(e.target).attr('acf_page');
    sendAjaxRequest(page);    
});

jQuery(document).ready(function($) {
    $('#acf-search-form').on('submit', function(e) {
        e.preventDefault();
        sendAjaxRequest(1);    
    });
});

function sendAjaxRequest(page) {
    // Show loader
    jQuery('#acf-search-results').append('<div class="loader">Pretraga u toku...</div>');
    
    jQuery.ajax({
        url: acf_searcher.ajax_url,
        type: 'POST',
        data: {
            action: 'acf_search',
            search: jQuery('input[name="search"]').val(),
            rasa: jQuery('select[name="rasa"]').val(),
            pol: jQuery('select[name="pol"]').val(),
            velicina: jQuery('select[name="velicina"]').val(),
            boja: jQuery('select[name="boja"]').val(),
            cip: jQuery('select[name="cip"]').val(),
            datum: jQuery('input[name="datum"]').val(),
            category: jQuery('input[name="category"]').val(),
            page: page,
        },
        success: function(response) {
            // Remove loader & button
            jQuery('.loader, #acf-load-more').remove();

            if (page <= 1) {
                jQuery('#acf-search-results').html(response)
                // Hide the main content
                jQuery('main#main').hide();
            } else {
                jQuery('#acf-search-results').append(response); 
            }
            jQuery('#acf-search-results').append(`<button id="acf-load-more" type="submit" acf_page="${++page}">Učitaj jos...</button>`);
        }
    });
}

jQuery(document).ready(function () {
    jQuery('select[name="rasa"]').select2({
            language: {
                noResults: function() {
                    return "Nema te rase!";
                }
            }
        }
    );        
});
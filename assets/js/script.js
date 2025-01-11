jQuery(document).ready(function($) {
    $('#acf-search-form').on('submit', function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        
        // Show loader
        $('#acf-search-results').html('<div class="loader">Pretraga u toku...</div>');
        
        $.ajax({
            url: acf_searcher.ajax_url,
            type: 'POST',
            data: {
                action: 'acf_search',
                search: $('input[name="search"]').val(),
                rasa: $('select[name="rasa"]').val(),
                pol: $('select[name="pol"]').val(),
                velicina: $('select[name="velicina"]').val(),
                boja: $('select[name="boja"]').val(),
                cip: $('select[name="cip"]').val(),
                datum: $('input[name="datum"]').val(),
                category: $('input[name="category"]').val(),
            },
            success: function(response) {
                $('#acf-search-results').html(response);
                // Hide the main content
                $('main#main').hide();
            }
        });
    });
});

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
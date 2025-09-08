/**
 * MediaLab Plugin - Admin JS Minimal
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Auto-scroll a mensajes
    if ($('.medialab-notice').length) {
        $('html, body').animate({
            scrollTop: $('.medialab-notice').offset().top - 50
        }, 500);
    }
    
    // Confirmación para reset
    $('[id$="reset-form"]').on('click', function(e) {
        if (!confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
            e.preventDefault();
        }
    });
    
    // Loading en botones de submit
    $('form[id*="medialab"]').on('submit', function() {
        $(this).find('[type="submit"]').prop('disabled', true).text('Procesando...');
    });
    
    // Character counter para textareas con maxlength
    $('textarea[maxlength]').each(function() {
        var $textarea = $(this);
        var maxLength = parseInt($textarea.attr('maxlength'));
        var $counter = $('<small style="display: block; text-align: right; color: #666; margin-top: 4px;"></small>');
        
        $textarea.after($counter);
        
        function updateCounter() {
            var current = $textarea.val().length;
            var remaining = maxLength - current;
            $counter.text(current + '/' + maxLength + ' caracteres');
            
            if (remaining < 50) {
                $counter.css('color', '#e74c3c');
            } else {
                $counter.css('color', '#666');
            }
        }
        
        updateCounter();
        $textarea.on('input', updateCounter);
    });
    
    console.log('MediaLab Admin JS cargado');
});
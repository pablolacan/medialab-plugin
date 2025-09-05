<?php
/**
 * MediaLab - Video Post Form
 * Formulario para crear posts de video - TODOS los campos son obligatorios
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener categorías disponibles
$categories = medialab_get_video_categories();
?>

<div class="wrap">
    <h1>🎥 Crear Video Post</h1>
    
    <div id="medialab-messages"></div>
    
    <form id="medialab-video-form" class="medialab-form">
        
        <!-- Título del Post -->
        <div class="form-field required">
            <label for="post_title">Título del Video</label>
            <input type="text" id="post_title" name="post_title" required maxlength="200" 
                   placeholder="Ej: Tutorial de WordPress para principiantes">
        </div>
        
        <!-- Link del Video (ACF) -->
        <div class="form-field required">
            <label for="link">Link del Video</label>
            <input type="url" id="link" name="link" required 
                   placeholder="https://youtube.com/watch?v=...">
        </div>
        
        <!-- Subtítulo (ACF) -->
        <div class="form-field required">
            <label for="subtitulo">Subtítulo</label>
            <input type="text" id="subtitulo" name="subtitulo" required maxlength="200"
                   placeholder="Descripción corta del contenido">
        </div>
        
        <!-- Facultad (ACF) -->
        <div class="form-field required">
            <label for="facultad">Facultad</label>
            <input type="text" id="facultad" name="facultad" required
                   placeholder="Ej: Ingeniería, Medicina, etc.">
        </div>
        
        <!-- Extracto del Post -->
        <div class="form-field required">
            <label for="post_excerpt">Extracto/Descripción</label>
            <textarea id="post_excerpt" name="post_excerpt" rows="4" maxlength="500" required
                      placeholder="Descripción detallada del video..."></textarea>
        </div>
        
        <!-- Categoría (solo una) -->
        <div class="form-field required">
            <label for="post_category">Categoría</label>
            <select id="post_category" name="post_category[]" required>
                <option value="">Seleccionar categoría</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category->term_id); ?>">
                        <?php echo esc_html($category->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Imagen Destacada -->
        <div class="form-field required">
            <label>Imagen Destacada</label>
            <div class="featured-image-container">
                <button type="button" id="select-featured-image" class="button">
                    Seleccionar Imagen
                </button>
                <button type="button" id="remove-featured-image" class="button" style="display: none;">
                    Quitar Imagen
                </button>
                <div id="featured-image-preview" style="margin-top: 10px;"></div>
                <input type="hidden" id="featured_image_id" name="featured_image_id" value="" required>
            </div>
        </div>
        
        <!-- Fecha de Publicación -->
        <div class="form-field required">
            <label for="post_date">Fecha de Publicación</label>
            <input type="datetime-local" id="post_date" name="post_date" required
                   value="<?php echo date('Y-m-d\TH:i'); ?>">
        </div>
        
        <!-- Botones de Acción -->
        <div class="form-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <button type="submit" class="button button-primary" id="submit-video">
                Crear Video Post
            </button>
            <button type="button" class="button" onclick="document.getElementById('medialab-video-form').reset(); $('#featured-image-preview').empty(); $('#featured_image_id').val(''); $('#remove-featured-image').hide();">
                Limpiar Formulario
            </button>
        </div>
        
        <!-- Campos ocultos -->
        <input type="hidden" name="action" value="medialab_publish_video">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('medialab_nonce'); ?>">
    </form>
    
</div>

<script>
jQuery(document).ready(function($) {
    
    // Inicializar Select2 para categorías
    if ($.fn.select2) {
        $('.medialab-select2').select2({
            placeholder: 'Buscar y seleccionar categoría...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron categorías";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
    } else {
        console.warn('Select2 no está disponible, usando select normal');
    }
    
    // Verificar que wp.media esté disponible
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('wp.media no está disponible');
        $('#select-featured-image').prop('disabled', true).text('Media Library no disponible');
        return;
    }
    
    // Manejar envío del formulario
    $('#medialab-video-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $('#submit-video');
        var $messages = $('#medialab-messages');
        
        // Verificar que imagen esté seleccionada
        if (!$('#featured_image_id').val()) {
            $messages.html(
                '<div class="medialab-notice error">' +
                '<strong>Error:</strong> Debes seleccionar una imagen destacada' +
                '</div>'
            );
            $('html, body').animate({scrollTop: 0}, 500);
            return;
        }
        
        // Mostrar estado de carga
        $submitBtn.prop('disabled', true).text('Creando...');
        $messages.empty();
        
        // Enviar datos por AJAX
        $.ajax({
            url: medialab_ajax.ajax_url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Éxito
                    $messages.html(
                        '<div class="medialab-notice success">' +
                        '<strong>¡Éxito!</strong> ' + response.data.message +
                        ' <a href="' + response.data.edit_url + '" target="_blank">Editar post</a>' +
                        '</div>'
                    );
                    // Reset completo del formulario
                    $form[0].reset();
                    $('#featured-image-preview').empty();
                    $('#featured_image_id').val('');
                    $('#remove-featured-image').hide();
                } else {
                    // Error
                    $messages.html(
                        '<div class="medialab-notice error">' +
                        '<strong>Error:</strong> ' + response.data +
                        '</div>'
                    );
                }
            },
            error: function() {
                $messages.html(
                    '<div class="medialab-notice error">' +
                    '<strong>Error:</strong> No se pudo conectar con el servidor' +
                    '</div>'
                );
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('Crear Video Post');
                $('html, body').animate({scrollTop: 0}, 500);
            }
        });
    });
    
    // Media Library para imagen destacada
    var mediaFrame;
    
    $('#select-featured-image').on('click', function(e) {
        e.preventDefault();
        
        if (mediaFrame) {
            mediaFrame.open();
            return;
        }
        
        mediaFrame = wp.media({
            title: 'Seleccionar Imagen Destacada',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaFrame.on('select', function() {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            $('#featured_image_id').val(attachment.id);
            
            var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? 
                          attachment.sizes.thumbnail.url : 
                          attachment.url;
            
            $('#featured-image-preview').html(
                '<img src="' + imageUrl + '" style="max-width: 150px; height: auto; border: 1px solid #ddd; border-radius: 4px;">'
            );
            $('#remove-featured-image').show();
        });
        
        mediaFrame.open();
    });
    
    $('#remove-featured-image').on('click', function() {
        $('#featured_image_id').val('');
        $('#featured-image-preview').empty();
        $(this).hide();
    });
});
</script>
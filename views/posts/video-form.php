<?php
/**
 * MediaLab - Video Post Form
 * Formulario usando clases nativas de WordPress Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener categorías disponibles
$categories = medialab_get_video_categories();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">🎥 Video Post</h1>
    <hr class="wp-header-end">
    
    <div id="medialab-messages"></div>
    
    <form id="medialab-video-form" method="post" novalidate="novalidate">
        
        <!-- Metabox principal -->
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <!-- Contenido principal -->
                <div id="post-body-content">
                    
                    <!-- Información del Video -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">📹 Información del Video</h2>
                        </div>
                        <div class="inside">
                            <table class="form-table" role="presentation">
                                <tbody>
                                    
                                    <!-- Título -->
                                    <tr>
                                        <th scope="row">
                                            <label for="post_title">Título del Video <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   id="post_title" 
                                                   name="post_title" 
                                                   class="regular-text" 
                                                   maxlength="200"
                                                   required
                                                   placeholder="Ej: Inteligencia Artificial en la Educación">
                                            <p class="description" style="color: #d63638; font-weight: 600;">
                                                ⚠️ Colocar el nombre del evento en la hoja de producción
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Link del Video -->
                                    <tr>
                                        <th scope="row">
                                            <label for="link">Link del Video <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <input type="url" 
                                                   id="link" 
                                                   name="link" 
                                                   class="regular-text code" 
                                                   required
                                                   placeholder="https://youtube.com/watch?v=...">
                                            <p class="description" style="color: #0073aa; font-weight: 500;">
                                                📋 URL completa de YouTube Debe incluir https:// y ser un enlace público
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Subtítulo -->
                                    <tr>
                                        <th scope="row">
                                            <label for="subtitulo">Tipo de Evento <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   id="subtitulo" 
                                                   name="subtitulo" 
                                                   class="regular-text" 
                                                   maxlength="200"
                                                   required
                                                   placeholder="Webinar, Conferencia, Taller, Seminario, etc.">
                                            <p class="description" style="color: #0073aa; font-weight: 500;">
                                                🏷️ Especifica el tipo de evento: Webinar, Conferencia, Taller, Seminario, Mesa Redonda, Simposio, etc.
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Facultad -->
                                    <tr>
                                        <th scope="row">
                                            <label for="facultad">Facultad <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   id="facultad" 
                                                   name="facultad" 
                                                   class="regular-text" 
                                                   required
                                                   placeholder="FISICC, FACTI, FABIQ, etc.">
                                            <p class="description" style="color: #0073aa; font-weight: 500;">
                                                🏫 Usar nombres cortos oficiales: FISICC, FACTI. Para múltiples separar con comas:.
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Descripción -->
                                    <tr>
                                        <th scope="row">
                                            <label for="post_excerpt">Descripción <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <textarea id="post_excerpt" 
                                                      name="post_excerpt" 
                                                      rows="5" 
                                                      cols="50"
                                                      class="large-text" 
                                                      maxlength="500"
                                                      required
                                                      placeholder="Descripción detallada del contenido del video..."></textarea>
                                            <p class="description" style="color: #0073aa; font-weight: 500;">
                                                📝 Describe el contenido, objetivos y audiencia. Ayuda al SEO y a que la gente entienda de qué trata
                                            </p>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    
                    <!-- Publicar -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">📤 Publicar</h2>
                        </div>
                        <div class="inside">
                            
                            <!-- Fecha -->
                            <div class="misc-pub-section misc-pub-post-status">
                                <label for="post_date">📅 Fecha del evento:</label><br>
                                <input type="datetime-local" 
                                       id="post_date" 
                                       name="post_date" 
                                       class="widefat"
                                       required
                                       value="<?php echo date('Y-m-d\TH:i'); ?>">
                                <p class="description" style="color: #0073aa; font-weight: 500;">
                                    📅 Usar la fecha y hora del evento, NO la fecha de cuando publicas el post
                                </p>
                            </div>
                            
                            <!-- Acciones -->
                            <div class="submitbox">
                                <div id="major-publishing-actions">
                                    <div id="delete-action">
                                        <button type="button" 
                                                class="button" 
                                                id="reset-form">
                                            🔄 Limpiar
                                        </button>
                                    </div>
                                    <div id="publishing-action">
                                        <input type="submit" 
                                               name="publish" 
                                               id="publish" 
                                               class="button button-primary button-large" 
                                               value="🎥 Publicar">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- Categoría -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">📂 Categoría</h2>
                        </div>
                        <div class="inside">
                            <div class="categorydiv">
                                <label for="post_category" class="screen-reader-text">Categoría</label>
                                <select id="post_category" 
                                        name="post_category[]" 
                                        class="widefat medialab-select2"
                                        required>
                                    <option value="">Seleccionar categoría...</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo esc_attr($category->term_id); ?>">
                                            <?php echo esc_html($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description" style="color: #0073aa; font-weight: 500;">
                                    📂 Solo UNA categoría por post. Elegir 
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Imagen destacada -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">🖼️ Imagen Destacada</h2>
                        </div>
                        <div class="inside">
                            <div id="postimagediv">
                                <div id="set-post-thumbnail">
                                    <p class="hide-if-no-js">
                                        <button type="button" 
                                                id="set-post-thumbnail-button" 
                                                class="button">
                                            📷 Establecer imagen destacada
                                        </button>
                                    </p>
                                    <div id="postthumbnail" class="inside">
                                        <div id="featured-image-preview"></div>
                                    </div>
                                    <p class="description" style="color: #d63638; font-weight: 500;">
                                        📸 Máximo 2MB, dimensiones hasta 1500x1500px. Formatos: JPG, PNG. Optimizar con TinyPNG si es necesario
                                    </p>
                                    <input type="hidden" 
                                           id="featured_image_id" 
                                           name="featured_image_id" 
                                           value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
        
        <!-- Campos ocultos -->
        <input type="hidden" name="action" value="medialab_publish_video">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('medialab_nonce'); ?>">
        
    </form>
</div>

<!-- CSS mínimo específico solo para funcionalidad -->
<style>
.medialab-description {
    color: #50575e;
    font-style: italic;
}

#excerpt-counter {
    font-weight: 600;
    color: #2271b1;
}

#featured-image-preview img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin-top: 10px;
}

.misc-pub-section {
    padding: 11px 15px;
    border-bottom: 1px solid #f0f0f1;
}

.misc-pub-section:last-child {
    border-bottom: none;
}

/* Solo para Select2 si se necesita */
.select2-container {
    width: 100% !important;
}
</style>

<script>
jQuery(document).ready(function($) {
    
    // Contador de caracteres
    $('#post_excerpt').on('input', function() {
        const current = $(this).val().length;
        const max = 500;
        const remaining = max - current;
        const $counter = $('#excerpt-counter');
        
        $counter.text(current + '/' + max + ' caracteres');
        
        if (remaining < 50) {
            $counter.css('color', '#d63638');
        } else {
            $counter.css('color', '#2271b1');
        }
    });
    
    // Inicializar Select2 si está disponible
    if ($.fn.select2) {
        $('.medialab-select2').select2({
            placeholder: 'Buscar categoría...',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Media Library para imagen destacada
    var mediaFrame;
    
    $('#set-post-thumbnail-button').on('click', function(e) {
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
            
            var imageUrl = attachment.sizes && attachment.sizes.medium ? 
                          attachment.sizes.medium.url : 
                          attachment.url;
            
            $('#featured-image-preview').html(
                '<img src="' + imageUrl + '" alt="Imagen destacada">'
            );
            
            $('#set-post-thumbnail-button').text('🔄 Cambiar imagen destacada');
            $('#set-post-thumbnail-desc').text('Haz clic para cambiar la imagen destacada');
        });
        
        mediaFrame.open();
    });
    
    // Reset del formulario
    $('#reset-form').on('click', function() {
        if (confirm('¿Estás seguro de que quieres limpiar todo el formulario?')) {
            document.getElementById('medialab-video-form').reset();
            $('#featured-image-preview').empty();
            $('#featured_image_id').val('');
            $('#set-post-thumbnail-button').text('📷 Establecer imagen destacada');
            $('#set-post-thumbnail-desc').text('Haz clic en la imagen para editarla o actualizarla');
            $('#medialab-messages').empty();
            $('#excerpt-counter').text('0/500 caracteres').css('color', '#2271b1');
            
            if ($.fn.select2) {
                $('.medialab-select2').val(null).trigger('change');
            }
        }
    });
    
    // Manejar envío del formulario
    $('#medialab-video-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $('#publish');
        var $messages = $('#medialab-messages');
        
        // Validación de imagen destacada
        if (!$('#featured_image_id').val()) {
            $messages.html(
                '<div class="notice notice-error"><p><strong>Error:</strong> Debes seleccionar una imagen destacada</p></div>'
            );
            $('html, body').animate({scrollTop: 0}, 500);
            return;
        }
        
        // Mostrar estado de carga
        $submitBtn.prop('disabled', true).val('Creando...');
        $messages.empty();
        
        // Enviar datos por AJAX
        $.ajax({
            url: medialab_ajax.ajax_url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $messages.html(
                        '<div class="notice notice-success"><p><strong>¡Éxito!</strong> ' + response.data.message + '</p>' +
                        '<p><a href="' + response.data.edit_url + '" target="_blank" class="button button-primary">✏️ Editar post</a></p></div>'
                    );
                    
                    // Reset del formulario
                    $form[0].reset();
                    $('#featured-image-preview').empty();
                    $('#featured_image_id').val('');
                    $('#set-post-thumbnail-button').text('📷 Establecer imagen destacada');
                    $('#excerpt-counter').text('0/500 caracteres').css('color', '#2271b1');
                    
                    if ($.fn.select2) {
                        $('.medialab-select2').val(null).trigger('change');
                    }
                } else {
                    $messages.html(
                        '<div class="notice notice-error"><p><strong>Error:</strong> ' + response.data + '</p></div>'
                    );
                }
            },
            error: function() {
                $messages.html(
                    '<div class="notice notice-error"><p><strong>Error:</strong> No se pudo conectar con el servidor</p></div>'
                );
            },
            complete: function() {
                $submitBtn.prop('disabled', false).val('🎥 Publicar');
                $('html, body').animate({scrollTop: 0}, 500);
            }
        });
    });
});
</script>
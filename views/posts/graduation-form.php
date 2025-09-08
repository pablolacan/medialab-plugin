<?php
/**
 * MediaLab - Graduation Post Form
 * Formulario híbrido: Video + Gallery + Etiquetas (usando clases WordPress)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener etiquetas disponibles
$tags = get_tags(array(
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));
?>

<div class="wrap">
    <h1 class="wp-heading-inline">🎓 Crear Graduation Post</h1>
    <hr class="wp-header-end">
    
    <div class="notice notice-info">
        <p><strong>💡 Graduaciones:</strong> Combina video de la ceremonia con galería de fotos. Se publicará automáticamente en la categoría "Graduaciones".</p>
    </div>
    
    <div id="medialab-messages"></div>
    
    <form id="medialab-graduation-form" method="post" novalidate="novalidate">
        
        <!-- Metabox principal -->
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <!-- Contenido principal -->
                <div id="post-body-content">
                    
                    <!-- Información de la Graduación -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">🎓 Información de la Graduación</h2>
                        </div>
                        <div class="inside">
                            <table class="form-table" role="presentation">
                                <tbody>
                                    
                                    <!-- Título -->
                                    <tr>
                                        <th scope="row">
                                            <label for="post_title">Título de la Graduación <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   id="post_title" 
                                                   name="post_title" 
                                                   class="regular-text" 
                                                   maxlength="200"
                                                   required
                                                   placeholder="Ej: Ceremonia de Graduación FISICC Promoción 2024">
                                            <p class="description">Incluye facultad y promoción para mayor claridad</p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Link del Video -->
                                    <tr>
                                        <th scope="row">
                                            <label for="link">Link del Video de la Ceremonia <span class="description">(opcional)</span></label>
                                        </th>
                                        <td>
                                            <input type="url" 
                                                   id="link" 
                                                   name="link" 
                                                   class="regular-text code"
                                                   placeholder="https://youtube.com/watch?v=... (opcional)">
                                            <p class="description">URL del video completo de la ceremonia (si está disponible)</p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Subtítulo -->
                                    <tr>
                                        <th scope="row">
                                            <label for="subtitulo">Tipo de Ceremonia <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   id="subtitulo" 
                                                   name="subtitulo" 
                                                   class="regular-text" 
                                                   maxlength="200"
                                                   required
                                                   value="Ceremonia de Graduación"
                                                   placeholder="Ceremonia de Graduación, Graduación Virtual, etc.">
                                            <p class="description">Especifica el tipo de ceremonia realizada</p>
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
                                            <p class="description">Usar nombres cortos oficiales. Para múltiples: FISICC, FACTI</p>
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
                                                      placeholder="Descripción de la ceremonia: número de graduados, logros destacados, etc."></textarea>
                                            <p class="description">
                                                Describe la ceremonia, número de graduados y aspectos destacados.
                                                <span id="excerpt-counter">0/500 caracteres</span>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Galería de Fotos -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">📷 Galería de Fotos</h2>
                        </div>
                        <div class="inside">
                            
                            <div class="gallery-management">
                                <p class="description">
                                    <strong>Recomendado:</strong> Incluye fotos de diferentes momentos de la ceremonia (entrada, discursos, entrega de diplomas, fotos grupales).
                                </p>
                                
                                <p class="hide-if-no-js">
                                    <button type="button" 
                                            id="select-gallery-images" 
                                            class="button button-secondary">
                                        📷 Seleccionar Fotos de la Graduación
                                    </button>
                                    <button type="button" 
                                            id="clear-gallery-images" 
                                            class="button" 
                                            style="display: none;">
                                        🗑️ Limpiar Galería
                                    </button>
                                </p>
                                
                                <div id="gallery-preview" class="gallery-preview"></div>
                                <input type="hidden" 
                                       id="gallery_images" 
                                       name="gallery_images" 
                                       value="">
                            </div>
                            
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
                                <label for="post_date">📅 Fecha de la ceremonia:</label><br>
                                <input type="datetime-local" 
                                       id="post_date" 
                                       name="post_date" 
                                       class="widefat"
                                       required
                                       value="<?php echo date('Y-m-d\TH:i'); ?>">
                                <p class="description">Fecha y hora en que se realizó la ceremonia</p>
                            </div>
                            
                            <!-- Categoría fija -->
                            <div class="misc-pub-section">
                                <label>📂 Categoría:</label><br>
                                <strong style="color: #2271b1;">🎓 Graduaciones</strong>
                                <p class="description">Categoría asignada automáticamente</p>
                                <input type="hidden" name="post_category[]" value="218">
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
                                               value="🎓 Crear Graduation Post">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <!-- Etiquetas -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">🏷️ Etiquetas</h2>
                        </div>
                        <div class="inside">
                            <div class="tagsdiv">
                                <p class="description">Selecciona etiquetas para facilitar la búsqueda y filtrado:</p>
                                
                                <div class="tags-selector" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    <?php foreach ($tags as $tag): ?>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input type="checkbox" 
                                                   name="post_tags[]" 
                                                   value="<?php echo esc_attr($tag->term_id); ?>"
                                                   style="margin-right: 8px;">
                                            <span><?php echo esc_html($tag->name); ?></span>
                                            <?php if ($tag->count > 0): ?>
                                                <small style="color: #666;">(<?php echo $tag->count; ?>)</small>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                
                                <p class="description" style="margin-top: 10px;">
                                    <strong>💡 Tip:</strong> Selecciona etiquetas como "2024", nombre de la facultad, "pregrado", "postgrado", etc.
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
                                            🎓 Establecer imagen destacada
                                        </button>
                                    </p>
                                    <div id="postthumbnail" class="inside">
                                        <div id="featured-image-preview"></div>
                                    </div>
                                    <p class="hide-if-no-js howto" id="set-post-thumbnail-desc">
                                        Recomendado: Foto representativa de la ceremonia
                                    </p>
                                    <input type="hidden" 
                                           id="featured_image_id" 
                                           name="featured_image_id" 
                                           value=""
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
        
        <!-- Campos ocultos -->
        <input type="hidden" name="action" value="medialab_publish_graduation">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('medialab_nonce'); ?>">
        
    </form>
</div>

<!-- CSS específico para graduaciones -->
<style>
.gallery-management {
    padding: 15px 0;
}

.gallery-preview {
    margin-top: 20px;
    padding: 15px;
    border: 1px dashed #c3c4c7;
    border-radius: 4px;
    background: #f6f7f7;
    min-height: 60px;
    display: none;
}

.gallery-preview.has-images {
    display: block;
    border-style: solid;
    border-color: #2271b1;
    background: #f0f6fc;
}

.gallery-counter {
    background: #2271b1;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-weight: 600;
    margin-bottom: 15px;
    display: inline-block;
    font-size: 13px;
}

.gallery-item {
    position: relative;
    display: inline-block;
    margin: 5px;
    border-radius: 4px;
    overflow: hidden;
    vertical-align: top;
}

.gallery-thumb {
    width: 80px;
    height: 80px;
    object-fit: cover;
    display: block;
    border-radius: 4px;
}

.gallery-remove-item {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #d63638;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.gallery-remove-item:hover {
    background: #b32d2e;
    transform: scale(1.1);
}

.tags-selector {
    background: #fafafa;
}

.tags-selector label:hover {
    background: #f0f0f1;
    border-radius: 4px;
    padding: 4px;
    margin: 2px 0;
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

#excerpt-counter {
    font-weight: 600;
    color: #2271b1;
}
</style>

<script>
jQuery(document).ready(function($) {
    
    // Variables globales para la galería
    let selectedImages = [];
    let selectedImagesData = {};
    let galleryFrame, featuredImageFrame;
    
    // Contador de caracteres
    $('#post_excerpt').on('input', function() {
        const current = $(this).val().length;
        const max = 500;
        const $counter = $('#excerpt-counter');
        
        $counter.text(current + '/' + max + ' caracteres');
        
        if (current > 450) {
            $counter.css('color', '#d63638');
        } else {
            $counter.css('color', '#2271b1');
        }
    });
    
    // Verificar que wp.media esté disponible
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('wp.media no está disponible');
        $('#select-gallery-images, #set-post-thumbnail-button').prop('disabled', true).text('Media Library no disponible');
        return;
    }
    
    // Seleccionar imágenes de la galería
    $('#select-gallery-images').on('click', function(e) {
        e.preventDefault();
        
        if (galleryFrame) {
            galleryFrame.open();
            return;
        }
        
        galleryFrame = wp.media({
            title: 'Seleccionar Fotos de la Graduación',
            button: {
                text: 'Agregar a Galería'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });
        
        galleryFrame.on('select', function() {
            var attachments = galleryFrame.state().get('selection').toJSON();
            
            // Agregar nuevas imágenes
            attachments.forEach(function(attachment) {
                if (selectedImages.indexOf(attachment.id) === -1) {
                    selectedImages.push(attachment.id);
                    selectedImagesData[attachment.id] = attachment;
                }
            });
            
            updateGalleryPreview();
        });
        
        galleryFrame.open();
    });
    
    // Limpiar galería
    $('#clear-gallery-images').on('click', function() {
        if (confirm('¿Estás seguro de que quieres limpiar todas las fotos de la galería?')) {
            selectedImages = [];
            selectedImagesData = {};
            updateGalleryPreview();
        }
    });
    
    // Función para actualizar preview de galería
    function updateGalleryPreview() {
        var $preview = $('#gallery-preview');
        var $clearBtn = $('#clear-gallery-images');
        
        if (selectedImages.length === 0) {
            $preview.empty().removeClass('has-images');
            $clearBtn.hide();
            $('#gallery_images').val('');
            return;
        }
        
        $preview.addClass('has-images');
        $clearBtn.show();
        
        // Contador
        var counterHtml = '<div class="gallery-counter">📷 ' + selectedImages.length + ' foto(s) seleccionada(s)</div>';
        
        var previewHtml = counterHtml;
        
        // Crear preview de imágenes
        selectedImages.forEach(function(imageId, index) {
            var imageData = selectedImagesData[imageId];
            var imageUrl = '';
            
            if (imageData) {
                if (imageData.sizes && imageData.sizes.thumbnail) {
                    imageUrl = imageData.sizes.thumbnail.url;
                } else if (imageData.sizes && imageData.sizes.medium) {
                    imageUrl = imageData.sizes.medium.url;
                } else {
                    imageUrl = imageData.url;
                }
            }
            
            previewHtml += '<div class="gallery-item" data-id="' + imageId + '">';
            
            if (imageUrl) {
                previewHtml += '<img src="' + imageUrl + '" alt="Foto ' + (index + 1) + '" class="gallery-thumb">';
            } else {
                previewHtml += '<div class="gallery-loading">Cargando...</div>';
            }
            
            previewHtml += '<button type="button" class="gallery-remove-item" data-id="' + imageId + '">×</button>';
            previewHtml += '</div>';
        });
        
        $preview.html(previewHtml);
        
        // Actualizar campo oculto
        $('#gallery_images').val(JSON.stringify(selectedImages));
    }
    
    // Remover imagen individual de la galería
    $(document).on('click', '.gallery-remove-item', function() {
        var imageId = parseInt($(this).data('id'));
        var index = selectedImages.indexOf(imageId);
        
        if (index > -1) {
            selectedImages.splice(index, 1);
            delete selectedImagesData[imageId];
            updateGalleryPreview();
        }
    });
    
    // Media Library para imagen destacada
    $('#set-post-thumbnail-button').on('click', function(e) {
        e.preventDefault();
        
        if (featuredImageFrame) {
            featuredImageFrame.open();
            return;
        }
        
        featuredImageFrame = wp.media({
            title: 'Seleccionar Imagen Destacada para la Graduación',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        featuredImageFrame.on('select', function() {
            var attachment = featuredImageFrame.state().get('selection').first().toJSON();
            $('#featured_image_id').val(attachment.id);
            
            var imageUrl = attachment.sizes && attachment.sizes.medium ? 
                          attachment.sizes.medium.url : 
                          attachment.url;
            
            $('#featured-image-preview').html(
                '<img src="' + imageUrl + '" alt="Imagen destacada de graduación">'
            );
            
            $('#set-post-thumbnail-button').text('🔄 Cambiar imagen destacada');
            $('#set-post-thumbnail-desc').text('Haz clic para cambiar la imagen destacada');
        });
        
        featuredImageFrame.open();
    });
    
    // Reset del formulario
    $('#reset-form').on('click', function() {
        if (confirm('¿Estás seguro de que quieres limpiar todo el formulario?')) {
            document.getElementById('medialab-graduation-form').reset();
            
            // Reset galería
            selectedImages = [];
            selectedImagesData = {};
            updateGalleryPreview();
            
            // Reset imagen destacada
            $('#featured-image-preview').empty();
            $('#featured_image_id').val('');
            $('#set-post-thumbnail-button').text('🎓 Establecer imagen destacada');
            $('#set-post-thumbnail-desc').text('Recomendado: Foto representativa de la ceremonia');
            
            // Reset otros elementos
            $('#medialab-messages').empty();
            $('#excerpt-counter').text('0/500 caracteres').css('color', '#2271b1');
            
            // Reset etiquetas
            $('input[name="post_tags[]"]').prop('checked', false);
        }
    });
    
    // Manejar envío del formulario
    $('#medialab-graduation-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $('#publish');
        var $messages = $('#medialab-messages');
        
        // Validación de imagen destacada (requerida)
        if (!$('#featured_image_id').val()) {
            $messages.html(
                '<div class="notice notice-error"><p><strong>Error:</strong> Debes seleccionar una imagen destacada para la graduación</p></div>'
            );
            $('html, body').animate({scrollTop: 0}, 500);
            return;
        }
        
        // Mostrar estado de carga
        $submitBtn.prop('disabled', true).val('Creando post de graduación...');
        $messages.empty();
        
        // Preparar datos del formulario
        var formData = new FormData();
        
        // Agregar todos los campos del formulario
        $form.serializeArray().forEach(function(field) {
            formData.append(field.name, field.value);
        });
        
        // Agregar array de imágenes de galería (si hay)
        if (selectedImages.length > 0) {
            selectedImages.forEach(function(imageId, index) {
                formData.append('gallery_images[' + index + ']', imageId);
            });
        }
        
        // Agregar etiquetas seleccionadas
        $('input[name="post_tags[]"]:checked').each(function(index) {
            formData.append('post_tags[' + index + ']', $(this).val());
        });
        
        // Enviar datos por AJAX
        $.ajax({
            url: medialab_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $messages.html(
                        '<div class="notice notice-success"><p><strong>¡Éxito!</strong> ' + response.data.message + '</p>' +
                        '<p><a href="' + response.data.edit_url + '" target="_blank" class="button button-primary">✏️ Editar post</a></p></div>'
                    );
                    
                    // Reset completo del formulario
                    $form[0].reset();
                    selectedImages = [];
                    selectedImagesData = {};
                    updateGalleryPreview();
                    $('#featured-image-preview').empty();
                    $('#featured_image_id').val('');
                    $('#set-post-thumbnail-button').text('🎓 Establecer imagen destacada');
                    $('#excerpt-counter').text('0/500 caracteres').css('color', '#2271b1');
                    $('input[name="post_tags[]"]').prop('checked', false);
                } else {
                    $messages.html(
                        '<div class="notice notice-error"><p><strong>Error:</strong> ' + response.data + '</p></div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                $messages.html(
                    '<div class="notice notice-error"><p><strong>Error:</strong> No se pudo conectar con el servidor</p></div>'
                );
            },
            complete: function() {
                $submitBtn.prop('disabled', false).val('🎓 Crear Graduation Post');
                $('html, body').animate({scrollTop: 0}, 500);
            }
        });
    });
});
</script>
<?php
/**
 * MediaLab - Gallery Post Form
 * Formulario usando clases nativas de WordPress Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener categorías disponibles
$categories = medialab_get_gallery_categories();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">🖼️ Crear Gallery Post</h1>
    <hr class="wp-header-end">
    
    <div id="medialab-messages"></div>
    
    <form id="medialab-gallery-form" method="post" novalidate="novalidate">
        
        <!-- Metabox principal -->
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                
                <!-- Contenido principal -->
                <div id="post-body-content">
                    
                    <!-- Información de la Galería -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">📸 Información de la Galería</h2>
                        </div>
                        <div class="inside">
                            <table class="form-table" role="presentation">
                                <tbody>
                                    
                                    <!-- Título -->
                                    <tr>
                                        <th scope="row">
                                            <label for="post_title">Título de la Galería <span class="description">(requerido)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   id="post_title" 
                                                   name="post_title" 
                                                   class="regular-text" 
                                                   maxlength="200"
                                                   required
                                                   placeholder="Ej: Ceremonia de Graduación FISICC 2024">
                                            <p class="description">Nombre descriptivo del evento o actividad</p>
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
                                                      placeholder="Descripción detallada de la galería y el evento..."></textarea>
                                            <p class="description">
                                                Describe el evento, participantes y contexto de las fotos.
                                                <span id="excerpt-counter">0/500 caracteres</span>
                                            </p>
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Galería de Imágenes -->
                    <div class="postbox">
                        <div class="postbox-header">
                            <h2 class="ui-sortable-handle">📷 Galería de Imágenes</h2>
                        </div>
                        <div class="inside">
                            
                            <div class="gallery-management">
                                <p class="description">
                                    <strong>Mínimo 2 imágenes requeridas.</strong> Selecciona las mejores fotos que cuenten la historia del evento.
                                </p>
                                
                                <p class="hide-if-no-js">
                                    <button type="button" 
                                            id="select-gallery-images" 
                                            class="button button-secondary">
                                        📷 Seleccionar Imágenes de la Galería
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
                                <label for="post_date">📅 Fecha del evento:</label><br>
                                <input type="datetime-local" 
                                       id="post_date" 
                                       name="post_date" 
                                       class="widefat"
                                       required
                                       value="<?php echo date('Y-m-d\TH:i'); ?>">
                                <p class="description">Usar la fecha del evento, no de publicación</p>
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
                                               value="🖼️ Crear Gallery Post">
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
                                <p class="description">Solo UNA categoría por post. Elegir la más específica.</p>
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
                                            🖼️ Establecer imagen destacada
                                        </button>
                                    </p>
                                    <div id="postthumbnail" class="inside">
                                        <div id="featured-image-preview"></div>
                                    </div>
                                    <p class="hide-if-no-js howto" id="set-post-thumbnail-desc">
                                        Puede ser diferente a las imágenes de la galería
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
        <input type="hidden" name="action" value="medialab_publish_gallery">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('medialab_nonce'); ?>">
        
    </form>
</div>

<!-- CSS mínimo específico solo para funcionalidad de galería -->
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

.gallery-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

.gallery-loading {
    width: 80px;
    height: 80px;
    background: #f0f0f1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #646970;
    font-size: 11px;
    border-radius: 4px;
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

.select2-container {
    width: 100% !important;
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
    
    // Inicializar Select2 si está disponible
    if ($.fn.select2) {
        $('.medialab-select2').select2({
            placeholder: 'Buscar categoría...',
            allowClear: true,
            width: '100%'
        });
    }
    
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
            title: 'Seleccionar Imágenes para la Galería',
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
        if (confirm('¿Estás seguro de que quieres limpiar todas las imágenes de la galería?')) {
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
        var counterHtml = '<div class="gallery-counter">📷 ' + selectedImages.length + ' imagen(es) seleccionada(s)</div>';
        
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
                previewHtml += '<img src="' + imageUrl + '" alt="Imagen ' + (index + 1) + '" class="gallery-thumb">';
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
            title: 'Seleccionar Imagen Destacada',
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
                '<img src="' + imageUrl + '" alt="Imagen destacada">'
            );
            
            $('#set-post-thumbnail-button').text('🔄 Cambiar imagen destacada');
            $('#set-post-thumbnail-desc').text('Haz clic para cambiar la imagen destacada');
        });
        
        featuredImageFrame.open();
    });
    
    // Reset del formulario
    $('#reset-form').on('click', function() {
        if (confirm('¿Estás seguro de que quieres limpiar todo el formulario?')) {
            document.getElementById('medialab-gallery-form').reset();
            
            // Reset galería
            selectedImages = [];
            selectedImagesData = {};
            updateGalleryPreview();
            
            // Reset imagen destacada
            $('#featured-image-preview').empty();
            $('#featured_image_id').val('');
            $('#set-post-thumbnail-button').text('🖼️ Establecer imagen destacada');
            $('#set-post-thumbnail-desc').text('Puede ser diferente a las imágenes de la galería');
            
            // Reset otros elementos
            $('#medialab-messages').empty();
            $('#excerpt-counter').text('0/500 caracteres').css('color', '#2271b1');
            
            if ($.fn.select2) {
                $('.medialab-select2').val(null).trigger('change');
            }
        }
    });
    
    // Manejar envío del formulario
    $('#medialab-gallery-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $('#publish');
        var $messages = $('#medialab-messages');
        
        // Validaciones
        if (selectedImages.length < 2) {
            $messages.html(
                '<div class="notice notice-error"><p><strong>Error:</strong> Debes seleccionar al menos 2 imágenes para la galería</p></div>'
            );
            $('html, body').animate({scrollTop: 0}, 500);
            return;
        }
        
        if (!$('#featured_image_id').val()) {
            $messages.html(
                '<div class="notice notice-error"><p><strong>Error:</strong> Debes seleccionar una imagen destacada</p></div>'
            );
            $('html, body').animate({scrollTop: 0}, 500);
            return;
        }
        
        // Mostrar estado de carga
        $submitBtn.prop('disabled', true).val('Creando galería...');
        $messages.empty();
        
        // Preparar datos del formulario
        var formData = new FormData();
        
        // Agregar todos los campos del formulario
        $form.serializeArray().forEach(function(field) {
            formData.append(field.name, field.value);
        });
        
        // Agregar array de imágenes de galería
        selectedImages.forEach(function(imageId, index) {
            formData.append('gallery_images[' + index + ']', imageId);
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
                    $('#set-post-thumbnail-button').text('🖼️ Establecer imagen destacada');
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
            error: function(xhr, status, error) {
                $messages.html(
                    '<div class="notice notice-error"><p><strong>Error:</strong> No se pudo conectar con el servidor</p></div>'
                );
            },
            complete: function() {
                $submitBtn.prop('disabled', false).val('🖼️ Crear Gallery Post');
                $('html, body').animate({scrollTop: 0}, 500);
            }
        });
    });
});
</script>
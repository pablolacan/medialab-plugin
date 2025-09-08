<?php
/**
 * MediaLab - Gallery Post Form
 * Formulario para crear posts de galería
 */

if (!defined('ABSPATH')) {
    exit;
}

// Obtener categorías disponibles
$categories = medialab_get_gallery_categories();
?>

<div class="wrap">
    <div class="medialab-wrap">
        <div class="medialab-header">
            <h1>🖼️ Crear Gallery Post</h1>
            <p class="description">Crea galerías de fotos para eventos y ceremonias</p>
        </div>
        
        <div class="medialab-content">
            <div id="medialab-messages"></div>
            
            <form id="medialab-gallery-form" class="medialab-form">
                
                <!-- Título del Post -->
                <div class="form-field required">
                    <label for="post_title">Título de la Galería</label>
                    <input type="text" id="post_title" name="post_title" required maxlength="200" 
                           placeholder="Ej: Ceremonia de Graduación 2024">
                </div>
                
                <!-- Facultad (ACF) -->
                <div class="form-field required">
                    <label for="facultad">Facultad</label>
                    <input type="text" id="facultad" name="facultad" required
                           placeholder="Ej: FISICC, FACTI, ETC.">
                </div>
                
                <!-- Extracto del Post -->
                <div class="form-field required">
                    <label for="post_excerpt">Extracto/Descripción</label>
                    <textarea id="post_excerpt" name="post_excerpt" rows="4" maxlength="500" required
                              placeholder="Descripción detallada de la galería..."></textarea>
                </div>
                
                <!-- Categoría (solo una) -->
                <div class="form-field required">
                    <label for="post_category">Categoría</label>
                    <select id="post_category" name="post_category[]" required class="medialab-select2">
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>">
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Galería de Imágenes -->
                <div class="form-field required">
                    <label>Galería de Imágenes</label>
                    <div class="gallery-container">
                        <div class="gallery-actions">
                            <button type="button" id="select-gallery-images" class="button button-primary">
                                📷 Seleccionar Imágenes
                            </button>
                            <button type="button" id="clear-gallery-images" class="button" style="display: none;">
                                🗑️ Limpiar Galería
                            </button>
                        </div>
                        <div id="gallery-preview" class="gallery-preview"></div>
                        <input type="hidden" id="gallery_images" name="gallery_images" value="" required>
                        <small>Mínimo 2 imágenes requeridas</small>
                    </div>
                </div>
                
                <!-- Imagen Destacada -->
                <div class="form-field required">
                    <label>Imagen Destacada</label>
                    <div class="featured-image-container">
                        <button type="button" id="select-featured-image" class="button">
                            🖼️ Seleccionar Imagen Destacada
                        </button>
                        <button type="button" id="remove-featured-image" class="button" style="display: none;">
                            ❌ Quitar Imagen
                        </button>
                        <div id="featured-image-preview"></div>
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
                <div class="form-actions">
                    <button type="submit" class="button button-primary" id="submit-gallery">
                        🖼️ Crear Gallery Post
                    </button>
                    <button type="button" class="button" id="reset-form">
                        🔄 Limpiar Formulario
                    </button>
                </div>
                
                <!-- Campos ocultos -->
                <input type="hidden" name="action" value="medialab_publish_gallery">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('medialab_nonce'); ?>">
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    // Variables globales
    let selectedImages = [];
    let selectedImagesData = {}; 
    let galleryFrame, featuredImageFrame;
    
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
    }
    
    // Verificar que wp.media esté disponible
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('wp.media no está disponible');
        $('#select-gallery-images, #select-featured-image').prop('disabled', true).text('Media Library no disponible');
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
            
            // Agregar nuevas imágenes a las existentes
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
        selectedImages = [];
        selectedImagesData = {};
        updateGalleryPreview();
    });
    
    // Función para actualizar preview de galería
    function updateGalleryPreview() {
        var $preview = $('#gallery-preview');
        var $container = $('.gallery-container');
        var $clearBtn = $('#clear-gallery-images');
        
        if (selectedImages.length === 0) {
            $preview.empty();
            $container.removeClass('has-images');
            $clearBtn.hide();
            $('#gallery_images').val('');
            return;
        }
        
        $container.addClass('has-images');
        $clearBtn.show();
        
        // Agregar contador
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
                previewHtml += '<img src="' + imageUrl + '" alt="Imagen ' + (index + 1) + '">';
            } else {
                previewHtml += '<div class="loading-placeholder">Cargando...</div>';
            }
            
            previewHtml += '<button type="button" class="remove-image" data-id="' + imageId + '">×</button>';
            previewHtml += '</div>';
        });
        
        $preview.html(previewHtml);
        
        // Actualizar campo oculto
        $('#gallery_images').val(JSON.stringify(selectedImages));
    }
    
    // Remover imagen individual de la galería
    $(document).on('click', '.remove-image', function() {
        var imageId = parseInt($(this).data('id'));
        var index = selectedImages.indexOf(imageId);
        
        if (index > -1) {
            selectedImages.splice(index, 1);
            delete selectedImagesData[imageId];
            updateGalleryPreview();
        }
    });
    
    // Media Library para imagen destacada
    $('#select-featured-image').on('click', function(e) {
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
            
            var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? 
                          attachment.sizes.thumbnail.url : 
                          attachment.url;
            
            $('#featured-image-preview').html(
                '<img src="' + imageUrl + '" style="max-width: 150px; height: auto; border: 1px solid #ddd; border-radius: 4px; margin-top: 10px;">'
            );
            $('#remove-featured-image').show();
            $('.featured-image-container').addClass('has-image');
        });
        
        featuredImageFrame.open();
    });
    
    // Remover imagen destacada
    $('#remove-featured-image').on('click', function() {
        $('#featured_image_id').val('');
        $('#featured-image-preview').empty();
        $(this).hide();
        $('.featured-image-container').removeClass('has-image');
    });
    
    // Reset del formulario
    $('#reset-form').on('click', function() {
        if (confirm('¿Estás seguro de que quieres limpiar todo el formulario?')) {
            document.getElementById('medialab-gallery-form').reset();
            selectedImages = [];
            selectedImagesData = {};
            updateGalleryPreview();
            $('#featured-image-preview').empty();
            $('#featured_image_id').val('');
            $('#remove-featured-image').hide();
            $('.featured-image-container').removeClass('has-image');
            $('#medialab-messages').empty();
        }
    });
    
    // Manejar envío del formulario
    $('#medialab-gallery-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitBtn = $('#submit-gallery');
        var $messages = $('#medialab-messages');
        
        // Validaciones del frontend
        if (selectedImages.length < 2) {
            $messages.html(
                '<div class="medialab-notice error">' +
                '<strong>Error:</strong> Debes seleccionar al menos 2 imágenes para la galería' +
                '</div>'
            );
            $('html, body').animate({scrollTop: 0}, 500);
            return;
        }
        
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
        $submitBtn.prop('disabled', true).text('Creando galería...');
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
                    // Éxito
                    $messages.html(
                        '<div class="medialab-notice success">' +
                        '<strong>¡Éxito!</strong> ' + response.data.message + '<br>' +
                        '<a href="' + response.data.edit_url + '" target="_blank" class="button button-primary" style="margin-top: 10px;">✏️ Editar post</a>' +
                        '</div>'
                    );
                    
                    // Reset completo del formulario
                    $form[0].reset();
                    selectedImages = [];
                    selectedImagesData = {};
                    updateGalleryPreview();
                    $('#featured-image-preview').empty();
                    $('#featured_image_id').val('');
                    $('#remove-featured-image').hide();
                    $('.featured-image-container').removeClass('has-image');
                } else {
                    // Error
                    $messages.html(
                        '<div class="medialab-notice error">' +
                        '<strong>Error:</strong> ' + response.data +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                $messages.html(
                    '<div class="medialab-notice error">' +
                    '<strong>Error:</strong> No se pudo conectar con el servidor' +
                    '</div>'
                );
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text('🖼️ Crear Gallery Post');
                $('html, body').animate({scrollTop: 0}, 500);
            }
        });
    });
});
</script>
<?php
/**
 * MediaLab - Pending Material Admin View
 * Vista administrativa para gestionar material pendiente de graduaciones con modales
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Pending_Material {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_pending_menu'));
        
        // AJAX handlers
        add_action('wp_ajax_medialab_assign_responsible', array($this, 'handle_assign_responsible'));
        add_action('wp_ajax_medialab_complete_video', array($this, 'handle_complete_video'));
        add_action('wp_ajax_medialab_complete_gallery', array($this, 'handle_complete_gallery'));
        add_action('wp_ajax_medialab_get_post_data', array($this, 'handle_get_post_data'));
    }
    
    public function add_pending_menu() {
        add_submenu_page(
            'medialab',
            'Material Pendiente',
            'Material Pendiente',
            'edit_posts',
            'medialab-pending',
            array($this, 'pending_page')
        );
    }
    
    public function pending_page() {
        // Procesar filtros
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $responsable_filter = isset($_GET['responsable']) ? intval($_GET['responsable']) : 0;
        
        // Solo año en curso
        $current_year = date('Y');
        
        // Obtener graduaciones pendientes
        $pending_posts = $this->get_pending_graduations($status_filter, $responsable_filter, $current_year);
        
        // Obtener usuarios para filtros
        $users = get_users(array('who' => 'authors'));
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">📋 Material Pendiente - Graduaciones <?php echo $current_year; ?></h1>
            <hr class="wp-header-end">
            
            <!-- Filtros -->
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="get" style="display: inline-flex; gap: 10px; align-items: center;">
                        <input type="hidden" name="page" value="medialab-pending">
                        
                        <select name="status" id="filter-status">
                            <option value="">Todos los estados</option>
                            <option value="pendiente_todo" <?php selected($status_filter, 'pendiente_todo'); ?>>⏳ Pendiente Todo</option>
                            <option value="solo_video" <?php selected($status_filter, 'solo_video'); ?>>🎥 Solo Video</option>
                            <option value="solo_fotos" <?php selected($status_filter, 'solo_fotos'); ?>>📷 Solo Fotos</option>
                        </select>
                        
                        <select name="responsable" id="filter-responsable">
                            <option value="">Todos los responsables</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user->ID; ?>" <?php selected($responsable_filter, $user->ID); ?>>
                                    <?php echo esc_html($user->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="submit" class="button" value="Filtrar">
                        <a href="<?php echo admin_url('admin.php?page=medialab-pending'); ?>" class="button">Limpiar</a>
                    </form>
                </div>
                
                <div class="alignright">
                    <span class="displaying-num"><?php echo count($pending_posts); ?> elementos</span>
                </div>
            </div>
            
            <!-- Tabla -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" style="width: 25%;">Graduación</th>
                        <th scope="col" style="width: 12%;">Fecha</th>
                        <th scope="col" style="width: 10%;">Facultad</th>
                        <th scope="col" style="width: 15%;">Estado</th>
                        <th scope="col" style="width: 15%;">Responsables</th>
                        <th scope="col" style="width: 23%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pending_posts)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <strong>🎉 No hay material pendiente</strong><br>
                                <span style="color: #666;">Todas las graduaciones de <?php echo $current_year; ?> están completas</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pending_posts as $post): ?>
                            <?php
                            $estado = get_field('estado_material', $post->ID);
                            $responsable_video = get_field('responsable_video', $post->ID);
                            $responsable_fotos = get_field('responsable_fotos', $post->ID);
                            $facultad = get_field('facultad', $post->ID);
                            $post_date = get_the_date('d/m/Y', $post->ID);
                            
                            // Determinar estado visual
                            switch ($estado) {
                                case 'pendiente_todo':
                                    $estado_html = '<span style="color: #d63638;">⏳ Pendiente Todo</span>';
                                    break;
                                case 'solo_video':
                                    $estado_html = '<span style="color: #f56e28;">🎥 Solo Video</span>';
                                    break;
                                case 'solo_fotos':
                                    $estado_html = '<span style="color: #f56e28;">📷 Solo Fotos</span>';
                                    break;
                                default:
                                    $estado_html = '<span style="color: #666;">❓ Sin estado</span>';
                            }
                            ?>
                            <tr data-post-id="<?php echo $post->ID; ?>">
                                <td>
                                    <strong><?php echo esc_html($post->post_title); ?></strong><br>
                                    <small style="color: #666;"><?php echo get_field('subtitulo', $post->ID); ?></small>
                                </td>
                                <td><?php echo $post_date; ?></td>
                                <td><?php echo esc_html($facultad); ?></td>
                                <td><?php echo $estado_html; ?></td>
                                <td>
                                    <?php if ($responsable_video): ?>
                                        <div>📹 <?php echo esc_html($responsable_video->display_name); ?></div>
                                    <?php endif; ?>
                                    <?php if ($responsable_fotos): ?>
                                        <div>📷 <?php echo esc_html($responsable_fotos->display_name); ?></div>
                                    <?php endif; ?>
                                    <?php if (!$responsable_video && !$responsable_fotos): ?>
                                        <span style="color: #666;">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <!-- Botón Asignar Responsable -->
                                        <button type="button" 
                                                class="button button-small btn-assign-responsible" 
                                                data-post-id="<?php echo $post->ID; ?>"
                                                data-estado="<?php echo $estado; ?>">
                                            👥 Asignar
                                        </button>
                                        
                                        <!-- Botón Completar según estado -->
                                        <?php if ($estado === 'solo_video' || $estado === 'pendiente_todo'): ?>
                                            <button type="button" 
                                                    class="button button-small button-primary btn-complete-gallery" 
                                                    data-post-id="<?php echo $post->ID; ?>">
                                                📷 + Fotos
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($estado === 'solo_fotos' || $estado === 'pendiente_todo'): ?>
                                            <button type="button" 
                                                    class="button button-small button-primary btn-complete-video" 
                                                    data-post-id="<?php echo $post->ID; ?>">
                                                🎥 + Video
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Botón Editar post -->
                                        <a href="<?php echo admin_url('post.php?post=' . $post->ID . '&action=edit'); ?>" 
                                           class="button button-small" 
                                           target="_blank">
                                            ✏️ Editar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal Asignar Responsable -->
        <div id="modal-assign-responsible" style="display: none;">
            <div class="modal-content">
                <h3>👥 Asignar Responsable</h3>
                <form id="form-assign-responsible">
                    <table class="form-table">
                        <tbody id="assign-responsible-fields">
                            <!-- Campos dinámicos según estado -->
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Asignar Responsable">
                        <button type="button" class="button" onclick="tb_remove();">Cancelar</button>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Modal Completar Video -->
        <div id="modal-complete-video" style="display: none;">
            <div class="modal-content">
                <h3>🎥 Agregar Video de Graduación</h3>
                <form id="form-complete-video">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="video-link">Link del Video</label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="video-link" 
                                           name="video_link" 
                                           class="regular-text" 
                                           placeholder="https://youtube.com/watch?v=..."
                                           required>
                                    <p class="description">URL completa del video de la ceremonia</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Guardar Video">
                        <button type="button" class="button" onclick="tb_remove();">Cancelar</button>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Modal Completar Galería -->
        <div id="modal-complete-gallery" style="display: none;">
            <div class="modal-content">
                <h3>📷 Agregar Fotos de Graduación</h3>
                <form id="form-complete-gallery">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">Galería de Fotos</th>
                                <td>
                                    <button type="button" id="select-modal-gallery" class="button">
                                        📷 Seleccionar Fotos
                                    </button>
                                    <div id="modal-gallery-preview" style="margin-top: 15px;"></div>
                                    <input type="hidden" id="modal-gallery-images" name="gallery_images" value="">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Guardar Fotos">
                        <button type="button" class="button" onclick="tb_remove();">Cancelar</button>
                    </p>
                </form>
            </div>
        </div>
        
        <style>
        .modal-content {
            padding: 20px;
            background: white;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .modal-content h3 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        #modal-gallery-preview img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin: 3px;
            border-radius: 3px;
        }
        
        .btn-remove-modal-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #d63638;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var currentPostId = 0;
            var modalGalleryImages = [];
            var modalGalleryFrame;
            
            // Asignar responsable
            $('.btn-assign-responsible').on('click', function() {
                currentPostId = $(this).data('post-id');
                var estado = $(this).data('estado');
                
                // Crear campos dinámicos según estado
                var fields = '';
                
                if (estado === 'solo_fotos' || estado === 'pendiente_todo') {
                    fields += '<tr><th scope="row"><label for="responsable-video">Responsable Video</label></th>';
                    fields += '<td><select id="responsable-video" name="responsable_video"><option value="">Seleccionar usuario...</option>';
                    <?php foreach ($users as $user): ?>
                    fields += '<option value="<?php echo $user->ID; ?>"><?php echo esc_js($user->display_name); ?></option>';
                    <?php endforeach; ?>
                    fields += '</select></td></tr>';
                }
                
                if (estado === 'solo_video' || estado === 'pendiente_todo') {
                    fields += '<tr><th scope="row"><label for="responsable-fotos">Responsable Fotos</label></th>';
                    fields += '<td><select id="responsable-fotos" name="responsable_fotos"><option value="">Seleccionar usuario...</option>';
                    <?php foreach ($users as $user): ?>
                    fields += '<option value="<?php echo $user->ID; ?>"><?php echo esc_js($user->display_name); ?></option>';
                    <?php endforeach; ?>
                    fields += '</select></td></tr>';
                }
                
                $('#assign-responsible-fields').html(fields);
                
                tb_show('Asignar Responsable', '#TB_inline?inlineId=modal-assign-responsible&width=500&height=300');
            });
            
            // Completar video
            $('.btn-complete-video').on('click', function() {
                currentPostId = $(this).data('post-id');
                $('#video-link').val('');
                tb_show('Agregar Video', '#TB_inline?inlineId=modal-complete-video&width=500&height=250');
            });
            
            // Completar galería
            $('.btn-complete-gallery').on('click', function() {
                currentPostId = $(this).data('post-id');
                modalGalleryImages = [];
                $('#modal-gallery-preview').empty();
                $('#modal-gallery-images').val('');
                tb_show('Agregar Fotos', '#TB_inline?inlineId=modal-complete-gallery&width=600&height=400');
            });
            
            // Media Library para modal galería
            $('#select-modal-gallery').on('click', function(e) {
                e.preventDefault();
                
                if (modalGalleryFrame) {
                    modalGalleryFrame.open();
                    return;
                }
                
                modalGalleryFrame = wp.media({
                    title: 'Seleccionar Fotos de Graduación',
                    button: { text: 'Agregar Fotos' },
                    multiple: true,
                    library: { type: 'image' }
                });
                
                modalGalleryFrame.on('select', function() {
                    var attachments = modalGalleryFrame.state().get('selection').toJSON();
                    
                    attachments.forEach(function(attachment) {
                        if (modalGalleryImages.indexOf(attachment.id) === -1) {
                            modalGalleryImages.push(attachment.id);
                            
                            var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? 
                                          attachment.sizes.thumbnail.url : attachment.url;
                            
                            $('#modal-gallery-preview').append(
                                '<div style="position: relative; display: inline-block;">' +
                                '<img src="' + imageUrl + '" alt="Foto">' +
                                '<button type="button" class="btn-remove-modal-image" data-id="' + attachment.id + '">×</button>' +
                                '</div>'
                            );
                        }
                    });
                    
                    $('#modal-gallery-images').val(JSON.stringify(modalGalleryImages));
                });
                
                modalGalleryFrame.open();
            });
            
            // Remover imagen del modal
            $(document).on('click', '.btn-remove-modal-image', function() {
                var imageId = parseInt($(this).data('id'));
                var index = modalGalleryImages.indexOf(imageId);
                if (index > -1) {
                    modalGalleryImages.splice(index, 1);
                    $(this).parent().remove();
                    $('#modal-gallery-images').val(JSON.stringify(modalGalleryImages));
                }
            });
            
            // Submit asignar responsable
            $('#form-assign-responsible').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'medialab_assign_responsible',
                        post_id: currentPostId,
                        responsable_video: $('#responsable-video').val() || '',
                        responsable_fotos: $('#responsable-fotos').val() || '',
                        nonce: '<?php echo wp_create_nonce('medialab_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            tb_remove();
                            alert('✅ ' + response.data.message + '\nNuevo estado: ' + response.data.nuevo_estado);
                            location.reload();
                        } else {
                            alert('❌ Error: ' + response.data);
                        }
                    }
                });
            });
            
            // Submit completar video
            $('#form-complete-video').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'medialab_complete_video',
                        post_id: currentPostId,
                        video_link: $('#video-link').val(),
                        nonce: '<?php echo wp_create_nonce('medialab_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            tb_remove();
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
            
            // Submit completar galería
            $('#form-complete-gallery').on('submit', function(e) {
                e.preventDefault();
                
                if (modalGalleryImages.length === 0) {
                    alert('Selecciona al menos una foto');
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'medialab_complete_gallery',
                        post_id: currentPostId,
                        gallery_images: JSON.stringify(modalGalleryImages),
                        nonce: '<?php echo wp_create_nonce('medialab_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            tb_remove();
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    private function get_pending_graduations($status_filter = '', $responsable_filter = 0, $year = null) {
        $year = $year ?: date('Y');
        
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'category_name' => 'graduaciones',
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'year' => $year,
                ),
            ),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'estado_material',
                    'value' => 'completo',
                    'compare' => '!='
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        // Filtro por estado
        if (!empty($status_filter)) {
            $args['meta_query'][] = array(
                'key' => 'estado_material',
                'value' => $status_filter,
                'compare' => '='
            );
        }
        
        // Filtro por responsable
        if ($responsable_filter > 0) {
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => 'responsable_video',
                    'value' => $responsable_filter,
                    'compare' => '='
                ),
                array(
                    'key' => 'responsable_fotos',
                    'value' => $responsable_filter,
                    'compare' => '='
                )
            );
        }
        
        return get_posts($args);
    }
    
    // AJAX Handlers
    public function handle_assign_responsible() {
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        $post_id = intval($_POST['post_id']);
        $responsable_video = intval($_POST['responsable_video']);
        $responsable_fotos = intval($_POST['responsable_fotos']);
        
        if ($responsable_video > 0) {
            update_field('responsable_video', $responsable_video, $post_id);
        }
        
        if ($responsable_fotos > 0) {
            update_field('responsable_fotos', $responsable_fotos, $post_id);
        }
        
        wp_send_json_success('Responsable asignado correctamente');
    }
    
    public function handle_complete_video() {
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        $post_id = intval($_POST['post_id']);
        $video_link = esc_url_raw($_POST['video_link']);
        
        if (empty($video_link)) {
            wp_send_json_error('El link del video es obligatorio');
        }
        
        // Guardar video
        update_field('link', $video_link, $post_id);
        
        // Recalcular estado automáticamente (se ejecuta por hook acf/save_post)
        do_action('acf/save_post', $post_id);
        
        wp_send_json_success('Video agregado correctamente');
    }
    
    public function handle_complete_gallery() {
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        $post_id = intval($_POST['post_id']);
        $gallery_images = $_POST['gallery_images'];
        
        if (empty($gallery_images)) {
            wp_send_json_error('Debes seleccionar al menos una imagen');
        }
        
        // Validar JSON
        $images_array = json_decode($gallery_images, true);
        if (!is_array($images_array) || empty($images_array)) {
            wp_send_json_error('Formato de imágenes inválido');
        }
        
        // Guardar galería
        update_field('gallery_images', $gallery_images, $post_id);
        
        // Crear gallery block en content
        $gallery_block = $this->create_gallery_block($images_array);
        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $gallery_block
        ));
        
        // Asociar imágenes al post
        foreach ($images_array as $image_id) {
            wp_update_post(array(
                'ID' => intval($image_id),
                'post_parent' => $post_id
            ));
        }
        
        // Recalcular estado automáticamente
        do_action('acf/save_post', $post_id);
        
        wp_send_json_success('Galería agregada correctamente');
    }
    
    private function create_gallery_block($image_ids) {
        if (empty($image_ids) || !is_array($image_ids)) {
            return '';
        }
        
        $gallery_html = '<!-- wp:gallery {"ids":[' . implode(',', $image_ids) . '],"columns":3,"linkTo":"media","sizeSlug":"large"} -->';
        $gallery_html .= '<figure class="wp-block-gallery has-nested-images columns-3 is-cropped">';
        
        foreach ($image_ids as $image_id) {
            $image_id = intval($image_id);
            $image = wp_get_attachment_image_src($image_id, 'large');
            
            if ($image) {
                $gallery_html .= '<!-- wp:image {"id":' . $image_id . ',"sizeSlug":"large","linkDestination":"media"} -->';
                $gallery_html .= '<figure class="wp-block-image size-large">';
                $gallery_html .= '<a href="' . esc_url($image[0]) . '">';
                $gallery_html .= '<img src="' . esc_url($image[0]) . '" class="wp-image-' . $image_id . '"/>';
                $gallery_html .= '</a></figure>';
                $gallery_html .= '<!-- /wp:image -->';
            }
        }
        
        $gallery_html .= '</figure>';
        $gallery_html .= '<!-- /wp:gallery -->';
        
        return $gallery_html;
    }
}

// Inicializar la clase
new MediaLab_Pending_Material();
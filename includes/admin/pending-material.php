<?php
/**
 * MediaLab - Pending Material Admin View (CORREGIDO)
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
        
        // NUEVO: Actualización manual de estado
        add_action('wp_ajax_medialab_update_status_manual', array($this, 'handle_update_status_manual'));
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
                                <?php if (current_user_can('manage_options')): ?>
                                    <br><br>
                                    <details style="margin-top: 15px;">
                                        <summary style="cursor: pointer; color: #666; font-size: 12px;">🔍 Debug Info (Solo administradores)</summary>
                                        <div style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; font-size: 11px; text-align: left;">
                                            <?php
                                            // Debug: Mostrar todas las graduaciones del año
                                            $all_graduations = get_posts(array(
                                                'post_type' => 'post',
                                                'category_name' => 'graduaciones',
                                                'posts_per_page' => -1,
                                                'date_query' => array(array('year' => $current_year)),
                                                'orderby' => 'date',
                                                'order' => 'DESC'
                                            ));
                                            
                                            echo "<strong>Total graduaciones {$current_year}:</strong> " . count($all_graduations) . "<br>";
                                            
                                            foreach ($all_graduations as $grad) {
                                                $estado = get_field('estado_material', $grad->ID) ?: 'SIN_ESTADO';
                                                echo "• {$grad->post_title} → {$estado}<br>";
                                            }
                                            ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
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
                                        
                                        <!-- NUEVO: Botón para cambio manual de estado -->
                                        <button type="button" 
                                                class="button button-small btn-change-status" 
                                                data-post-id="<?php echo $post->ID; ?>"
                                                data-current-status="<?php echo $estado; ?>"
                                                style="background: #8c8f94; color: white;">
                                            ⚙️ Estado
                                        </button>
                                        
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
        
        <!-- NUEVO: Modal Cambiar Estado Manual -->
        <div id="modal-change-status" style="display: none;">
            <div class="modal-content">
                <h3>⚙️ Cambiar Estado del Material</h3>
                <form id="form-change-status">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="new-status">Nuevo Estado</label>
                                </th>
                                <td>
                                    <select id="new-status" name="new_status" class="regular-text" required>
                                        <option value="">Seleccionar estado...</option>
                                        <option value="completo">✅ Completo (Video + Fotos)</option>
                                        <option value="solo_video">🎥 Solo Video (Faltan Fotos)</option>
                                        <option value="solo_fotos">📷 Solo Fotos (Falta Video)</option>
                                        <option value="pendiente_todo">⏳ Pendiente Todo</option>
                                    </select>
                                    <p class="description">Cambio manual del estado. Se desactivará la detección automática.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="disable-auto-detect">Detección Automática</label>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="disable-auto-detect" name="disable_auto_detect" value="1">
                                        Desactivar detección automática para este post
                                    </label>
                                    <p class="description">Si se marca, el estado no se actualizará automáticamente al agregar/quitar contenido.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="Actualizar Estado">
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
            
            // NUEVO: Cambiar estado manual
            $('.btn-change-status').on('click', function() {
                currentPostId = $(this).data('post-id');
                var currentStatus = $(this).data('current-status');
                
                // Preseleccionar el estado actual
                $('#new-status').val(currentStatus);
                
                tb_show('Cambiar Estado', '#TB_inline?inlineId=modal-change-status&width=500&height=350');
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
                            alert('✅ ' + response.data.message);
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
                            alert('✅ Video agregado correctamente');
                            location.reload();
                        } else {
                            alert('❌ Error: ' + response.data);
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
                
                var $submitBtn = $(this).find('[type="submit"]');
                $submitBtn.prop('disabled', true).val('Guardando...');
                
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
                            var message = '✅ ' + response.data.message;
                            if (response.data.new_status === 'completo') {
                                message += '\n🎉 ¡La graduación está ahora completa!';
                            }
                            alert(message);
                            location.reload();
                        } else {
                            alert('❌ Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('❌ Error de conexión');
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).val('Guardar Fotos');
                    }
                });
            });
            
            // NUEVO: Submit cambiar estado manual
            $('#form-change-status').on('submit', function(e) {
                e.preventDefault();
                
                var newStatus = $('#new-status').val();
                var disableAutoDetect = $('#disable-auto-detect').is(':checked');
                
                if (!newStatus) {
                    alert('Selecciona un estado');
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'medialab_update_status_manual',
                        post_id: currentPostId,
                        new_status: newStatus,
                        disable_auto_detect: disableAutoDetect ? '1' : '0',
                        nonce: '<?php echo wp_create_nonce('medialab_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            tb_remove();
                            alert('✅ Estado actualizado correctamente');
                            location.reload();
                        } else {
                            alert('❌ Error: ' + response.data);
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
        
        wp_send_json_success(array('message' => 'Responsable asignado correctamente'));
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
        
        // MEJORA: Forzar actualización del estado después de agregar video
        $this->force_update_material_status($post_id);
        
        wp_send_json_success(array('message' => 'Video agregado correctamente'));
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
        
        // MEJORA: Forzar actualización del estado después de agregar galería
        $this->force_update_material_status($post_id);
        
        wp_send_json_success(array('message' => 'Galería agregada correctamente'));
    }
    
    // NUEVO: Handler para actualización manual de estado
    public function handle_update_status_manual() {
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        $post_id = intval($_POST['post_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        $disable_auto_detect = $_POST['disable_auto_detect'] === '1';
        
        // Validar estado
        $valid_statuses = array('completo', 'solo_video', 'solo_fotos', 'pendiente_todo');
        if (!in_array($new_status, $valid_statuses)) {
            wp_send_json_error('Estado inválido');
        }
        
        // Actualizar estado
        update_field('estado_material', $new_status, $post_id);
        
        // Actualizar configuración de detección automática
        update_field('auto_detect_status', !$disable_auto_detect, $post_id);
        
        $message = 'Estado actualizado correctamente';
        if ($disable_auto_detect) {
            $message .= ' (detección automática desactivada)';
        }
        
        wp_send_json_success(array('message' => $message));
    }
    
    /**
     * NUEVA FUNCIÓN: Forzar actualización de estado
     */
    private function force_update_material_status($post_id) {
        // Verificar si existe la función en graduation-post.php
        $graduation_class = new MediaLab_Graduation_Post();
        if (method_exists($graduation_class, 'force_update_material_status')) {
            return $graduation_class->force_update_material_status($post_id);
        }
        
        // Fallback: actualizar manualmente
        $tiene_video = !empty(get_field('link', $post_id));
        $gallery_images = get_field('gallery_images', $post_id);
        
        $tiene_fotos = false;
        if (!empty($gallery_images)) {
            if (is_string($gallery_images)) {
                $decoded = json_decode($gallery_images, true);
                $tiene_fotos = !empty($decoded) && is_array($decoded) && count($decoded) > 0;
            } else if (is_array($gallery_images)) {
                $tiene_fotos = count($gallery_images) > 0;
            }
        }
        
        // También verificar contenido del post
        if (!$tiene_fotos) {
            $post_content = get_post_field('post_content', $post_id);
            $tiene_fotos = strpos($post_content, 'wp:gallery') !== false || strpos($post_content, 'wp-block-gallery') !== false;
        }
        
        // Determinar estado
        if ($tiene_video && $tiene_fotos) {
            $estado = 'completo';
        } elseif ($tiene_video && !$tiene_fotos) {
            $estado = 'solo_video';
        } elseif (!$tiene_video && $tiene_fotos) {
            $estado = 'solo_fotos';
        } else {
            $estado = 'pendiente_todo';
        }
        
        update_field('estado_material', $estado, $post_id);
        
        return $estado;
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
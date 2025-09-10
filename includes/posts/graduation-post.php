<?php
/**
 * MediaLab - Graduation Post Module (LIMPIO + NOTIFICACIONES)
 * Maneja posts de graduación con categoría fija "Graduaciones" y sistema de notificaciones
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Graduation_Post {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_graduation_menu'));
        add_action('init', array($this, 'create_acf_fields'));
        
        // AJAX handlers
        add_action('wp_ajax_medialab_publish_graduation', array($this, 'handle_graduation_post'));
        add_action('wp_ajax_nopriv_medialab_publish_graduation', array($this, 'handle_graduation_post'));
        
        // Hook para detectar estado automáticamente
        add_action('acf/save_post', array($this, 'auto_detect_material_status'), 20);
        
        // Asegurar que existe la categoría Graduaciones
        add_action('init', array($this, 'ensure_graduaciones_category'));
        
        // NUEVOS: Hooks adicionales
        add_action('before_delete_post', array($this, 'cleanup_graduation_data'));
        add_filter('post_row_actions', array($this, 'add_graduation_row_actions'), 10, 2);
    }
    
    public function add_graduation_menu() {
        add_submenu_page(
            'medialab-posts',
            'Crear Graduation Post',
            'Graduation Post',
            'publish_posts',
            'medialab-graduation',
            array($this, 'graduation_page')
        );
    }
    
    public function graduation_page() {
        wp_enqueue_media();
        include MEDIALAB_PLUGIN_PATH . 'views/posts/graduation-form.php';
    }
    
    /**
     * Asegurar que existe la categoría Graduaciones
     */
    public function ensure_graduaciones_category() {
        if (!get_category_by_slug('graduaciones')) {
            wp_create_category('Graduaciones');
        }
    }
    
    /**
     * Obtener ID de categoría Graduaciones
     */
    public function get_graduaciones_category_id() {
        $cat = get_category_by_slug('graduaciones');
        return $cat ? $cat->term_id : false;
    }
    
    public function create_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        acf_add_local_field_group(array(
            'key' => 'group_medialab_graduation',
            'title' => 'MediaLab - Graduation Post',
            'fields' => array(
                array(
                    'key' => 'field_graduation_link',
                    'label' => 'Link del Video',
                    'name' => 'link',
                    'type' => 'url',
                    'required' => 0,
                    'placeholder' => 'https://youtube.com/watch?v=...'
                ),
                array(
                    'key' => 'field_graduation_subtitulo',
                    'label' => 'Subtítulo',
                    'name' => 'subtitulo',
                    'type' => 'text',
                    'required' => 1,
                    'default_value' => 'Ceremonia de Graduación',
                    'maxlength' => 200
                ),
                array(
                    'key' => 'field_graduation_facultad',
                    'label' => 'Facultad',
                    'name' => 'facultad',
                    'type' => 'text',
                    'required' => 1
                ),
                array(
                    'key' => 'field_graduation_gallery_images',
                    'label' => 'Galería de Imágenes',
                    'name' => 'gallery_images',
                    'type' => 'text',
                    'required' => 0
                ),
                // Campos para Material Pendiente
                array(
                    'key' => 'field_graduation_estado_material',
                    'label' => 'Estado del Material',
                    'name' => 'estado_material',
                    'type' => 'select',
                    'required' => 0,
                    'choices' => array(
                        'completo' => '✅ Completo (Video + Fotos)',
                        'solo_video' => '🎥 Solo Video (Faltan Fotos)',
                        'solo_fotos' => '📷 Solo Fotos (Falta Video)',
                        'pendiente_todo' => '⏳ Pendiente Todo'
                    ),
                    'default_value' => 'pendiente_todo',
                    'return_format' => 'value'
                ),
                array(
                    'key' => 'field_graduation_responsable_video',
                    'label' => 'Responsable Video',
                    'name' => 'responsable_video',
                    'type' => 'user',
                    'required' => 0,
                    'return_format' => 'object'
                ),
                array(
                    'key' => 'field_graduation_responsable_fotos',
                    'label' => 'Responsable Fotos',
                    'name' => 'responsable_fotos',
                    'type' => 'user',
                    'required' => 0,
                    'return_format' => 'object'
                )
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post'
                    ),
                    array(
                        'param' => 'post_category',
                        'operator' => '==',
                        'value' => 'graduaciones'
                    )
                )
            )
        ));
    }
    
    public function handle_graduation_post() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('publish_posts')) {
            wp_send_json_error('No tienes permisos suficientes');
        }
        
        // Validar datos
        $validation = $this->validate_graduation_data($_POST);
        if (!$validation['valid']) {
            wp_send_json_error($validation['message']);
        }
        
        // Crear el post
        $result = $this->create_graduation_post($_POST);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    private function validate_graduation_data($data) {
        $errors = array();
        
        if (empty($data['post_title'])) {
            $errors[] = 'El título es obligatorio';
        }
        
        if (empty($data['subtitulo'])) {
            $errors[] = 'El subtítulo es obligatorio';
        }
        
        if (empty($data['facultad'])) {
            $errors[] = 'La facultad es obligatoria';
        }
        
        if (empty($data['post_excerpt'])) {
            $errors[] = 'La descripción es obligatoria';
        }
        
        if (empty($data['featured_image_id'])) {
            $errors[] = 'La imagen destacada es obligatoria';
        }
        
        if (!empty($data['link']) && !filter_var($data['link'], FILTER_VALIDATE_URL)) {
            $errors[] = 'El link del video no es válido';
        }
        
        return array(
            'valid' => empty($errors),
            'message' => implode(', ', $errors)
        );
    }
    
    private function create_graduation_post($data) {
        try {
            // Procesar galería de imágenes
            $gallery_images = array();
            if (!empty($data['gallery_images'])) {
                if (is_string($data['gallery_images'])) {
                    $gallery_images = json_decode($data['gallery_images'], true);
                } else if (is_array($data['gallery_images'])) {
                    $gallery_images = $data['gallery_images'];
                }
            }
            
            // Crear contenido con gallery block si hay imágenes
            $post_content = '';
            if (!empty($gallery_images) && is_array($gallery_images)) {
                $post_content = $this->create_gallery_block($gallery_images);
            }
            
            // Datos del post
            $post_data = array(
                'post_title'    => sanitize_text_field($data['post_title']),
                'post_content'  => $post_content,
                'post_excerpt'  => sanitize_textarea_field($data['post_excerpt']),
                'post_status'   => 'publish',
                'post_type'     => 'post',
                'post_author'   => get_current_user_id(),
                'post_date'     => !empty($data['post_date']) ? $data['post_date'] : current_time('mysql')
            );
            
            // Insertar el post
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                return array(
                    'success' => false,
                    'message' => 'Error al crear el post: ' . $post_id->get_error_message()
                );
            }
            
            // Asignar categoría Graduaciones
            $graduaciones_cat_id = $this->get_graduaciones_category_id();
            if ($graduaciones_cat_id) {
                wp_set_post_categories($post_id, array($graduaciones_cat_id));
            }
            
            // Asignar etiquetas si se seleccionaron
            if (!empty($data['post_tags']) && is_array($data['post_tags'])) {
                $tag_ids = array_map('intval', $data['post_tags']);
                wp_set_post_tags($post_id, $tag_ids);
            }
            
            // Guardar campos ACF
            $this->save_graduation_fields($post_id, $data);
            
            // Asignar imagen destacada
            if (!empty($data['featured_image_id'])) {
                set_post_thumbnail($post_id, intval($data['featured_image_id']));
            }
            
            // Asociar imágenes de galería al post
            if (!empty($gallery_images) && is_array($gallery_images)) {
                $this->attach_images_to_post($post_id, $gallery_images);
            }
            
            // Detectar estado del material automáticamente
            $estado_final = $this->detect_and_save_material_status($post_id, $data);
            
            // NUEVO: Disparar hook para notificación de graduación pendiente
            if ($estado_final !== 'completo') {
                do_action('medialab_graduation_published_pending', $post_id, $estado_final);
            }
            
            // Mensaje con información del estado
            $status_message = $this->get_status_message($estado_final);
            $email_info = '';
            if ($estado_final !== 'completo' && get_option('medialab_email_enabled', 0) == 1) {
                $email_info = ' Notificación enviada a supervisores.';
            }
            
            return array(
                'success' => true,
                'message' => 'Post de graduación creado exitosamente. ' . $status_message . $email_info,
                'post_id' => $post_id,
                'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit'),
                'material_status' => $estado_final
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error inesperado: ' . $e->getMessage()
            );
        }
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
    
    private function save_graduation_fields($post_id, $data) {
        if (isset($data['link'])) {
            update_field('link', esc_url_raw($data['link']), $post_id);
        }
        
        if (!empty($data['subtitulo'])) {
            update_field('subtitulo', sanitize_text_field($data['subtitulo']), $post_id);
        }
        
        if (!empty($data['facultad'])) {
            update_field('facultad', sanitize_text_field($data['facultad']), $post_id);
        }
        
        if (!empty($data['gallery_images'])) {
            if (is_string($data['gallery_images'])) {
                update_field('gallery_images', $data['gallery_images'], $post_id);
            } else if (is_array($data['gallery_images'])) {
                update_field('gallery_images', json_encode($data['gallery_images']), $post_id);
            }
        }
    }
    
    /**
     * Detectar estado del material automáticamente
     */
    private function detect_and_save_material_status($post_id, $data = null) {
        if ($data) {
            // Desde formulario
            $tiene_video = !empty($data['link']);
            $tiene_fotos = !empty($data['gallery_images']) && 
                          (is_array($data['gallery_images']) ? count($data['gallery_images']) > 0 : 
                           (is_string($data['gallery_images']) && !empty(json_decode($data['gallery_images'], true))));
        } else {
            // Desde ACF hook
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
                $tiene_fotos = strpos($post_content, 'wp:gallery') !== false;
            }
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
    
    /**
     * Hook para detectar automáticamente en posts existentes
     */
    public function auto_detect_material_status($post_id) {
        // Evitar loops infinitos
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Solo para posts de graduación
        if (!has_category('graduaciones', $post_id)) {
            return;
        }
        
        // Solo para posts desde 2025
        $post_date = get_the_date('Y-m-d', $post_id);
        if ($post_date < '2025-01-01') {
            return;
        }
        
        // Obtener estado anterior
        $estado_anterior = get_field('estado_material', $post_id);
        
        // Detectar y guardar nuevo estado
        $estado_nuevo = $this->detect_and_save_material_status($post_id);
        
        // NUEVO: Disparar hook si el material se completó
        if ($estado_anterior !== 'completo' && $estado_nuevo === 'completo') {
            do_action('medialab_material_completed', $post_id, 'auto_completed');
        }
        
        // NUEVO: Disparar hook si hay nuevo material pendiente (solo si cambió el estado)
        if ($estado_anterior !== $estado_nuevo && $estado_nuevo !== 'completo') {
            do_action('medialab_graduation_published_pending', $post_id, $estado_nuevo);
        }
    }
    
    /**
     * Función pública para forzar actualización desde Material Pendiente
     */
    public function force_update_material_status($post_id) {
        return $this->detect_and_save_material_status($post_id);
    }
    
    /**
     * NUEVA FUNCIÓN: Obtener mensaje de estado amigable
     */
    private function get_status_message($estado) {
        switch ($estado) {
            case 'completo':
                return '✅ Material completo (video y fotos disponibles)';
            case 'solo_video':
                return '🎥 Solo video disponible - Aparecerá en Material Pendiente para agregar fotos';
            case 'solo_fotos':
                return '📷 Solo fotos disponibles - Aparecerá en Material Pendiente para agregar video';
            case 'pendiente_todo':
                return '⏳ Material pendiente - Aparecerá en Material Pendiente para completar';
            default:
                return 'Estado desconocido';
        }
    }
    
    /**
     * NUEVA FUNCIÓN: Obtener información completa del estado del material
     */
    public function get_material_status_info($post_id) {
        $estado = get_field('estado_material', $post_id);
        $responsable_video = get_field('responsable_video', $post_id);
        $responsable_fotos = get_field('responsable_fotos', $post_id);
        $link = get_field('link', $post_id);
        $gallery_images = get_field('gallery_images', $post_id);
        
        return array(
            'estado' => $estado,
            'mensaje' => $this->get_status_message($estado),
            'responsable_video' => $responsable_video,
            'responsable_fotos' => $responsable_fotos,
            'tiene_video' => !empty($link),
            'tiene_fotos' => !empty($gallery_images),
            'es_completo' => $estado === 'completo'
        );
    }
    
    /**
     * NUEVA FUNCIÓN: Verificar si un post necesita material
     */
    public function needs_material($post_id) {
        $estado = get_field('estado_material', $post_id);
        return in_array($estado, array('pendiente_todo', 'solo_video', 'solo_fotos'));
    }
    
    /**
     * NUEVA FUNCIÓN: Limpiar datos cuando se borra un post de graduación
     */
    public function cleanup_graduation_data($post_id) {
        // Solo para posts de graduación
        if (!has_category('graduaciones', $post_id)) {
            return;
        }
        
        // Disparar hook para notificación de eliminación si es necesario
        do_action('medialab_graduation_deleted', $post_id);
    }
    
    /**
     * NUEVA FUNCIÓN: Agregar acciones en el listado de posts
     */
    public function add_graduation_row_actions($actions, $post) {
        // Solo para posts de graduación
        if (!has_category('graduaciones', $post->ID)) {
            return $actions;
        }
        
        $status_info = $this->get_material_status_info($post->ID);
        
        // Agregar link a Material Pendiente si es necesario
        if (!$status_info['es_completo']) {
            $actions['material_pendiente'] = '<a href="' . admin_url('admin.php?page=medialab-pending') . '" title="Ver en Material Pendiente">📋 Material Pendiente</a>';
        }
        
        // Agregar indicador visual del estado
        $estado_icon = '';
        switch ($status_info['estado']) {
            case 'completo':
                $estado_icon = '<span title="Material completo">✅</span>';
                break;
            case 'solo_video':
                $estado_icon = '<span title="Solo video - faltan fotos">🎥</span>';
                break;
            case 'solo_fotos':
                $estado_icon = '<span title="Solo fotos - falta video">📷</span>';
                break;
            case 'pendiente_todo':
                $estado_icon = '<span title="Material pendiente">⏳</span>';
                break;
        }
        
        if ($estado_icon) {
            $actions['estado_material'] = $estado_icon . ' ' . $status_info['mensaje'];
        }
        
        return $actions;
    }
    
    private function attach_images_to_post($post_id, $image_ids) {
        foreach ($image_ids as $image_id) {
            wp_update_post(array(
                'ID' => intval($image_id),
                'post_parent' => $post_id
            ));
        }
    }
    
    public function get_tags() {
        return get_tags(array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty' => false
        ));
    }
}

// Inicializar la clase
new MediaLab_Graduation_Post();
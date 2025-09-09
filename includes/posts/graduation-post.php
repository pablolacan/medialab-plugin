<?php
/**
 * MediaLab - Graduation Post Module (Actualizado con Material Pendiente)
 * Maneja la lógica híbrida de posts de graduación (Video + Gallery + Tags) + Estado Material
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
    }
    
    public function add_graduation_menu() {
        // Submenú Graduation Post
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
        // Cargar scripts necesarios para Media Library
        wp_enqueue_media();
        
        // Incluir el formulario de graduación
        include MEDIALAB_PLUGIN_PATH . 'views/posts/graduation-form.php';
    }
    
    public function create_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        // Campos ACF para Graduation Post (combinando video, gallery y material pendiente)
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
                // NUEVOS CAMPOS PARA MATERIAL PENDIENTE
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
                    'return_format' => 'value',
                    'instructions' => 'Se actualiza automáticamente según el contenido subido'
                ),
                array(
                    'key' => 'field_graduation_responsable_video',
                    'label' => 'Responsable Video',
                    'name' => 'responsable_video',
                    'type' => 'user',
                    'required' => 0,
                    'instructions' => 'Usuario encargado de subir/completar el video',
                    'return_format' => 'object',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_graduation_estado_material',
                                'operator' => '==',
                                'value' => 'solo_fotos'
                            ),
                        ),
                        array(
                            array(
                                'field' => 'field_graduation_estado_material',
                                'operator' => '==',
                                'value' => 'pendiente_todo'
                            ),
                        )
                    )
                ),
                array(
                    'key' => 'field_graduation_responsable_fotos',
                    'label' => 'Responsable Fotos',
                    'name' => 'responsable_fotos',
                    'type' => 'user',
                    'required' => 0,
                    'instructions' => 'Usuario encargado de subir/completar las fotos',
                    'return_format' => 'object',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_graduation_estado_material',
                                'operator' => '==',
                                'value' => 'solo_video'
                            ),
                        ),
                        array(
                            array(
                                'field' => 'field_graduation_estado_material',
                                'operator' => '==',
                                'value' => 'pendiente_todo'
                            ),
                        )
                    )
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
                        'value' => '218' // ID de categoría Graduaciones
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
        
        // Validar datos requeridos
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
        
        // Validar título
        if (empty($data['post_title'])) {
            $errors[] = 'El título es obligatorio';
        }
        
        // Validar subtítulo
        if (empty($data['subtitulo'])) {
            $errors[] = 'El tipo de ceremonia es obligatorio';
        }
        
        // Validar facultad
        if (empty($data['facultad'])) {
            $errors[] = 'La facultad es obligatoria';
        }
        
        // Validar extracto
        if (empty($data['post_excerpt'])) {
            $errors[] = 'La descripción es obligatoria';
        }
        
        // Validar imagen destacada (obligatoria para graduaciones)
        if (empty($data['featured_image_id'])) {
            $errors[] = 'La imagen destacada es obligatoria para graduaciones';
        }
        
        // Validar link si se proporciona
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
            // Procesar galería de imágenes si existe
            $gallery_images = array();
            if (!empty($data['gallery_images'])) {
                if (is_string($data['gallery_images'])) {
                    $gallery_images = json_decode($data['gallery_images'], true);
                } else if (is_array($data['gallery_images'])) {
                    $gallery_images = $data['gallery_images'];
                }
            }
            
            // Preparar contenido del post
            $post_content = '';
            
            // Si hay galería, crear Gallery Block
            if (!empty($gallery_images) && is_array($gallery_images)) {
                $post_content = $this->create_gallery_block($gallery_images);
            }
            
            // Preparar datos del post
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
            
            // Asignar categoría fija "Graduaciones" (ID: 218)
            wp_set_post_categories($post_id, array(218));
            
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
            
            // Asociar imágenes de galería al post si existen
            if (!empty($gallery_images) && is_array($gallery_images)) {
                $this->attach_images_to_post($post_id, $gallery_images);
            }
            
            // DETECTAR Y ASIGNAR ESTADO DEL MATERIAL AUTOMÁTICAMENTE
            $this->detect_and_save_material_status($post_id, $data);
            
            return array(
                'success' => true,
                'message' => 'Post de graduación creado exitosamente',
                'post_id' => $post_id,
                'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit'),
                'material_status' => $this->get_material_status_message($post_id)
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
        
        // Crear el bloque de galería en formato Gutenberg
        $gallery_html = '<!-- wp:gallery {"ids":[' . implode(',', $image_ids) . '],"columns":3,"linkTo":"media","sizeSlug":"large"} -->';
        $gallery_html .= '<figure class="wp-block-gallery has-nested-images columns-3 is-cropped">';
        
        foreach ($image_ids as $image_id) {
            $image_id = intval($image_id);
            $image = wp_get_attachment_image_src($image_id, 'large');
            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $image_caption = wp_get_attachment_caption($image_id);
            
            if ($image) {
                $gallery_html .= '<!-- wp:image {"id":' . $image_id . ',"sizeSlug":"large","linkDestination":"media"} -->';
                $gallery_html .= '<figure class="wp-block-image size-large">';
                $gallery_html .= '<a href="' . esc_url($image[0]) . '">';
                $gallery_html .= '<img src="' . esc_url($image[0]) . '" alt="' . esc_attr($image_alt) . '" class="wp-image-' . $image_id . '"/>';
                $gallery_html .= '</a>';
                if (!empty($image_caption)) {
                    $gallery_html .= '<figcaption class="wp-element-caption">' . esc_html($image_caption) . '</figcaption>';
                }
                $gallery_html .= '</figure>';
                $gallery_html .= '<!-- /wp:image -->';
            }
        }
        
        $gallery_html .= '</figure>';
        $gallery_html .= '<!-- /wp:gallery -->';
        
        return $gallery_html;
    }
    
    private function save_graduation_fields($post_id, $data) {
        // Guardar link del video (puede estar vacío)
        if (isset($data['link'])) {
            update_field('link', esc_url_raw($data['link']), $post_id);
        }
        
        // Guardar subtítulo
        if (!empty($data['subtitulo'])) {
            update_field('subtitulo', sanitize_text_field($data['subtitulo']), $post_id);
        }
        
        // Guardar facultad
        if (!empty($data['facultad'])) {
            update_field('facultad', sanitize_text_field($data['facultad']), $post_id);
        }
        
        // Guardar galería como JSON si existe
        if (!empty($data['gallery_images'])) {
            if (is_string($data['gallery_images'])) {
                update_field('gallery_images', $data['gallery_images'], $post_id);
            } else if (is_array($data['gallery_images'])) {
                update_field('gallery_images', json_encode($data['gallery_images']), $post_id);
            }
        }
    }
    
    /**
     * NUEVA FUNCIÓN: Detectar estado del material automáticamente
     */
    private function detect_and_save_material_status($post_id, $data) {
        $tiene_video = !empty($data['link']);
        $tiene_fotos = !empty($data['gallery_images']) && 
                      (is_array($data['gallery_images']) ? count($data['gallery_images']) > 0 : 
                       (is_string($data['gallery_images']) && !empty(json_decode($data['gallery_images'], true))));
        
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
     * NUEVA FUNCIÓN: Hook para detectar automáticamente en posts existentes
     */
    public function auto_detect_material_status($post_id) {
        // Solo para posts de graduación (categoría 218)
        if (!has_category(218, $post_id)) {
            return;
        }
        
        // Solo para posts desde 2025
        $post_date = get_the_date('Y-m-d', $post_id);
        if ($post_date < '2025-01-01') {
            return;
        }
        
        $tiene_video = !empty(get_field('link', $post_id));
        $gallery_images = get_field('gallery_images', $post_id);
        $tiene_fotos = !empty($gallery_images);
        
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
    }
    
    /**
     * NUEVA FUNCIÓN: Obtener mensaje del estado del material
     */
    private function get_material_status_message($post_id) {
        $estado = get_field('estado_material', $post_id);
        
        switch ($estado) {
            case 'completo':
                return '✅ Material completo (video y fotos)';
            case 'solo_video':
                return '🎥 Solo video - Faltan fotos';
            case 'solo_fotos':
                return '📷 Solo fotos - Falta video';
            case 'pendiente_todo':
            default:
                return '⏳ Material pendiente';
        }
    }
    
    private function attach_images_to_post($post_id, $image_ids) {
        foreach ($image_ids as $image_id) {
            wp_update_post(array(
                'ID' => intval($image_id),
                'post_parent' => $post_id
            ));
        }
    }
    
    public function get_categories() {
        return get_categories(array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty' => false
        ));
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
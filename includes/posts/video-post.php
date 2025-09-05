<?php
/**
 * MediaLab - Video Post Module
 * Maneja toda la lógica relacionada con posts de video
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Video_Post {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_video_menu'));
        add_action('init', array($this, 'create_acf_fields'));
        
        // AJAX handlers
        add_action('wp_ajax_medialab_publish_video', array($this, 'handle_video_post'));
        add_action('wp_ajax_nopriv_medialab_publish_video', array($this, 'handle_video_post'));
    }
    
    public function add_video_menu() {
        // Submenú Video Post
        add_submenu_page(
            'medialab-posts',
            'Crear Video Post',
            'Video Post',
            'publish_posts',
            'medialab-video',
            array($this, 'video_page')
        );
    }
    
    public function video_page() {
        // Cargar scripts necesarios para Media Library
        wp_enqueue_media();
        
        // Cargar Select2 para el selector de categorías
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
        
        // Incluir el formulario de video
        include MEDIALAB_PLUGIN_PATH . 'views/posts/video-form.php';
    }
    
    public function create_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        // Campos ACF para Video Post
        acf_add_local_field_group(array(
            'key' => 'group_medialab_video',
            'title' => 'MediaLab - Video Post',
            'fields' => array(
                array(
                    'key' => 'field_video_link',
                    'label' => 'Link',
                    'name' => 'link',
                    'type' => 'url',
                    'required' => 1,
                    'placeholder' => 'https://youtube.com/watch?v=...'
                ),
                array(
                    'key' => 'field_video_subtitulo',
                    'label' => 'Subtítulo',
                    'name' => 'subtitulo',
                    'type' => 'text',
                    'required' => 1,
                    'maxlength' => 200
                ),
                array(
                    'key' => 'field_video_facultad',
                    'label' => 'Facultad',
                    'name' => 'facultad',
                    'type' => 'text',
                    'required' => 1
                )
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post'
                    )
                )
            )
        ));
    }
    
    public function handle_video_post() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('publish_posts')) {
            wp_send_json_error('No tienes permisos suficientes');
        }
        
        // Validar datos requeridos
        $validation = $this->validate_video_data($_POST);
        if (!$validation['valid']) {
            wp_send_json_error($validation['message']);
        }
        
        // Crear el post
        $result = $this->create_video_post($_POST);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    private function validate_video_data($data) {
        $errors = array();
        
        // Validar título
        if (empty($data['post_title'])) {
            $errors[] = 'El título es obligatorio';
        }
        
        // Validar link
        if (empty($data['link'])) {
            $errors[] = 'El link del video es obligatorio';
        } elseif (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
            $errors[] = 'El link del video no es válido';
        }
        
        // Validar subtítulo
        if (empty($data['subtitulo'])) {
            $errors[] = 'El subtítulo es obligatorio';
        }
        
        // Validar facultad
        if (empty($data['facultad'])) {
            $errors[] = 'La facultad es obligatoria';
        }
        
        // Validar extracto
        if (empty($data['post_excerpt'])) {
            $errors[] = 'La descripción es obligatoria';
        }
        
        // Validar categoría
        if (empty($data['post_category']) || !is_array($data['post_category']) || count($data['post_category']) > 1) {
            $errors[] = 'Debes seleccionar exactamente una categoría';
        }
        
        // Validar imagen destacada
        if (empty($data['featured_image_id'])) {
            $errors[] = 'La imagen destacada es obligatoria';
        }
        
        return array(
            'valid' => empty($errors),
            'message' => implode(', ', $errors)
        );
    }
    
    private function create_video_post($data) {
        try {
            // Preparar datos del post
            $post_data = array(
                'post_title'    => sanitize_text_field($data['post_title']),
                'post_content'  => '',
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
            
            // Asignar categoría
            if (!empty($data['post_category']) && is_array($data['post_category'])) {
                wp_set_post_categories($post_id, array(intval($data['post_category'][0])));
            }
            
            // Guardar campos ACF
            $this->save_video_fields($post_id, $data);
            
            // Asignar imagen destacada
            if (!empty($data['featured_image_id'])) {
                set_post_thumbnail($post_id, intval($data['featured_image_id']));
            }
            
            return array(
                'success' => true,
                'message' => 'Video post creado exitosamente',
                'post_id' => $post_id,
                'edit_url' => admin_url('post.php?post=' . $post_id . '&action=edit')
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error inesperado: ' . $e->getMessage()
            );
        }
    }
    
    private function save_video_fields($post_id, $data) {
        if (!empty($data['link'])) {
            update_field('link', esc_url_raw($data['link']), $post_id);
        }
        
        if (!empty($data['subtitulo'])) {
            update_field('subtitulo', sanitize_text_field($data['subtitulo']), $post_id);
        }
        
        if (!empty($data['facultad'])) {
            update_field('facultad', sanitize_text_field($data['facultad']), $post_id);
        }
    }
    
    public function get_categories() {
        return get_categories(array(
            'orderby' => 'name',
            'order'   => 'ASC',
            'hide_empty' => false
        ));
    }
}

// Inicializar la clase
new MediaLab_Video_Post();

// Función helper
function medialab_get_video_categories() {
    $video_post = new MediaLab_Video_Post();
    return $video_post->get_categories();
}
<?php
/**
 * MediaLab - Gallery Post Module
 * Maneja toda la lógica relacionada con posts de galería usando Gallery Block de Gutenberg
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Gallery_Post {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_gallery_menu'));
        add_action('init', array($this, 'create_acf_fields'));
        
        // AJAX handlers
        add_action('wp_ajax_medialab_publish_gallery', array($this, 'handle_gallery_post'));
        add_action('wp_ajax_nopriv_medialab_publish_gallery', array($this, 'handle_gallery_post'));

        add_action('wp_ajax_get_attachment_data', array($this, 'get_attachment_data'));
        add_action('wp_ajax_nopriv_get_attachment_data', array($this, 'get_attachment_data'));
    }
    
    public function add_gallery_menu() {
        // Submenú Gallery Post
        add_submenu_page(
            'medialab-posts',
            'Crear Gallery Post',
            'Gallery Post',
            'publish_posts',
            'medialab-gallery',
            array($this, 'gallery_page')
        );
    }

    public function get_attachment_data() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        
        if (!$attachment_id) {
            wp_send_json_error('ID de attachment inválido');
        }
        
        $attachment = get_post($attachment_id);
        
        if (!$attachment || $attachment->post_type !== 'attachment') {
            wp_send_json_error('Attachment no encontrado');
        }
        
        // Construir datos del attachment similar a wp.media
        $attachment_data = array(
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'sizes' => array()
        );
        
        // Obtener diferentes tamaños
        $image_sizes = get_intermediate_image_sizes();
        $image_sizes[] = 'full';
        
        foreach ($image_sizes as $size) {
            $image = wp_get_attachment_image_src($attachment_id, $size);
            if ($image) {
                $attachment_data['sizes'][$size] = array(
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2]
                );
            }
        }
        
        wp_send_json_success($attachment_data);
    }
    
    public function gallery_page() {
        // Cargar scripts necesarios para Media Library
        wp_enqueue_media();
        
        // Cargar Select2 para el selector de categorías
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
        
        // Incluir el formulario de galería
        include MEDIALAB_PLUGIN_PATH . 'views/posts/gallery-form.php';
    }
    
    public function create_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        // Campos ACF para Gallery Post - Solo Facultad
        acf_add_local_field_group(array(
            'key' => 'group_medialab_gallery',
            'title' => 'MediaLab - Gallery Post',
            'fields' => array(
                array(
                    'key' => 'field_gallery_facultad',
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
    
    public function handle_gallery_post() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'medialab_nonce')) {
            wp_send_json_error('Fallo de seguridad');
        }
        
        // Verificar permisos
        if (!current_user_can('publish_posts')) {
            wp_send_json_error('No tienes permisos suficientes');
        }
        
        // Validar datos requeridos
        $validation = $this->validate_gallery_data($_POST);
        if (!$validation['valid']) {
            wp_send_json_error($validation['message']);
        }
        
        // Crear el post
        $result = $this->create_gallery_post($_POST);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    private function validate_gallery_data($data) {
        $errors = array();
        
        // Validar título
        if (empty($data['post_title'])) {
            $errors[] = 'El título es obligatorio';
        }
        
        // Validar facultad
        if (empty($data['facultad'])) {
            $errors[] = 'La facultad es obligatoria';
        }
        
        // Validar extracto
        if (empty($data['post_excerpt'])) {
            $errors[] = 'El extracto es obligatorio';
        }
        
        // Validar categoría
        if (empty($data['post_category']) || !is_array($data['post_category']) || count($data['post_category']) > 1) {
            $errors[] = 'Debes seleccionar exactamente una categoría';
        }
        
        // Validar galería de imágenes
        if (empty($data['gallery_images']) || !is_array($data['gallery_images']) || count($data['gallery_images']) < 2) {
            $errors[] = 'Debes seleccionar al menos 2 imágenes para la galería';
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
    
    private function create_gallery_post($data) {
        try {
            // Decodificar gallery_images si viene como JSON string
            $gallery_images = $data['gallery_images'];
            if (is_string($gallery_images)) {
                $gallery_images = json_decode($gallery_images, true);
            }
            
            // Preparar contenido con Gallery Block
            $gallery_content = $this->create_gallery_block($gallery_images);
            
            // Preparar datos del post
            $post_data = array(
                'post_title'    => sanitize_text_field($data['post_title']),
                'post_content'  => $gallery_content,
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
            $this->save_gallery_fields($post_id, $data);
            
            // Asignar imagen destacada
            if (!empty($data['featured_image_id'])) {
                set_post_thumbnail($post_id, intval($data['featured_image_id']));
            }
            
            // Asociar todas las imágenes al post
            $this->attach_images_to_post($post_id, $gallery_images);
            
            return array(
                'success' => true,
                'message' => 'Gallery post creado exitosamente',
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
    
    private function save_gallery_fields($post_id, $data) {
        if (!empty($data['facultad'])) {
            update_field('facultad', sanitize_text_field($data['facultad']), $post_id);
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
}

// Inicializar la clase
new MediaLab_Gallery_Post();

// Función helper
function medialab_get_gallery_categories() {
    $gallery_post = new MediaLab_Gallery_Post();
    return $gallery_post->get_categories();
}
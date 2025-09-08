<?php
/**
 * Plugin Name: MediaLab
 * Plugin URI: https://medialab.galileo.edu
 * Description: Plugin central para gestionar todas las funcionalidades del MediaLab - Posts de video, galerías, graduaciones y más.
 * Version: 1.0.1
 * Author: Dojo Lab
 * License: GPL v2 or later
 * Text Domain: medialab
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('MEDIALAB_VERSION', '1.0.1');
define('MEDIALAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MEDIALAB_PLUGIN_PATH', plugin_dir_path(__FILE__));

// ===== FUNCIONES AUXILIARES GLOBALES =====

/**
 * Función auxiliar para padding hexadecimal
 */
function medialab_hex_pad($val) {
    return str_pad(dechex($val), 2, '0', STR_PAD_LEFT);
}

/**
 * Función auxiliar para filtrar archivos
 */
function medialab_filter_temp_files($file) {
    return !in_array(basename($file), array('.', '..', '.htaccess'));
}

/**
 * Función de limpieza diaria
 */
function medialab_daily_cleanup_function() {
    // Limpiar archivos temporales
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/medialab/temp';
    
    if (is_dir($temp_dir)) {
        $files = glob($temp_dir . '/*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > (24 * 60 * 60)) { // 24 horas
                unlink($file);
            }
        }
    }
    
    // Log de limpieza
    medialab_log('Limpieza diaria ejecutada');
}

/**
 * Función de desinstalación del plugin
 */
function medialab_uninstall_plugin() {
    // Eliminar opciones
    delete_option('medialab_version');
    delete_option('medialab_settings');
    delete_option('medialab_primary_color');
    delete_option('medialab_secondary_color');
    
    // Eliminar eventos programados
    wp_clear_scheduled_hook('medialab_daily_cleanup');
    
    // Eliminar directorio de uploads (opcional)
    $upload_dir = wp_upload_dir();
    $medialab_dir = $upload_dir['basedir'] . '/medialab';
    
    if (is_dir($medialab_dir)) {
        // Solo eliminar si está vacío o solo contiene archivos temporales
        $files = glob($medialab_dir . '/{,.}*', GLOB_BRACE);
        if ($files) {
            $files = array_filter($files, 'medialab_filter_temp_files');
        }
        
        if (empty($files)) {
            rmdir($medialab_dir);
        }
    }
}

class MediaLab_Plugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Hooks de activación
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Hook de actualización
        add_action('admin_init', array($this, 'check_version'));
    }
    
    public function init() {
        // Verificar si ACF está activo
        if (!class_exists('ACF')) {
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }
        
        // Cargar traducciones
        load_plugin_textdomain('medialab', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Cargar módulos
        $this->load_modules();
    }
    
    private function load_modules() {
        // Cargar módulo de video posts
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/video-post.php';
        
        // Cargar módulo de gallery posts
        require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/gallery-post.php';
        
        // Cargar módulo de documentación
        require_once MEDIALAB_PLUGIN_PATH . 'documentation.php';
        
        // Futuro:
        // require_once MEDIALAB_PLUGIN_PATH . 'includes/posts/graduation-post.php';
    }
    
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            __('MediaLab', 'medialab'),
            __('MediaLab', 'medialab'),
            'manage_options',
            'medialab',
            array($this, 'dashboard_page'),
            'dashicons-video-alt3',
            25
        );
        
        // Submenú Posts
        add_submenu_page(
            'medialab',
            __('MediaLab - Posts', 'medialab'),
            __('Posts', 'medialab'),
            'publish_posts',
            'medialab-posts',
            array($this, 'posts_page')
        );
        
        // Los submenús de video y gallery se agregan desde sus respectivos archivos
    }
    
    public function dashboard_page() {
        // Obtener estadísticas
        $stats = $this->get_dashboard_stats();
        
        // Fallback si no existe la vista
        if (file_exists(MEDIALAB_PLUGIN_PATH . 'views/dashboard.php')) {
            include MEDIALAB_PLUGIN_PATH . 'views/dashboard.php';
        } else {
            $this->render_fallback_dashboard($stats);
        }
    }
    
    public function posts_page() {
        // Fallback si no existe la vista
        if (file_exists(MEDIALAB_PLUGIN_PATH . 'views/posts.php')) {
            include MEDIALAB_PLUGIN_PATH . 'views/posts.php';
        } else {
            $this->render_fallback_posts();
        }
    }
    
    private function render_fallback_dashboard($stats) {
        echo '<div class="wrap">';
        echo '<div class="medialab-wrap">';
        echo '<div class="medialab-header">';
        echo '<h1>🎬 MediaLab Dashboard</h1>';
        echo '<p class="description">Bienvenido al panel central de MediaLab</p>';
        echo '</div>';
        echo '<div class="medialab-content">';
        
        if ($stats) {
            echo '<div class="medialab-stats">';
            echo '<div class="medialab-stat-card">';
            echo '<div class="medialab-stat-icon">📹</div>';
            echo '<div class="medialab-stat-number">' . $stats['video_posts'] . '</div>';
            echo '<div class="medialab-stat-label">Videos</div>';
            echo '</div>';
            echo '<div class="medialab-stat-card">';
            echo '<div class="medialab-stat-icon">🖼️</div>';
            echo '<div class="medialab-stat-number">' . $stats['gallery_posts'] . '</div>';
            echo '<div class="medialab-stat-label">Galerías</div>';
            echo '</div>';
            echo '<div class="medialab-stat-card">';
            echo '<div class="medialab-stat-icon">📝</div>';
            echo '<div class="medialab-stat-number">' . $stats['total_posts'] . '</div>';
            echo '<div class="medialab-stat-label">Total Posts</div>';
            echo '</div>';
            echo '</div>';
        }
        
        // Cards de módulos
        echo '<div class="medialab-modules-grid">';
        echo '<div class="medialab-module-card">';
        echo '<div class="medialab-module-header">';
        echo '<span class="medialab-module-icon">📹</span>';
        echo '<h3 class="medialab-module-title">Posts</h3>';
        echo '</div>';
        echo '<div class="medialab-module-body">';
        echo '<p class="medialab-module-description">Gestiona posts de video, galerías y graduaciones</p>';
        echo '</div>';
        echo '<div class="medialab-module-footer">';
        echo '<a href="' . admin_url('admin.php?page=medialab-posts') . '" class="medialab-btn medialab-btn-primary">Ir a Posts</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    private function render_fallback_posts() {
        echo '<div class="wrap">';
        echo '<div class="medialab-wrap">';
        echo '<div class="medialab-header">';
        echo '<h1>📹 MediaLab Posts</h1>';
        echo '<p class="description">Selecciona el tipo de post que quieres crear</p>';
        echo '</div>';
        echo '<div class="medialab-content">';
        
        echo '<div class="medialab-modules-grid">';
        
        // Video Post (activo)
        echo '<div class="medialab-module-card">';
        echo '<div class="medialab-module-header">';
        echo '<span class="medialab-module-icon">🎥</span>';
        echo '<h3 class="medialab-module-title">Video Post</h3>';
        echo '</div>';
        echo '<div class="medialab-module-body">';
        echo '<p class="medialab-module-description">Publica videos con información detallada</p>';
        echo '</div>';
        echo '<div class="medialab-module-footer">';
        echo '<a href="' . admin_url('admin.php?page=medialab-video') . '" class="medialab-btn medialab-btn-primary">Crear Video</a>';
        echo '</div>';
        echo '</div>';
        
        // Gallery Post (activo)
        echo '<div class="medialab-module-card">';
        echo '<div class="medialab-module-header">';
        echo '<span class="medialab-module-icon">🖼️</span>';
        echo '<h3 class="medialab-module-title">Gallery Post</h3>';
        echo '</div>';
        echo '<div class="medialab-module-body">';
        echo '<p class="medialab-module-description">Crea galerías de imágenes con Gallery Block nativo</p>';
        echo '</div>';
        echo '<div class="medialab-module-footer">';
        echo '<a href="' . admin_url('admin.php?page=medialab-gallery') . '" class="medialab-btn medialab-btn-primary">Crear Galería</a>';
        echo '</div>';
        echo '</div>';
        
        // Graduation Post (próximamente)
        echo '<div class="medialab-module-card disabled">';
        echo '<div class="medialab-module-header">';
        echo '<span class="medialab-module-icon">🎓</span>';
        echo '<h3 class="medialab-module-title">Graduation Post</h3>';
        echo '</div>';
        echo '<div class="medialab-module-body">';
        echo '<p class="medialab-module-description">Próximamente - Posts de graduación</p>';
        echo '</div>';
        echo '<div class="medialab-module-footer">';
        echo '<button class="medialab-btn" disabled>Próximamente</button>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    public function enqueue_admin_scripts($hook) {
        // ===== PROBLEMA 1: DETECCIÓN INCORRECTA DE PÁGINAS =====
        // Verificar si estamos en una página de MediaLab de forma más precisa
        $medialab_pages = array(
            'toplevel_page_medialab',           // Dashboard principal
            'medialab_page_medialab-posts',     // Posts page
            'medialab_page_medialab-video',     // Video form
            'medialab_page_medialab-gallery',   // Gallery form
            'medialab_page_medialab-docs',      // Documentación principal
            'medialab_page_medialab-docs-general',
            'medialab_page_medialab-docs-videos',
            'medialab_page_medialab-docs-gallery'
        );
        
        // Si no es una página de MediaLab, salir
        if (!in_array($hook, $medialab_pages)) {
            return;
        }
        
        // ===== PROBLEMA 2: ORDEN INCORRECTO DE CARGA =====
        // Cargar SIEMPRE core.css primero
        wp_enqueue_style(
            'medialab-core',
            MEDIALAB_PLUGIN_URL . 'assets/css/core.css',
            array(),
            MEDIALAB_VERSION
        );
        
        // Variables CSS dinámicas - AGREGAR ESTO QUE FALTABA
        $this->add_dynamic_css();
        
        // ===== PROBLEMA 3: DEPENDENCIAS ESPECÍFICAS MEJORADAS =====
        $dependencies = array('medialab-core');
        
        // Dashboard styles
        if ($hook === 'toplevel_page_medialab' || $hook === 'medialab_page_medialab-posts') {
            wp_enqueue_style(
                'medialab-dashboard',
                MEDIALAB_PLUGIN_URL . 'assets/css/dashboard.css',
                array('medialab-core'),
                MEDIALAB_VERSION
            );
            $dependencies[] = 'medialab-dashboard';
        }
        
        // Form styles
        if (in_array($hook, array('medialab_page_medialab-video', 'medialab_page_medialab-gallery'))) {
            wp_enqueue_style(
                'medialab-forms',
                MEDIALAB_PLUGIN_URL . 'assets/css/forms.css',
                array('medialab-core'),
                MEDIALAB_VERSION
            );
            $dependencies[] = 'medialab-forms';
        }
        
        // Documentation styles
        if (strpos($hook, 'medialab-docs') !== false) {
            wp_enqueue_style(
                'medialab-docs',
                MEDIALAB_PLUGIN_URL . 'assets/css/documentation.css',
                array('medialab-core'),
                MEDIALAB_VERSION
            );
            $dependencies[] = 'medialab-docs';
        }
        
        // ===== PROBLEMA 4: MAIN.CSS DEBE CARGAR ÚLTIMO =====
        wp_enqueue_style(
            'medialab-main',
            MEDIALAB_PLUGIN_URL . 'assets/css/main.css',
            $dependencies,
            MEDIALAB_VERSION
        );
        
        // ===== SCRIPTS =====
        wp_enqueue_script(
            'medialab-admin-script',
            MEDIALAB_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            MEDIALAB_VERSION,
            true
        );
        
        // Scripts específicos para formularios
        if (in_array($hook, array('medialab_page_medialab-video', 'medialab_page_medialab-gallery'))) {
            wp_enqueue_media();
            
            wp_enqueue_script(
                'select2',
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                array('jquery'),
                '4.0.13',
                true
            );
            
            wp_enqueue_style(
                'select2',
                'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                array(),
                '4.0.13'
            );
        }
        
        // Localizar script para AJAX
        wp_localize_script('medialab-admin-script', 'medialab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medialab_nonce')
        ));
    }
    
    /**
     * ===== PROBLEMA 5: FALTABAN VARIABLES CSS DINÁMICAS =====
     * Agregar CSS dinámico con variables personalizadas
     */
    private function add_dynamic_css() {
        $primary_color = get_option('medialab_primary_color', '#2563eb');
        $secondary_color = get_option('medialab_secondary_color', '#64748b');
        
        $custom_css = "
        :root {
            --ml-primary: {$primary_color};
            --ml-primary-hover: " . $this->darken_color($primary_color, 10) . ";
            --ml-primary-light: " . $this->lighten_color($primary_color, 85) . ";
            
            --ml-secondary: {$secondary_color};
            --ml-secondary-hover: " . $this->darken_color($secondary_color, 10) . ";
            --ml-secondary-light: " . $this->lighten_color($secondary_color, 85) . ";
        }
        
        /* ===== PROBLEMA 6: FORZAR APLICACIÓN DE ESTILOS EN ADMIN ===== */
        .wrap .medialab-wrap {
            background: var(--ml-gray-50, #f8fafc) !important;
            padding: 0 !important;
            margin: 0 -20px !important;
            min-height: calc(100vh - 32px) !important;
        }
        
        .wrap .medialab-form {
            background: white !important;
            border-radius: var(--ml-radius-lg, 0.75rem) !important;
            box-shadow: var(--ml-shadow-sm, 0 1px 2px 0 rgb(0 0 0 / 0.05)) !important;
            border: 1px solid var(--ml-gray-200, #e2e8f0) !important;
            padding: var(--ml-space-2xl, 3rem) !important;
            margin-bottom: var(--ml-space-xl, 2rem) !important;
        }
        
        .wrap .medialab-form .form-field {
            margin-bottom: var(--ml-space-lg, 1.5rem) !important;
        }
        
        .wrap .medialab-form label {
            display: block !important;
            font-weight: 600 !important;
            font-size: var(--ml-text-sm, 0.875rem) !important;
            color: var(--ml-gray-700, #334155) !important;
            margin-bottom: var(--ml-space-sm, 0.5rem) !important;
        }
        
        .wrap .medialab-form input[type='text'],
        .wrap .medialab-form input[type='url'],
        .wrap .medialab-form input[type='email'],
        .wrap .medialab-form input[type='datetime-local'],
        .wrap .medialab-form textarea,
        .wrap .medialab-form select {
            width: 100% !important;
            padding: var(--ml-space-sm, 0.5rem) var(--ml-space-md, 1rem) !important;
            font-size: var(--ml-text-sm, 0.875rem) !important;
            line-height: 1.5 !important;
            color: var(--ml-gray-800, #1e293b) !important;
            background: white !important;
            border: 2px solid var(--ml-gray-200, #e2e8f0) !important;
            border-radius: var(--ml-radius-md, 0.5rem) !important;
            transition: all 0.2s ease !important;
            box-shadow: var(--ml-shadow-sm, 0 1px 2px 0 rgb(0 0 0 / 0.05)) !important;
        }
        
        .wrap .medialab-form input:focus,
        .wrap .medialab-form textarea:focus,
        .wrap .medialab-form select:focus {
            outline: none !important;
            border-color: var(--ml-primary, #2563eb) !important;
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1) !important;
        }
        
        .wrap .button.button-primary {
            background: var(--ml-primary, #2563eb) !important;
            border-color: var(--ml-primary, #2563eb) !important;
            color: white !important;
            border-radius: var(--ml-radius-md, 0.5rem) !important;
            font-weight: 500 !important;
            padding: var(--ml-space-sm, 0.5rem) var(--ml-space-lg, 1.5rem) !important;
            transition: all 0.2s ease !important;
            box-shadow: var(--ml-shadow-sm, 0 1px 2px 0 rgb(0 0 0 / 0.05)) !important;
        }
        
        .wrap .button.button-primary:hover {
            background: var(--ml-primary-hover, #1d4ed8) !important;
            border-color: var(--ml-primary-hover, #1d4ed8) !important;
            transform: translateY(-1px) !important;
            box-shadow: var(--ml-shadow-md, 0 4px 6px -1px rgb(0 0 0 / 0.1)) !important;
        }
        ";
        
        wp_add_inline_style('medialab-core', $custom_css);
    }
    
    /**
     * Obtener estadísticas para el dashboard
     */
    private function get_dashboard_stats() {
        // Obtener posts por tipo usando meta query
        $video_posts = new WP_Query(array(
            'post_type' => 'post',
            'meta_query' => array(
                array(
                    'key' => 'link',
                    'compare' => 'EXISTS'
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        $gallery_posts = new WP_Query(array(
            'post_type' => 'post',
            'meta_query' => array(
                array(
                    'key' => 'link',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => 'facultad',
                    'compare' => 'EXISTS'
                )
            ),
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        // Posts totales publicados
        $total_posts = wp_count_posts();
        
        return array(
            'video_posts' => $video_posts->found_posts,
            'gallery_posts' => $gallery_posts->found_posts,
            'total_posts' => $total_posts->publish,
            'draft_posts' => $total_posts->draft,
            'recent_posts' => $this->get_recent_posts(5)
        );
    }
    
    /**
     * Obtener posts recientes
     */
    private function get_recent_posts($limit = 5) {
        $posts = get_posts(array(
            'numberposts' => $limit,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'facultad',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        $recent = array();
        foreach ($posts as $post) {
            $type = get_post_meta($post->ID, 'link', true) ? 'video' : 'gallery';
            $recent[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $type,
                'date' => $post->post_date,
                'edit_url' => get_edit_post_link($post->ID)
            );
        }
        
        return $recent;
    }
    
    /**
     * Verificar versión y ejecutar actualizaciones si es necesario
     */
    public function check_version() {
        $installed_version = get_option('medialab_version', '0.0.0');
        
        if (version_compare($installed_version, MEDIALAB_VERSION, '<')) {
            $this->upgrade($installed_version);
            update_option('medialab_version', MEDIALAB_VERSION);
        }
    }
    
    /**
     * Ejecutar rutinas de actualización
     */
    private function upgrade($from_version) {
        // Futuras actualizaciones se manejarían aquí
        if (version_compare($from_version, '1.0.1', '<')) {
            // Ejemplo: migrar configuraciones antiguas
            $old_settings = get_option('medialab_old_settings', array());
            if (!empty($old_settings)) {
                // Migrar a nuevo formato
                update_option('medialab_settings', $old_settings);
                delete_option('medialab_old_settings');
            }
        }
    }
    
    public function activate() {
        // Crear opciones por defecto
        add_option('medialab_version', MEDIALAB_VERSION);
        add_option('medialab_settings', array(
            'video_post_status' => 'publish',
            'gallery_post_status' => 'publish',
            'enable_notifications' => true,
            'auto_save_drafts' => true,
            'image_optimization' => true,
            'max_gallery_images' => 50
        ));
        
        // Opciones de personalización
        add_option('medialab_primary_color', '#2563eb');
        add_option('medialab_secondary_color', '#64748b');
        
        // Crear directorio de assets si no existe
        $upload_dir = wp_upload_dir();
        $medialab_dir = $upload_dir['basedir'] . '/medialab';
        
        if (!file_exists($medialab_dir)) {
            wp_mkdir_p($medialab_dir);
            
            // Crear archivo .htaccess para proteger archivos temporales
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "deny from all\n";
            file_put_contents($medialab_dir . '/.htaccess', $htaccess_content);
        }
        
        // Limpiar permalinks
        flush_rewrite_rules();
        
        // Programar evento de limpieza diaria
        if (!wp_next_scheduled('medialab_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'medialab_daily_cleanup');
        }
    }
    
    public function deactivate() {
        // Limpiar permalinks
        flush_rewrite_rules();
        
        // Cancelar eventos programados
        wp_clear_scheduled_hook('medialab_daily_cleanup');
    }
    
    public function acf_missing_notice() {
        $class = 'notice notice-error is-dismissible';
        $message = sprintf(
            __('<strong>MediaLab:</strong> Este plugin requiere %s para funcionar correctamente.', 'medialab'),
            '<a href="' . admin_url('plugin-install.php?s=advanced+custom+fields&tab=search&type=term') . '">Advanced Custom Fields (ACF)</a>'
        );
        
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }
    
    // ===== FUNCIONES AUXILIARES =====
    
    /**
     * Oscurecer un color hexadecimal
     */
    private function darken_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . 
                   str_repeat(substr($hex, 1, 1), 2) . 
                   str_repeat(substr($hex, 2, 1), 2);
        }
        
        $rgb = array_map('hexdec', str_split($hex, 2));
        
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = max(0, min(255, $rgb[$i] - ($rgb[$i] * $percent / 100)));
        }
        
        return '#' . implode('', array_map('medialab_hex_pad', $rgb));
    }
    
    /**
     * Aclarar un color hexadecimal
     */
    private function lighten_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . 
                   str_repeat(substr($hex, 1, 1), 2) . 
                   str_repeat(substr($hex, 2, 1), 2);
        }
        
        $rgb = array_map('hexdec', str_split($hex, 2));
        
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = max(0, min(255, $rgb[$i] + ((255 - $rgb[$i]) * $percent / 100)));
        }
        
        return '#' . implode('', array_map('medialab_hex_pad', $rgb));
    }
    
    /**
     * Obtener configuración del plugin
     */
    public static function get_setting($key, $default = null) {
        $settings = get_option('medialab_settings', array());
        return isset($settings[$key]) ? $settings[$key] : $default;
    }
    
    /**
     * Actualizar configuración del plugin
     */
    public static function update_setting($key, $value) {
        $settings = get_option('medialab_settings', array());
        $settings[$key] = $value;
        return update_option('medialab_settings', $settings);
    }
    
    /**
     * Log de errores del plugin
     */
    public static function log($message, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_entry = sprintf(
                '[%s] MediaLab %s: %s',
                date('Y-m-d H:i:s'),
                strtoupper($level),
                $message
            );
            
            error_log($log_entry);
        }
    }
}

// ===== FUNCIONES GLOBALES =====

/**
 * Obtener instancia del plugin
 */
function medialab() {
    global $medialab_plugin;
    
    if (!isset($medialab_plugin)) {
        $medialab_plugin = new MediaLab_Plugin();
    }
    
    return $medialab_plugin;
}

/**
 * Obtener configuración rápida
 */
function medialab_get_setting($key, $default = null) {
    return MediaLab_Plugin::get_setting($key, $default);
}

/**
 * Actualizar configuración rápida
 */
function medialab_update_setting($key, $value) {
    return MediaLab_Plugin::update_setting($key, $value);
}

/**
 * Log rápido
 */
function medialab_log($message, $level = 'info') {
    MediaLab_Plugin::log($message, $level);
}

// ===== HOOKS ADICIONALES =====

// Limpieza diaria automática
add_action('medialab_daily_cleanup', 'medialab_daily_cleanup_function');

// Hook de desinstalación
register_uninstall_hook(__FILE__, 'medialab_uninstall_plugin');

// Inicializar el plugin
medialab();
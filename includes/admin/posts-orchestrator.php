<?php
/**
 * MediaLab - Posts Orchestrator
 * Controlador principal para manejar todos los tipos de posts en tabs
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Posts_Orchestrator {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_posts_menu'));
        
        // Remover los menús individuales de cada módulo
        add_action('admin_menu', array($this, 'remove_individual_menus'), 999);
    }
    
    public function add_posts_menu() {
        // Menú principal consolidado
        add_submenu_page(
            'medialab',
            'Crear Posts',
            'Crear Posts',
            'publish_posts',
            'medialab-posts',
            array($this, 'posts_page')
        );
    }
    
    public function remove_individual_menus() {
        // Remover submenús individuales que ya no necesitamos
        remove_submenu_page('medialab', 'medialab-video');
        remove_submenu_page('medialab', 'medialab-gallery');
        remove_submenu_page('medialab', 'medialab-graduation');
    }
    
    public function posts_page() {
        // Determinar tab activo
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'video';
        
        // Validar tab
        $valid_tabs = array('video', 'gallery', 'graduation');
        if (!in_array($active_tab, $valid_tabs)) {
            $active_tab = 'video';
        }
        
        // Cargar assets necesarios
        $this->enqueue_tab_assets($active_tab);
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">📝 Crear Posts</h1>
            <hr class="wp-header-end">
            
            <!-- Navegación de Tabs -->
            <nav class="nav-tab-wrapper wp-clearfix" aria-label="Tipos de Posts">
                <a href="<?php echo admin_url('admin.php?page=medialab-posts&tab=video'); ?>" 
                   class="nav-tab <?php echo $active_tab === 'video' ? 'nav-tab-active' : ''; ?>">
                    Publicar Video
                </a>
                <a href="<?php echo admin_url('admin.php?page=medialab-posts&tab=gallery'); ?>" 
                   class="nav-tab <?php echo $active_tab === 'gallery' ? 'nav-tab-active' : ''; ?>">
                    Publicar Galería
                </a>
                <a href="<?php echo admin_url('admin.php?page=medialab-posts&tab=graduation'); ?>" 
                   class="nav-tab <?php echo $active_tab === 'graduation' ? 'nav-tab-active' : ''; ?>">
                    Publicar Graduación
                </a>
            </nav>
            
            <!-- Indicador de tipo de contenido -->
            <div class="tab-indicator">
                <?php $this->render_tab_indicator($active_tab); ?>
            </div>
            
            <!-- Contenido del Tab Activo -->
            <div class="tab-content">
                <?php $this->render_tab_content($active_tab); ?>
            </div>
        </div>
        
        <!-- CSS para tabs -->
        <style>
        .tab-content {
            margin-top: 20px;
        }
        
        .nav-tab-wrapper {
            border-bottom: 1px solid #c3c4c7;
            margin-bottom: 0;
        }
        
        .nav-tab {
            position: relative;
            display: inline-block;
            padding: 12px 18px;
            margin-right: 4px;
            text-decoration: none;
            border: 1px solid transparent;
            border-bottom: none;
            color: #50575e;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .nav-tab:hover {
            color: #135e96;
            background-color: #f6f7f7;
        }
        
        .nav-tab-active {
            background-color: #fff;
            border-color: #c3c4c7;
            border-bottom-color: #fff;
            color: #000;
            position: relative;
            z-index: 2;
        }
        
        .nav-tab-active:hover {
            color: #000;
            background-color: #fff;
        }
        
        /* Badge especial para graduaciones */
        .nav-tab-special::after {
            content: "ESPECIAL";
            position: absolute;
            top: -6px;
            right: -6px;
            background: #d4a574;
            color: white;
            font-size: 9px;
            padding: 2px 5px;
            border-radius: 10px;
            font-weight: bold;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        .tab-indicator {
            background: #f0f6fc;
            border: 1px solid #c3c4c7;
            border-top: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .tab-indicator h3 {
            margin: 0 0 8px 0;
            color: #2271b1;
            font-size: 16px;
        }
        
        .tab-indicator p {
            margin: 0;
            color: #50575e;
            font-size: 14px;
        }
        
        .tab-indicator-graduation {
            border-color: #3f3f3fff;
        }
        
        .tab-indicator-graduation h3 {
            color: #1d1d1dff;
        }
        
        /* Animación suave para cambios de tab */
        .tab-content {
            opacity: 1;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        </style>
        <?php
    }
    
    private function enqueue_tab_assets($active_tab) {
        // Assets comunes
        wp_enqueue_media();
        wp_enqueue_script('medialab-admin', MEDIALAB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MEDIALAB_VERSION, true);
        wp_localize_script('medialab-admin', 'medialab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medialab_nonce')
        ));
        
        // Assets específicos según el tab
        switch ($active_tab) {
            case 'gallery':
            case 'video':
                // Select2 para categorías
                wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
                wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
                break;
                
            case 'graduation':
                // No necesita Select2 porque usa categoría fija
                break;
        }
    }
    
    private function render_tab_indicator($active_tab) {
        $indicators = array(
            'video' => array(
                'title' => 'Póst de Vídeo',
                'description' => 'Para webinars, conferencias, seminarios y eventos virtuales con video principal.',
                'class' => ''
            ),
            'gallery' => array(
                'title' => 'Post de Galería', 
                'description' => 'Para eventos presenciales documentados con múltiples fotos (sin video principal).',
                'class' => ''
            ),
            'graduation' => array(
                'title' => 'Post de Graduación',
                'description' => 'Específico para ceremonias de graduación. Puede incluir video, fotos o ambos. Se categoriza automáticamente.',
                'class' => 'tab-indicator-graduation'
            )
        );
        
        $indicator = $indicators[$active_tab];
        
        echo '<div class="' . $indicator['class'] . '">';
        echo '<h3>' . $indicator['title'] . '</h3>';
        echo '<p>' . $indicator['description'] . '</p>';
        echo '</div>';
    }
    
    private function render_tab_content($active_tab) {
        // Incluir el formulario correspondiente sin modificar los archivos originales
        switch ($active_tab) {
            case 'video':
                $this->render_video_form();
                break;
                
            case 'gallery':
                $this->render_gallery_form();
                break;
                
            case 'graduation':
                $this->render_graduation_form();
                break;
        }
    }
    
    private function render_video_form() {
        // Wrapper para aislar el formulario de video
        echo '<div id="video-form-container">';
        
        // Incluir el formulario original sin modificaciones
        $video_form_path = MEDIALAB_PLUGIN_PATH . 'views/posts/video-form.php';
        if (file_exists($video_form_path)) {
            include $video_form_path;
        } else {
            echo '<div class="notice notice-error"><p>Formulario de video no encontrado</p></div>';
        }
        
        echo '</div>';
    }
    
    private function render_gallery_form() {
        // Wrapper para aislar el formulario de galería
        echo '<div id="gallery-form-container">';
        
        // Incluir el formulario original sin modificaciones
        $gallery_form_path = MEDIALAB_PLUGIN_PATH . 'views/posts/gallery-form.php';
        if (file_exists($gallery_form_path)) {
            include $gallery_form_path;
        } else {
            echo '<div class="notice notice-error"><p>Formulario de galería no encontrado</p></div>';
        }
        
        echo '</div>';
    }
    
    private function render_graduation_form() {
        // Wrapper para aislar el formulario de graduación
        echo '<div id="graduation-form-container">';
        
        // Incluir el formulario original sin modificaciones
        $graduation_form_path = MEDIALAB_PLUGIN_PATH . 'views/posts/graduation-form.php';
        if (file_exists($graduation_form_path)) {
            include $graduation_form_path;
        } else {
            echo '<div class="notice notice-error"><p>Formulario de graduación no encontrado</p></div>';
        }
        
        echo '</div>';
    }
}

// Inicializar el orquestador
new MediaLab_Posts_Orchestrator();
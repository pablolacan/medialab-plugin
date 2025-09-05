<?php
/**
 * MediaLab - Documentation Orchestrator
 * Orquestador simple del sistema de documentación
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Documentation {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_documentation_menu'));
        $this->load_documentation_modules();
    }
    
    private function load_documentation_modules() {
        // Cargar módulos de documentación desde carpeta separada
        require_once MEDIALAB_PLUGIN_PATH . 'includes/documentation/videos.php';
        
        // Futuro:
        // require_once MEDIALAB_PLUGIN_PATH . 'documentation/galleries.php';
        // require_once MEDIALAB_PLUGIN_PATH . 'documentation/graduations.php';
    }
    
    public function add_documentation_menu() {
        // Menú principal de documentación
        add_submenu_page(
            'medialab',
            'MediaLab - Documentación',
            '📖 Documentación',
            'read',
            'medialab-docs',
            array($this, 'documentation_page')
        );
    }
    
    public function documentation_page() {
        ?>
        <div class="wrap">
            <h1>📖 MediaLab - Documentación</h1>
            
            <div class="medialab-dashboard">
                <p>Centro de documentación y guías para usar MediaLab correctamente.</p>
                
                <div class="medialab-modules">
                    
                    <!-- Video Posts Documentation -->
                    <div class="module-card">
                        <h3>🎥 Video Posts</h3>
                        <p>Guía completa para crear y gestionar posts de video</p>
                        <a href="<?php echo admin_url('admin.php?page=medialab-docs-videos'); ?>" class="button button-primary">Ver Guía</a>
                    </div>
                    
                    <!-- Gallery Posts Documentation (Futuro) -->
                    <div class="module-card" style="opacity: 0.6;">
                        <h3>🖼️ Gallery Posts</h3>
                        <p>Próximamente - Documentación de galerías</p>
                        <button class="button" disabled>Próximamente</button>
                    </div>
                    
                    <!-- Graduation Posts Documentation (Futuro) -->
                    <div class="module-card" style="opacity: 0.6;">
                        <h3>🎓 Graduation Posts</h3>
                        <p>Próximamente - Documentación de graduaciones</p>
                        <button class="button" disabled>Próximamente</button>
                    </div>
                    
                </div>
            </div>
        </div>
        <?php
    }
}

// Inicializar documentación
new MediaLab_Documentation();
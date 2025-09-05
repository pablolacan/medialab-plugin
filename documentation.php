<?php
/**
 * MediaLab - Documentation Orchestrator
 * Orquestador principal del sistema de documentación
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
        require_once MEDIALAB_PLUGIN_PATH . 'includes/documentation/general.php';
        require_once MEDIALAB_PLUGIN_PATH . 'includes/documentation/videos.php';
        require_once MEDIALAB_PLUGIN_PATH . 'includes/documentation/gallery.php';
        
        // Futuro:
        // require_once MEDIALAB_PLUGIN_PATH . 'includes/documentation/graduations.php';
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
            <!-- Header mejorado -->
            <div class="docs-header">
                <h1>📖 Centro de Documentación MediaLab</h1>
                <p class="description">Guías y tutoriales para usar MediaLab correctamente</p>
            </div>
            
            <!-- Navegación principal -->
            <div class="docs-navigation">
                <h3>📚 Guías Disponibles</h3>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=medialab-docs-general'); ?>">📋 Guía General</a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=medialab-docs-videos'); ?>">🎥 Video Posts</a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=medialab-docs-gallery'); ?>">🖼️ Gallery Posts</a></li>
                </ul>
            </div>
            
            <!-- Módulos disponibles -->
            <div class="docs-section">
                <h2>📋 Tipos de Posts Disponibles</h2>
                
                <div class="docs-cards">
                    
                    <!-- Video Posts Documentation -->
                    <div class="docs-card success">
                        <h3>🎥 Video Posts</h3>
                        <p>Guía completa para crear y gestionar posts de video con conferencias, webinars y seminarios.</p>
                        <a href="<?php echo admin_url('admin.php?page=medialab-docs-videos'); ?>" class="button button-primary">Ver Guía</a>
                    </div>
                    
                    <!-- Gallery Posts Documentation -->
                    <div class="docs-card success">
                        <h3>🖼️ Gallery Posts</h3>
                        <p>Aprende a crear galerías perfectas para documentar eventos, ceremonias y actividades.</p>
                        <a href="<?php echo admin_url('admin.php?page=medialab-docs-gallery'); ?>" class="button button-primary">Ver Guía</a>
                    </div>
                    
                    <!-- Graduation Posts Documentation (Futuro) -->
                    <div class="docs-card" style="opacity: 0.6;">
                        <h3>🎓 Graduation Posts</h3>
                        <p>Próximamente - Documentación especializada para posts de graduación</p>
                        <button class="button" disabled>Próximamente</button>
                    </div>
                    
                </div>
            </div>

            <!-- Guía general -->
            <div class="docs-section">
                <h2>📐 Normas Generales</h2>
                
                <div class="docs-alert info">
                    <h4>💡 Importante para todos los posts</h4>
                    <p>Antes de crear cualquier tipo de post, revisa la guía general que contiene normas aplicables a todos los contenidos.</p>
                </div>

                <div class="docs-cards">
                    <div class="docs-card info">
                        <h4>📸 Requisitos de Imágenes</h4>
                        <p>Tamaño máximo, dimensiones y formatos permitidos</p>
                    </div>
                    
                    <div class="docs-card info">
                        <h4>🏫 Formato de Facultades</h4>
                        <p>Nombres cortos oficiales y cómo manejar múltiples facultades</p>
                    </div>
                    
                    <div class="docs-card info">
                        <h4>📂 Reglas de Categorías</h4>
                        <p>Solo una categoría por post y cómo elegir la correcta</p>
                    </div>
                    
                    <div class="docs-card info">
                        <h4>📅 Fechas de Eventos</h4>
                        <p>Usar fecha del evento, no de publicación</p>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="<?php echo admin_url('admin.php?page=medialab-docs-general'); ?>" class="button button-primary" style="padding: 15px 30px; font-size: 16px;">
                        📋 Ver Guía General Completa
                    </a>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="docs-actions">
                <h3>🚀 Acciones Rápidas</h3>
                <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" class="button">
                    🎥 Crear Video Post
                </a>
                <a href="<?php echo admin_url('admin.php?page=medialab-gallery'); ?>" class="button">
                    🖼️ Crear Gallery Post
                </a>
                <a href="<?php echo admin_url('admin.php?page=medialab-posts'); ?>" class="button">
                    📋 Ver Todos los Posts
                </a>
            </div>

            <!-- Tips rápidos -->
            <div class="docs-section">
                <h2>⚡ Tips Rápidos</h2>
                
                <div class="docs-cards">
                    <div class="docs-card warning">
                        <h4>📸 Imágenes</h4>
                        <p>Máximo 2MB y 1500px por lado. Usa TinyPNG para optimizar.</p>
                    </div>
                    
                    <div class="docs-card warning">
                        <h4>📅 Fechas</h4>
                        <p>Siempre usa la fecha del evento, no la de publicación.</p>
                    </div>
                    
                    <div class="docs-card warning">
                        <h4>🏫 Facultades</h4>
                        <p>Usa nombres cortos: FISICC, FACTI, Medicina, etc.</p>
                    </div>
                    
                    <div class="docs-card warning">
                        <h4>📂 Categorías</h4>
                        <p>Solo UNA categoría por post. Elige la más específica.</p>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }
}

// Inicializar documentación
new MediaLab_Documentation();
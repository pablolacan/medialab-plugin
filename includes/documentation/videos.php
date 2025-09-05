<?php
/**
 * MediaLab - Video Posts Documentation
 * Documentación práctica y directa para posts de video
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Video_Documentation {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_video_docs_menu'));
    }
    
    public function add_video_docs_menu() {
        add_submenu_page(
            'medialab-docs',
            'Video Posts - Guía Rápida',
            'Video Posts',
            'read',
            'medialab-docs-videos',
            array($this, 'video_docs_page')
        );
    }
    
    public function video_docs_page() {
        ?>
        <div class="wrap">
            <h1>🎥 Guía Rápida: Video Posts</h1>
            
            <!-- Recordatorio importante -->
            <div style="background: #e8f6f3; border-left: 4px solid #1abc9c; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0;"><strong>📋 Normas generales:</strong> <a href="<?php echo admin_url('admin.php?page=medialab-docs-general'); ?>">Ver guía general</a> para requisitos de imágenes, facultades y categorías.</p>
            </div>

            <!-- Campos específicos -->
            <div class="medialab-form">
                <h2>📝 Cómo llenar cada campo</h2>
                
                <div style="display: grid; gap: 20px;">
                    
                    <!-- Título -->
                    <div style="border-left: 4px solid #27ae60; padding-left: 15px;">
                        <h4>🎯 Título = Nombre del evento</h4>
                        <p><strong>Ejemplos:</strong></p>
                        <ul>
                            <li>Conferencia: Inteligencia Artificial en Medicina</li>
                            <li>Webinar: Marketing Digital para Pymes</li>
                            <li>Seminario de Investigación en Biotecnología 2024</li>
                        </ul>
                    </div>

                    <!-- Subtítulo -->
                    <div style="border-left: 4px solid #e67e22; padding-left: 15px;">
                        <h4>🏷️ Subtítulo = Tipo de evento</h4>
                        <p><strong>Opciones comunes:</strong> Conferencia, Webinar, Seminario, Taller, Mesa redonda, Simposio, Congreso, Ceremonia</p>
                    </div>

                    <!-- Link -->
                    <div style="border-left: 4px solid #e74c3c; padding-left: 15px;">
                        <h4>🔗 Link del video</h4>
                        <div style="background: #fff5f5; padding: 10px; border-radius: 4px;">
                            <p><strong>✅ Válidos:</strong> YouTube, Vimeo, Facebook</p>
                            <p><strong>⚠️ Importante:</strong> El video debe ser público</p>
                            <p><strong>✨ Tip:</strong> Prueba el link en incógnito antes de publicar</p>
                        </div>
                    </div>

                    <!-- Facultad -->
                    <div style="border-left: 4px solid #9b59b6; padding-left: 15px;">
                        <h4>🏫 Facultad</h4>
                        <p><strong>Usar nombres cortos:</strong> FISICC, FACTI, Medicina, Derecho, Diseño</p>
                        <p><strong>Múltiples:</strong> FISICC, FACTI (separar con comas)</p>
                    </div>

                    <!-- Fecha -->
                    <div style="border-left: 4px solid #3498db; padding-left: 15px;">
                        <h4>📅 Fecha = Cuándo fue el evento</h4>
                        <div style="background: #f0f8ff; padding: 10px; border-radius: 4px;">
                            <p><strong>🔥 Importante:</strong> Usa la fecha del evento, NO de cuando publicas</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Ejemplo completo -->
            <div class="medialab-form" style="background: #f8f9fa;">
                <h2>💡 Ejemplo Completo</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Título</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Desarrollo de APIs REST con Node.js</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Subtítulo</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Webinar</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Link</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">https://youtube.com/watch?v=abc123</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Facultad</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">FISICC</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Descripción</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Webinar técnico para desarrolladores sobre APIs REST. Se abordan mejores prácticas y casos reales.</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Fecha</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">15 de marzo de 2024 (fecha del evento)</td>
                    </tr>
                </table>
            </div>

            <!-- Errores comunes -->
            <div class="medialab-form">
                <h2>⚠️ Errores Comunes</h2>
                <div style="display: grid; gap: 15px;">
                    <div style="background: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c;">
                        <strong>❌ Link no funciona</strong>
                        <p>Solución: Verificar que el video sea público y probar en incógnito</p>
                    </div>
                    <div style="background: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c;">
                        <strong>❌ Título genérico</strong>
                        <p>Evitar: "Video conferencia" → Usar: "Conferencia: IA en Medicina"</p>
                    </div>
                    <div style="background: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c;">
                        <strong>❌ Fecha incorrecta</strong>
                        <p>Usar fecha del evento, no de publicación</p>
                    </div>
                </div>
            </div>

            <!-- Botón de acción -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo admin_url('admin.php?page=medialab-video'); ?>" class="button button-primary" style="padding: 15px 30px; font-size: 16px;">
                    🎥 Crear Video Post
                </a>
            </div>

        </div>
        <?php
    }
}

// Inicializar documentación de videos
new MediaLab_Video_Documentation();
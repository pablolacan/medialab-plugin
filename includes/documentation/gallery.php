<?php
/**
 * MediaLab - Gallery Posts Documentation
 * Documentación práctica y directa para posts de galería
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class MediaLab_Gallery_Documentation {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_gallery_docs_menu'));
    }
    
    public function add_gallery_docs_menu() {
        add_submenu_page(
            'medialab-docs',
            'Gallery Posts - Guía Rápida',
            'Gallery Posts',
            'read',
            'medialab-docs-gallery',
            array($this, 'gallery_docs_page')
        );
    }
    
    public function gallery_docs_page() {
        ?>
        <div class="wrap">
            <h1>🖼️ Guía Rápida: Gallery Posts</h1>
            
            <!-- Recordatorio importante -->
            <div style="background: #e8f6f3; border-left: 4px solid #1abc9c; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0;"><strong>📋 Normas generales:</strong> <a href="<?php echo admin_url('admin.php?page=medialab-docs-general'); ?>">Ver guía general</a> para requisitos de imágenes, facultades y categorías.</p>
            </div>

            <!-- Cuándo usar -->
            <div class="medialab-form">
                <h2>📅 ¿Cuándo usar Gallery Posts?</h2>
                <div style="background: #f0f8ff; padding: 15px; border-radius: 4px; border-left: 4px solid #3498db;">
                    <strong>✅ Perfecto para:</strong>
                    <ul>
                        <li>Ceremonias de graduación</li>
                        <li>Ferias científicas</li>
                        <li>Eventos académicos</li>
                        <li>Actividades estudiantiles</li>
                        <li>Inauguraciones</li>
                    </ul>
                    <strong>💡 Regla simple:</strong> Si tienes fotos del evento (no video), usa Gallery Post
                </div>
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
                            <li>Ceremonia de Graduación FISICC Promoción 2024</li>
                            <li>Feria Científica de Medicina</li>
                            <li>Inauguración Laboratorio de Robótica</li>
                        </ul>
                    </div>

                    <!-- Galería -->
                    <div style="border-left: 4px solid #e67e22; padding-left: 15px;">
                        <h4>📷 Galería de imágenes</h4>
                        <div style="background: #fdf2e9; padding: 10px; border-radius: 4px;">
                            <p><strong>📐 Requisitos:</strong> Mínimo 2, recomendado 5-15 imágenes</p>
                            <p><strong>🎯 Tip:</strong> Selecciona las mejores fotos que cuenten la historia del evento</p>
                        </div>
                    </div>

                    <!-- Imagen destacada -->
                    <div style="border-left: 4px solid #1abc9c; padding-left: 15px;">
                        <h4>🖼️ Imagen destacada</h4>
                        <p><strong>💡 Elige:</strong> La foto más representativa del evento (puede ser diferente a las de la galería)</p>
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

            <!-- Selección de imágenes -->
            <div class="medialab-form">
                <h2>🎨 Consejos para Seleccionar Imágenes</h2>
                <div style="display: grid; gap: 15px;">
                    <div style="background: #f0fff0; padding: 15px; border-radius: 4px; border-left: 4px solid #27ae60;">
                        <strong>✅ Buenas fotos</strong>
                        <ul>
                            <li>Nítidas y bien iluminadas</li>
                            <li>Muestran diferentes momentos del evento</li>
                            <li>Incluyen personas, actividades, ambientes</li>
                            <li>Cuentan una historia visual</li>
                        </ul>
                    </div>
                    <div style="background: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c;">
                        <strong>❌ Evitar</strong>
                        <ul>
                            <li>Fotos borrosas o muy oscuras</li>
                            <li>Imágenes muy similares</li>
                            <li>Fotos que no aporten al evento</li>
                            <li>Imágenes muy pesadas (máx. 2MB)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Ejemplo completo -->
            <div class="medialab-form" style="background: #f8f9fa;">
                <h2>💡 Ejemplo Completo</h2>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Título</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Ceremonia de Graduación FISICC Promoción 2024</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Facultad</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">FISICC</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Descripción</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">Ceremonia de graduación de 120 nuevos ingenieros realizada en el Aula Magna. Las imágenes capturan los momentos más emotivos del evento.</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd; background: #e9ecef; font-weight: bold;">Galería</td>
                        <td style="padding: 10px; border: 1px solid #ddd;">8 imágenes seleccionadas</td>
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
                        <strong>❌ Muy pocas imágenes</strong>
                        <p>Solución: Mínimo 2 imágenes, ideal 5-15 para contar bien la historia</p>
                    </div>
                    <div style="background: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c;">
                        <strong>❌ Imágenes muy pesadas</strong>
                        <p>Solución: Optimizar antes de subir (máx. 2MB, 1500px)</p>
                    </div>
                    <div style="background: #fff5f5; padding: 15px; border-radius: 4px; border-left: 4px solid #e74c3c;">
                        <strong>❌ No seleccionar imagen destacada</strong>
                        <p>Es obligatoria - elige la más representativa</p>
                    </div>
                </div>
            </div>

            <!-- Botón de acción -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo admin_url('admin.php?page=medialab-gallery'); ?>" class="button button-primary" style="padding: 15px 30px; font-size: 16px;">
                    🖼️ Crear Gallery Post
                </a>
            </div>

        </div>
        <?php
    }
}

// Inicializar documentación de galerías
new MediaLab_Gallery_Documentation();
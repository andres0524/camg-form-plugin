<?php
/**
* Plugin Name: CAMG Form Plugin
* Author: Carlos Andres
* Description: shortcode [camg-form-plugin]
*/

register_activation_hook(__FILE__, 'camg_form_regis');

function camg_form_regis(){
    global $wpdb;
    $tabla = $wpdb->prefix . 'formulario';
    $charset_collate = $wpdb->get_charset_collate();
    $query = "CREATE TABLE IF NOT EXISTS $tabla (id mediumint(10) NOT NULL
              AUTO_INCREMENT,
              nombre varchar(50) NOT NULL,
              correo varchar(50) NOT NULL,
              telefono varchar(12) NOT NULL,
              texto text,
              terminos smallint(4) NOT NULL,
              UNIQUE (id) ) $charset_collate";

        include_once ABSPATH . "wp-admin/includes/upgrade.php";
        dbDelta($query);
}

add_shortcode('camg-form-plugin', 'CAMG_Form_Plugin' );

function CAMG_Form_Plugin(){

    global $wpdb;
    $asunto="Confirmacion de envio solicitud";
    $header = "From: noreply@lumina.com" . "\r\n";
    $header.= "Reply-To: noreply@lumina.com" . "\r\n";
    $header.= "x-Mailer: PHP/". phpversion();
    
    if (!empty($_POST) 
        AND $_POST['nombre'] != '' 
        AND is_email($_POST['correo']) 
        AND $_POST['telefono'] != ""
        AND $_POST['texto'] !=""
        AND $_POST['terminos'] == 1)
        {
        $tabla = $wpdb->prefix . 'formulario';
        $nombre = sanitize_text_field($_POST['nombre']);
        $correo = sanitize_email($_POST['correo']);
        $telefono = sanitize_text_field($_POST['telefono']);
        $texto = sanitize_text_field($_POST['texto']);
        $terminos = (int)$_POST['terminos'];
        $wpdb->insert($tabla, array('nombre' => $nombre, 
                                    'correo' => $correo, 
                                    'telefono' => $telefono,
                                    'texto' => $texto, 
                                    'terminos' => $terminos));
    
        $mail = mail($correo,$asunto,$texto,$header);
        if ($mail){
            echo "<p class='exito'><b>Solicitud enviada con exito</b>. Daremenos respuesta en lo mas pronto posible.<p>";
        }                                    
    }
    wp_enqueue_style('css_aspirante', plugins_url('style.css', __FILE__));
    ob_start();
    ?>
        <form method="POST" class="form" action="<?php get_the_permalink(); ?>">
        <?php wp_nonce_field('guarda_pregunta', 'pregunta_once'); ?>
            <div class="form-input">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id ="nombre" required>
            </div>
            <div class="form-input">
                <label for="correo">Correo</label>
                <input type="email" name="correo" id ="correo" required>
            </div>
            <div class="form-input">
                <label for="telefono">Telefono</label>
                <input type="tel" name="telefono" id ="telefono" required>
            </div>
            <div class="form-input">
            <label for="texto">¿Que pregunta tienes?</label>
            <textarea name="texto" id="texto" required></textarea>
        </div>
            <div class="form-input">
                <label for="terminos">El presente Política de Privacidad establece
                     los términos en que  usa y protege la información que es 
                     proporcionada por sus usuarios al momento de utilizar su sitio web. </label>
                <input type="checkbox" id="terminos" name="terminos" value="1" required> Acepto los Terminos y condiciones
            </div>
            <div class="form-input">
                <input type="Submit" value="Enviar">
            </div>
            </div>

        </form>
    <?php
    return ob_get_clean();
}

add_action("admin_menu", "CAMG_menu_form");

function CAMG_menu_form(){
    add_menu_page("Formulario de Contacto", "Formulario", "manage_options", "camg_menu_form", "camg_admin_form", "dashicons-feedback", 75);
}

function camg_admin_form(){
    global $wpdb;
    $tabla = $wpdb->prefix . 'formulario';
    $formulario = $wpdb->get_results("SELECT * FROM $tabla");
    echo '<div class="wrap"><h1>Lista de Solicitudes</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th width="20%">Nombre</th><th width="20%">Correo</th>';
    echo '<th width="20%">Telefono</th><th width="40%">Solicitud</th>';
    echo '</tr></thead>';
    echo '<tbody id="the-list">';
    foreach ($formulario as $formulario){
        $nombre = esc_textarea($formulario->nombre);
        $correo = esc_textarea($formulario->correo);
        $telefono = esc_textarea($formulario->telefono);
        $texto = esc_textarea($formulario->texto);
        echo "<tr><td>$nombre</td>";
        echo "<td>$correo</td>";
        echo "<td>$telefono</td>";
        echo "<td>$texto</td>";
    }
    echo '</tbody></table></div>';

}


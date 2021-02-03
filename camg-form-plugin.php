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
              terminos smallint(4) NOT NULL,
              UNIQUE (id) ) $charset_collate";

        include_once ABSPATH . "wp-admin/includes/upgrade.php";
        dbDelta($query);
}

add_shortcode('camg-form-plugin', 'CAMG_Form_Plugin' );

function CAMG_Form_Plugin(){

    global $wpdb;
    
    if (!empty($_POST) 
        AND $_POST['nombre'] != '' 
        AND is_email($_POST['correo']) 
        AND $_POST['telefono'] != ""
        AND $_POST['terminos'] == 1)
        {
        $tabla = $wpdb->prefix . 'formulario';
        $nombre = sanitize_text_field($_POST['nombre']);
        $correo = sanitize_email($_POST['correo']);
        $telefono = sanitize_text_field($_POST['telefono']);
        $terminos = (int)$_POST['terminos'];
        $wpdb->insert($tabla, array('nombre' => $nombre, 'correo' => $correo, 'telefono' => $telefono, 'terminos' => $terminos));
    }
    ob_start();
    ?>
        <form method="POST" class="form" action="<?php get_the_permalink(); ?>">
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


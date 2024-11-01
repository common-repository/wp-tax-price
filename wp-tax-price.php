<?php
/**
 * Plugin Name: WP Tax Price
 * Plugin URI:  http://free-free-wheeling.com
 * Description: This plugin is for tax support.
 * Version:     0.1.2
 * Author:      Akinori Tateyama
 * Author URI:  http://free-free-wheeling.com
 * License:     GPLv2
 * Text Domain: wp_tax_price
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 Akinori Tateyama ( http://free-free-wheeling.com )
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */



define( 'WP_TAX_PRICE_URL',  plugins_url( '', __FILE__ ) );
define( 'WP_TAX_PRICE_PATH', dirname( __FILE__ ) );

$wp_tax_price = new WP_Tax_Price();
$wp_tax_price->register();

function wp_tax_price($args=0){

    global $wp_tax_price;
    $price = $wp_tax_price->wp_tax_price_calc($args);

    echo $price;

}

function wp_tax_price_short($args) {
    extract(shortcode_atts(array(

        'price' => 0,

    ), $args));

    global $wp_tax_price;

    $price = $wp_tax_price->wp_tax_price_calc($price);

    return $price;
}
add_shortcode('wp_tax_price', 'wp_tax_price_short');


class WP_Tax_Price {

private $version = '';
private $langs   = '';

function __construct()
{
    $data = get_file_data(
        __FILE__,
        array( 'ver' => 'Version', 'langs' => 'Domain Path' )
    );
    $this->version = $data['ver'];
    $this->langs   = $data['langs'];
}

public function register()
{
    add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
}

public function plugins_loaded()
{
    load_plugin_textdomain(
        'wp_tax_price',
        false,
        dirname( plugin_basename( __FILE__ ) ).$this->langs
    );

    add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    add_action( 'admin_init', array( $this, 'admin_init' ) );

}

public function admin_menu()
{
    // See http://codex.wordpress.org/Administration_Menus
    add_options_page(
        __( 'WP Tax Price', 'wp_tax_price' ),
        __( 'WP Tax Price', 'wp_tax_price' ),
        'manage_options', // http://codex.wordpress.org/Roles_and_Capabilities
        'wp_tax_price',
        array( $this, 'options_page' )
    );
}

public function admin_init()
{
    
    $updflg=false;

    if ( isset( $_POST['wtp-tax'] )  ){

        if ( check_admin_referer( '_wpnonce_wp_tax_price' ) ){

            // save something
            update_option( 'wtp-tax', stripslashes($_POST['wtp-tax'] ));

            $updflg=true;

        }
            

    }

    if ( isset( $_POST['wtp-tax-calc'] ) && $_POST['wtp-tax-calc'] ){
    
        if ( check_admin_referer( '_wpnonce_wp_tax_price' ) ){

            // save something
            update_option( 'wtp-tax-calc', stripslashes($_POST['wtp-tax-calc'] ));

            $updflg=true;
        
        }

    }
    if ( isset( $_POST['wtp-tax-camma'] ) && $_POST['wtp-tax-camma'] ){       

        if ( check_admin_referer( '_wpnonce_wp_tax_price' ) ){

            // save something
            update_option( 'wtp-tax-camma', stripslashes($_POST['wtp-tax-camma'] ));

            $updflg=true;
        } 

    }


    if($updflg==true){
        wp_safe_redirect( menu_page_url( 'wp_tax_price', false ) );
    }
    
}

public function options_page()
{

    $wtp_tax = esc_attr(get_option( 'wtp-tax' ));
    $wtp_tax_calc = esc_attr(get_option( 'wtp-tax-calc' ));
    $wtp_tax_camma = esc_attr(get_option( 'wtp-tax-camma' ));

    if ($wtp_tax ==""){
       $wtp_tax=0;
    }
    
?>
<div id="wp_tax_price" class="wrap">
<h2><?php _e( 'WP Tax Price', 'wp_tax_price' ); ?></h2>

<form method="post" action="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ); ?>">
<?php wp_nonce_field( '_wpnonce_wp_tax_price' ); ?>

ビジュアルエディタに書いた価格やphpファイルに書いた価格を消費税対応で表示します。<br>こちらの設定画面で税率や表示方法を設定します。<br>※消費税率：0%とすると、そのままの値を表示します。

<p style="margin-top: 2.5em;">
    <?php _e("Tax Rate","wp_tax_price"); ?>：<input type="number" name="wtp-tax" id="wtp-tax" class="wtp-tax"
            value="<?php echo $wtp_tax; ?>" min="0" required>％</p>

</p>
    
<p style="margin-top: 2.5em;">

    <?php _e("Calculation method","wp_tax_price"); ?>：<select name="wtp-tax-calc" id="wtp-tax-calc">
    <?php
    $Options = array("1"=>__("Round off","wp_tax_price"),"2"=>__("Truncation","wp_tax_price"),"3"=>__("Round up","wp_tax_price"));
    foreach($Options as $key=>$value){
    $selected="";
    if($key==$wtp_tax_calc){$selected =" selected";}
    print "<option value=\"$key\"$selected>$value</option>\n";
    }
    ?>
</select>
</p>


<p style="margin-top: 2.5em;">

    <?php _e("Comma-separated","wp_tax_price"); ?>：<select name="wtp-tax-camma" id="wtp-tax-camma">
    <?php
    $Options = array("1"=>__("Yes","wp_tax_price"),"2"=>__("No","wp_tax_price"));
    foreach($Options as $key=>$value){
    $selected="";
    if($key==$wtp_tax_camma){$selected =" selected";}
    print "<option value=\"$key\"$selected>$value</option>\n";
    }
    ?>
</select>
</p>
 


<p style="margin-top: 2.5em;">
    <h3>使い方</h3>パラメータには<b>消費税抜きの価格</b>を入力してください<br>phpファイル：&lt;?php wp_tax_price("1000"); ?&gt;<br>ショートコード：[wp_tax_price price="1000"]
</p>

<p style="margin-top: 2.5em;">
    <h3>例</h3>パラメータ:1000<br>消費税率:8%<br>税計算方法:四捨五入<br>価格のカンマ区切り:あり<br><b>表示結果:1,080</b>
</p>

<p style="margin-top: 2.5em;">
    <input type="submit" name="submit" id="submit" class="button button-primary"
            value="<?php _e( "Save Changes", "wp_tax_price" ); ?>"></p>
</form>
</div><!-- #wp_tax_price -->
<?php
}

public function admin_enqueue_scripts($hook)
{
    if ( 'settings_page_wp_tax_price' === $hook ) {
        wp_enqueue_style(
            'admin-wp_tax_price-style',
            plugins_url( 'css/admin-wp-tax-price.min.css', __FILE__ ),
            array(),
            $this->version,
            'all'
        );

        wp_enqueue_script(
            'admin-wp_tax_price-script',
            plugins_url( 'js/admin-wp-tax-price.min.js', __FILE__ ),
            array( 'jquery' ),
            $this->version,
            true
        );
    }
}

public function wp_enqueue_scripts()
{
    wp_enqueue_style(
        'wp-tax-price-style',
        plugins_url( 'css/wp-tax-price.min.css', __FILE__ ),
        array(),
        $this->version,
        'all'
    );

    wp_enqueue_script(
        'wp-tax-price-script',
        plugins_url( 'js/wp-tax-price.min.js', __FILE__ ),
        array( 'jquery' ),
        $this->version,
        true
    );
}



public function wp_tax_price_calc($args){

    $price=$args;

    if($price==0 || !is_numeric($price)){
        return 0;
    }

    $wtp_tax = esc_attr(get_option( 'wtp-tax' ));
    $wtp_tax_camma = esc_attr(get_option( 'wtp-tax-camma' ));
    $wtp_tax_calc = esc_attr(get_option( 'wtp-tax-calc' ));

    if($wtp_tax==""){
        $wtp_tax=0;
    }
    if($wtp_tax_camma==""){
        $wtp_tax_camma=1;
    }


    if($wtp_tax==0){
        if($wtp_tax_camma==1){
            $price = number_format($price);
        }

        return $price;
    }
    

    $price = intval($price) * (1 + intval($wtp_tax)*0.01);

    switch ($wtp_tax_calc) {
        case '1':
            $price = round($price);
            break;

        case '2':
            $price = floor(intval($price));
            break;
        
        default:
            $price = ceil($price);
            break;
    }

    if($wtp_tax_camma==1){
        $price = number_format($price);
    }

    return $price;
}



} // end class WP_Tax_Price





// EOF

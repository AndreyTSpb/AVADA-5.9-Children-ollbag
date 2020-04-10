<?php 

add_action('wp_enqueue_scripts', 'theme_styles' );
function theme_styles() {
	wp_enqueue_style('parent-theme-css', get_template_directory_uri() .'/style.css' );
	// не обязательно, правильная родительская тема подключит его сама.
    //wp_enqueue_style('child-theme-css', get_stylesheet_directory_uri() .'/style.css', array('parent-theme-css'));
}

/**
 * Подключаем свои стили
 */
add_action('wp_enqueue_scripts', 'my_theme_styles', 999999);

function my_theme_styles(){
    wp_enqueue_style('child-theme-css', get_stylesheet_directory_uri() .'/style.css', array('parent-theme-css'));
}

/* Меняем значок рубля на руб*/
add_filter( 'woocommerce_currencies', 'add_my_currency' );

function add_my_currency( $currencies ) {

    $currencies['RUBL'] = __( 'Российский рубль текст', 'woocommerce' );

    return $currencies;

}

add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol( $currency_symbol, $currency ) {

     switch( $currency ) {

        case 'RUB': $currency_symbol = 'руб.'; break;

    }

    return $currency_symbol;

}

//Delete hook calling 'function avada_woocommerce_before_cart_table'
function child_remove_parent_before_cart_avada() {
    remove_action( 'woocommerce_before_cart_table', 'avada_woocommerce_before_cart_table', 20 );
}

add_action( 'wp_loaded', 'child_remove_parent_before_cart_avada' );
//Add new function and hook - сейчас не нужно название итак есть.
//add_action( 'woocommerce_before_cart_table', 'avada_woocommerce_before_cart_table_zen', 26 );
function avada_woocommerce_before_cart_table_zen( $args ) {
	global $woocommerce;

	$html = '<div class="zen woocommerce-content-box full-width clearfix">';
	
		$html .= '<h2>' . sprintf( esc_html( _n( 'You Have %s Item In Your Cart','You Have  %s Item(s) In Your Cart1', $woocommerce->cart->get_cart_contents_count(), 'avada_child' )), $woocommerce->cart->get_cart_contents_count() ) . '</h2>';
		echo $html;
}

//To delete avada_social_share block after product loop (as i cant change avada/includes/woo-config.php to childs theme one) 
//and i use instead of it any share plugin
function remove_avada_social_after_summary_action() {
    remove_action( 'woocommerce_after_single_product_summary', 'avada_woocommerce_after_single_product_summary', 15 );
}
// Call  during WP initialization
add_action('wp_loaded','remove_avada_social_after_summary_action');


/**
 * Переносим описание категории товаров под товары.
 */
if(strpos($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], '/product-category/')){
    // удаляем описание категории на странице категорий
    remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
    // выводим описание категории под товарами
    add_action( 'woocommerce_after_shop_loop', 'woocommerce_taxonomy_archive_description', 100 );
}

/**
 * Пагинация над каталогом товара
 */
if(strpos($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], '/product-category/')){
    add_action( 'woocommerce_before_shop_loop', 'woocommerce_pagination', 100 );
}

 
/**
 * Вывод title для категорий товаров 
 */
//add_filter('single_term_title', 'mayak_filter_single_cat_title', 10, 1);
//add_filter( 'single_term_title', 'mayak_poduct_cat_title', 10, 1);
function mayak_filter_single_cat_title() {
    $pci =  get_queried_object()->term_id;
    return get_term_meta ($pci, 'title', true);
}

function mayak_poduct_cat_title($pct){
    if(empty($pct)){
        $pct = get_queried_object()->name;
    }
    return $pct;
}

if(strpos($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], '/product-category/')){
    
    add_filter ( 'woocommerce_show_page_title' , 'mayak_product_cat_h1' , 10 , 2 );
    
    function mayak_product_cat_h1(){
        $pch = get_term_meta (get_queried_object()->term_id, 'h1', true);
        
        echo '<h1 class="woocommerce-products-header__title page-title">'.$pch.'</h1>';
        
        if(empty($pch)){
            echo '<h1 class="woocommerce-products-header__title page-title">'.get_queried_object()->name.'</h1>';
        }
    }

    function mayak_woocommerce_product_cat_h1(){
        return  mayak_product_cat_h1($pch);    
    }

}

// замена стандартных текстов
add_filter('gettext', 'translate_text');
add_filter('ngettext', 'translate_text');
 
function translate_text($translated) {
    //$translated = str_ireplace('Item(s)', 'Товар(ов)', $translated);
    $translated = str_ireplace('You Have %s Items In Your Cart', 'В вашей корзине %s Товара', $translated);
    return $translated;
}

add_filter('woocommerce_helper_suppress_admin_notices', '__return_true');

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
 
// Все $fields в этой функции будут пропущены через фильтр
function custom_override_checkout_fields( $fields ) {
    $fields["billing"]["billing_last_name"]["priority"] = 1;
    $fields["billing"]["billing_last_name"]["class"][0] = 'form-row-first';
    $fields["billing"]["billing_first_name"]["priority"] = 2;
    $fields["billing"]["billing_first_name"]["class"][0] = 'form-row-last';
    $fields["billing"]["billing_email"]["priority"] = 3;
    $fields["billing"]["billing_phone"]["priority"] = 4;
    $fields["billing"]["billing_state"]["priority"] = 5;
    $fields["billing"]["billing_city"]["priority"] = 6;
    $fields["billing"]["billing_address_1"]["priority"] = 7;
    $fields["billing"]["billing_postcode"]["priority"] = 8;

	//$fields["billing"]["billing_company"]["priority"] = 6;
	//$fields["billing"]["billing_address_2"]["priority"] = 8;
	//$fields["billing"]["billing_country"]["priority"] = 3;
    return $fields;
}

/** 
Array ( 

    [billing] => Array ( 
        [billing_last_name] => Array ( 
            [label] => Фамилия 
            [required] => 1 
            [class] => Array ( 
                [0] => form-row-last 
                [1] => thwcfd-field-wrapper 
                [2] => thwcfd-field-text 
                ) 
            [autocomplete] => family-name 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [billing_first_name] => Array ( 
            [label] => Имя Отчество 
            [required] => 1 
            [class] => Array ( 
                [0] => form-row-first 
                [1] => thwcfd-field-wrapper 
                [2] => thwcfd-field-text 
                ) 
            [autocomplete] => given-name 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [billing_email] => Array ( 
            [label] => Email-адрес 
            [required] => 1 
            [type] => email 
            [class] => Array ( [0] => form-row-first [1] => thwcfd-field-wrapper [2] => thwcfd-field-email ) 
            [validate] => Array ( [0] => email ) 
            [autocomplete] => email username 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
        ) 
        [billing_phone] => Array ( 
            [label] => Телефон 
            [required] => 1 
            [type] => tel 
            [class] => Array ( [0] => form-row-last [1] => thwcfd-field-wrapper [2] => thwcfd-field-tel ) 
            [validate] => Array ( [0] => phone ) 
            [autocomplete] => tel 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
        ) 
        [billing_state] => Array ( 
            [type] => state 
            [label] => Область/регион 
            [required] => 
            [class] => Array ( [0] => form-row-first [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-state ) 
            [validate] => Array ( [0] => state ) 
            [autocomplete] => address-level1 
            [priority] => 
            [country_field] => billing_country 
            [country] => RU 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
        ) 
        [billing_city] => Array ( 
            [label] => Населённый пункт 
            [required] => 1 
            [class] => Array ( [0] => form-row-wide [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-text ) 
            [autocomplete] => address-level2 
            [priority] => 
            [default] => 
            [placeholder] => Город/поселение 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [billing_address_1] => Array ( 
            [label] => Адрес 
            [placeholder] => Улица/строение/квартира 
            [required] => 1 
            [class] => Array ( [0] => form-row-wide [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-text ) 
            [autocomplete] => address-line1 
            [priority] => 
            [default] => 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [billing_postcode] => Array ( 
            [label] => Почтовый индекс 
            [required] => 
            [class] => Array ( [0] => form-row-last [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-text ) 
            [validate] => Array ( [0] => postcode ) 
            [autocomplete] => postal-code 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
        ) 
    ) 
    [shipping] => Array ( 
        [shipping_first_name] => Array ( 
            [label] => Имя 
            [required] => 1 
            [class] => Array ( [0] => form-row-first [1] => thwcfd-field-wrapper [2] => thwcfd-field-text ) 
            [autocomplete] => given-name 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [shipping_last_name] => Array ( 
            [label] => Фамилия 
            [required] => 1 
            [class] => Array ( [0] => form-row-last [1] => thwcfd-field-wrapper [2] => thwcfd-field-text ) 
            [autocomplete] => family-name 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [shipping_state] => Array ( 
            [type] => state 
            [label] => Область/регион 
            [required] => 
            [class] => Array ( [0] => form-row-first [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-state ) 
            [validate] => Array ( [0] => state ) 
            [autocomplete] => address-level1 
            [priority] => 
            [country_field] => shipping_country 
            [country] => RU 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
        ) 
        [shipping_city] => Array ( 
            [label] => Населённый пункт 
            [required] => 1 
            [class] => Array ( [0] => form-row-wide [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-text ) 
            [autocomplete] => address-level2 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [shipping_address_1] => Array ( 
            [label] => Адрес 
            [placeholder] => Улица/строение/квартира 
            [required] => 1 
            [class] => Array ( [0] => form-row-wide [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-text ) 
            [autocomplete] => address-line1 
            [priority] => 
            [default] => 
            [label_class] => Array ( ) 
            [validate] => Array ( ) 
        ) 
        [shipping_postcode] => Array ( 
            [label] => Почтовый индекс 
            [required] => 
            [class] => Array ( [0] => form-row-last [1] => address-field [2] => thwcfd-field-wrapper [3] => thwcfd-field-text ) 
            [validate] => Array ( [0] => postcode ) 
            [autocomplete] => postal-code 
            [priority] => 
            [default] => 
            [placeholder] => 
            [label_class] => Array ( ) 
        ) 
    ) 
    [account] => Array ( 
        [account_password] => Array ( 
            [type] => password 
            [label] => Создать пароль учетной записи 
            [required] => 1 
            [placeholder] => Пароль 
            ) 
        ) 
        [order] => Array ( 
            [order_comments] => Array ( 
                [type] => textarea 
                [class] => Array ( [0] => notes ) 
                [label] => Примечание к заказу 
                [placeholder] => Примечания к вашему заказу, например, особые пожелания отделу доставки. ) 
            ) 
        )
*/
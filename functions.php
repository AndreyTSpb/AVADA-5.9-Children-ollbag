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


add_filter('woocommerce_helper_suppress_admin_notices', '__return_true');

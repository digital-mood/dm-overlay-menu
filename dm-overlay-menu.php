<?php
/**
 * Plugin Name: DM Overlay Menu
 * Description: Menú overlay tipo panel doble (oscuro + naranja) para digitalMood, sin dependencias de Divi. Usa un location de menú y opciones simples para logo, marca y tagline.
 * Version: 1.0.0
 * Author: digitalMood
 * Text Domain: dm-overlay-menu
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class DM_Overlay_Menu {
    const OPTION_KEY = 'dmom_options';
    const MENU_LOCATION = 'dmom_primary';

    public function __construct() {
        add_action('after_setup_theme', [$this, 'register_menu_location']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_body_open', [$this, 'render_header_and_overlay']); // modern hook
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /** Register WP Menu location */
    public function register_menu_location() {
        register_nav_menu(self::MENU_LOCATION, __('DM Overlay Menu', 'dm-overlay-menu'));
    }

    /** Enqueue CSS/JS */
    public function enqueue_assets() {
        $ver = '1.0.0';
        wp_enqueue_style('dmom-style', plugins_url('assets/css/style.css', __FILE__), [], $ver);
        wp_enqueue_script('dmom-script', plugins_url('assets/js/menu.js', __FILE__), [], $ver, true);
        // Pass PHP options to JS
        $opts = $this->get_options();
        wp_localize_script('dmom-script', 'DMOM', [
            'lockScroll' => true,
        ]);
    }

    /** Get plugin options with defaults */
    public function get_options() {
        $defaults = [
            'logo_url'   => '',
            'mark_url'   => '',
            'tagline'    => 'Shaping ideas into digital realities',
            'blue'       => '#20A0FF',
            'dark'       => '#0f2733',
            'orange'     => '#ff6a3d',
        ];
        $saved = get_option(self::OPTION_KEY, []);
        return wp_parse_args($saved, $defaults);
    }

    /** Render header and overlay markup */
    public function render_header_and_overlay() {
        // Avoid rendering in admin or if theme doesn't support body open
        if ( is_admin() ) return;
        $opts = $this->get_options();
        ?>
        <header class="dm-header" style="--dm-blue: <?php echo esc_attr($opts['blue']); ?>;">
            <a class="dm-brand" href="<?php echo esc_url(home_url('/')); ?>">
                <?php if(!empty($opts['logo_url'])): ?>
                    <img src="<?php echo esc_url($opts['logo_url']); ?>" alt="<?php bloginfo('name'); ?>">
                <?php else: ?>
                    <span class="dm-brand__text"><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </a>
            <button class="dm-burger" id="dmBurger" aria-label="<?php esc_attr_e('Open menu','dm-overlay-menu'); ?>" aria-expanded="false" aria-controls="dmOverlay">
                <span></span><span></span><span></span>
            </button>
        </header>

        <aside class="dm-overlay" id="dmOverlay" aria-hidden="true" style="--dm-dark: <?php echo esc_attr($opts['dark']); ?>; --dm-orange: <?php echo esc_attr($opts['orange']); ?>;">
            <section class="dm-panel dm-panel--dark">
                <div class="dm-panel-inner">
                    <?php if(!empty($opts['mark_url'])): ?>
                        <img class="dm-mark" src="<?php echo esc_url($opts['mark_url']); ?>" alt="" aria-hidden="true">
                    <?php endif; ?>
                    <?php if(!empty($opts['tagline'])): ?>
                        <p class="dm-tagline"><?php echo esc_html($opts['tagline']); ?></p>
                    <?php endif; ?>
                </div>
            </section>
            <nav class="dm-panel dm-panel--orange" aria-label="<?php esc_attr_e('Main','dm-overlay-menu'); ?>">
                <button class="dm-close" id="dmClose" aria-label="<?php esc_attr_e('Close menu','dm-overlay-menu'); ?>">✕</button>
                <?php
                    // Render WP Menu assigned to our location
                    wp_nav_menu([
                        'theme_location' => self::MENU_LOCATION,
                        'container'      => false,
                        'menu_class'     => 'dm-menu',
                        'fallback_cb'    => [$this, 'fallback_menu'],
                        'walker'         => new DM_Overlay_Walker(),
                    ]);
                ?>
            </nav>
        </aside>
        <?php
    }

    /** Fallback menu if no menu assigned */
    public function fallback_menu() {
        echo '<ul class="dm-menu"><li class="is-section"><a href="'.esc_url(home_url('/')).'">Home</a></li></ul>';
    }

    /** Admin: settings page */
    public function register_settings_page() {
        add_options_page(
            __('DM Overlay Menu', 'dm-overlay-menu'),
            __('DM Overlay Menu', 'dm-overlay-menu'),
            'manage_options',
            'dm-overlay-menu',
            [$this, 'settings_page_html']
        );
    }

    /** Register settings */
    public function register_settings() {
        register_setting('dmom_group', self::OPTION_KEY);
        add_settings_section('dmom_main', __('General', 'dm-overlay-menu'), function(){
            echo '<p>'.esc_html__('Configura logos, tagline y colores.', 'dm-overlay-menu').'</p>';
        }, 'dm-overlay-menu');

        $fields = [
            'logo_url' => __('Logo URL (cabecera)', 'dm-overlay-menu'),
            'mark_url' => __('Marca circular URL (panel oscuro)', 'dm-overlay-menu'),
            'tagline'  => __('Tagline', 'dm-overlay-menu'),
            'blue'     => __('Color azul (header)', 'dm-overlay-menu'),
            'dark'     => __('Color oscuro (panel)', 'dm-overlay-menu'),
            'orange'   => __('Color naranja (panel)', 'dm-overlay-menu'),
        ];
        foreach ($fields as $key => $label) {
            add_settings_field($key, $label, function() use ($key){
                $opts = get_option(DM_Overlay_Menu::OPTION_KEY, []);
                $val = isset($opts[$key]) ? $opts[$key] : '';
                printf('<input type="text" style="width:420px" name="%s[%s]" value="%s" />', esc_attr(DM_Overlay_Menu::OPTION_KEY), esc_attr($key), esc_attr($val));
            }, 'dm-overlay-menu', 'dmom_main');
        }
    }

    /** Settings page HTML */
    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('DM Overlay Menu', 'dm-overlay-menu'); ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields('dmom_group');
                    do_settings_sections('dm-overlay-menu');
                    submit_button();
                ?>
            </form>
            <p><?php esc_html_e('Asigna el menú en Apariencia → Menús → Ubicaciones: "DM Overlay Menu".', 'dm-overlay-menu'); ?></p>
        </div>
        <?php
    }
}

/**
 * Custom walker to add classes "is-section" for top-level headings (no link)
 * and normal links for real items.
 */
class DM_Overlay_Walker extends Walker_Nav_Menu {
    // Render each item
    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $is_section = in_array('menu-item-has-children', $classes) && $item->url === '#';

        if ( $is_section ) {
            $output .= '<li class="is-section">'. esc_html($item->title) .'</li>';
        } else {
            $atts = ' href="'. esc_url($item->url) .'"';
            $output .= '<li><a'. $atts .'>'. esc_html($item->title) .'</a></li>';
        }
    }
}

new DM_Overlay_Menu();

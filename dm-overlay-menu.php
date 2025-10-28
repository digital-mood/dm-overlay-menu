<?php
/**
 * Plugin Name: DM Overlay Menu
 * Version: 1.2.1
 * Author: digitalMood
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DM_Overlay_Menu {
    const OPTION_KEY   = 'dmom_options';
    const MENU_LOCATION = 'dmom_primary';

    public function __construct() {
        add_action('after_setup_theme', [$this, 'register_menu_location']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_body_open',       [$this, 'render_header_and_overlay']);
        add_action('admin_menu',         [$this, 'register_settings_page']);
        add_action('admin_init',         [$this, 'register_settings']);
    }

    public function register_menu_location() {
        register_nav_menu(self::MENU_LOCATION, __('DM Overlay Menu', 'dm-overlay-menu'));
    }

    public function enqueue_assets() {
        $ver_css = filemtime(plugin_dir_path(__FILE__).'assets/css/style.css');
        $ver_js  = filemtime(plugin_dir_path(__FILE__).'assets/js/menu.js');
        wp_enqueue_style ('dmom-style', plugins_url('assets/css/style.css', __FILE__), [], $ver_css);
        wp_enqueue_script('dmom-script', plugins_url('assets/js/menu.js',  __FILE__), [], $ver_js, true);
    }

    private function get_options() {
        $defaults = [
            'logo_url'    => '',
            'mark_url'    => '',
            'tagline'     => 'Shaping ideas into digital realities',
            'blue'        => '',
            'dark'        => '',
            'orange'      => '',
            'menu_color'  => '',
            'menu_hover'  => '',
            'menu_active' => '',
        ];
        $saved = get_option(self::OPTION_KEY, []);
        return wp_parse_args($saved, $defaults);
    }

    public function render_header_and_overlay() {
        if ( is_admin() ) return;
        $opts = $this->get_options();

        // Construction of dynamic styles.
        $overlay_vars = [];
        foreach (['dark','orange','menu_color','menu_hover','menu_active'] as $var) {
            if (!empty($opts[$var])) {
                $overlay_vars[] = '--dm-' . str_replace('_','-',$var) . ': ' . $opts[$var];
            }
        }
        $overlay_style = $overlay_vars ? ' style="'.implode('; ',$overlay_vars).'"' : '';
        ?>
        <header class="dm-header">
            <a class="dm-brand" href="<?php echo esc_url(home_url('/')); ?>">
                <?php if(!empty($opts['logo_url'])): ?>
                    <img src="<?php echo esc_url($opts['logo_url']); ?>" alt="<?php bloginfo('name'); ?>">
                <?php else: ?>
                    <span class="dm-brand__text"><?php bloginfo('name'); ?></span>
                <?php endif; ?>
            </a>
            <button class="dm-burger" id="dmBurger"><span></span><span></span><span></span></button>
        </header>

        <aside class="dm-overlay" id="dmOverlay" aria-hidden="true"<?php echo $overlay_style; ?>>
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
            <nav class="dm-panel dm-panel--orange" aria-label="Main">
                <button class="dm-close" id="dmClose">✕</button>
                <?php
                wp_nav_menu([
                    'theme_location' => self::MENU_LOCATION,
                    'container'      => false,
                    'menu_class'     => 'dm-menu',
                ]);
                ?>
            </nav>
        </aside>
        <?php
    }

    public function register_settings_page() {
        add_options_page('DM Overlay Menu', 'DM Overlay Menu', 'manage_options', 'dm-overlay-menu', [$this,'settings_page_html']);
    }

    public function register_settings() {
        register_setting('dmom_group', self::OPTION_KEY);
        add_settings_section('dmom_main', __('General', 'dm-overlay-menu'), function(){
            echo '<p>Configura los colores y estilos del menú overlay.</p>';
        }, 'dm-overlay-menu');

        $fields = [
            'logo_url'    => 'Logo URL (NavBar)',
            'mark_url'    => 'Logo (Panel left)',
            'tagline'     => 'Tagline',
            'dark'        => 'Color (Right panel)',
            'orange'      => 'Color (Left panel)',
            'menu_color'  => 'Text color menu',
            'menu_hover'  => 'Hover color menu',
            'menu_active' => 'Active color menu',
        ];

        foreach ($fields as $key => $label) {
            add_settings_field($key, $label, function() use ($key){
                $opts = get_option(DM_Overlay_Menu::OPTION_KEY, []);
                $val = $opts[$key] ?? '';
                printf(
                    '<input type="text" style="width:320px" name="%s[%s]" value="%s" placeholder="%s" />',
                    esc_attr(DM_Overlay_Menu::OPTION_KEY),
                    esc_attr($key),
                    esc_attr($val),
                    esc_attr('#ffffff o rgba(255,255,255,.9)')
                );
            }, 'dm-overlay-menu', 'dmom_main');
        }
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>DM Overlay Menu</h1>
            <form method="post" action="options.php">
                <?php settings_fields('dmom_group'); do_settings_sections('dm-overlay-menu'); submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
new DM_Overlay_Menu();

<?php

namespace WPLinkedEvents;

/**
 * Class LinkedEventsPlugin
 */
class LinkedEventsPlugin {

    /**
     * Holds the singleton.
     *
     * @var LinkedEventsPlugin
     */
    protected static $instance;


    /**
     * Current plugin version.
     *
     * @var string
     */
    protected $version = '';

    /**
     * Path to assets distribution versions.
     *
     * @var string
     */
    protected string $dist_path = '';

    /**
     * Uri to assets distribution versions.
     *
     * @var string
     */
    protected string $dist_uri = '';

    /**
     * The plugin directory path.
     *
     * @var string
     */
    protected $plugin_path = '';

    /**
     * The plugin root uri without trailing slash.
     *
     * @var string
     */
    protected $plugin_uri = '';

    /**
     * Event query var
     */
    const EVENT_QUERY_VAR = 'linked-events-id';

    /**
     * Start plugin
     *
     * @param string $version     Plugin version
     * @param string $plugin_url  Plugin uri
     * @param string $plugin_path Plugin path.
     */
    public function __construct( string $version, string $plugin_url, string $plugin_path ) {
        $this->version     = $version;
        $this->plugin_uri  = $plugin_url;
        $this->plugin_path = $plugin_path;


        $this->hooks();
    }

    /**
     * Start plugin
     *
     * @param string $version     Plugin version
     * @param string $plugin_url  Plugin uri
     * @param string $plugin_path Plugin path.
     */
    public static function boot( string $version, string $plugin_url, string $plugin_path ) {
        if ( empty( self::$instance ) ) {
            self::$instance = new self( $version, $plugin_url, $plugin_path );
        }
    }

    /**
     * Get the instance.
     *
     * @return LinkedEventsPlugin
     */
    public static function get_instance() : LinkedEventsPlugin {
        return self::$instance;
    }


    /**
     * Get the version.
     *
     * @return string
     */
    public function get_version() : string {
        return $this->version;
    }

    /**
     * Get the plugin directory path.
     *
     * @return string
     */
    public function get_plugin_path() : string {
        return $this->plugin_path;
    }

    /**
     * Get the plugin directory uri.
     *
     * @return string
     */
    public function get_plugin_uri() : string {
        return $this->plugin_uri;
    }

    /**
     * Get dist url
     *
     * @return string
     */
    public function get_dist_uri() : string {
        return $this->get_plugin_uri() . 'assets/dist/';
    }

    /**
     * Get dist path
     *
     * @return string
     */
    public function get_dist_path() : string {
        return $this->get_plugin_path() . 'assets/dist/';
    }

    /**
     * Add plugin hooks and filters.
     */
    protected function hooks() : void {
        add_action( 'init', \Closure::fromCallable( [ $this, 'load_localization' ] ), 0 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_scripts' ] );
        add_filter(
            'page_template',
            \Closure::fromCallable( [ $this, 'register_page_template_path' ] )
        );
        add_filter(
            'theme_page_templates',
            \Closure::fromCallable( [ $this, 'register_page_template' ] )
        );
        add_filter(
            'query_vars',
            \Closure::fromCallable( [ $this, 'add_query_vars' ] )
        );
        add_filter(
            'document_title_parts',
            \Closure::fromCallable( [ $this, 'alter_page_template_title' ] ),
            50
        );

        add_filter(
            'the_seo_framework_title_from_generation',
            \Closure::fromCallable( [ $this, 'alter_seo_framework_title' ] )
        );

        ( new Settings() )->hooks();
        ( new LinkedEventsBlock() )->hooks();
    }

    /**
     * Load plugin localization
     */
    protected function load_localization() {
        load_plugin_textdomain(
            'wp-linked-events',
            false,
            'wp-linkedevents/languages/'
        );
    }

    /**
     * Enqueue public side scripts if they exist.
     */
    public function enqueue_public_scripts() : void {
        if ( ! file_exists( $this->dist_path . 'public.css' ) ) {
            wp_enqueue_style(
                'wp-linked-events-public',
                $this->get_dist_uri() . 'public.css',
                [],
                $this->mod_time( 'public.css' )
            );
        }
    }

    /**
     * Get cache busting modification time or plugin version.
     *
     * @param string $file File inside assets/dist/ folder.
     *
     * @return int|string
     */
    private function mod_time( string $file = '' ) {
        return file_exists( $this->get_dist_path() . $file )
            ? (int) filemtime( $this->get_dist_path() . $file )
            : $this->get_version();
    }

    /**
     * Register page-materials.php template path.
     *
     * @param string $template Page template name.
     *
     * @return string
     */
    private function register_page_template_path( string $template ) : string {
        if ( get_page_template_slug() === 'page-wp-linked-events.php' ) {
            $template = $this->get_plugin_path() . '/src/PageTemplates/page-wp-linked-events.php';
        }

        return $template;
    }

    /**
     * Register page template making it accessible via page template picker.
     *
     * @param array $templates Page template choices.
     *
     * @return array
     */
    private function register_page_template( array $templates ) : array {
        $templates['page-wp-linked-events.php'] = __( 'WP LinkedEvents' );

        return $templates;
    }

    /**
     * Add plugin query vars
     *
     * @param array $vars Query vars
     *
     * @return array
     */
    private function add_query_vars( array $vars ) : array {
        $vars[] = static::EVENT_QUERY_VAR;

        return $vars;
    }

    /**
     * Alter document title
     *
     * @param array $title_parts Array of title parts.
     *
     * @return array
     */
    protected function alter_page_template_title( $title_parts ) {
        if ( ! is_page_template( 'page-wp-linked-events.php' ) ) {
            return $title_parts;
        }

        $title_parts['title'] = $this->get_page_title( $title_parts['title'] );

        return $title_parts;
    }

    /**
     * Add event name to title
     *
     * @param string $title Title.
     *
     * @return string
     */
    protected function alter_seo_framework_title( string $title ) : string {
        if ( ! is_page_template( 'page-wp-linked-events.php' ) ) {
            return $title;
        }

        return $this->get_page_title( $title );
    }

    /**
     * Get page title
     *
     * @param string $title
     *
     * @return string|null
     */
    private function get_page_title( $title ) {
        $api   = new \WPLinkedEvents\Integrations\LinkedEvents\ApiClient();
        $event = $api->get_event_by_id( get_query_var( self::EVENT_QUERY_VAR ) );

        if ( ! empty( $event ) ) {
            return $event->get_name();
        }

        return $title;
    }
}

<?php

namespace WPLinkedEvents;

use \Geniem\ACF\Block;
use \Geniem\ACF\Field\DatePicker;
use \Geniem\ACF\Field\Message;
use \Geniem\ACF\Field\Number;
use \Geniem\ACF\Field\Select;
use \Geniem\ACF\Renderer\PHP;

/**
 * LinkedEventsBlock
 */
class LinkedEventsBlock {

    /**
     * Block name
     */
    const NAME = 'wp-linked-events-block';

    /**
     * Block title
     *
     * @var string
     */
    private string $title;

    /**
     * Field key prefix
     *
     * @var string
     */
    private string $key_prefix;

    /**
     * Constructor
     */
    public function __construct() {
        $this->title      = __( 'WP LinkedEvents Block', 'wp-linked-events' );
        $this->key_prefix = static::NAME;
    }

    /**
     * Hooks
     */
    public function hooks() : void {
        add_filter(
            'acf/init',
            \Closure::fromCallable( [ $this, 'register' ] )
        );

        add_filter(
            'acf/load_field/key=' . $this->key_prefix . '_keywords',
            \Closure::fromCallable( [ $this, 'fill_keywords' ] )
        );

        add_filter(
            'acf/load_field/key=' . $this->key_prefix . '_places',
            \Closure::fromCallable( [ $this, 'fill_places' ] )
        );
    }

    /**
     * Register block
     *
     * @throws \Geniem\ACF\Exception
     */
    protected function register() : void {
        $block = ( new Block( $this->title, static::NAME ) )
            ->set_category( 'common' )
            ->set_icon( 'tickets' )
            ->set_description( __( 'Display LinkedEvent events', 'wp-linked-events' ) )
            ->set_mode( 'edit' )
            ->set_supports( [ 'anchor' ] );

        try {
            $block->set_renderer( $this->get_renderer() );

            $block->add_fields( $this->get_fields() )
                ->add_data_filter( [ $this, 'filter_data' ] )
                ->register();
        }
        catch ( \Exception $e ) {
            error_log( $e->getMessage() );
        }
    }

    /**
     * Get renderer for block
     *
     * @return PHP
     * @throws \Exception
     */
    protected function get_renderer() {
        $partial_file_name = static::NAME . '.php';
        $partial           = get_stylesheet_directory() . '/' . $partial_file_name;

        if ( ! file_exists( $partial ) ) {
            $partial = __DIR__ . '/Partials/' . $partial_file_name;
        }

        if ( file_exists( $partial ) ) {
            return new PHP( $partial );
        }

        throw new \Exception( "{$partial} was not found" );
    }

    protected function get_fields() : array {
        $strings = [
            'start'    => [
                'label'        => __( 'Start date', 'wp-linked-events' ),
                'instructions' => __( '', 'wp-linked-events' ),
            ],
            'end'      => [
                'label'        => __( 'End date', 'wp-linked-events' ),
                'instructions' => __( '', 'wp-linked-events' ),
            ],
            'keywords' => [
                'label'        => __( 'Keywords', 'wp-linked-events' ),
                'instructions' => __( '', 'wp-linked-events' ),
            ],
            'places'   => [
                'label'        => __( 'Places', 'wp-linked-events' ),
                'instructions' => __( '', 'wp-linked-events' ),
            ],
            'limit'    => [
                'label'        => __( 'Limit', 'wp-linked-events' ),
                'instructions' => '',
            ],
        ];

        $key = $this->key_prefix;

        $start_field = ( new DatePicker( $strings['start']['label'] ) )
            ->set_key( "${key}_start" )
            ->set_name( 'start' )
            ->set_display_format( 'd.m.Y' )
            ->set_return_format( 'Y-m-d' )
            ->set_instructions( $strings['start']['instructions'] );

        $end_field = ( new DatePicker( $strings['end']['label'] ) )
            ->set_key( "${key}_end" )
            ->set_name( 'end' )
            ->set_display_format( 'd.m.Y' )
            ->set_return_format( 'Y-m-d' )
            ->set_instructions( $strings['end']['instructions'] );

        $keywords_field = ( new Select( $strings['keywords']['label'] ) )
            ->set_key( "${key}_keywords" )
            ->set_name( 'keywords' )
            ->use_ui()
            ->allow_multiple()
            ->use_ajax()
            ->set_instructions( $strings['keywords']['instructions'] );

        $places_field = ( new Select( $strings['places']['label'] ) )
            ->set_key( "${key}_places" )
            ->set_name( 'places' )
            ->use_ui()
            ->allow_multiple()
            ->use_ajax()
            ->set_instructions( $strings['places']['instructions'] );

        $limit_field = ( new Number( $strings['limit']['label'] ) )
            ->set_key( "${key}_limit" )
            ->set_name( 'limit' )
            ->set_instructions( $strings['limit']['instructions'] );

        $block_name_field = new Message( $this->title, 'block_name_field_' . static::NAME, 'block_name_field' );

        return [
            $block_name_field,
            $start_field,
            $end_field,
            $keywords_field,
            $places_field,
            $limit_field,
        ];
    }

    /**
     * This filters the block ACF data.
     *
     * @param array             $data       Block's ACF data.
     * @param \Geniem\ACF\Block $instance   The block instance.
     * @param array             $block      The original ACF block array.
     * @param string            $content    The HTML content.
     * @param bool              $is_preview A flag that shows if we're in preview.
     * @param int               $post_id    The parent post's ID.
     *
     * @return array The block data.
     */
    public function filter_data( $data, $instance, $block, $content, $is_preview, $post_id ) : array { // phpcs:ignore
        if ( $data === false ) {
            return [];
        }

        $settings  = new Settings();
        $params    = $this->format_params( $data );
        $cache_key = 'wp-linked-events-block-' . md5( json_encode( $params ) );
        $response  = get_transient( $cache_key );

        if ( ! $response ) {
            $response = ( new Integrations\LinkedEvents\ApiClient() )->get( 'event', $params );

            set_transient( $cache_key, $response, HOUR_IN_SECONDS );
        }

        if ( $response && ! empty( $response->data ) ) {

            $event_settings = [
                'event_page_id' => $settings->get_event_page(),
            ];

            $data['events'] = array_map( function ( $event ) use ( $event_settings ) {
                return new Integrations\LinkedEvents\Entities\Event( $event, $event_settings );
            }, $response->data ?? [] );
        }

        return $data;
    }

    /**
     * Format params
     *
     * @param array $data Block data.
     *
     * @return array
     */
    public function format_params( array $data ) : array {
        global $post;

        $params = [
            'start'       => empty( $data['start'] )
                ? date( 'Y-m-d' )
                : $data['start'],
            'include'     => 'organizer,location,keywords',
            'super_event' => 'none',
            'sort'        => 'start_time',
            'page_size'   => $data['limit'] ?? 6,
        ];

        if ( ! empty( $data['end'] ) ) {
            $params['end'] = $data['end'];
        }

        if ( ! empty( $data['keywords'] ) ) {
            $params['keywords'] = is_array( $data['keywords'] )
                ? implode( ',', $data['keywords'] )
                : $data['keywords'];
        }

        if ( ! empty( $data['places'] ) ) {
            if ( is_array( $data['places'] ) ) {
                $data['places'] = implode( ',', $data['places'] );
            }

            $params['location'] = $data['places'];
        }

        return apply_filters(
            'wp_linked_events_event_block_params',
            $params,
            $post->ID ?? null
        );
    }

    /**
     * Fill field choices from settings or from API.
     *
     * @param array $field ACF field.
     *
     * @return array
     */
    public function fill_keywords( array $field ) : array {
        $keywords = ( new Settings() )->get_event_keywords();

        if ( empty( $keywords ) ) {
            return ( new Settings() )->fill_event_keyword_group_keywords_choices( $field );
        }

        $choices = [];

        foreach ( $keywords as $value ) {
            $id_list             = implode( ',', $value['event_keyword_group_keywords'] );
            $choices[ $id_list ] = $value['event_keyword_group_text'];
        }

        $field['choices'] = $choices;

        return $field;
    }

    /**
     * Fill field places choices from settings or from API.
     *
     * @param array $field ACF field.
     *
     * @return array
     */
    public function fill_places( array $field ) : array {
        $settings = new Settings();
        $choices  = $settings->get_places_from_options();

        if ( empty( $choices ) ) {
            return $field;
        }

        $field['choices'] = $choices;

        return $field;
    }
}

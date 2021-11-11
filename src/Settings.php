<?php

namespace WPLinkedEvents;

use Geniem\ACF\Field\PostObject;
use Geniem\ACF\Field\Repeater;
use Geniem\ACF\Field\Select;
use Geniem\ACF\Field\Text;
use Geniem\ACF\Group;
use Geniem\ACF\RuleGroup;

/**
 * Settings
 */
class Settings {

    /**
     * Settings page options slug
     */
    const OPTIONS_SLUG = 'wp-linked-events';

    /**
     * Options ACF group key
     */
    const OPTIONS_GROUP_KEY = 'wp_linked_events_settings';

    /**
     * Hooks
     */
    public function hooks() : void {
        add_action(
            'acf/init',
            \Closure::fromCallable( [ $this, 'create_options_page' ] )
        );

        add_action(
            'acf/init',
            \Closure::fromCallable( [ $this, 'register_fields' ] ),
            20
        );

        if ( is_admin() ) {
            add_filter(
                'acf/load_field/key=' . self::OPTIONS_GROUP_KEY . '_event_keyword_group_keywords',
                \Closure::fromCallable( [ $this, 'fill_event_keyword_group_keywords_choices' ] )
            );
        }
    }

    /**
     * Create options page
     */
    protected function create_options_page() : void {
        if ( ! function_exists( 'acf_add_options_page' ) ) {
            return;
        }

        acf_add_options_page( [
            'page_title' => 'WP LinkedEvents',
            'menu_title' => 'WP LinkedEvents',
            'menu_slug'  => self::OPTIONS_SLUG,
            'capability' => 'edit_posts',
            'redirect'   => false,
        ] );
    }

    /**
     * Register fields for options page
     */
    protected function register_fields() : void {
        $group_title = __( 'WP LinkedEvents settings', 'wp-linked-events' );
        $key         = static::OPTIONS_GROUP_KEY;

        $field_group = new Group( $group_title );
        $field_group->set_key( $key );

        try {
            $rule_group = ( new RuleGroup() )
                ->add_rule( 'options_page', '==', self::OPTIONS_SLUG );

            $field_group->add_rule_group( $rule_group );

            $strings = [
                'place'                        => [
                    'label'        => __( 'Place', 'wp-linked-events' ),
                    'instructions' => __( '', 'wp-linked-events' ),
                ],
                'event_page'                   => [
                    'label'        => __( 'Event page', 'wp-linked-events' ),
                    'instructions' => __( 'Page with Geniem LinkedEvents Page template selected', 'wp-linked-events' ),
                ],
                'event_keywords'               => [
                    'label'        => __( 'Keyword groups', 'wp-linked-events' ),
                    'instructions' => '',
                ],
                'event_keyword_group_text'     => [
                    'label'        => __( 'Keyword group text', 'wp-linked-events' ),
                    'instructions' => __( 'In example music', 'wp-linked-events' ),
                ],
                'event_keyword_group_keywords' => [
                    'label'        => __( 'Selected keywords', 'wp-linked-events' ),
                    'instructions' => '',
                ],
            ];

            $place_field = ( new Select( $strings['place']['label'] ) )
                ->set_key( "${key}_place" )
                ->set_name( 'place' )
                ->use_ui()
                ->allow_multiple()
                ->allow_null()
                ->set_choices( [ $this, 'get_place_choices' ] )
                ->set_instructions( $strings['place']['instructions'] );

            $event_page_field = ( new PostObject( $strings['event_page']['label'] ) )
                ->set_key( "${key}_event_page" )
                ->set_name( 'event_page' )
                ->set_post_types( [ 'page' ] )
                ->set_return_format( 'id' )
                ->set_instructions( $strings['event_page']['instructions'] );

            $event_keywords = ( new Repeater( $strings['event_keywords']['label'] ) )
                ->set_key( "${key}_event_keywords" )
                ->set_name( 'event_keywords' )
                ->set_instructions( $strings['event_keywords']['instructions'] );

            $event_keyword_group_text = ( new Text( $strings['event_keyword_group_text']['label'] ) )
                ->set_key( "${key}_event_keyword_group_text" )
                ->set_name( 'event_keyword_group_text' )
                ->set_instructions( $strings['event_keyword_group_text']['instructions'] );

            $event_keyword_group_keywords = ( new Select( $strings['event_keyword_group_keywords']['label'] ) )
                ->set_key( "${key}_event_keyword_group_keywords" )
                ->set_name( 'event_keyword_group_keywords' )
                ->use_ui()
                ->allow_multiple()
                ->use_ajax()
                ->set_instructions( $strings['event_keyword_group_keywords']['instructions'] );

            $event_keywords->add_fields( [
                $event_keyword_group_text,
                $event_keyword_group_keywords,
            ] );

            $field_group->add_fields( [
                $place_field,
                $event_page_field,
                $event_keywords,
            ] );

        }
        catch ( \Exception $e ) {
            error_log( $e->getMessage() );
        }

        $field_group->register();
    }

    /**
     * Get place choices
     *
     * @return array
     */
    public function get_place_choices() : array {
        $units   = ( new Integrations\Palvelukartta\ApiClient() )->get_units();
        $choices = [];

        if ( ! empty( $units ) ) {
            foreach ( $units as $unit ) {
                $choices[ $unit->id ] = esc_html( $unit->name_fi ?? null );
            }

            asort( $choices );
        }

        return $choices;
    }

    /**
     * Fill choices for event keyword group keywords field
     *
     * @param array $field ACF field.
     *
     * @return array
     */
    public function fill_event_keyword_group_keywords_choices( array $field ) : array {
        $text     = isset( $_POST['s'] ) ? filter_var( $_POST['s'], FILTER_SANITIZE_STRING ) : '';
        $keywords = ( new Integrations\LinkedEvents\ApiClient() )->get_all_keywords( $text );
        $choices  = [];

        if ( ! empty( $keywords ) ) {
            foreach ( $keywords as $keyword ) {
                $choices[ $keyword->get_id() ] = $keyword->get_name();
            }

            $field['choices'] = $choices;
        }

        return $field;
    }

    /**
     * Get event keywords
     *
     * @return mixed
     */
    public function get_event_keywords() {
        return get_field(
            self::OPTIONS_GROUP_KEY . '_event_keywords',
            'option'
        );
    }

    /**
     * Get event places
     *
     * @return mixed
     */
    public function get_places_from_options() {
        return get_field(
            self::OPTIONS_GROUP_KEY . '_place',
            'option'
        );
    }

    /**
     * Get event page
     *
     * @return mixed
     */
    public function get_event_page() {
        return get_field(
            self::OPTIONS_GROUP_KEY . '_event_page',
            'option'
        );
    }
}

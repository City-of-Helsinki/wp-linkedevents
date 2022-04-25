<?php
/**
 * Event entity
 *
 * @link: https://api.hel.fi/linkedevents/v1/event/helsinki:afxsfaqz44/?include=keywords,location
 * @link: https://dev.hel.fi/apis/linkedevents#documentation
 */

namespace WPLinkedEvents\Integrations\LinkedEvents\Entities;

use DateTime;
use Exception;
use WPLinkedEvents\LinkedEventsPlugin;

/**
 * Class Event
 */
class Event extends Entity {

    /**
     * Event settings
     *
     * @var array
     */
    private array $settings;

    /**
     * Event constructor.
     *
     * @param mixed $entity_data Entity data.
     * @param array $settings    Event settings.
     */
    public function __construct( $entity_data, array $settings = [] ) {
        $this->settings = $settings;

        parent::__construct( $entity_data );
    }

    /**
     * Get event permalink
     *
     * @return string|false
     */
    public function get_permalink() {
        $events_page_id = $this->settings['event_page_id'] ?? null;

        if ( empty( $events_page_id ) ) {
            return false;
        }

        return add_query_arg(
            [
                LinkedEventsPlugin::EVENT_QUERY_VAR => $this->get_id(),
            ],
            get_the_permalink( $events_page_id )
        );
    }

    /**
     * Get Id
     *
     * @return mixed
     */
    public function get_id() {
        return $this->entity_data->id;
    }

    /**
     * Has super event
     *
     * @return bool
     */
    public function has_super_event() {
        return ! empty( $this->entity_data->super_event );
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function get_name() {
        return $this->get_key_by_language( 'name' );
    }

    /**
     * Get status
     *
     * @return mixed
     */
    public function get_status() {
        return $this->entity_data->event_status ?? null;
    }

    /**
     * Get short description
     *
     * @return string|null
     */
    public function get_short_description() {
        return $this->get_key_by_language( 'short_description' );
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function get_description() {
        return $this->get_key_by_language( 'description' );
    }

    /**
     * Get publisher
     *
     * @return mixed
     */
    public function get_publisher() {
        return $this->entity_data->publisher ?? null;
    }

    /**
     * Get start time as DateTime instance.
     *
     * @return DateTime|null
     */
    public function get_start_time() {
        if ( empty( $this->entity_data->start_time ) ) {
            return null;
        }

        try {
            $dt = new \DateTime( $this->entity_data->start_time );
            $dt->setTimezone( new \DateTimeZone( 'Europe/Helsinki' ) );

            return $dt;
        }
        catch ( Exception $e ) {
            error_log( $e->getMessage() );
        }

        return null;
    }

    /**
     * Get end time as DateTime instance.
     *
     * @return DateTime|null
     */
    public function get_end_time() {
        if ( empty( $this->entity_data->end_time ) ) {
            return null;
        }

        try {
            $dt = new \DateTime( $this->entity_data->end_time );
            $dt->setTimezone( new \DateTimeZone( 'Europe/Helsinki' ) );

            return $dt;
        }
        catch ( Exception $e ) {
            error_log( $e->getMessage() );
        }

        return null;
    }

    /**
     * Get event's formatted time string
     *
     * @return string|bool
     */
    public function get_formatted_time_string() {
        $start_time = $this->get_start_time();
        $end_time   = $this->get_end_time();

        if ( empty( $start_time ) ) {
            return false;
        }

        $dates = [
            1 => __( 'Mon', 'wp-linked-events' ),
            2 => __( 'Tue', 'wp-linked-events' ),
            3 => __( 'Wed', 'wp-linked-events' ),
            4 => __( 'Thu', 'wp-linked-events' ),
            5 => __( 'Fri', 'wp-linked-events' ),
            6 => __( 'Sat', 'wp-linked-events' ),
            7 => __( 'Sun', 'wp-linked-events' ),
        ];

        $date_format = 'd.n.Y';
        $time_format = 'H:i';

        if ( $start_time && $end_time ) {
            // 13.12.2020 - 24.12.2020
            if ( $start_time->diff( $end_time )->days >= 1 ) {
                return sprintf(
                    '%s - %s',
                    $start_time->format( $date_format ),
                    $end_time->format( $date_format )
                );
            }

            // 13.12.2020 at 18:30 - 21:45
            return sprintf(
                '%s %s, %s %s - %s',
                $dates[ $start_time->format( 'N' ) ],
                $start_time->format( $date_format ),
                __( 'at', 'wp-linked-events', ),
                $start_time->format( $time_format ),
                $end_time->format( $time_format )
            );
        }

        // 13.12.2020 at 18:30
        return sprintf(
            '%s %s, %s %s',
            $dates[ $start_time->format( 'N' ) ],
            $start_time->format( $date_format ),
            __( 'at', 'wp-linked-events', ),
            $start_time->format( $time_format )
        );
    }

    /**
     * Get location
     *
     * @return Place
     */
    public function get_location() {
        return new Place( $this->entity_data->location ?? null );
    }

    /**
     * Get location string
     *
     * @return false|string
     */
    public function get_location_string() {
        $location = $this->get_location();

        if ( empty( $location ) ) {
            return false;
        }

        $location_string = [
            $location->get_name(),
            $location->get_street_address(),
            $location->get_address_locality(),
        ];

        $location_string = array_filter( $location_string );

        return implode( ', ', $location_string );
    }

    /**
     * Get offers
     *
     * @return false|Offer[]
     */
    public function get_offers() {
        if ( empty( $this->entity_data->offers ) ) {
            return false;
        }

        return array_map( fn( $offer ) => new Offer( $offer ), $this->entity_data->offers );
    }

    /**
     * Get single ticket url for offers
     *
     * @return bool|string
     */
    public function get_single_ticket_url() {
        $offers = $this->get_offers();

        if ( empty( $offers ) ) {
            return false;
        }

        $urls = array_filter( array_map( fn( $offer ) => $offer->get_info_url(), $offers ) );

        return 1 === count( array_unique( $urls ) ) ? $urls[0] : false;
    }

    /**
     * Get keywords
     *
     * @param int|bool $limit Limit keywords.
     *
     * @return array|Keyword[]
     */
    public function get_keywords( $limit = false ) {
        if ( empty( $this->entity_data->keywords ) ) {
            return [];
        }

        $keywords = array_map( fn( $keyword ) => new Keyword( $keyword ), $this->entity_data->keywords );

        if ( $limit && ! empty( $keywords ) ) {
            $keywords = array_slice( $keywords, 0, $limit );
        }

        return $keywords;
    }

    /**
     * Get info url
     *
     * @return string|null
     */
    public function get_info_url() {
        return $this->get_key_by_language( 'info_url' );
    }

    /**
     * Get images
     *
     * @return array|Image[]
     */
    public function get_images() {
        if ( empty( $this->entity_data->images ) ) {
            return [];
        }

        return array_map( fn( $image ) => new Image( $image ), $this->entity_data->images );
    }

    /**
     * Get primary image.
     *
     * @return false|Image|mixed
     */
    public function get_primary_image() {
        $images = $this->get_images();

        if ( ! empty( $images ) ) {
            return $images[0];
        }

        return false;
    }
}

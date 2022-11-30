<?php
/**
 * ApiClient
 */

namespace WPLinkedEvents\Integrations\LinkedEvents;

use WPLinkedEvents\Integrations\LinkedEvents\Entities\Keyword;
use WPLinkedEvents\Integrations\LinkedEvents\Entities\Event;
use WPLinkedEvents\Settings;

/**
 * Class ApiClient
 */
class ApiClient extends \WPLinkedEvents\ApiClient {

    /**
     * Get API base url
     *
     * @return string
     */
    protected function get_base_url() : string {
        return 'https://api.hel.fi/linkedevents/v1';
    }

    /**
     * Do request to 'next' url returned by the API.
     *
     * @param string $request_url Request url.
     *
     * @return false|mixed
     */
    protected function next( string $request_url ) {
        $response = wp_remote_get( $request_url );

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }

        return false;
    }

    /**
     * Get related events
     *
     * @param Event $event
     *
     * @return Event[]|null
     */
    public function get_related_events( Event $event ) : ?array {
        $params = [
            'publisher' => $event->get_publisher(),
            'include'   => 'organizer,location,keywords',
            'page_size' => 7,
        ];
        $limit  = 6;

        if ( ! empty( $event->get_keywords() ) ) {
            $params['keywords'] = implode(
                ',',
                array_map(
                    fn( $keyword ) => $keyword->get_id(),
                    $event->get_keywords()
                )
            );
        }

        $params    = apply_filters( 'wp_linked_events_event_related_events_query_params', $params );
        $cache_key = sprintf( 'wp-linked-events-related-events-%s', md5( wp_json_encode( $params ) ) );
        $response  = get_transient( $cache_key );

        if ( $response ) {
            return $response;
        }

        $response = $this->get( 'event', $params );

        if ( $response && ! empty( $response->data ) ) {
            $event_settings = [
                'event_page_id' => ( new Settings() )->get_event_page(),
            ];

            $events = array_map( function ( $event ) use ( $event_settings ) {
                return new Entities\Event( $event, $event_settings );
            }, $response->data );

            $events = array_filter( $events, function ( $e ) use ( $event ) {
                return $event->get_id() !== $e->get_id();
            } );

            if ( ! empty( $events ) && count( $events ) > $limit ) {
                $events = array_slice( $events, 0, $limit );
            }

            set_transient( $cache_key, $events, HOUR_IN_SECONDS * 2 );
        }

        return $events ?? null;
    }

    /**
     * Get event by id
     *
     * @param string $id Event Id.
     *
     * @return mixed|bool|string
     */
    public function get_event_by_id( string $id ) {
        $cache_key = sprintf( 'wp-linked-events-event-%s', $id );
        $response  = get_transient( $cache_key );

        if ( $response ) {
            return $response;
        }

        $response = $this->get( [ 'event', $id ], [ 'include' => 'organizer,location,keywords' ] );

        if ( $response ) {
            $event = new Event( $response );

            set_transient( $cache_key, $event, HOUR_IN_SECONDS * 2 );

            return $event;
        }

        return false;
    }

    /**
     * Get all keywords
     *
     * @param string $text Filter text.
     *
     * @return Keyword[]|false
     */
    public function get_all_keywords( string $text = '' ) {
        $cache_key = sprintf( 'wp-linked-events-all-keywords-%s', $text );
        $keywords  = get_transient( $cache_key );

        if ( $keywords ) {
            return $keywords;
        }

        $keywords = $this->do_get_all_keywords( '', [], $text );

        if ( $keywords ) {
            set_transient( $cache_key, $keywords, HOUR_IN_SECONDS );
        }

        return $keywords;
    }

    /**
     * Get all keywords
     *
     * @param string $next_url Next url.
     * @param array  $keywords Array of keywords.
     * @param string $text     Text filter for query.
     *
     * @return Keyword[]
     */
    protected function do_get_all_keywords( string $next_url = '', array $keywords = [], string $text = '' ) {
        $cache_key = 'do-get-all-keywords-' . sanitize_title_with_dashes( $next_url );
        $response  = get_transient( $cache_key );
        $params    = [ 'page_size' => 50 ];

        if ( ! $response ) {
            if ( empty( $next_url ) ) {
                if ( ! empty( $text ) ) {
                    $params['text'] = $text;
                }

                $response = $this->get( 'keyword', $params ?? [] );
            }
            else {
                $response = $this->next( $next_url );
            }

            set_transient( $cache_key, $response, HOUR_IN_SECONDS );
        }

        if ( $response && ! empty( $response->data ) ) {
            foreach ( $response->data as $data ) {
                $keywords[] = new Keyword( $data );
            }

            if ( ! empty( $response->meta->next ) ) {
                $keywords = $this->do_get_all_keywords(
                    $response->meta->next,
                    $keywords
                );
            }
        }

        return $keywords;
    }
}

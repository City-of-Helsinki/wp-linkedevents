<?php
/**
 * Palvelukartta ApiClient
 *
 * @link https://www.hel.fi/palvelukarttaws/restpages/ver4.html
 */

namespace WPLinkedEvents\Integrations\Palvelukartta;

/**
 * Class ApiClient
 *
 */
class ApiClient extends \WPLinkedEvents\ApiClient {

    /**
     * Get API base url
     *
     * @return string
     */
    protected function get_base_url() : string {
        return 'https://www.hel.fi/palvelukarttaws/rest/v4';
    }

    /**
     * Get units by ontology word id or array of ids.
     *
     * @return array|bool
     */
    public function get_units() {
        $cache_key = 'wp-linked-events-palvelukartta-units';
        $response  = get_transient( $cache_key );

        if ( $response ) {
            return $response;
        }

        $response = $this->get(
            'unit',
            [
                'ontologyword' => 468,
                'organization' => '83e74666-0836-4c1d-948a-4b34a8b90301',
            ]
        );

        if ( empty( $response ) ) {
            return false;
        }

        set_transient( $cache_key, $response, HOUR_IN_SECONDS );

        return $response;
    }
}

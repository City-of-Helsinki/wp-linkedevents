<?php

namespace WPLinkedEvents\Integrations\LinkedEvents\Entities;


/**
 * Class ServiceLinks
 */
class ServiceLinks {

    /**
     * Get HSL directions link
     *
     * @param string|null $street_address Street address.
     * @param string|null $city           City.
     *
     * @return false|string
     */
    public static function get_hsl_directions_link( string $street_address = null, string $city = null ) : string {
        return add_query_arg(
            [
                'to' => sprintf(
                    '%s,%s',
                    $street_address,
                    $city
                ),
            ],
            'https://reittiopas.hsl.fi/'
        );
    }

    /**
     * Get Google directions link
     *
     * @param string|null $street_address Street address.
     * @param string|null $city           City.
     *
     * @return false|string
     */
    public static function get_google_directions_link( string $street_address = null, string $city = null ) : string {
        return sprintf(
            'https://google.fi/maps/dir/%s,%s',
            $street_address,
            $city
        );
    }

    /**
     * Get Google Maps link
     *
     * @param array $address_pieces Array of address pieces.
     *
     * @return null|string
     */
    public static function get_google_maps_link( array $address_pieces ) : ?string {
        if ( empty( $address_pieces ) ) {
            return null;
        }

        return add_query_arg(
            [
                'api'   => 1,
                'query' => sprintf( '%s', implode( ',', $address_pieces ) ),
            ],
            'https://www.google.fi/maps/search/'
        );
    }


}

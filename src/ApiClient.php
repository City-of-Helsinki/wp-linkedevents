<?php

namespace WPLinkedEvents;

abstract class ApiClient {

    /**
     * Get API base url
     *
     * @return string
     */
    abstract protected function get_base_url() : string;

    /**
     * Create request url.
     *
     * @param string       $base_url Request base url.
     * @param string|array $path     Request path.
     * @param array        $params   Request parameters.
     *
     * @return string Request url
     */
    protected function create_request_url( string $base_url, $path, array $params ) : string {
        if ( is_array( $path ) ) {
            $path = trailingslashit( implode( '/', $path ) );
        }

        $path = trailingslashit( $path );

        if ( empty( $params ) ) {
            $path = trailingslashit( $path );
        }

        return add_query_arg(
            $params,
            sprintf(
                '%s/%s?',
                $base_url,
                $path
            )
        );
    }

    /**
     * Do an API request
     *
     * @param string|array $path   Request path.
     * @param array        $params Request parameters.
     *
     * @return bool|mixed
     */
    public function get( $path, array $params = [] ) {
        $base_url = $this->get_base_url();

        if ( empty( $base_url ) ) {
            return false;
        }

        $request_url = $this->create_request_url( $base_url, $path, $params );
        $response    = wp_remote_get( $request_url );

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }

        return false;
    }
}

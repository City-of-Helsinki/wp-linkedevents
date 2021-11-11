<?php
/**
 * Offer entity
 */

namespace WPLinkedEvents\Integrations\LinkedEvents\Entities;

/**
 * Class Offer
 */
class Offer extends Entity {

    /**
     * Is free
     *
     * @return bool|null
     */
    public function is_free() {
        return $this->entity_data->is_free ?? null;
    }

    /**
     * Get price
     *
     * @return string|null
     */
    public function get_price() {
        return $this->get_key_by_language( 'price' );
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
     * Get description
     *
     * @return string|null
     */
    public function get_description() {
        return $this->get_key_by_language( 'description' );
    }
}

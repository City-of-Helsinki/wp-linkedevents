<?php
/**
 * Image entity
 */

namespace WPLinkedEvents\Integrations\LinkedEvents\Entities;

/**
 * Class Image
 */
class Image extends Entity {

    /**
     * Get photographer name
     *
     * @return mixed
     */
    public function get_photographer_name() {
        return $this->entity_data->photographer_name ?? null;
    }

    /**
     * Get url
     *
     * @return mixed
     */
    public function get_url() {
        return $this->entity_data->url ?? null;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function get_name() {
        return $this->entity_data->name ?? null;
    }

    /**
     * Get alt text
     *
     * @return mixed
     */
    public function get_alt_text() {
        return $this->entity_data->alt_text ?? null;
    }
}

<?php
/**
 * Entity
 */

namespace WPLinkedEvents\Integrations\LinkedEvents\Entities;

/**
 * Class Entity
 */
class Entity {

    /**
     * Entity data
     *
     * @var mixed
     */
    protected $entity_data;

    /**
     * Entity constructor.
     *
     * @param mixed $entity_data Entity data.
     */
    public function __construct( $entity_data ) {
        $this->entity_data = $entity_data;
    }

    /**
     * Get current language
     *
     * @return bool|\PLL_Language|string
     */
    public function get_current_language() {
        if ( function_exists( 'pll_current_language' ) ) {
            return \pll_current_language() ?? get_locale();
        }

        return get_locale();
    }

    /**
     * Get default language
     *
     * @return bool|\PLL_Language|string
     */
    public function get_default_language() {
        if ( function_exists( 'pll_default_language' ) ) {
            return \pll_default_language() ?? get_locale();
        }

        return get_locale();
    }

    /**
     * Get key by language
     *
     * @param string      $key         Event object key.
     * @param bool|object $entity_data Entity data.
     *
     * @return string|null
     */
    protected function get_key_by_language( string $key, $entity_data = false ) {
        $current_language = $this->get_current_language();
        $default_language = $this->get_default_language();

        if ( ! $entity_data ) {
            $entity_data = $this->entity_data;
        }

        if ( isset( $entity_data->{$key} ) ) {
            if ( isset( $entity_data->{$key}->{$current_language} ) ) {
                return $entity_data->{$key}->{$current_language};
            }

            if ( isset( $entity_data->{$key}->{$default_language} ) ) {
                return $entity_data->{$key}->{$default_language};
            }
        }

        return null;
    }
}

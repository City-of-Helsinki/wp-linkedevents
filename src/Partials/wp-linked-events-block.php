<?php
/**
 * Block
 *
 * @var array $fields
 */
$block_data = $fields['data'] ?? [];

if ( empty( $block_data ) || empty( $block_data['events'] ) ) {
    return;
}
?>
<div class="wp-linked-events-block">
    <?php
    /**
     * Event
     *
     * @var \Geniem\Theme\Integrations\LinkedEvents\Entities\Event $event
     */
    ?>
    <?php foreach ( $block_data['events'] as $event ) : ?>
        <?php
        $image    = $event->get_primary_image();
        $keywords = $event->get_keywords( 3 );
        $classes  = empty( $image )
            ? 'wp-linked-events-block__event--has-image'
            : '';
        ?>
        <div class="wp-linked-events-block__event <?php esc_attr_e( $classes ); ?>">
            <?php if ( ! empty( $image ) ) : ?>
                <div class="wp-linked-events-block__image-wrapper">
                    <div class="wp-linked-events-block__image-container">
                        <a href="<?php echo esc_url( $event->get_permalink() ); ?>" aria-hidden="true" tabindex="-1">
                            <img src="<?php echo esc_url( $image->get_url() ); ?>"
                                 class="wp-linked-events-block__image"
                                 alt="<?php echo esc_html( $image->get_alt_text() ); ?>">
                        </a>
                    </div>

                    <?php if ( ! empty( $keywords ) ) : ?>
                        <ul class="wp-linked-events-block__keywords wp-linked-events-block__keywords--floated">
                            <?php foreach ( $keywords as $keyword ) : ?>
                                <li>
                                    <div class="wp-linked-events-block__keyword">
                                        <span class="wp-linked-events-block__keyword-label">
                                            <?php echo esc_html( $keyword->get_name() ); ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="wp-linked-events-block__fields">
                <?php if ( ! empty( $keywords ) && empty( $image ) ) : ?>
                    <ul class="wp-linked-events-block__keywords">
                        <?php foreach ( $keywords as $keyword ) : ?>
                            <li>
                                <div class="wp-linked-events-block__keyword">
                                    <span class="wp-linked-events-block__keyword-label">
                                        <?php echo esc_html( $keyword->get_name() ); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="wp-linked-events-block__date">
                    <?php echo esc_html( $event->get_formatted_time_string() ); ?>
                </div>

                <h3 class="wp-linked-events-block__title">
                    <a href="<?php echo esc_url( $event->get_permalink() ); ?>"
                       class="wp-linked-events-block__link">
                        <?php echo esc_html( $event->get_name() ); ?>
                    </a>
                </h3>

                <div class="wp-linked-events-block__location">
                    <?php echo esc_html( $event->get_location_string() ); ?>
                </div>

                <?php
                $tickets = $event->get_offers();

                if ( ! empty( $tickets ) ) :
                    ?>
                    <div class="wp-linked-events-block__tickets">
                        <?php foreach ( $tickets as $ticket ) : ?>
                            <?php if ( $ticket->is_free() ) : ?>
                                <div class="wp-linked-events-block__price">
                                    <?php echo esc_html__( 'Free', 'nuhe' ); ?>
                                </div>
                            <?php else : // phpcs:ignore ?>
                                <div class="wp-linked-events-block__price">
                                    <?php echo wp_kses_post( $ticket->get_price() ); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

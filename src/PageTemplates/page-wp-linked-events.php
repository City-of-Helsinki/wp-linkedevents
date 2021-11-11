<?php
/**
 * Page template for displaying LinkedEvents event
 *
 * Template Name: WP LinkedEvents (tapahtuman sivu)
 */

use WPLinkedEvents\Integrations\LinkedEvents\ApiClient;
use WPLinkedEvents\LinkedEventsPlugin;

get_header();
?>

<?php
$api   = new ApiClient();
$event = $api->get_event_by_id( get_query_var( LinkedEventsPlugin::EVENT_QUERY_VAR ) );

if ( $event ) {
    $related_events = $api->get_related_events( $event );
    $image          = $event->get_primary_image();
    $keywords       = $event->get_keywords();
}
?>
<main class="main-content page-wp-linked-events" id="main-content">
    <article class="page-wp-linked-events__article">
        <?php if ( empty( $event ) ) : ?>
            <h1>
                <?php esc_html_e( 'Event not found', 'wp-linked-events' ); ?>
            </h1>
        <?php else: ?>
            <header
                class="page-wp-linked-events__header <?php echo $image ? 'page-wp-linked-events__header--has-image' : ''; ?>">
                <div class="page-wp-linked-events__container">
                    <div class="page-wp-linked-events__header-inner">
                        <?php if ( ! empty( $image ) ) : ?>
                            <div class="page-wp-linked-events__image-wrapper">
                                <div class="page-wp-linked-events__image-container">
                                    <img src="<?php echo esc_url( $image->get_url() ); ?>"
                                        class="page-wp-linked-events__image"
                                        alt="<?php echo esc_html( $image->get_alt_text() ); ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="page-wp-linked-events__top-info">
                            <?php if ( ! empty( $keywords ) ) : ?>
                                <ul class="page-wp-linked-events__keywords">
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

                            <div class="page-wp-linked-events__date">
                                <?php echo esc_html( $event->get_formatted_time_string() ); ?>
                            </div>

                            <h1 class="page-wp-linked-events__title">
                                <?php echo esc_html( $event->get_name() ); ?>
                            </h1>

                            <div class="page-wp-linked-events__lead">
                                <?php echo esc_html( $event->get_short_description() ); ?>
                            </div>

                            <div class="page-wp-linked-events__location">
                                <i class="hds-icon hds-icon--location hds-icon--size-m" aria-hidden="true"></i>
                                <span>
                                    <?php echo esc_html( $event->get_location_string() ); ?>
                                </span>
                            </div>

                            <?php
                            $tickets = $event->get_offers();

                            if ( ! empty( $tickets ) ) :
                                $single_ticket_url = $event->get_single_ticket_url();
                                ?>
                                <div class="page-wp-linked-events__tickets">
                                    <?php foreach ( $tickets as $ticket ) : ?>
                                        <div class="page-wp-linked-events__price">
                                            <i class="hds-icon hds-icon--ticket hds-icon--size-m"
                                                aria-hidden="true"></i>
                                            <span>
                                                <?php
                                                if ( $ticket->is_free() ) {
                                                    echo esc_html__( 'Free', 'wp-linked-events' );
                                                }
                                                else {
                                                    if ( ! empty( $ticket->get_price() ) ) {
                                                        echo wp_kses_post(
                                                            sprintf(
                                                                '%s %s',
                                                                $ticket->get_price(),
                                                                $ticket->get_description()
                                                            )
                                                        );
                                                    }
                                                    else {
                                                        echo wp_kses_post( $ticket->get_description() );
                                                    }
                                                }
                                                ?>
                                            </span>
                                        </div>

                                        <?php if ( ! empty( $ticket->get_info_url() ) && ! $single_ticket_url ) : ?>
                                            <div class="page-wp-linked-events__actions">
                                                <a href="<?php echo esc_url( $ticket->get_info_url() ); ?>" class="page-wp-linked-events__button">
                                                    <span>
                                                        <?php esc_html_e( 'Buy tickets', 'wp-linked-events' ); ?>
                                                    </span>
                                                    <i class="hds-icon hds-icon--link-external hds-icon--size-s"
                                                        aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <?php if ( $single_ticket_url ) : ?>
                                        <div class="page-wp-linked-events__ticket-actions">
                                            <a href="<?php echo esc_url( $single_ticket_url ); ?>" class="page-wp-linked-events__button">
                                                <span>
                                                    <?php esc_html_e( 'Buy tickets', 'wp-linked-events' ); ?>
                                                </span>
                                                <i class="hds-icon hds-icon--link-external hds-icon--size-s"
                                                    aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>

            <div class="page-wp-linked-events__container">
                <div class="page-wp-linked-events__grid">
                    <div class="page-wp-linked-events__description">
                        <h2 class="page-wp-linked-events__description-title">
                            <?php esc_html_e( 'Description', 'wp-linked-events' ); ?>
                        </h2>

                        <?php echo wp_kses_post( $event->get_description() ); ?>
                    </div>

                    <div class="page-wp-linked-events__details">
                        <div class="page-wp-linked-events__detail-block">
                            <div class="page-wp-linked-events__detail-block-title">
                                <i class="hds-icon hds-icon--size-s hds-icon--calendar-clock" aria-hidden="true"></i>
                                <?php esc_html_e( 'Date and time', 'wp-linked-events' ); ?>
                            </div>

                            <div class="page-wp-linked-events__detail-block-content">
                                <?php echo esc_html( $event->get_formatted_time_string() ); ?>
                            </div>
                        </div>

                        <div class="page-wp-linked-events__detail-block">
                            <div class="page-wp-linked-events__detail-block-title">
                                <i class="hds-icon hds-icon--size-s hds-icon--location" aria-hidden="true"></i>
                                <?php esc_html_e( 'Location', 'wp-linked-events' ); ?>
                            </div>

                            <div class="page-wp-linked-events__detail-block-content">
                                <ul class="page-wp-linked-events__detail-block-content-links">
                                    <li>
                                        <?php
                                        $location = $event->get_location();

                                        echo wp_kses_post( sprintf(
                                            '<div>%s</div> <div>%s</div> <div>%s</div> <div>%s</div>',
                                            $location->get_name(),
                                            $location->get_street_address(),
                                            $location->get_neighborhood(),
                                            $location->get_address_locality()
                                        ) );
                                        ?>
                                    </li>
                                    <?php if ( $location->get_google_maps_link() ) : ?>
                                        <li>
                                            <a href="<?php echo esc_url( $location->get_google_maps_link() ); ?>"
                                                class="page-wp-linked-events__icon-link"
                                                target="_blank">
                                                <span class="hds-icon hds-icon--size-s hds-icon--link-external"></span>
                                                <span> <?php esc_html_e( 'Open map', 'wp-linked-events' ); ?> </span>
                                                <span class="page-wp-linked-events__sr-text">
                                                    <?php esc_html_e( 'Opens in new tab', 'wp-linked-events' ); ?>
                                                </span>
                                                <span class="hds-icon hds-icon--size-s hds-icon--angle-right"></span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="page-wp-linked-events__detail-block">
                            <div class="page-wp-linked-events__detail-block-title">
                                <i class="hds-icon hds-icon--size-s hds-icon--info-circle" aria-hidden="true"></i>
                                <?php esc_html_e( 'Other information', 'wp-linked-events' ); ?>
                            </div>

                            <div class="page-wp-linked-events__detail-block-content">
                                <ul class="page-wp-linked-events__detail-block-content-links">
                                    <?php if ( ! empty( $location->get_telephone() ) ) : ?>
                                        <li>
                                            <a href="<?php echo esc_url( 'tel:' . $location->get_telephone() ); ?>"
                                                class="page-wp-linked-events__icon-link">
                                                <span> <?php esc_html_e( $location->get_telephone() ); ?> </span>
                                                <span class="hds-icon hds-icon--size-s hds-icon--angle-right"
                                                    aria-hidden="true"></span>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $event->get_info_url() ) ) : ?>
                                        <li>
                                            <a href="<?php echo esc_url( $event->get_info_url() ); ?>"
                                                class="page-wp-linked-events__icon-link"
                                                target="_blank">
                                                <span class="hds-icon hds-icon--size-s hds-icon--link-external"
                                                    aria-hidden="true"></span>
                                                <span> <?php esc_html_e( 'Directions (Google)', 'wp-linked-events' ); ?> </span>
                                                <span class="page-wp-linked-events__sr-text">
                                                    <?php esc_html_e( 'Opens in new tab', 'wp-linked-events' ); ?>
                                                </span>
                                                <span class="hds-icon hds-icon--size-s hds-icon--angle-right"
                                                    aria-hidden="true"></span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="page-wp-linked-events__detail-block">
                            <div class="page-wp-linked-events__detail-block-title">
                                <span class="hds-icon hds-icon--size-s hds-icon--map" aria-hidden="true"></span>
                                <?php esc_html_e( 'Directions', 'wp-linked-events' ); ?>
                            </div>

                            <div class="page-wp-linked-events__detail-block-content">
                                <ul class="page-wp-linked-events__detail-block-content-links">
                                    <li>
                                        <a href="<?php echo esc_url( $location->get_hsl_directions_link() ); ?>"
                                            class="page-wp-linked-events__icon-link"
                                            target="_blank">
                                            <span class="hds-icon hds-icon--size-s hds-icon--link-external"
                                                aria-hidden="true"></span>
                                            <span> <?php esc_html_e( 'Directions (HSL)', 'wp-linked-events' ); ?> </span>

                                            <span class="page-wp-linked-events__sr-text">
                                                <?php esc_html_e( 'Opens in new tab', 'wp-linked-events' ); ?>
                                            </span>

                                            <span class="hds-icon hds-icon--size-s hds-icon--angle-right"
                                                aria-hidden="true"></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo esc_url( $location->get_google_directions_link() ); ?>"
                                            class="page-wp-linked-events__icon-link"
                                            target="_blank">
                                            <span class="hds-icon hds-icon--size-s hds-icon--link-external"
                                                aria-hidden="true"></span>
                                            <span> <?php esc_html_e( 'Directions (Google)', 'wp-linked-events' ); ?> </span>

                                            <span class="page-wp-linked-events__sr-text">
                                                <?php esc_html_e( 'Opens in new tab', 'wp-linked-events' ); ?>
                                            </span>

                                            <span class="hds-icon hds-icon--size-s hds-icon--angle-right"
                                                aria-hidden="true"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </article>

    <?php if ( isset( $related_events ) && ! empty( $related_events ) ) : ?>
        <section class="wp-linked-events-related-events">
            <div class="wp-linked-events-related-events__container">
                <h2 class="wp-linked-events-related-events__title">
                    <?php esc_html_e( 'Related Events', 'wp-linked-events' ); ?>
                </h2>

                <?php
                $fields = [
                    'data' => [
                        'events' => $related_events,
                    ],
                ];

                $plugin = LinkedEventsPlugin::get_instance();

                require $plugin->get_plugin_path() . 'src/Partials/wp-linked-events-block.php';
                ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php get_footer(); ?>

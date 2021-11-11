# WP LinkedEvents

## Requirements

- ACF Codifier
- ACF PRO

## Installation


## Filters

### `wp_linked_events_event_block_params`

Filter block's API query params.

### `wp_linked_events_event_related_events_query_params`

Filter related events API query params.

## Development

- `composer install`
- `npm ci && npm run watch`

### Build for production

- `composer install --no-dev --optimize-autoloader`
- `npm run build`

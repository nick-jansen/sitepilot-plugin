<?php

namespace Sitepilot\Plugin\Services;

use Sitepilot\Framework\Foundation\Application;

class CacheService
{
    /**
     * The application instance.
     */
    private Application $app;

    /**
     * Create a new cache service instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get the cache purge capability.
     */
    public function capability(): string
    {
        return $this->app->filter('cache/capability', 'manage_options');
    }

    /**
     * Get the public post types which require a cache purge.
     */
    public function public_post_types(): array
    {
        $post_types = get_post_types([
            'public' => true,
        ]);

        return $this->app->filter('cache/public_post_types', $post_types);
    }

    /**
     * Determine wether we should purge the cache on a post status change.
     */
    public function should_purge_post_status(): bool
    {
        return $this->app->filter('cache/should_purge_post_status', true);
    }

    /**
     * Get post types that should only purge their own public facing URL.
     */
    public function types_needing_single_purge(): array
    {
        return $this->app->filter('cache/types_needing_single_purge', []);
    }

    /**
     * Get post types that should never trigger a cache purge.
     */
    public function types_excluded_from_purge(): array
    {
        return $this->app->filter('cache/types_excluded_from_purge', [
            'attachment',
            'custom_css',
            'revision',
            'user_request'
        ]);
    }

    /**
     * Determine if the page cache is enabled.
     */
    public function is_page_cache_enabled(): bool
    {
        return $this->app->filter('cache/page_cache_enabled', getenv('SITEPILOT_CACHE_ENABLED') && in_array(getenv('SITEPILOT_CACHE_ENABLED'), ['Enabled', 'True', 'On']));
    }

    /**
     * Determine if the object cache is enabled.
     */
    public function is_object_cache_enabled(): bool
    {
        return $this->app->filter('cache/object_cache_enabled', wp_using_ext_object_cache() ? true : false);
    }

    /**
     * Purge an URL from the cache.
     */
    public function purge_url(string $url): bool
    {
        $url = parse_url($url);

        $response = wp_remote_post('https://127.0.0.1', [
            'method' => 'PURGE',
            'sslverify' => false,
            'headers' => [
                'Host' => $url['host']
            ]
        ]);

        $result = !is_wp_error($response) && $response['response']['code'] ?? null == 200;

        $this->app->action('cache/url_purged', $result);

        return $result;
    }

    /**
     * Purge an post from the cache.
     */
    public function purge_post($post): bool
    {
        $result = $this->purge_url(get_permalink($post));

        $this->app->action('cache/post_purged', $post, $result);

        return $result;
    }

    /**
     * Purge a post from the cache by comment id.
     */
    public function purge_post_by_comment($comment_id): bool
    {
        $comment = get_comment($comment_id);

        if ($comment && $comment->comment_post_ID) {
            $post = get_post($comment->comment_post_ID);

            return $this->purge_post($post);
        }

        return false;
    }

    /**
     * Purge the entire cache.
     */
    public function purge_page_cache(): bool
    {
        $result = $this->purge_url(site_url());

        $this->app->action('cache/purged', $result);

        return $result;
    }

    /**
     * Purge the entire object cache.
     */
    public function purge_object_cache(): bool
    {
        $result = wp_cache_flush();

        $this->app->action('cache/object_purged', $result);

        return $result;
    }
}

<?php

namespace Sitepilot\Plugin\Services;

use Sitepilot\Framework\Foundation\Application;

class LitespeedCacheService
{
    /**
     * The application instance.
     */
    private Application $app;

    /**
     * The default excluded keywords.
     */
    private array $default_exclude_keywords = [
        '/login',
        '/cart',
        '/checkout',
        '/account',
        '/my-account',
        '/wp-admin',
        '/feed',
        '.xml',
        '.txt',
        '.php',
    ];

    /**
     * The default ignored queries.
     */
    private array $default_ignore_queries = [
        'age-verified',
        'ao_noptimize',
        'usqp',
        'cn-reloaded',
        'sscid',
        'ef_id',
        's_kwcid',
        '_bta_tid',
        '_bta_c',
        'dm_i',
        'fb_action_ids',
        'fb_action_types',
        'fb_source',
        'fbclid',
        'utm_id',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_expid',
        'utm_term',
        'utm_content',
        '_ga',
        'gclid',
        'campaignid',
        'adgroupid',
        'adid',
        '_gl',
        'gclsrc',
        'gdfms',
        'gdftrk',
        'gdffi',
        '_ke',
        'trk_contact',
        'trk_msg',
        'trk_module',
        'trk_sid',
        'mc_cid',
        'mc_eid',
        'mkwid',
        'pcrid',
        'mtm_source',
        'mtm_medium',
        'mtm_campaign',
        'mtm_keyword',
        'mtm_cid',
        'mtm_content',
        'msclkid',
        'epik',
        'pp',
        'pk_source',
        'pk_medium',
        'pk_campaign',
        'pk_keyword',
        'pk_cid',
        'pk_content',
        'redirect_log_mongo_id',
        'redirect_mongo_id',
        'sb_referer_host',
        'ref'
    ];

    /**
     * Create a new cache service instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Check if LSCACHE is enabled.
     */
    public function is_enabled(): bool
    {
        return !empty($_SERVER['X-LSCACHE']) && !defined('LSCWP_V');
    }

    /**
     * The cache max age.
     */
    public function max_age(): int
    {
        return $this->app->filter('cache/ignore_queries', 3600);
    }

    /**
     * Get the ignored queries.
     */
    public function ignore_queries(): array
    {
        return $this->app->filter('cache/ignore_queries', $this->default_ignore_queries);
    }

    /**
     * Get the excluded keywords.
     */
    public function exclude_keywords(): array
    {
        return $this->app->filter('cache/exclude_keywords', $this->default_exclude_keywords);
    }

    /**
     * Get the cache purge capability.
     */
    public function capability(): string
    {
        return $this->app->filter('cache/capability', 'manage_options');
    }

    /**
     * Get the public post types.
     */
    public function public_post_types(): array
    {
        return get_post_types([
            'public' => true,
        ]);
    }

    /**
     * Get post types that should never trigger a cache purge.
     */
    public function post_types_excluded_from_purge(): array
    {
        return $this->app->filter('cache/post_types_excluded_from_purge', [
            'attachment',
            'custom_css',
            'revision',
            'user_request'
        ]);
    }

    /**
     * Get post types that should only purge their own public facing URL.
     */
    public function post_types_needing_single_purge(): array
    {
        return $this->app->filter('cache/post_types_needing_single_purge', []);
    }

    /**
     * Check if the current page is cacheable.
     */
    public function is_page_cacheable(): bool
    {
        // Check if request method is GET
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'GET') {
            return false;
        }

        // Prevent caching if user is logged in
        if (is_user_logged_in()) {
            return false;
        }

        // Prevent caching 404 pages
        if (is_404()) {
            return false;
        }

        // Check URI on keywords
        if (!empty($_SERVER['REQUEST_URI'])) {
            foreach ($this->exclude_keywords() as $keyword) {
                if (stripos($_SERVER['REQUEST_URI'], $keyword) !== false) {
                    return false;
                }
            }
        }

        // Check GET queries
        if (!empty($_GET)) {
            $queries_regex = join('|', $this->ignore_queries());
            $queries_regex = "/^($queries_regex)$/";

            if (sizeof(preg_grep($queries_regex, array_keys($_GET), PREG_GREP_INVERT)) > 0) {
                return false;
            }
        }

        // Check cookies
        if (!empty($_COOKIE)) {
            $cookies_regex =
                '/(wordpress_[a-f0-9]+|comment_author|wp-postpass|wordpress_no_cache|wordpress_logged_in|woocommerce_cart_hash|woocommerce_items_in_cart|woocommerce_recently_viewed|edd_items_in_cart)/';

            $cookies = implode('', array_keys($_COOKIE));

            if (preg_match($cookies_regex, $cookies)) {
                return false;
            }
        }

        return true;
    }

    /** 
     * Get the purge queue.
     */
    public function purge_queue(): array
    {
        return get_option('sitepilot_purge_queue', []);
    }

    /**
     * Reset the purge queue.
     */
    public function reset_purge_queue(): bool
    {
        return update_option('sitepilot_purge_queue', []);
    }

    /**
     * Purge an URL from the cache.
     */
    public function purge(string $path): bool
    {
        $path = untrailingslashit($path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = parse_url($path)['path'] ?? null;
        }

        $queue = $this->purge_queue();

        if (!in_array($path, $queue)) {
            $queue[] = $path;
        }

        return update_option('sitepilot_purge_queue', $queue);
    }

    /**
     * Purge a post from the cache.
     */
    public function purge_post($post): bool
    {
        return $this->purge(get_permalink($post));
    }

    /**
     * Purge a post from the cache by comment id.
     */
    public function purge_comment($comment_id): bool
    {
        $comment = get_comment($comment_id);

        if ($comment && $comment->comment_post_ID) {
            $post = get_post($comment->comment_post_ID);
            return $this->purge_post($post);
        }

        return false;
    }
}

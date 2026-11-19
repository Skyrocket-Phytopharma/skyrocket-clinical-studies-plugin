<?php
if (!defined('ABSPATH')) {
    exit;
}

class Skyrocket_Clinical_Studies
{
    private $urls = array(
        'neuropad' => 'https://raw.githubusercontent.com/Skyrocket-Phytopharma/clinical-studies-management/refs/heads/main/resources/json/neuropad_clinical_studies.json',
        'monofilament' => 'https://raw.githubusercontent.com/Skyrocket-Phytopharma/clinical-studies-management/refs/heads/main/resources/json/monofilament_clinical_studies.json',
    );
    private $cache_expiration = 12 * HOUR_IN_SECONDS;
    private $style_registered = false;

    public function __construct()
    {
        add_shortcode('skyrocket_clinical_studies', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'register_styles'));
    }

    /**
     * Register inline CSS for responsive styling (enqueued only when shortcode renders).
     */
    public function register_styles()
    {
        $css = "
            .clinical-studies-list {
                margin: 20px 0;
                padding: 0;
                list-style: none;
            }
            .clinical-study-item {
                margin-bottom: 24px;
                padding: 20px;
                background: #f9f9f9;
                border-left: 4px solid #0073aa;
                border-radius: 4px;
            }
            .clinical-study-title {
                margin: 0 0 8px 0;
                font-size: 1.2em;
                line-height: 1.4;
            }
            .clinical-study-title a {
                color: #0073aa;
                text-decoration: none;
            }
            .clinical-study-title a:hover {
                text-decoration: underline;
            }
            .clinical-study-authors {
                font-size: 0.95em;
                color: #444;
                margin-bottom: 6px;
            }
            .clinical-study-meta {
                font-size: 0.85em;
                color: #666;
                font-style: italic;
            }
            .clinical-study-tag {
                display: inline-block;
                background: #e0e0e0;
                color: #333;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 0.75em;
                font-style: normal;
                margin-right: 8px;
            }
        ";

        wp_register_style('clinical-studies-style', false);
        wp_enqueue_style('clinical-studies-style');
        wp_add_inline_style('clinical-studies-style', $css);
        $this->style_registered = true;
    }

    /**
     * Fetch and cache JSON data based on scope.
     */
    private function get_studies_data($scope)
    {
        if (!array_key_exists($scope, $this->urls)) {
            $scope = 'neuropad';
        }

        $transient_key = 'skyrocket_clinical_studies_' . $scope;
        $cached_data = get_transient($transient_key);
        if (false !== $cached_data) {
            return $cached_data;
        }

        $args = array(
            'timeout' => 15,
            'redirection' => 5,
            'headers' => array(
                'Accept' => 'application/json, text/plain, */*',
            ),
        );

        $response = wp_remote_get($this->urls[$scope], $args);

        if (is_wp_error($response)) {
            error_log('Clinical Studies Fetch Error (' . $scope . '): ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            error_log('Clinical Studies HTTP Error Code (' . $scope . '): ' . $response_code);
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data)) {
            error_log('Clinical Studies JSON Decode Error (' . $scope . ')');
            return false;
        }

        if (empty($data)) {
            error_log('Clinical Studies Empty JSON Array (' . $scope . ')');
            return false;
        }

        set_transient($transient_key, $data, $this->cache_expiration);

        return $data;
    }

    /**
     * Format author array into readable text strings.
     */
    private function format_authors($authors)
    {
        if (!is_array($authors)) {
            return esc_html((string) $authors);
        }

        $formatted = array();
        $count = count($authors);

        for ($i = 0; $i < $count; $i++) {
            $next = $authors[$i + 1] ?? null;
            if ($i < $count - 1 && is_string($next) && $next !== 'et al.' && strlen($next) <= 6) {
                $formatted[] = $authors[$i] . ' ' . $next;
                $i++;
            } else {
                $formatted[] = $authors[$i];
            }
        }

        return esc_html(implode(', ', $formatted));
    }

    /**
     * Shortcode callback handler.
     */
    public function render_shortcode($atts)
    {
        if ($this->style_registered) {
            wp_enqueue_style('clinical-studies-style');
        }

        $atts = shortcode_atts(array(
            'scope' => 'neuropad',
            'from' => '',
            'priority' => '',
            'max-count' => '',
            'sort-by' => 'priority',
        ), $atts, 'skyrocket_clinical_studies');

        $scope = sanitize_text_field(strtolower($atts['scope']));
        $from_year = !empty($atts['from']) ? (int) $atts['from'] : 0;
        $max_priority = ($atts['priority'] !== '' && is_numeric($atts['priority'])) ? (int) $atts['priority'] : null;
        $max_count = !empty($atts['max-count']) ? (int) $atts['max-count'] : 0;
        $sort_by = sanitize_text_field(strtolower($atts['sort-by']));

        $studies = $this->get_studies_data($scope);

        if (false === $studies || empty($studies)) {
            return '<p class="clinical-studies-error">Unable to load clinical studies at this time.</p>';
        }

        // 1. Filter by year ("from")
        if ($from_year > 0) {
            $studies = array_filter($studies, function ($study) use ($from_year) {
                $year = isset($study['year']) ? (int) $study['year'] : 0;
                return $year >= $from_year;
            });
        }

        // 2. Filter by priority (<= specified priority)
        if ($max_priority !== null) {
            $studies = array_filter($studies, function ($study) use ($max_priority) {
                $priority = isset($study['priority']) ? (int) $study['priority'] : 999;
                return $priority <= $max_priority;
            });
        }

        // 3. Sort studies
        usort($studies, function ($a, $b) use ($sort_by) {
            $y1 = isset($a['year']) ? (int) $a['year'] : 0;
            $y2 = isset($b['year']) ? (int) $b['year'] : 0;
            $p1 = isset($a['priority']) ? (int) $a['priority'] : 999;
            $p2 = isset($b['priority']) ? (int) $b['priority'] : 999;
            $t1 = isset($a['title']) ? (string) $a['title'] : '';
            $t2 = isset($b['title']) ? (string) $b['title'] : '';

            switch ($sort_by) {
                case 'year':
                    if ($y1 !== $y2) {
                        return $y2 <=> $y1; // Year descending
                    }
                    if ($p1 !== $p2) {
                        return $p1 <=> $p2; // Priority ascending
                    }
                    return strcasecmp($t1, $t2); // Title ascending

                case 'title':
                    return strcasecmp($t1, $t2); // Title ascending

                case 'priority':
                default:
                    if ($p1 !== $p2) {
                        return $p1 <=> $p2; // Priority ascending
                    }
                    if ($y1 !== $y2) {
                        return $y2 <=> $y1; // Year descending
                    }
                    return strcasecmp($t1, $t2); // Title ascending
            }
        });

        // 4. Limit maximum records if specified
        if ($max_count > 0) {
            $studies = array_slice($studies, 0, $max_count);
        }

        if (empty($studies)) {
            return '<p class="clinical-studies-empty">No clinical studies found matching the criteria.</p>';
        }

        ob_start();
        ?>
        <div class="clinical-studies-container">
            <ul class="clinical-studies-list">
                <?php foreach ($studies as $study): ?>
                    <li class="clinical-study-item">
                        <h3 class="clinical-study-title">
                            <?php if (!empty($study['url'])): ?>
                                <a href="<?php echo esc_url($study['url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html($study['title']); ?>
                                </a>
                            <?php else: ?>
                                <?php echo esc_html($study['title']); ?>
                            <?php endif; ?>
                        </h3>

                        <?php if (!empty($study['authors'])): ?>
                            <div class="clinical-study-authors">
                                <strong>Authors:</strong>
                                <?php echo $this->format_authors($study['authors']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="clinical-study-meta">
                            <?php if (!empty($study['subject'])): ?>
                                <span class="clinical-study-tag">
                                    <?php echo esc_html($study['subject']); ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($study['publication'])): ?>
                                <span class="clinical-study-publication">
                                    <?php echo esc_html($study['publication']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}
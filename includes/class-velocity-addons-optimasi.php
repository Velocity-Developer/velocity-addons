<?php

class Velocity_Addons_Optimasi
{
    public static function get_item_labels()
    {
        return array(
            'revisions'              => 'Revisions',
            'auto_drafts'            => 'Auto Draft',
            'trash_posts'            => 'Posts di Trash',
            'orphan_postmeta'        => 'Orphan Postmeta',
            'orphan_term_rel_object' => 'Orphan Term Relationships (Object)',
            'orphan_term_rel_tax'    => 'Orphan Term Relationships (Taxonomy)',
            'orphan_termmeta'        => 'Orphan Termmeta',
            'comments_spam_trash'    => 'Komentar Spam & Trash',
            'comments_pending_old'   => 'Komentar Pending > 90 Hari',
            'orphan_commentmeta'     => 'Orphan Commentmeta',
            'expired_transients'     => 'Transients Kedaluwarsa',
            'oembed_cache'           => 'Cache oEmbed',
        );
    }

    public static function get_allowed_items()
    {
        return array_keys(self::get_item_labels());
    }

    public function __construct()
    {
        if (!get_option('velocity_optimasi')) {
            return;
        }

        add_action('admin_post_velocity_optimize_db', array($this, 'handle_optimize'));
    }

    public static function render_optimize_db_page()
    {
        self::render_page();
    }

    public static function format_bytes($bytes)
    {
        $bytes = (float) $bytes;
        $units = array('B', 'KB', 'MB', 'GB');
        $i     = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, $i ? 2 : 0) . ' ' . $units[$i];
    }

    public static function stats()
    {
        global $wpdb;

        $posts         = $wpdb->posts;
        $postmeta      = $wpdb->postmeta;
        $comments      = $wpdb->comments;
        $commentmeta   = $wpdb->commentmeta;
        $options       = $wpdb->options;
        $terms         = $wpdb->terms;
        $termmeta      = $wpdb->termmeta;
        $term_taxonomy = $wpdb->term_taxonomy;
        $term_rel      = $wpdb->term_relationships;

        $s = array();

        $s['revisions'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$posts} WHERE post_type='revision'"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(post_content) + OCTET_LENGTH(post_title) + OCTET_LENGTH(post_excerpt)),0) FROM {$posts} WHERE post_type='revision'"),
        );

        $s['auto_drafts'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$posts} WHERE post_status='auto-draft'"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(post_content) + OCTET_LENGTH(post_title) + OCTET_LENGTH(post_excerpt)),0) FROM {$posts} WHERE post_status='auto-draft'"),
        );

        $s['trash_posts'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$posts} WHERE post_status='trash'"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(post_content) + OCTET_LENGTH(post_title) + OCTET_LENGTH(post_excerpt)),0) FROM {$posts} WHERE post_status='trash'"),
        );

        $s['orphan_postmeta'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$postmeta} pm LEFT JOIN {$posts} p ON p.ID=pm.post_id WHERE p.ID IS NULL"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(pm.meta_key) + OCTET_LENGTH(pm.meta_value)),0) FROM {$postmeta} pm LEFT JOIN {$posts} p ON p.ID=pm.post_id WHERE p.ID IS NULL"),
        );

        $s['orphan_term_rel_object'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$term_rel} tr LEFT JOIN {$posts} p ON p.ID=tr.object_id WHERE p.ID IS NULL"),
            'size'  => 0,
        );

        $s['orphan_term_rel_tax'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$term_rel} tr LEFT JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id WHERE tt.term_taxonomy_id IS NULL"),
            'size'  => 0,
        );

        $s['orphan_termmeta'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$termmeta} tm LEFT JOIN {$terms} t ON t.term_id=tm.term_id WHERE t.term_id IS NULL"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(tm.meta_key) + OCTET_LENGTH(tm.meta_value)),0) FROM {$termmeta} tm LEFT JOIN {$terms} t ON t.term_id=tm.term_id WHERE t.term_id IS NULL"),
        );

        $s['comments_spam_trash'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$comments} WHERE comment_approved IN ('spam','trash')"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(comment_content) + OCTET_LENGTH(comment_author) + OCTET_LENGTH(comment_author_email) + OCTET_LENGTH(comment_author_url)),0) FROM {$comments} WHERE comment_approved IN ('spam','trash')"),
        );

        $s['comments_pending_old'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$comments} WHERE comment_approved='0' AND comment_date < NOW() - INTERVAL 90 DAY"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(comment_content) + OCTET_LENGTH(comment_author) + OCTET_LENGTH(comment_author_email) + OCTET_LENGTH(comment_author_url)),0) FROM {$comments} WHERE comment_approved='0' AND comment_date < NOW() - INTERVAL 90 DAY"),
        );

        $s['orphan_commentmeta'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$commentmeta} cm LEFT JOIN {$comments} c ON c.comment_ID=cm.comment_id WHERE c.comment_ID IS NULL"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(cm.meta_key) + OCTET_LENGTH(cm.meta_value)),0) FROM {$commentmeta} cm LEFT JOIN {$comments} c ON c.comment_ID=cm.comment_id WHERE c.comment_ID IS NULL"),
        );

        $s['expired_transients'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$options} WHERE option_name LIKE '_transient_timeout_%' AND CAST(option_value AS UNSIGNED) < UNIX_TIMESTAMP()"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(o2.option_value)),0) FROM {$options} o1 JOIN {$options} o2 ON o2.option_name = REPLACE(o1.option_name,'_transient_timeout_','_transient_') WHERE o1.option_name LIKE '_transient_timeout_%' AND CAST(o1.option_value AS UNSIGNED) < UNIX_TIMESTAMP()"),
        );

        $s['oembed_cache'] = array(
            'count' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$postmeta} WHERE meta_key='_oembed_cache' OR meta_key LIKE '_oembed_%'"),
            'size'  => (int) $wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(meta_value)),0) FROM {$postmeta} WHERE meta_key='_oembed_cache' OR meta_key LIKE '_oembed_%'"),
        );

        return $s;
    }

    private static function delete_items($items)
    {
        global $wpdb;

        $posts         = $wpdb->posts;
        $postmeta      = $wpdb->postmeta;
        $comments      = $wpdb->comments;
        $commentmeta   = $wpdb->commentmeta;
        $options       = $wpdb->options;
        $terms         = $wpdb->terms;
        $termmeta      = $wpdb->termmeta;
        $term_taxonomy = $wpdb->term_taxonomy;
        $term_rel      = $wpdb->term_relationships;

        $results = array();

        if (in_array('revisions', $items, true)) {
            $results['revisions'] = (int) $wpdb->query("DELETE FROM {$posts} WHERE post_type='revision'");
        }
        if (in_array('auto_drafts', $items, true)) {
            $results['auto_drafts'] = (int) $wpdb->query("DELETE FROM {$posts} WHERE post_status='auto-draft'");
        }
        if (in_array('trash_posts', $items, true)) {
            $results['trash_posts'] = (int) $wpdb->query("DELETE FROM {$posts} WHERE post_status='trash'");
        }
        if (in_array('orphan_postmeta', $items, true)) {
            $results['orphan_postmeta'] = (int) $wpdb->query("DELETE pm FROM {$postmeta} pm LEFT JOIN {$posts} p ON p.ID=pm.post_id WHERE p.ID IS NULL");
        }
        if (in_array('orphan_term_rel_object', $items, true)) {
            $results['orphan_term_rel_object'] = (int) $wpdb->query("DELETE tr FROM {$term_rel} tr LEFT JOIN {$posts} p ON p.ID=tr.object_id WHERE p.ID IS NULL");
        }
        if (in_array('orphan_term_rel_tax', $items, true)) {
            $results['orphan_term_rel_tax'] = (int) $wpdb->query("DELETE tr FROM {$term_rel} tr LEFT JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id WHERE tt.term_taxonomy_id IS NULL");
        }
        if (in_array('orphan_termmeta', $items, true)) {
            $results['orphan_termmeta'] = (int) $wpdb->query("DELETE tm FROM {$termmeta} tm LEFT JOIN {$terms} t ON t.term_id=tm.term_id WHERE t.term_id IS NULL");
        }
        if (in_array('comments_spam_trash', $items, true)) {
            $results['comments_spam_trash'] = (int) $wpdb->query("DELETE FROM {$comments} WHERE comment_approved IN ('spam','trash')");
        }
        if (in_array('comments_pending_old', $items, true)) {
            $results['comments_pending_old'] = (int) $wpdb->query("DELETE FROM {$comments} WHERE comment_approved='0' AND comment_date < NOW() - INTERVAL 90 DAY");
        }
        if (in_array('orphan_commentmeta', $items, true)) {
            $results['orphan_commentmeta'] = (int) $wpdb->query("DELETE cm FROM {$commentmeta} cm LEFT JOIN {$comments} c ON c.comment_ID=cm.comment_id WHERE c.comment_ID IS NULL");
        }
        if (in_array('expired_transients', $items, true)) {
            $wpdb->query("DELETE FROM {$options} WHERE option_name LIKE '_transient_timeout_%' AND CAST(option_value AS UNSIGNED) < UNIX_TIMESTAMP()");
            $results['expired_transients'] = (int) $wpdb->query("DELETE t FROM {$options} t JOIN {$options} tt ON tt.option_name = REPLACE(t.option_name,'_transient_timeout_','_transient_') WHERE t.option_name LIKE '_transient_timeout_%' AND CAST(t.option_value AS UNSIGNED) < UNIX_TIMESTAMP()");
        }
        if (in_array('oembed_cache', $items, true)) {
            $results['oembed_cache'] = (int) $wpdb->query("DELETE FROM {$postmeta} WHERE meta_key='_oembed_cache' OR meta_key LIKE '_oembed_%'");
        }

        return $results;
    }

    public static function get_stats_payload()
    {
        $stats      = self::stats();
        $labels_map = self::get_item_labels();
        $rows_total = 0;
        $size_total = 0;
        $items      = array();

        foreach ($labels_map as $key => $label) {
            $count = isset($stats[$key]['count']) ? (int) $stats[$key]['count'] : 0;
            $size  = isset($stats[$key]['size']) ? (int) $stats[$key]['size'] : 0;

            $rows_total += $count;
            $size_total += $size;

            $items[$key] = array(
                'label'       => $label,
                'count'       => $count,
                'size_bytes'  => $size,
                'count_label' => number_format_i18n($count),
                'size_label'  => self::format_bytes($size),
            );
        }

        $rank = array();
        foreach ($items as $key => $item) {
            $rank[$key] = (int) $item['size_bytes'];
        }
        arsort($rank);

        $top = array();
        foreach (array_slice($rank, 0, 3, true) as $key => $size) {
            $top[] = array(
                'key'         => $key,
                'label'       => isset($items[$key]['label']) ? $items[$key]['label'] : ucwords(str_replace('_', ' ', $key)),
                'count'       => isset($items[$key]['count']) ? (int) $items[$key]['count'] : 0,
                'count_label' => isset($items[$key]['count_label']) ? (string) $items[$key]['count_label'] : '0',
                'size_bytes'  => (int) $size,
                'size_label'  => self::format_bytes($size),
            );
        }

        $chart = array();
        foreach ($items as $item) {
            $chart[] = array(
                'label' => $item['label'],
                'size'  => $item['size_bytes'],
                'count' => $item['count'],
            );
        }

        return array(
            'totals' => array(
                'rows'       => $rows_total,
                'rows_label' => number_format_i18n($rows_total),
                'size_bytes' => $size_total,
                'size_label' => self::format_bytes($size_total),
            ),
            'items' => $items,
            'top'   => $top,
            'chart' => $chart,
        );
    }

    public static function run_optimization($do = 'selected', $items = array())
    {
        $allowed = self::get_allowed_items();
        $do      = sanitize_text_field((string) $do);

        if (!is_array($items)) {
            $items = array();
        }
        $items = array_map('sanitize_text_field', $items);

        if ($do === 'all') {
            $items = $allowed;
        } else {
            $items = array_values(array_intersect($items, $allowed));
        }

        $pre      = self::stats();
        $results  = self::delete_items($items);
        $labels   = self::get_item_labels();
        $rows_sum = 0;
        $size_sum = 0;
        $detail   = array();

        foreach ($results as $key => $affected_rows) {
            $rows_sum += (int) $affected_rows;
            $size_bytes = isset($pre[$key]['size']) ? (int) $pre[$key]['size'] : 0;
            $size_sum += $size_bytes;

            $label = isset($labels[$key]) ? $labels[$key] : ucwords(str_replace('_', ' ', $key));
            $message = sprintf('%s: %s (%s)', $label, number_format_i18n((int) $affected_rows), self::format_bytes($size_bytes));

            $detail[] = array(
                'key'           => $key,
                'label'         => $label,
                'deleted_rows'  => (int) $affected_rows,
                'deleted_label' => number_format_i18n((int) $affected_rows),
                'size_bytes'    => $size_bytes,
                'size_label'    => self::format_bytes($size_bytes),
                'message'       => $message,
            );
        }

        $summary = sprintf(
            'Total: %s row, %s',
            number_format_i18n($rows_sum),
            self::format_bytes($size_sum)
        );

        return array(
            'do'          => $do,
            'items'       => array_values($items),
            'details'     => $detail,
            'summary'     => $summary,
            'rows_total'  => $rows_sum,
            'size_total'  => $size_sum,
            'size_label'  => self::format_bytes($size_sum),
            'notice_text' => implode(' | ', wp_list_pluck($detail, 'message')),
        );
    }

    public static function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $done    = isset($_GET['velocity_optimize_done']) ? sanitize_text_field($_GET['velocity_optimize_done']) : '';
        $payload = self::get_stats_payload();
        $url     = admin_url('admin-post.php');

        echo '<div class="wrap">';
        echo '<h2>Optimize Database</h2>';
        echo '<style>
        .vd-grid{display:grid;grid-template-columns:2fr 1fr;gap:15px;align-items:start;margin-top:10px}
        @media(max-width:1024px){.vd-grid{grid-template-columns:1fr}}
        .vd-chart{width:100%;height:260px}
        .vd-chart canvas{width:100%!important;height:100%!important}
        @media(max-width:782px){.vd-chart{height:200px}}
        .vd-table-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
        .vd-table{width:100%;table-layout:auto!important}
        .vd-table th,.vd-table td{vertical-align:middle}
        .vd-col-select{width:40px;text-align:center}
        .vd-col-label{word-break:break-word}
        .vd-col-count,.vd-col-size{white-space:nowrap;text-align:right}
        @media(max-width:782px){.vd-col-select{width:30px}.vd-col-count,.vd-col-size{min-width:50px}}
        </style>';

        $chart_json = wp_json_encode(isset($payload['chart']) ? $payload['chart'] : array());

        echo '<div style="margin:10px 0;background:#fff;padding:12px;border:1px solid #ddd;border-radius:4px;">';
        echo '<strong>Statistik Kandidat</strong>: <span id="vd-optimize-total-rows">' . esc_html(isset($payload['totals']['rows_label']) ? $payload['totals']['rows_label'] : '0') . '</span> row, <span id="vd-optimize-total-size">' . esc_html(isset($payload['totals']['size_label']) ? $payload['totals']['size_label'] : '0 B') . '</span>';
        echo '<div style="margin-top:8px;color:#555"><strong><em>Top berdasarkan ukuran:</em></strong> <ul id="vd-optimize-top-list">';
        if (!empty($payload['top'])) {
            foreach ($payload['top'] as $top_item) {
                echo '<li data-top-key="' . esc_attr($top_item['key']) . '">' . esc_html($top_item['label']) . ': ' . esc_html($top_item['size_label']) . ' (' . esc_html($top_item['count_label']) . ' row)</li>';
            }
        }
        echo '</ul></div>';
        echo '<div class="vd-chart"><canvas id="optimizeChart" data-chart=\'' . esc_attr($chart_json) . '\'></canvas></div>';
        echo '</div>';

        echo '<div id="velocity-optimize-notice" class="notice" style="display:' . ($done ? 'block' : 'none') . '">';
        if ($done) {
            echo '<p><strong>Optimize selesai</strong> - ' . esc_html($done) . '</p>';
        }
        echo '</div>';

        echo '<form id="velocity-optimize-form" method="post" action="' . esc_url($url) . '">';
        echo '<input type="hidden" name="action" value="velocity_optimize_db" />';
        echo wp_nonce_field('velocity_optimize_db', '_velocity_optimize_db', true, false);

        $items = self::get_item_labels();

        echo '<div class="vd-grid">';
        echo '<div>';
        echo '<div class="vd-table-wrap"><table class="widefat fixed vd-table">';
        echo '<thead><tr><th class="vd-col-select">Pilih</th><th class="vd-col-label" style="width:30%">Item</th><th class="vd-col-count" style="text-align:right;">Row</th><th class="vd-col-size" style="text-align:right;">Ukuran</th></tr></thead><tbody>';
        foreach ($items as $key => $label) {
            $item = isset($payload['items'][$key]) ? $payload['items'][$key] : array();
            echo '<tr data-optimize-item="' . esc_attr($key) . '">';
            echo '<td class="vd-col-select"><input type="checkbox" name="items[]" value="' . esc_attr($key) . '" /></td>';
            echo '<td class="vd-col-label">' . esc_html($label) . '</td>';
            echo '<td class="vd-col-count"><span data-item-count="' . esc_attr($key) . '">' . esc_html(isset($item['count_label']) ? $item['count_label'] : '0') . '</span> row</td>';
            echo '<td class="vd-col-size"><span data-item-size="' . esc_attr($key) . '">' . esc_html(isset($item['size_label']) ? $item['size_label'] : '0 B') . '</span></td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';

        echo '</div>';
        echo '<div>';
        echo '<div style="margin-top:0;background:#fff;padding:15px;border:1px solid #ddd;border-radius:4px;">'
            . '<h3 style="margin:0 0 10px">Penjelasan & Dampak</h3>'
            . '<p>Kolom "Row" menampilkan jumlah row yang akan dihapus; "Estimasi Ukuran" adalah perkiraan total byte konten terkait.</p>'
            . '<ul style="margin:0 0 0 18px;list-style:disc">'
            . '<li>Revisions, Auto Draft, Trash: membersihkan cadangan/konsep, aman untuk konten publik.</li>'
            . '<li>Orphan Postmeta/Term/Relasi/Commentmeta: hanya menghapus data tanpa induk, aman.</li>'
            . '<li>Komentar Spam/Trash/Pending > 90 Hari: mengurangi bloat moderasi.</li>'
            . '<li>Transients Kedaluwarsa: mengosongkan cache yang sudah lewat waktu; cache akan terisi ulang.</li>'
            . '<li>Cache oEmbed: cache akan diregenerasi saat konten diakses.</li>'
            . '</ul>'
            . '<p>Kompatibilitas: tidak menyentuh meta/page builder (mis. Beaver Builder); fokus pada data yatim/cadangan/sampah/cache.</p>'
            . '</div>';
        echo '</div>';
        echo '</div>';

        echo '<p style="margin-top:15px">';
        echo '<button class="button button-primary" type="submit" name="do" value="selected">Hapus Terpilih</button> ';
        echo '<button class="button" type="submit" name="do" value="all">Hapus Semua</button>';
        echo '</p>';

        echo '</form>';
        echo '</div>';
    }

    public function handle_optimize()
    {
        if (!current_user_can('manage_options')) {
            wp_die('');
        }
        if (!isset($_POST['_velocity_optimize_db']) || !wp_verify_nonce($_POST['_velocity_optimize_db'], 'velocity_optimize_db')) {
            wp_die('');
        }

        $do    = isset($_POST['do']) ? sanitize_text_field($_POST['do']) : 'selected';
        $items = isset($_POST['items']) && is_array($_POST['items']) ? array_map('sanitize_text_field', $_POST['items']) : array();

        $result = self::run_optimization($do, $items);
        $notice = isset($result['notice_text']) ? (string) $result['notice_text'] : '';
        $summary = isset($result['summary']) ? (string) $result['summary'] : '';

        $notice_text = trim($notice . ' || ' . $summary, ' |');
        $redirect = add_query_arg(
            array('velocity_optimize_done' => rawurlencode($notice_text)),
            admin_url('admin.php?page=velocity_optimize_db')
        );

        wp_safe_redirect($redirect);
        exit;
    }
}

$velocity_addons_Optimasi = new Velocity_Addons_Optimasi();

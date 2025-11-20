<?php

class Velocity_Addons_Optimasi
{
    public function __construct()
    {
        if (!get_option('velocity_optimasi')) return;
        add_action('admin_post_velocity_optimize_db', [$this, 'handle_optimize']);
    }

    public static function render_optimize_db_page()
    {
        self::render_page();
    }

    private static function format_bytes($bytes)
    {
        $bytes = (float)$bytes;
        $units = ['B','KB','MB','GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return number_format($bytes, $i ? 2 : 0) . ' ' . $units[$i];
    }

    private static function stats()
    {
        global $wpdb;
        $posts = $wpdb->posts;
        $postmeta = $wpdb->postmeta;
        $comments = $wpdb->comments;
        $commentmeta = $wpdb->commentmeta;
        $options = $wpdb->options;
        $terms = $wpdb->terms;
        $termmeta = $wpdb->termmeta;
        $term_taxonomy = $wpdb->term_taxonomy;
        $term_rel = $wpdb->term_relationships;

        $s = [];

        $s['revisions'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$posts} WHERE post_type='revision'"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(post_content) + OCTET_LENGTH(post_title) + OCTET_LENGTH(post_excerpt)),0) FROM {$posts} WHERE post_type='revision'")
        ];

        $s['auto_drafts'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$posts} WHERE post_status='auto-draft'"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(post_content) + OCTET_LENGTH(post_title) + OCTET_LENGTH(post_excerpt)),0) FROM {$posts} WHERE post_status='auto-draft'")
        ];

        $s['trash_posts'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$posts} WHERE post_status='trash'"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(post_content) + OCTET_LENGTH(post_title) + OCTET_LENGTH(post_excerpt)),0) FROM {$posts} WHERE post_status='trash'")
        ];

        $s['orphan_postmeta'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$postmeta} pm LEFT JOIN {$posts} p ON p.ID=pm.post_id WHERE p.ID IS NULL"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(pm.meta_key) + OCTET_LENGTH(pm.meta_value)),0) FROM {$postmeta} pm LEFT JOIN {$posts} p ON p.ID=pm.post_id WHERE p.ID IS NULL")
        ];

        $s['orphan_term_rel_object'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$term_rel} tr LEFT JOIN {$posts} p ON p.ID=tr.object_id WHERE p.ID IS NULL"),
            'size'  => 0
        ];

        $s['orphan_term_rel_tax'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$term_rel} tr LEFT JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id WHERE tt.term_taxonomy_id IS NULL"),
            'size'  => 0
        ];

        $s['orphan_termmeta'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$termmeta} tm LEFT JOIN {$terms} t ON t.term_id=tm.term_id WHERE t.term_id IS NULL"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(tm.meta_key) + OCTET_LENGTH(tm.meta_value)),0) FROM {$termmeta} tm LEFT JOIN {$terms} t ON t.term_id=tm.term_id WHERE t.term_id IS NULL")
        ];

        $s['comments_spam_trash'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$comments} WHERE comment_approved IN ('spam','trash')"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(comment_content) + OCTET_LENGTH(comment_author) + OCTET_LENGTH(comment_author_email) + OCTET_LENGTH(comment_author_url)),0) FROM {$comments} WHERE comment_approved IN ('spam','trash')")
        ];

        $s['comments_pending_old'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$comments} WHERE comment_approved='0' AND comment_date < NOW() - INTERVAL 90 DAY"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(comment_content) + OCTET_LENGTH(comment_author) + OCTET_LENGTH(comment_author_email) + OCTET_LENGTH(comment_author_url)),0) FROM {$comments} WHERE comment_approved='0' AND comment_date < NOW() - INTERVAL 90 DAY")
        ];

        $s['orphan_commentmeta'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$commentmeta} cm LEFT JOIN {$comments} c ON c.comment_ID=cm.comment_id WHERE c.comment_ID IS NULL"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(cm.meta_key) + OCTET_LENGTH(cm.meta_value)),0) FROM {$commentmeta} cm LEFT JOIN {$comments} c ON c.comment_ID=cm.comment_id WHERE c.comment_ID IS NULL")
        ];

        $s['expired_transients'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$options} WHERE option_name LIKE '_transient_timeout_%' AND CAST(option_value AS UNSIGNED) < UNIX_TIMESTAMP()"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(o2.option_value)),0) FROM {$options} o1 JOIN {$options} o2 ON o2.option_name = REPLACE(o1.option_name,'_transient_timeout_','_transient_') WHERE o1.option_name LIKE '_transient_timeout_%' AND CAST(o1.option_value AS UNSIGNED) < UNIX_TIMESTAMP()")
        ];

        $s['oembed_cache'] = [
            'count' => (int)$wpdb->get_var("SELECT COUNT(*) FROM {$postmeta} WHERE meta_key='_oembed_cache' OR meta_key LIKE '_oembed_%'"),
            'size'  => (int)$wpdb->get_var("SELECT COALESCE(SUM(OCTET_LENGTH(meta_value)),0) FROM {$postmeta} WHERE meta_key='_oembed_cache' OR meta_key LIKE '_oembed_%'")
        ];

        return $s;
    }

    private function delete_items($items)
    {
        global $wpdb;
        $posts = $wpdb->posts;
        $postmeta = $wpdb->postmeta;
        $comments = $wpdb->comments;
        $commentmeta = $wpdb->commentmeta;
        $options = $wpdb->options;
        $terms = $wpdb->terms;
        $termmeta = $wpdb->termmeta;
        $term_taxonomy = $wpdb->term_taxonomy;
        $term_rel = $wpdb->term_relationships;

        $results = [];

        if (in_array('revisions', $items, true)) {
            $results['revisions'] = (int)$wpdb->query("DELETE FROM {$posts} WHERE post_type='revision'");
        }
        if (in_array('auto_drafts', $items, true)) {
            $results['auto_drafts'] = (int)$wpdb->query("DELETE FROM {$posts} WHERE post_status='auto-draft'");
        }
        if (in_array('trash_posts', $items, true)) {
            $results['trash_posts'] = (int)$wpdb->query("DELETE FROM {$posts} WHERE post_status='trash'");
        }
        if (in_array('orphan_postmeta', $items, true)) {
            $results['orphan_postmeta'] = (int)$wpdb->query("DELETE pm FROM {$postmeta} pm LEFT JOIN {$posts} p ON p.ID=pm.post_id WHERE p.ID IS NULL");
        }
        if (in_array('orphan_term_rel_object', $items, true)) {
            $results['orphan_term_rel_object'] = (int)$wpdb->query("DELETE tr FROM {$term_rel} tr LEFT JOIN {$posts} p ON p.ID=tr.object_id WHERE p.ID IS NULL");
        }
        if (in_array('orphan_term_rel_tax', $items, true)) {
            $results['orphan_term_rel_tax'] = (int)$wpdb->query("DELETE tr FROM {$term_rel} tr LEFT JOIN {$term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id WHERE tt.term_taxonomy_id IS NULL");
        }
        if (in_array('orphan_termmeta', $items, true)) {
            $results['orphan_termmeta'] = (int)$wpdb->query("DELETE tm FROM {$termmeta} tm LEFT JOIN {$terms} t ON t.term_id=tm.term_id WHERE t.term_id IS NULL");
        }
        if (in_array('comments_spam_trash', $items, true)) {
            $results['comments_spam_trash'] = (int)$wpdb->query("DELETE FROM {$comments} WHERE comment_approved IN ('spam','trash')");
        }
        if (in_array('comments_pending_old', $items, true)) {
            $results['comments_pending_old'] = (int)$wpdb->query("DELETE FROM {$comments} WHERE comment_approved='0' AND comment_date < NOW() - INTERVAL 90 DAY");
        }
        if (in_array('orphan_commentmeta', $items, true)) {
            $results['orphan_commentmeta'] = (int)$wpdb->query("DELETE cm FROM {$commentmeta} cm LEFT JOIN {$comments} c ON c.comment_ID=cm.comment_id WHERE c.comment_ID IS NULL");
        }
        if (in_array('expired_transients', $items, true)) {
            $wpdb->query("DELETE FROM {$options} WHERE option_name LIKE '_transient_timeout_%' AND CAST(option_value AS UNSIGNED) < UNIX_TIMESTAMP()");
            $results['expired_transients'] = (int)$wpdb->query("DELETE t FROM {$options} t JOIN {$options} tt ON tt.option_name = REPLACE(t.option_name,'_transient_timeout_','_transient_') WHERE t.option_name LIKE '_transient_timeout_%' AND CAST(t.option_value AS UNSIGNED) < UNIX_TIMESTAMP()");
        }
        if (in_array('oembed_cache', $items, true)) {
            $results['oembed_cache'] = (int)$wpdb->query("DELETE FROM {$postmeta} WHERE meta_key='_oembed_cache' OR meta_key LIKE '_oembed_%'");
        }

        return $results;
    }

    public static function render_page()
    {
        if (!current_user_can('manage_options')) return;
        $done = isset($_GET['velocity_optimize_done']) ? sanitize_text_field($_GET['velocity_optimize_done']) : '';
        $stats = self::stats();
        $rows_total = 0;
        $size_total = 0;
        foreach ($stats as $it) { $rows_total += isset($it['count']) ? (int)$it['count'] : 0; $size_total += isset($it['size']) ? (int)$it['size'] : 0; }
        $url = admin_url('admin-post.php');
        echo '<div class="wrap">';
        echo '<h2>Optimize Database</h2>';
        $rank = [];
        foreach ($stats as $k=>$it) { $rank[$k] = isset($it['size']) ? (int)$it['size'] : 0; }
        arsort($rank);
        $top = array_slice($rank, 0, 3, true);
        $top_html = '';
        if (!empty($top)) {
            $top_html .= '<div style="margin-top:8px;color:#555"><strong><em>Top berdasarkan ukuran:</em></strong> <ul>';
            foreach ($top as $tk=>$sz) {
                $label = ucwords(str_replace('_',' ',$tk));
                $cnt   = isset($stats[$tk]['count']) ? (int)$stats[$tk]['count'] : 0;
                $top_html .= '<li>' . esc_html($label) . ': ' . esc_html(self::format_bytes($sz)) . ' (' . esc_html(number_format($cnt)) . ' row)</li>';
            }
            $top_html .= '</ul></div>';
        }
        $labels_map = [
            'revisions' => 'Revisions',
            'auto_drafts' => 'Auto Draft',
            'trash_posts' => 'Posts di Trash',
            'orphan_postmeta' => 'Orphan Postmeta',
            'orphan_term_rel_object' => 'Orphan Term Relationships (Object)',
            'orphan_term_rel_tax' => 'Orphan Term Relationships (Taxonomy)',
            'orphan_termmeta' => 'Orphan Termmeta',
            'comments_spam_trash' => 'Komentar Spam & Trash',
            'comments_pending_old' => 'Komentar Pending > 90 Hari',
            'orphan_commentmeta' => 'Orphan Commentmeta',
            'expired_transients' => 'Transients Kedaluwarsa',
            'oembed_cache' => 'Cache oEmbed'
        ];
        $chart_data = [];
        foreach ($labels_map as $key=>$label) {
            $sz  = isset($stats[$key]['size']) ? (int)$stats[$key]['size'] : 0;
            $cnt = isset($stats[$key]['count']) ? (int)$stats[$key]['count'] : 0;
            // if ($cnt > 0 || $sz > 0) {
                $chart_data[] = ['label' => $label, 'size' => $sz, 'count' => $cnt];
            // }
        }
        $chart_json = wp_json_encode($chart_data);
        $chart_html = '<div style="margin-top:8px"><canvas id="optimizeChart" height="160" data-chart=\'' . esc_attr($chart_json) . '\'></canvas></div>';
        echo '<div style="margin:10px 0;background:#fff;padding:12px;border:1px solid #ddd;border-radius:4px;">'
            . '<strong>Statistik Kandidat</strong>: ' . esc_html(number_format($rows_total)) . ' row, ' . esc_html(self::format_bytes($size_total))
            . $top_html
            . $chart_html
            . '</div>';
        if ($done) {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Optimize selesai</strong> — ' . esc_html($done) . '</p></div>';
        }
        echo '<form method="post" action="' . esc_url($url) . '">';
        echo '<input type="hidden" name="action" value="velocity_optimize_db" />';
        echo wp_nonce_field('velocity_optimize_db', '_velocity_optimize_db', true, false);

        $items = [
            'revisions' => 'Revisions',
            'auto_drafts' => 'Auto Draft',
            'trash_posts' => 'Posts di Trash',
            'orphan_postmeta' => 'Orphan Postmeta',
            'orphan_term_rel_object' => 'Orphan Term Relationships (Object)',
            'orphan_term_rel_tax' => 'Orphan Term Relationships (Taxonomy)',
            'orphan_termmeta' => 'Orphan Termmeta',
            'comments_spam_trash' => 'Komentar Spam & Trash',
            'comments_pending_old' => 'Komentar Pending > 90 Hari',
            'orphan_commentmeta' => 'Orphan Commentmeta',
            'expired_transients' => 'Transients Kedaluwarsa',
            'oembed_cache' => 'Cache oEmbed'
        ];

        echo '<div class="vd-grid" style="display:grid;grid-template-columns:2fr 1fr;gap:15px;align-items:start;margin-top:10px">';
        echo '<div>';
        echo '<table class="widefat fixed">';
        echo "<thead><tr><th style=\"width:40px\">Pilih</th><th>Item</th><th style=\"width:140px\">Jumlah Baris</th><th style=\"width:180px\">Estimasi Ukuran</th></tr></thead><tbody>";
        foreach ($items as $key => $label) {
            $count = isset($stats[$key]['count']) ? (int)$stats[$key]['count'] : 0;
            $size = isset($stats[$key]['size']) ? (int)$stats[$key]['size'] : 0;
            echo '<tr>';
            echo '<td><input type="checkbox" name="items[]" value="' . esc_attr($key) . '" /></td>';
            echo '<td>' . esc_html($label) . '</td>';
            echo '<td>' . esc_html(number_format($count)) . ' row</td>';
            echo '<td>' . esc_html(self::format_bytes($size)) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

        echo '</div>';
        echo '<div>';
        echo '<div style="margin-top:0;background:#fff;padding:15px;border:1px solid #ddd;border-radius:4px;">'
            . '<h3 style="margin:0 0 10px">Penjelasan & Dampak</h3>'
            . '<p>Kolom "Jumlah Baris" menampilkan jumlah row yang akan dihapus; "Estimasi Ukuran" adalah perkiraan total byte konten terkait.</p>'
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
        if (!current_user_can('manage_options')) wp_die('');
        if (!isset($_POST['_velocity_optimize_db']) || !wp_verify_nonce($_POST['_velocity_optimize_db'], 'velocity_optimize_db')) wp_die('');

        $keys = [
            'revisions','auto_drafts','trash_posts','orphan_postmeta','orphan_term_rel_object','orphan_term_rel_tax','orphan_termmeta','comments_spam_trash','comments_pending_old','orphan_commentmeta','expired_transients','oembed_cache'
        ];

        $do = isset($_POST['do']) ? sanitize_text_field($_POST['do']) : 'selected';
        $items = isset($_POST['items']) && is_array($_POST['items']) ? array_map('sanitize_text_field', $_POST['items']) : [];

        if ($do === 'all') {
            $items = $keys;
        } else {
            $items = array_values(array_intersect($items, $keys));
        }

        $pre = self::stats();
        $results = $this->delete_items($items);

        $rows_total = 0;
        $size_total = 0;
        $msg = [];
        foreach ($results as $k => $v) {
            $rows_total += (int)$v;
            $size = isset($pre[$k]['size']) ? (int)$pre[$k]['size'] : 0;
            $size_total += $size;
            $label = ucwords(str_replace('_',' ',$k));
            $msg[] = sprintf('%s: %s (%s)', $label, number_format((int)$v), self::format_bytes($size));
        }
        $notice = implode(' | ', array_filter($msg));
        $summary = sprintf('Total: %s row, %s', number_format($rows_total), self::format_bytes($size_total));
        $redirect = add_query_arg(['velocity_optimize_done' => rawurlencode($notice . ' || ' . $summary)], admin_url('admin.php?page=velocity_optimize_db'));
        wp_safe_redirect($redirect);
        exit;
    }
}

$velocity_addons_Optimasi = new Velocity_Addons_Optimasi();
<?php

/**
 * Statistics admin page renderer.
 *
 * @package Velocity_Addons
 * @subpackage Velocity_Addons/admin/pages
 */
class Velocity_Addons_Admin_Page_Statistics
{
    public static function render()
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        static $stats_handler = null;
        if (!$stats_handler) {
            $stats_handler = new Velocity_Addons_Statistic();
        }

        $rebuild_message = '';
        if (isset($_POST['reset_stats']) && check_admin_referer('reset_stats')) {
            $stats_handler->reset_statistics();
            $rebuild_message = 'Statistik berhasil di-reset. Semua data statistik dan meta hit telah dihapus.';
        }

        $summary_stats = $stats_handler->get_summary_stats();
        $page_stats    = $stats_handler->get_page_stats(30);
        $referer_stats = $stats_handler->get_referer_stats(30);
?>
        <div class="velocity-dashboard-wrapper vd-ons" id="velocity-statistics-page">
            <div class="vd-header">
                <h1 class="vd-title">Statistik Pengunjung</h1>
                <p class="vd-subtitle">Ringkasan trafik dan halaman populer situs.</p>
            </div>
            <div id="velocity-statistics-notice" class="notice <?php echo $rebuild_message ? 'notice-success' : ''; ?>" style="display:<?php echo $rebuild_message ? 'block' : 'none'; ?>">
                <?php if ($rebuild_message): ?>
                    <p><?php echo esc_html($rebuild_message); ?></p>
                <?php endif; ?>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Reset & Tools</h3>
                </div>
                <div class="vd-section-body">
                    <form method="post" style="display:inline;" id="velocity-statistics-reset-form">
                        <?php wp_nonce_field('reset_stats'); ?>
                        <input type="hidden" name="reset_stats" value="1">
                        <button type="submit" class="button button-secondary"
                            id="velocity-statistics-reset-button"
                            data-confirm-message="Apakah Anda yakin ingin me-reset statistik? Tindakan ini akan menghapus semua data statistik dan meta hit secara permanen.">
                            Reset Statistik
                        </button>
                        <span style="vertical-align:middle;margin-left:10px;color:#666;font-size:13px;">Gunakan ini untuk mengosongkan seluruh data statistik</span>
                    </form>
                </div>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Ringkasan</h3>
                </div>
                <div class="vd-section-body">
                    <div class="vd-grid">
                        <?php
                        $cards = array(
                            'today'      => array('label' => 'Hari Ini', 'data' => $summary_stats['today']),
                            'this_week'  => array('label' => 'Minggu Ini', 'data' => $summary_stats['this_week']),
                            'this_month' => array('label' => 'Bulan Ini', 'data' => $summary_stats['this_month']),
                            'all_time'   => array('label' => 'All Time', 'data' => $summary_stats['all_time']),
                        );
                        foreach ($cards as $card_key => $card):
                            $label = $card['label'];
                            $obj   = $card['data'];
                        ?>
                            <div class="vd-card" style="text-align:center" data-stat-card="<?php echo esc_attr($card_key); ?>">
                                <h3 style="margin:0 0 10px;color:#0073aa;"><?php echo esc_html($label); ?></h3>
                                <div style="font-size:24px;font-weight:700;color:#23282d;" data-stat-unique><?php echo number_format_i18n((int) ($obj->unique_visitors ?? 0)); ?></div>
                                <div style="color:#666;font-size:14px;">Pengunjung Unik</div>
                                <div style="color:#999;font-size:12px;"><span data-stat-total><?php echo number_format_i18n((int) ($obj->total_visits ?? 0)); ?></span> total visits</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="vd-grid-2">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Daily Visits (Last 30 Days)</h3>
                    </div>
                    <div class="vd-section-body">
                        <canvas id="dailyVisitsChart" style="width:100%;height:220px"></canvas>
                    </div>
                </div>
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Halaman Teratas</h3>
                    </div>
                    <div class="vd-section-body">
                        <canvas id="topPagesChart" style="width:100%;height:220px"></canvas>
                    </div>
                </div>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Shortcode: [velocity-statistics]</h3>
                </div>
                <div class="vd-section-body">
                    <p>Tampilkan statistik visitor di halaman, post, atau widget.</p>
                    <ul class="vd-list">
                        <li><span class="vd-code">style</span>: pilih tampilan statistik. <span class="vd-code">list</span> atau <span class="vd-code">inline</span></li>
                        <li><span class="vd-code">show</span>: filter data yang ditampilkan. <span class="vd-code">all</span>, <span class="vd-code">today</span>, atau <span class="vd-code">total</span></li>
                        <li><span class="vd-code">with_online</span>: tampilkan jumlah pengunjung online saat ini</li>
                        <li><span class="vd-code">label_*</span>: ganti label baris counter</li>
                    </ul>
                    <div class="vd-grid-2">
                        <div>
                            <h6>Basic</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics]')">[velocity-statistics]</span>
                                <button onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Semua statistik</div>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin:10px 0;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics show=&quot;today&quot;]')">[velocity-statistics show="today"]</span>
                                <button onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics show=&quot;today&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Hanya hari ini</div>
                        </div>
                        <div>
                            <h6>Advanced</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics with_online=&quot;0&quot;]')">[velocity-statistics with_online="0"]</span>
                                <button onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics with_online=&quot;0&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Sembunyikan baris Pengunjung Online</div>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin:10px 0;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics label_today_visits=&quot;Traffic Hari Ini&quot; label_today_visitors=&quot;Visitor Hari Ini&quot; label_total_visits=&quot;Total Traffic&quot; label_total_visitors=&quot;Total Visitor&quot;]')">[velocity-statistics label_today_visits="Traffic Hari Ini" label_today_visitors="Visitor Hari Ini" label_total_visits="Total Traffic" label_total_visitors="Total Visitor"]</span>
                                <button onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-statistics label_today_visits=&quot;Traffic Hari Ini&quot; label_today_visitors=&quot;Visitor Hari Ini&quot; label_total_visits=&quot;Total Traffic&quot; label_total_visitors=&quot;Total Visitor&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Custom label counter</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vd-section">
                <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                    <h3 style="margin:0; font-size:1.1rem; color:#374151;">Shortcode: [velocity-hits]</h3>
                </div>
                <div class="vd-section-body">
                    <p>Tampilkan nilai meta hit pada posting.</p>
                    <ul class="vd-list">
                        <li><span class="vd-code">post_id</span>: ID posting (opsional)</li>
                        <li><span class="vd-code">format</span>: <span class="vd-code">number</span> atau <span class="vd-code">compact</span></li>
                        <li><span class="vd-code">before</span>/<span class="vd-code">after</span>: teks sebelum/sesudah angka</li>
                        <li><span class="vd-code">class</span>: CSS class untuk elemen angka</li>
                    </ul>
                    <div class="vd-grid-2">
                        <div>
                            <h6>Basic</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-hits]')">[velocity-hits]</span>
                                <button onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-hits]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Memakai get_the_ID()</div>
                        </div>
                        <div>
                            <h6>Advanced</h6>
                            <div style="background:#f1f1f1;padding:12px;border-radius:6px;font-family:monospace;margin-bottom:10px;overflow:hidden;">
                                <span style="color:#0073aa;cursor:pointer;" onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-hits post_id=&quot;123&quot; format=&quot;compact&quot; before=&quot;&quot; after=&quot; views&quot;]')">[velocity-hits post_id="123" format="compact" before="" after=" views"]</span>
                                <button onclick="VelocityAddonsAdmin.copyToClipboard('[velocity-hits post_id=&quot;123&quot; format=&quot;compact&quot; before=&quot;&quot; after=&quot; views&quot;]')" class="button button-secondary" style="float: right;background: #0073aa;color: white;border: none;padding: 1px 8px;border-radius: 4px;font-size: 11px;cursor: pointer;line-height: 13px;min-height: 20px;">Copy</button>
                            </div>
                            <div style="font-size:13px;color:#666;">Pakai ID tertentu + format singkat + label</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vd-grid-2">
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Halaman Teratas (30 Hari)</h3>
                    </div>
                    <div class="vd-section-body">
                        <table class="widefat striped" style="margin-top:5px;">
                            <thead>
                                <tr>
                                    <th>Page URL</th>
                                    <th>Pengunjung Unik</th>
                                    <th>Total Tampilan</th>
                                </tr>
                            </thead>
                            <tbody id="velocity-statistics-pages-body">
                                <?php if (empty($page_stats)) : ?>
                                    <tr>
                                        <td colspan="3" style="text-align:center;color:#666;">No data available</td>
                                    </tr>
                                    <?php else: foreach ($page_stats as $page): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $full = home_url($page->page_url);
                                                echo '<a href="' . esc_url($full) . '" target="_blank" rel="noopener noreferrer"><code>' . esc_html($page->page_url) . '</code></a>';
                                                ?>
                                            </td>
                                            <td><?php echo number_format_i18n((int) $page->unique_visitors); ?></td>
                                            <td><?php echo number_format_i18n((int) $page->total_views); ?></td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="vd-section">
                    <div class="vd-section-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <h3 style="margin:0; font-size:1.1rem; color:#374151;">Rujukan Teratas (30 Hari)</h3>
                    </div>
                    <div class="vd-section-body">
                        <table class="widefat striped" style="margin-top:5px;">
                            <thead>
                                <tr>
                                    <th>Referrer</th>
                                    <th>Visits</th>
                                </tr>
                            </thead>
                            <tbody id="velocity-statistics-referrers-body">
                                <?php if (empty($referer_stats)) : ?>
                                    <tr>
                                        <td colspan="2" style="text-align:center;color:#666;">No data available</td>
                                    </tr>
                                    <?php else: foreach ($referer_stats as $ref): ?>
                                        <tr>
                                            <td><code><?php echo esc_html(parse_url($ref->referer, PHP_URL_HOST) ?: $ref->referer); ?></code></td>
                                            <td><?php echo number_format_i18n((int) $ref->visits); ?></td>
                                        </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="vd-footer">
                <small>Powered by <a href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
            </div>
        </div>
<?php
    }
}


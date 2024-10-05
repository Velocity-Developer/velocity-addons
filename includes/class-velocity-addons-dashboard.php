<?php

/**
 * Display Dashboard Menu in the WordPress admin panel
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Velocity_Addons
 * @subpackage Velocity_Addons/includes
 */

class Velocity_Addons_Dashboard
{

    public function __construct()
    {

    }

    public static function render_dashboard_page(){
        // Menghitung jumlah post, page, dan media
        $post_count = wp_count_posts()->publish;
        $page_count = wp_count_posts('page')->publish;
        $media_count = wp_count_posts('attachment')->publish;
    ?>
        <div class="container ps-0 mt-3">
            <h4>Dashboard Velocity Addons</h4>
            <div class="row m-0">
                <div class="col-md-4">
                    <div class="card border-0 p-0 bg-warning bg-opacity-10 shadow">
                        <div class="card-header p-2 bg-warning d-flex align-items-center justify-content-between">
                            <div><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-file-post" viewBox="0 0 16 16"><path d="M4 3.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/></svg></div>
                            <div class="text-end">
                                <strong class="h5 opacity-75">Jumlah Post</strong><br/>
                                <h3><?php echo $page_count;?></h3>
                            </div>
                        </div>
                        <div class="card-body p-2 opacity-50">Total post yang dibuat</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 p-0 bg-danger bg-opacity-10 shadow">
                        <div class="card-header p-2 bg-danger d-flex align-items-center justify-content-between">
                            <div><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16"><path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/></svg></div>
                            <div class="text-end">
                                <strong class="h5 opacity-75">Jumlah Page</strong><br/>
                                <h3><?php echo $page_count;?></h3>
                            </div>
                            
                        </div>
                        <div class="card-body p-2 opacity-50">Total page yang dibuat</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 p-0 bg-info bg-opacity-10 shadow">
                        <div class="card-header p-2 bg-info d-flex align-items-center justify-content-between">
                            <div><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-camera" viewBox="0 0 16 16"><path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/><path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/></svg></div>
                            <div class="text-end">
                                <strong class="h5 opacity-75">Jumlah Media</strong><br/>
                                <h3><?php echo $media_count;?></h3>
                            </div>
                        </div>
                        <div class="card-body p-2 opacity-50">Total media yang diupload</div>
                    </div>
                </div>
            </div>

            <div class="row m-0 mt-3">
                <div class="col-md-8">
                    <div class="bg-white border rounded-3 p-3 shadow">
                        <h6>Statistik Kunjungan</h6>
                        <?php self::display_statistik_kunjungan(); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white border rounded-3 p-3 shadow">
                        <h6>Traffic Kunjungan</h6>
                        <?php
                        // Tampilkan statistik kunjungan
                        $today_unique_visitors = Velocity_Addons_Statistic::get_today_unique_visitors();
                        $today_visits = Velocity_Addons_Statistic::get_today_visits();
                        $unique_visitors = Velocity_Addons_Statistic::get_unique_visitors();
                        $total_visits = Velocity_Addons_Statistic::get_total_visits();
                        $online_visitors = Velocity_Addons_Statistic::get_online_visitors();
                        ?>
                        <div class="alert alert-success text-success p-2" role="alert">
                            <i class="fa fa-circle"></i> Online User <strong class="h5 m-0"><?php echo $online_visitors; ?></strong>
                        </div>
                        <ul class="p-0 m-0">
                            <li>Pengunjung Hari Ini : <strong class="h5 m-0"><?php echo $today_unique_visitors;?></strong></li>
                            <li>Kunjungan Hari Ini: <strong class="h5 m-0"><?php echo $today_visits;?></strong></li>
                            <li>Total Pengunjung: <strong class="h5 m-0"><?php echo $unique_visitors;?></strong></li>
                            <li>Total Kunjungan: <strong class="h5 m-0"><?php echo $total_visits;?></strong></li>
                            <li>Pengunjung Online: <strong class="h5 m-0"><?php echo $online_visitors;?></strong></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion my-3" id="accordionDashboard">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <h6>QC List Check</h6>
                    </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        <?php Velocity_Addons_Maintenance_Mode::qc_maintenance(); ?>
                    </div>
                    </div>
                </div>
            </div>

            <small class="text-secondary">Powered by <a class="text-secondary" href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
        </div>
    <?php
    }

    public static function display_statistik_kunjungan(){
        // Fetch data from the last 30 days
        global $wpdb;
        $table_name = $wpdb->prefix . 'vd_statistic';
        $results = $wpdb->get_results("
            SELECT DATE(timestamp) as date
            FROM $table_name
            WHERE timestamp >= CURDATE() - INTERVAL 30 DAY
        ");

        // Prepare data for the chart
        $chart_data = [
            'labels' => [],    // Dates
            'counts' => []     // Count of records per date
        ];

        // Initialize an array to hold the counts by date
        $counts_by_date = [];

        foreach ($results as $row) {
            $date = $row->date;

            // Increment the count for the respective date
            if (!isset($counts_by_date[$date])) {
                $counts_by_date[$date] = 0;
            }
            $counts_by_date[$date]++;
        }

        // Populate chart data
        foreach ($counts_by_date as $date => $count) {
            // Format date as day-month (d-m)
            $formatted_date = date('d M', strtotime($date));
            $chart_data['labels'][] = $formatted_date;
            $chart_data['counts'][] = $count;
        }

        // Generate JSON for the chart
        $labels_json = json_encode($chart_data['labels']);
        $counts_json = json_encode($chart_data['counts']);

        // Display the chart
        // echo '<pre>'.print_r($chart_data,true).'</pre>';

        ?>
        <canvas id="myChart"></canvas>
        <script>
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line', // You can change this to 'line' for a line chart
            data: {
                labels: <?php echo $labels_json; ?>, // Dates
                datasets: [{
                    label: '30 Hari Terakhir',
                    data: <?php echo $counts_json; ?>, // Total count per date
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.4 // Add line tension for smooth curves
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        </script>      

<?php
    }
}

// Inisialisasi class Velocity_Addons_Dashboard
$velocity_news = new Velocity_Addons_Dashboard();
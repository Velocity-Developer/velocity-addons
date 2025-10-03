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

    public function __construct() {}

    public static function render_dashboard_page()
    {
        // Menghitung jumlah post, page, dan media
        $post_count = wp_count_posts()->publish;
        $page_count = wp_count_posts('page')->publish;
        $media_count = wp_count_posts('attachment')->inherit;
?>
        <div class="container ps-0 mt-3">
            <h4>Dashboard Velocity Addons</h4>
            <div class="row m-0">
                <div class="col-md-4">
                    <div class="card border-0 p-0 bg-warning bg-opacity-10 shadow">
                        <div class="card-header p-2 bg-warning d-flex align-items-center justify-content-between">
                            <div><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-file-post" viewBox="0 0 16 16">
                                    <path d="M4 3.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v8a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5z" />
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1" />
                                </svg></div>
                            <div class="text-end">
                                <strong class="h5 opacity-75">Jumlah Post</strong><br />
                                <h3><?php echo $post_count; ?></h3>
                            </div>
                        </div>
                        <div class="card-body p-2 opacity-50">Total post yang dibuat</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 p-0 bg-danger bg-opacity-10 shadow">
                        <div class="card-header p-2 bg-danger d-flex align-items-center justify-content-between">
                            <div><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16">
                                    <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z" />
                                </svg></div>
                            <div class="text-end">
                                <strong class="h5 opacity-75">Jumlah Page</strong><br />
                                <h3><?php echo $page_count; ?></h3>
                            </div>

                        </div>
                        <div class="card-body p-2 opacity-50">Total page yang dibuat</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 p-0 bg-info bg-opacity-10 shadow">
                        <div class="card-header p-2 bg-info d-flex align-items-center justify-content-between">
                            <div><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-camera" viewBox="0 0 16 16">
                                    <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z" />
                                    <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0" />
                                </svg></div>
                            <div class="text-end">
                                <strong class="h5 opacity-75">Jumlah Media</strong><br />
                                <h3><?php echo $media_count; ?></h3>
                            </div>
                        </div>
                        <div class="card-body p-2 opacity-50">Total media yang diupload</div>
                    </div>
                </div>
            </div>

            <div class="accordion m-3" id="accordionDashboard">
                <div class="accordion-item mb-3">
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

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            <h6>List Shortcode</h6>
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                            <h6>#Breadcrumbs</h6>
                            <p><code>[vd-breadcrumbs]</code></p>

                            <h6>#Velocity Recaptcha</h6>
                            <p><code>[velocity_captcha]</code></p>

                            <h6>#Share Post</h6>
                            <p><code>[velocity-sharepost title='' label_share='' platforms='']</code></p>
                            <ul>
                                <li><strong>title</strong>: nama label share. Share this post</li>
                                <li><strong>label_share</strong>: tampilkan label share. true/false</li>
                                <li><strong>platforms</strong>: platform berbagi. facebook/twitter/whatsapp/telegram/email</li>
                            </ul>

                            <h6>#Velocity Statistics</h6>
                            <p><code>[velocity-statistics style='' show='' columns='']</code></p>
                            <ul>
                                <li><strong>style</strong>: pilih tampilan statistik. minimal/cards (default minimal)</li>
                                <li><strong>show</strong>: filter data yang ditampilkan. all/today/total (default all)</li>
                                <li><strong>columns</strong>: jumlah kolom untuk mode cards. 1/2/3/4 (default 1)</li>
                            </ul>

                            <h6>#Velocity Hits</h6>
                            <p><code>[velocity-hits post_id='' format='' before='' after='' class='']</code></p>
                            <ul>
                                <li><strong>post_id</strong>: ID posting (opsional; default get_the_ID())</li>
                                <li><strong>format</strong>: format angka. compact/number (default compact)</li>
                                <li><strong>before</strong>: teks/HTML sebelum angka hit</li>
                                <li><strong>after</strong>: teks/HTML setelah angka hit</li>
                                <li><strong>class</strong>: kelas CSS untuk elemen angka hit</li>
                            </ul>

                            <h6>#VD Gallery</h6>
                            <p><code>[vdgallery id='']</code></p>

                            <h6>#VD Gallery Slide</h6>
                            <p><code>[vdgalleryslide id='']</code></p>
                        </div>
                    </div>
                </div>
            </div>

            <small class="text-secondary">Powered by <a class="text-secondary" href="https://velocitydeveloper.com/" target="_blank">velocitydeveloper.com</a></small>
        </div>
<?php
    }
}

// Inisialisasi class Velocity_Addons_Dashboard
$velocity_news = new Velocity_Addons_Dashboard();

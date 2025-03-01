<?php
global $wpdb;

// Lekérdezzük az összes értékelendő terméket és azok statisztikáit
$sql_ertekelesek = "
    SELECT termek_id, ossz_ertekeles, atlag, 
           egycsillag, ketcsillag, haromcsillag, negycsillag, otcsillag
    FROM {$wpdb->prefix}wc_odin_review_ertekelendo_termekek
";
$ertekelesek = $wpdb->get_results($sql_ertekelesek);

// Lekérdezzük az elfogadásra váró véleményeket
$sql_pending_reviews = "
    SELECT COUNT(*) as pending_count
    FROM {$wpdb->prefix}wc_odin_review_ertekeles_ellenorzo
    WHERE statusz = 'pending'
";
$pending_reviews = $wpdb->get_var($sql_pending_reviews);

// Lekérdezzük a top 10 terméket átlagos értékelés alapján
$sql_top10 = "
    SELECT termek_id, atlag
    FROM {$wpdb->prefix}wc_odin_review_ertekelendo_termekek
    ORDER BY atlag DESC
    LIMIT 10
";
$top10_termekek = $wpdb->get_results($sql_top10);

// Admin email címének lekérése
$adminmailcim = get_meta_value_by_key("admin_email_cim");

?>
<div class="wrap">
    <h1>Vezérlőpult</h1>
    <p><strong>Admin email címe:</strong> <?php echo $adminmailcim; ?></p>
    
    <div class="dashboard-container">
        <!-- Statisztikai rész -->
        <div class="stats-container">
            <h2>Statisztikák</h2>
            <div class="stat-box">
                <h3>Top 10 Termék (Átlagos értékelés)</h3>
                <ul>
                    <?php foreach ($top10_termekek as $termek): ?>
                        <li>Termék ID: <?php echo $termek->termek_id; ?> - Átlag: <?php echo number_format($termek->atlag, 2); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="stat-box">
                <h3>Összes értékelés</h3>
                <p><strong><?php echo array_sum(array_column($ertekelesek, 'ossz_ertekeles')); ?></strong> értékelés érkezett összesen.</p>
            </div>

            <div class="stat-box">
                <h3>Leggyakoribb értékelés</h3>
                <p>1 csillag: <?php echo array_sum(array_column($ertekelesek, 'egycsillag')); ?> | 5 csillag: <?php echo array_sum(array_column($ertekelesek, 'otcsillag')); ?> </p>
            </div>
        </div>

        <!-- Elfogadásra váró vélemények -->
        <div class="pending-reviews-container">
            <h2>Elfogadásra váró vélemények</h2>
            <p>Jelenleg <strong><?php echo $pending_reviews; ?></strong> értékelés vár elfogadásra.</p>
            
            <!-- Kártya elrendezés az elfogadásra váró véleményekhez -->
            <div class="pending-reviews-cards">
                <?php
                $sql_pending_details = "
                    SELECT rendeles_id, termek_id, keresztnev, szoveges_ertekeles, csillag
                    FROM {$wpdb->prefix}wc_odin_review_ertekeles_ellenorzo
                    WHERE statusz = 'pending'
                ";
                $pending_reviews_details = $wpdb->get_results($sql_pending_details);
                foreach ($pending_reviews_details as $review):
                ?>
                    <div class="pending-review-card">
                        <h4>Termék ID: <?php echo $review->termek_id; ?></h4>
                        <p><strong>Keresztnév:</strong> <?php echo $review->keresztnev; ?></p>
                        <p><strong>Értékelés:</strong> <?php echo wp_trim_words($review->szoveges_ertekeles, 10, '...'); ?></p>
                        <p><strong>Csillagok:</strong> <?php echo $review->csillag; ?> csillag</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard konténer stílusok */
    .dashboard-container {
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }

    /* Statisztikai panel */
    .stats-container {
        width: 70%;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .stat-box {
        background-color: #fff;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .stat-box h3 {
        margin-top: 0;
    }
    .stat-box ul {
        list-style-type: none;
        padding-left: 0;
    }
    .stat-box li {
        margin-bottom: 10px;
    }

    /* Elfogadásra váró vélemények kártyái */
    .pending-reviews-container {
        width: 25%;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .pending-reviews-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .pending-review-card {
        background-color: #f4f4f4;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease-in-out;
    }
    .pending-review-card:hover {
        transform: translateY(-5px);
    }

    .pending-review-card h4 {
        margin-top: 0;
    }

    .pending-review-card p {
        margin-bottom: 8px;
        font-size: 14px;
    }
</style>

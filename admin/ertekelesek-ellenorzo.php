<div class="wrap">
    <h1>Értékelés ellenőrző</h1>
    <h2>Várakozó értékelések:</h2>

    <table id="pending_reviews_table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Rendelés ID</th>
                <th>Termék ID</th>
                <th>Keresztnév</th>
                <th>Értékelés</th>
                <th>Csillagok</th>
                <th>Műveletek</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . "wc_odin_review_ertekeles_ellenorzo";
            $pending_reviews = $wpdb->get_results("SELECT * FROM $table_name WHERE statusz = 'pending'");

            foreach ($pending_reviews as $review) {
                echo "<tr data-id='{$review->id}'>";
                echo "<td>{$review->id}</td>";
                echo "<td>{$review->rendeles_id}</td>";
                echo "<td>{$review->termek_id}</td>";
                echo "<td>{$review->keresztnev}</td>";
                echo "<td>{$review->szoveges_ertekeles}</td>";
                // Csillagok vizuális megjelenítése
                echo "<td>";
                $csillagok = $review->csillag;
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $csillagok) {
                        echo "<span class='star filled'>★</span>";
                    } else {
                        echo "<span class='star'>★</span>";
                    }
                }
                echo "</td>";
                echo "<td>
                        <button class='approve-btn' onclick='approveReview({$review->id})'>Elfogadás</button>
                        <button class='reject-btn' onclick='rejectReview({$review->id})'>Elutasítás</button>
                        <button class='edit-btn' onclick='editReview({$review->id}, \"{$review->keresztnev}\", \"{$review->szoveges_ertekeles}\", {$review->csillag})'>Módosítás</button>
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<style>
    /* Alap dizájn a táblázatnak */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-family: Arial, sans-serif;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #f4f4f4;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    button {
        padding: 5px 10px;
        border: none;
        cursor: pointer;
    }
    .approve-btn {
        background-color: #4CAF50;
        color: white;
    }
    .reject-btn {
        background-color: #ff4d4d;
        color: white;
    }
    .edit-btn {
        background-color: #f0ad4e;
        color: white;
    }
    
    /* Csillagok megjelenítése */
    .star {
        font-size: 20px;
        color: #ddd; /* Szürke szín az üres csillagoknak */
    }

    .star.filled {
        color: gold; /* Arany szín a kitöltött csillagoknak */
    }
</style>

<script>
function approveReview(id) {
    if (!confirm("Biztosan elfogadod ezt az értékelést?")) return;
    fetch("../wp-content/plugins/odin-review/admin/ertekeles-elfogadas.php", {
        method: "POST",
        body: JSON.stringify({ id }),
        headers: { "Content-Type": "application/json" }
    }).then(() => location.reload());
}

function rejectReview(id) {
    if (!confirm("Biztosan elutasítod ezt az értékelést?")) return;
    fetch("../wp-content/plugins/odin-review/admin/ertekeles-elutasitas.php", {
        method: "POST",
        body: JSON.stringify({ id }),
        headers: { "Content-Type": "application/json" }
    }).then(() => location.reload());
}

function editReview(id, keresztnev, szoveges, csillagok) {
    const newText = prompt("Új szöveges értékelés:", szoveges);
    const newStars = prompt("Új csillag értékelés (1-5):", csillagok);
    if (newText !== null && newStars !== null) {
        fetch("../wp-content/plugins/odin-review/admin/ertekeles-modositas.php", {
            method: "POST",
            body: JSON.stringify({ id, szoveges: newText, csillagok: newStars }),
            headers: { "Content-Type": "application/json" }
        }).then(() => location.reload());
    }
}
</script>

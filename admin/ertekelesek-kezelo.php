<div class="wrap">
    <h1>Értékesítés kezelő</h1>
    <h2>Értékelés hozzáadása manuálisan:</h2>
    <form id="review_form" class="review-form">
        <div class="form-group">
            <label for="termek_id">Termék ID:</label>
            <input type="number" name="termek_id" id="termek_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="keresztnev">Keresztnév:</label>
            <input type="text" name="keresztnev" id="keresztnev" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="szoveges_ertekeles">Szöveges értékelés:</label>
            <textarea name="szoveges_ertekeles" id="szoveges_ertekeles" class="form-control" rows="4" cols="50"></textarea>
        </div>
        <div class="form-group">
            <label for="csillag_ertekeles">Csillag értékelés:</label>
            <select name="csillag_ertekeles" id="csillag_ertekeles" class="form-control" required>
                <option value="1">1 csillag</option>
                <option value="2">2 csillag</option>
                <option value="3">3 csillag</option>
                <option value="4">4 csillag</option>
                <option value="5">5 csillag</option>
            </select>
        </div>
        <button type="submit" class="submit-btn">Értékelés hozzáadása</button>
    </form>
    <h2>Értékelések kezelése:</h2>
    <label for="filter">Szűrés:</label>
    <input type="text" id="filter" class="filter-input" placeholder="Keresztnév, rendelés ID, termék ID..." onkeyup="filterTable()">
    <table id="reviews_table" class="reviews-table">
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
            $table_name = $wpdb->prefix . "wc_odin_review_ertekelesek";
          $reviews = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");


            foreach ($reviews as $review) {
                echo "<tr data-id='{$review->id}'>";
                echo "<td>{$review->id}</td>";
                echo "<td>{$review->rendeles_id}</td>";
                echo "<td>{$review->termek_id}</td>";
                echo "<td>{$review->keresztnev}</td>";
                echo "<td>{$review->szoveges_ertekeles}</td>";
                echo "<td>";
                $csillagok = $review->csillag_ertekeles;
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $csillagok) {
                        echo "<span class='star filled'>★</span>";
                    } else {
                        echo "<span class='star'>★</span>";
                    }
                }
                echo "</td>";
                echo "<td>
                        <button class='delete-btn' onclick='deleteReview({$review->id})'>Törlés</button>
                        <button class='edit-btn' onclick='editReview({$review->id}, \"{$review->keresztnev}\", \"{$review->szoveges_ertekeles}\", {$review->csillag_ertekeles})'>Módosítás</button>
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<style>
table{width:100%;border-collapse:collapse;margin-top:20px;font-family:Arial,sans-serif;}
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
    }
    th {
        background-color: #f4f4f4;
        font-weight: bold;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        font-weight: bold;
    }
    .form-control {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }
    .submit-btn {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }
    .submit-btn:hover {
        background-color: #45a049;
    }
    .filter-input {
        padding: 10px;
        width: 300px;
        margin-bottom: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    button {
        padding: 6px 12px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }
    .delete-btn {
        background-color: #ff4d4d;
        color: white;
    }
    .edit-btn {
        background-color: #4CAF50;
        color: white;
    }
    .star {
        font-size: 20px;
        color: #ddd; /* Szürke szín az üres csillagoknak */
    }
    .star.filled {
        color: gold; /* Arany szín a kitöltött csillagoknak */
    }
</style>
<script>
function deleteReview(id) {
    if (!confirm("Biztosan törlöd ezt az értékelést?")) return;
    
    // Létrehozzuk a FormData objektumot, hogy a post adatokat küldjük
    var formData = new FormData();
    formData.append('id', id);  // hozzáadjuk a törlendő értékelés ID-ját
    
    fetch("../wp-content/plugins/odin-review/admin/ertekeles-torles.php", {
        method: "POST",
        body: formData  // Itt a FormData küldése történik, nem JSON
    })
    .then(response => response.json())  // A válasz JSON formátumban
    .then(data => {
        alert(data.message);  // A válasz üzenetének megjelenítése
        location.reload();  // Az oldal frissítése
    });
}
function editReview(id, keresztnev, szoveges, csillagok) {
    const newText = prompt("Új szöveges értékelés:", szoveges);
    const newStars = prompt("Új csillag értékelés (1-5):", csillagok);
    
    if (newText !== null && newStars !== null) {
        // Létrehozzuk a FormData objektumot
        var formData = new FormData();
        formData.append('id', id);  // Hozzáadjuk az ID-t
        formData.append('szoveges', newText);  // Hozzáadjuk az új szöveges értékelést
        formData.append('csillagok', newStars);  // Hozzáadjuk az új csillag értékelést

        fetch("../wp-content/plugins/odin-review/admin/ertekeles-modositas.php", {
            method: "POST",
            body: formData  // A FormData-t küldjük
        })
        .then(response => response.json())  // A válasz JSON formátumban
        .then(data => {
            alert(data.message);  // A válasz üzenetének megjelenítése
            location.reload();  // Az oldal frissítése
        });
    }
}

function filterTable() {
    const filter = document.getElementById("filter").value.toLowerCase();
    document.querySelectorAll("#reviews_table tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
}
document.getElementById("review_form").addEventListener("submit", function(event) {
    event.preventDefault();
    var formData = new FormData(this);
    fetch("../wp-content/plugins/odin-review/admin/ertekeles-feldolgozo.php", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(data => {
          alert(data.message);
          location.reload(); // Frissítjük az oldalt
      });
});
</script>

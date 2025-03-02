<?php
$adminmailcim = get_meta_value_by_key("admin_email_cim");
$adminsubmail = get_meta_value_by_key("admin_email_sub");
$adminmailform = get_meta_value_by_key("admin_email_form");
$vasarlosubmail = get_meta_value_by_key("vasarlo_email_sub");
$vasarlosubform = get_meta_value_by_key("vasarlo_email_form");
$varakozasinapokszama = get_meta_value_by_key("varakozasi_ido");
$kuponmailsubori = get_meta_value_by_key("kupon_email_sub");
$kuponmailformori = get_meta_value_by_key("kupon_email_form");

function my_admin_page() {
    if (isset($_POST['submit'])) {
        $adminmailcim_uj = sanitize_email($_POST['adminmailcim']);
        if ($adminmailcim_uj) {
            update_meta_value_by_key('admin_email_cim', $adminmailcim_uj);
            echo '<div class="updated"><p>Admin e-mail cím frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Kérlek, adj meg egy érvényes e-mail címet!</p></div>';
        }
    }
}
function varakozasmodify() {
    if (isset($_POST['waiting'])) {
        $varakozasinap_uj =$_POST['varakozasinapokszamainput'];
        if ($varakozasinap_uj) {
            update_meta_value_by_key('varakozasi_ido', $varakozasinap_uj);
            echo '<div class="updated"><p>Admin e-mail tárgy frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Hiba az admin e-mail tárgy firssítésénél!</p></div>';
        }
    }
}
function adminsubmodify() {
    if (isset($_POST['adminsub'])) {
        $adminsub_uj =$_POST['adminsubmodi'];
        if ($adminsub_uj) {
            update_meta_value_by_key('admin_email_sub', $adminsub_uj);
            echo '<div class="updated"><p>Admin e-mail tárgy frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Hiba az admin e-mail tárgy firssítésénél!</p></div>';
        }
    }
}
function adminmailformmodify() {
    if (isset($_POST['adminformsub'])) {
        $adminform_uj =$_POST['adminformmodi'];
        if ($adminform_uj) {
            update_meta_value_by_key('admin_email_form', $adminform_uj);
            echo '<div class="updated"><p>Admin e-mail form frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Hiba az admin e-mail form firssítésénél!</p></div>';
        }
    }
}
function vasarlomailsubmodify() {
    if (isset($_POST['vasarlosubmailsub'])) {
        $adminform_uj =$_POST['vasarlosubmodi'];
        if ($adminform_uj) {
            update_meta_value_by_key('vasarlo_email_sub', $adminform_uj);
            echo '<div class="updated"><p>Vásárló e-mail tárgy frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']); 
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Hiba a vásárló e-mail tárgy firssítésénél!</p></div>';
        }
    }
}
function vasarlomailformmodify() {
    if (isset($_POST['vasarloemailformsub'])) {
        $vasarloformuj_uj =$_POST['vasarlomailformmodi'];
        if ($vasarloformuj_uj) {
            update_meta_value_by_key('vasarlo_email_form', $vasarloformuj_uj);
            echo '<div class="updated"><p>Vásárló e-mail tárgy frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Hiba a vásárló e-mail tárgy firssítésénél!</p></div>';
        }
    }
}
function kuponmailmodify() {
    if (isset($_POST['kuponsubsub'])) {
        $kuponsubuj =$_POST['kuponsubmodi'];
        if ($kuponsubuj) {
            update_meta_value_by_key('kupon_email_sub', $kuponsubuj);
            echo '<div class="updated"><p>A kupon e-mail tárgy frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Hiba a kupon e-mail tárgy firssítésénél!</p></div>';
        }
    }
}
function kuponmailformmodify() {
    if (isset($_POST['kuponmailsubsub'])) {
        $kuponmailuj =$_POST['kuponmailmodi'];
        if ($kuponmailuj) {
            update_meta_value_by_key('kupon_email_form', $kuponmailuj);
            echo '<div class="updated"><p>A kupon e-mail tartalma frissítve!</p></div>';
			header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="error"><p>Hiba a kupon e-mail tárgy firssítésénél!</p></div>';
        }
    }
}
my_admin_page();
adminsubmodify();
adminmailformmodify();
vasarlomailsubmodify();
vasarlomailformmodify();
kuponmailmodify();
kuponmailformmodify();
varakozasmodify();
?>
<div class="wrap">
    <h1 class="settings-header">Beállítások</h1>

    <!-- Alap beállítások szekció -->
    <section class="settings-section">
        <h2>Alap beállítások</h2>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="adminmailcim">Admin email címe:</label>
                <input type="text" id="adminmailcim" name="adminmailcim" value="<?php echo esc_attr($adminmailcim); ?>" class="input-field">
                <button type="submit" name="submit" class="submit-btn">Módosít</button>
            </div>
        </form>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="varakozasinapokszamainput">Várakozási napok száma az értékeléshez:</label>
                <input type="text" id="varakozasinapokszamainput" name="varakozasinapokszamainput" value="<?php echo esc_attr($varakozasinapokszama); ?>" class="input-field">
                <button type="submit" name="waiting" class="submit-btn">Módosít</button>
            </div>
        </form>
    </section>
    <section class="settings-section">
        <h2>Admin e-mail form</h2>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="adminsubmodi">Admin e-mail tárgya új beérkező értékelés esetén:</label>
                <input type="text" id="adminsubmodi" name="adminsubmodi" value="<?php echo esc_attr($adminsubmail); ?>" class="input-field">
                <button type="submit" name="adminsub" class="submit-btn">Módosít</button>
            </div>
        </form>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="adminformmodi">Admin e-mail tartalma új beérkező értékelés esetén:</label>
                <textarea id="adminformmodi" name="adminformmodi" rows="10" class="input-field"><?php echo esc_attr($adminmailform); ?></textarea>
                <button type="submit" name="adminformsub" class="submit-btn">Módosít</button>
            </div>
        </form>
    </section>
    <section class="settings-section">
        <h2>Vásárló e-mail form</h2>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="vasarlosubmodi">Vásárló e-mail tárgya:</label>
                <input type="text" id="vasarlosubmodi" name="vasarlosubmodi" value="<?php echo esc_attr($vasarlosubmail); ?>" class="input-field">
                <button type="submit" name="vasarlosubmailsub" class="submit-btn">Módosít</button>
            </div>
        </form>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="vasarlomailformmodi">Vásárló e-mail tartalma:</label>
                <textarea id="vasarlomailformmodi" name="vasarlomailformmodi" rows="10" class="input-field"><?php echo esc_attr($vasarlosubform); ?></textarea>
                <button type="submit" name="vasarloemailformsub" class="submit-btn">Módosít</button>
            </div>
        </form>
    </section>
    <section class="settings-section">
        <h2>Kupon email form</h2>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="kuponsubmodi">Kupon e-mail tárgya:</label>
                <input type="text" id="kuponsubmodi" name="kuponsubmodi" value="<?php echo esc_attr($kuponmailsubori); ?>" class="input-field">
                <button type="submit" name="kuponsubsub" class="submit-btn">Módosít</button>
            </div>
        </form>
        <form method="post" action="" class="settings-form">
            <div class="form-row">
                <label for="kuponmailmodi">Kupon e-mail tartalma:</label>
                <textarea id="kuponmailmodi" name="kuponmailmodi" rows="10" class="input-field"><?php echo esc_attr($kuponmailformori); ?></textarea>
                <button type="submit" name="kuponmailsubsub" class="submit-btn">Módosít</button>
            </div>
        </form>
    </section>
</div>
<style>
    /* Általános stílusok */
    .wrap {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .settings-header {
        font-size: 2rem;
        color: #333;
        margin-bottom: 20px;
        text-align: center;
    }

    .settings-section {
        margin-bottom: 40px;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .settings-section h2 {
        font-size: 1.5rem;
        color: #333;
        margin-bottom: 15px;
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .form-row label {
        flex: 1;
        font-size: 1rem;
        color: #555;
    }

    .input-field {
        flex: 2;
        padding: 10px;
        font-size: 1rem;
        border-radius: 5px;
        border: 1px solid #ccc;
        margin-right: 10px;
        width: 60%;
    }

    .submit-btn {
        padding: 10px 20px;
        font-size: 1rem;
        color: white;
        background-color: #4CAF50;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .submit-btn:hover {
        background-color: #45a049;
    }

    textarea.input-field {
        width: 60%;
        resize: vertical;
    }
</style>

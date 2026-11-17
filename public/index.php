<?php
declare(strict_types=1);

session_start();

// ========================================
// LOAD KONFIGURASI TEMPLATE
// ========================================

$configPath = __DIR__ . '/../config/templates.php';

// Jika konfigurasi belum dibuat, gunakan
// template lama sebagai konfigurasi default.
if (is_file($configPath)) {
    $templateConfig = require $configPath;
} else {
    $templateConfig = [
        'template_po.docx' => [
            'label' => 'PO BIS SCG',
            'description' => 'Template Purchase Order standar.',
            'fields' => [
                'kapal' => [
                    'label' => 'Nama Kapal',
                    'type' => 'text',
                    'required' => true,
                ],
                'quantity' => [
                    'label' => 'Quantity (Zak)',
                    'type' => 'number',
                    'required' => true,
                ],
                'harga' => [
                    'label' => 'Harga Satuan (Rp)',
                    'type' => 'number',
                    'required' => true,
                ],
            ],
            'placeholders' => [
                'kapal' => 'kapal',
                'quantity' => 'quantity',
                'harga' => 'harga',
            ],
        ],
    ];
}

// Validasi konfigurasi
if (!is_array($templateConfig) || empty($templateConfig)) {
    http_response_code(500);
    exit('Konfigurasi template tidak valid.');
}
?>

<!doctype html>
<html lang="id">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Generator Purchase Order</title>

<style>

:root {
    font-family: Arial, sans-serif;
    color: #202938;
    background: #f3f5f8;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 28px;
}

main {
    max-width: 760px;
    margin: 20px auto;
    background: #fff;
    padding: 28px;
    border-radius: 14px;
    box-shadow: 0 8px 30px #1c2a3a12;
}

h1 {
    font-size: 25px;
    margin: 0 0 8px;
}

p {
    color: #647084;
    line-height: 1.5;
}

.grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin: 15px 0 7px;
}

input,
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd3dd;
    border-radius: 8px;
    font-size: 15px;
    background: #fff;
}

input:focus,
select:focus {
    outline: none;
    border-color: #205da5;
    box-shadow: 0 0 0 3px #205da51a;
}

.template-box {
    background: #f3f6fa;
    padding: 16px;
    border-radius: 10px;
    margin: 20px 0;
}

.template-box label {
    margin-top: 0;
}

.template-info {
    font-size: 13px;
    color: #647084;
    margin-top: 10px;
    line-height: 1.5;
}

.section-title {
    font-size: 16px;
    margin: 24px 0 5px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5eaf0;
}

.template-fields {
    display: none;
    grid-column: 1 / -1;
}

.template-fields.active {
    display: block;
}

.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.total {
    background: #f3f6fa;
    padding: 16px;
    border-radius: 10px;
    margin-top: 20px;
}

.line {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 5px 0;
}

#words {
    font-size: 13px;
    text-align: right;
    max-width: 72%;
}

button {
    margin-top: 20px;
    width: 100%;
    padding: 14px;
    border: 0;
    border-radius: 9px;
    background: #205da5;
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}

button:hover {
    background: #174b88;
}

button:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.note {
    font-size: 13px;
    background: #fff8df;
    padding: 12px;
    border-radius: 8px;
    margin-top: 16px;
    color: #65521c;
}

@media(max-width:560px) {

    body {
        padding: 10px;
    }

    main {
        padding: 19px;
    }

    .grid,
    .field-grid {
        grid-template-columns: 1fr;
    }

    .line {
        align-items: flex-start;
        flex-direction: column;
    }

    #words {
        text-align: left;
        max-width: 100%;
    }

}

</style>

</head>

<body>

<main>

<h1>Generator Purchase Order</h1>

<p>
Isi data PO, pilih template, periksa perhitungan otomatis,
lalu unduh dokumen Word dan PDF.
</p>

<form action="generate.php" method="post" id="poForm">

<!-- ================================== -->
<!-- PILIH TEMPLATE -->
<!-- ================================== -->

<div class="template-box">

    <label for="template">Pilih Template PO</label>

    <select name="template" id="template" required>

        <option value="">-- Pilih Template --</option>

        <?php foreach ($templateConfig as $file => $config): ?>

            <option value="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($config['label'] ?? $file, ENT_QUOTES, 'UTF-8') ?>
            </option>

        <?php endforeach; ?>

    </select>

    <div class="template-info" id="templateInfo">
        Silakan pilih template PO yang ingin digunakan.
    </div>

</div>


<!-- ================================== -->
<!-- DATA UMUM PO -->
<!-- ================================== -->

<h3 class="section-title">Informasi PO</h3>

<div class="grid">

    <div>
        <label for="tanggal">Tanggal PO</label>

        <input
            id="tanggal"
            type="date"
            name="tanggal"
            value="<?= date('Y-m-d') ?>"
            required
        >
    </div>

    <div>
        <label for="nomor_po">Nomor PO</label>

        <input
            id="nomor_po"
            name="nomor_po"
            value="59"
            maxlength="40"
            required
        >
    </div>

    <div>
        <label for="bulan">Bulan (Romawi)</label>

        <input
            id="bulan"
            name="bulan"
            placeholder="Otomatis dari tanggal"
        >
    </div>

    <div>
        <label for="tahun">Tahun (2 digit)</label>

        <input
            id="tahun"
            name="tahun"
            maxlength="2"
            placeholder="Otomatis dari tanggal"
        >
    </div>


    <!-- ================================== -->
    <!-- FIELD DINAMIS TEMPLATE -->
    <!-- ================================== -->

    <?php foreach ($templateConfig as $file => $config): ?>

        <div
            class="template-fields"
            data-template="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
            data-description="<?= htmlspecialchars($config['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >

            <h3 class="section-title">
                <?= htmlspecialchars($config['label'] ?? $file, ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <div class="field-grid">

                <?php foreach ($config['fields'] as $name => $field): ?>

                    <?php
                    $fieldType = $field['type'] ?? 'text';

                    if (!in_array($fieldType, ['text', 'number'], true)) {
                        $fieldType = 'text';
                    }

                    $defaultValue = '';

                    if ($name === 'quantity') {
                        $defaultValue = '5120';
                    } elseif ($name === 'harga') {
                        $defaultValue = '43743';
                    } elseif ($name === 'kapal') {
                        $defaultValue = 'KM. STB 25';
                    }
                    ?>

                    <div>

                        <label for="<?= htmlspecialchars('field_' . $file . '_' . $name, ENT_QUOTES, 'UTF-8') ?>">

                            <?= htmlspecialchars($field['label'] ?? $name, ENT_QUOTES, 'UTF-8') ?>

                        </label>

                        <input
                            type="<?= $fieldType ?>"
                            id="<?= htmlspecialchars('field_' . $file . '_' . $name, ENT_QUOTES, 'UTF-8') ?>"
                            name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                            value="<?= htmlspecialchars($defaultValue, ENT_QUOTES, 'UTF-8') ?>"
                            <?= !empty($field['required']) ? 'required' : '' ?>
                            <?= $fieldType === 'number' ? 'min="0" step="1"' : '' ?>
                            disabled
                        >

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endforeach; ?>

</div>


<!-- ================================== -->
<!-- TOTAL DAN TERBILANG -->
<!-- ================================== -->

<section class="total">

    <div class="line">

        <strong>Sub Total</strong>

        <strong id="subtotal">Rp 0</strong>

    </div>

    <div class="line">

        <strong>Terbilang</strong>

        <span id="words">
            Pilih template terlebih dahulu.
        </span>

    </div>

</section>


<!-- ================================== -->
<!-- TOMBOL GENERATE -->
<!-- ================================== -->

<button type="submit" id="generateButton">
    Generate Word + PDF
</button>

</form>


<div class="note">

Template PO asli digunakan sebagai dasar.
Struktur, susunan tabel, catatan, dan tanda tangan
tidak didesain ulang. Hanya data yang ditentukan
yang diganti sesuai template yang dipilih.

</div>

</main>


<script>

// ========================================
// ELEMENT
// ========================================

const templateSelect = document.getElementById('template');

const templateInfo = document.getElementById('templateInfo');

const sections = document.querySelectorAll('.template-fields');

const subtotal = document.getElementById('subtotal');

const words = document.getElementById('words');

const roman = [
    'I','II','III','IV','V','VI',
    'VII','VIII','IX','X','XI','XII'
];


// ========================================
// PILIH TEMPLATE
// ========================================

function changeTemplate() {

    const selected = templateSelect.value;

    let activeSection = null;

    sections.forEach(section => {

        const active = section.dataset.template === selected;

        section.classList.toggle('active', active);

        section.querySelectorAll('input').forEach(input => {

            input.disabled = !active;

        });

        if (active) {
            activeSection = section;
        }

    });

    templateInfo.textContent = activeSection
        ? activeSection.dataset.description
        : 'Silakan pilih template PO yang ingin digunakan.';

    updateTotal();

}

templateSelect.addEventListener('change', changeTemplate);


// ========================================
// TERBILANG
// ========================================

const ones = [
    '',
    'Satu',
    'Dua',
    'Tiga',
    'Empat',
    'Lima',
    'Enam',
    'Tujuh',
    'Delapan',
    'Sembilan'
];

function terbilang(n) {

    n = Math.floor(Math.abs(n));

    if (n === 0) return 'Nol';

    if (n < 10) return ones[n];

    if (n < 20) {

        return n === 10
            ? 'Sepuluh'
            : n === 11
                ? 'Sebelas'
                : ones[n - 10] + ' Belas';

    }

    if (n < 100) {

        return ones[Math.floor(n / 10)] + ' Puluh' +
            (n % 10 ? ' ' + terbilang(n % 10) : '');

    }

    if (n < 200) {

        return 'Seratus' +
            (n > 100 ? ' ' + terbilang(n - 100) : '');

    }

    if (n < 1000) {

        return ones[Math.floor(n / 100)] + ' Ratus' +
            (n % 100 ? ' ' + terbilang(n % 100) : '');

    }

    if (n < 2000) {

        return 'Seribu' +
            (n > 1000 ? ' ' + terbilang(n - 1000) : '');

    }

    const units = [
        [1e12, 'Triliun'],
        [1e9, 'Miliar'],
        [1e6, 'Juta'],
        [1000, 'Ribu']
    ];

    for (const [value, label] of units) {

        if (n >= value) {

            const q = Math.floor(n / value);

            const r = n % value;

            return terbilang(q) + ' ' + label +
                (r ? ' ' + terbilang(r) : '');

        }

    }

    return String(n);

}


// ========================================
// HITUNG SUBTOTAL
// ========================================

function updateTotal() {

    const activeSection = document.querySelector(
        '.template-fields.active'
    );

    if (!activeSection) {

        subtotal.textContent = 'Rp 0';

        words.textContent = 'Pilih template terlebih dahulu.';

        return;

    }

    const qtyInput = activeSection.querySelector(
        'input[name="quantity"]'
    );

    const priceInput = activeSection.querySelector(
        'input[name="harga"]'
    );

    if (!qtyInput || !priceInput) {

        subtotal.textContent = 'Tidak tersedia';

        words.textContent =
            'Template ini belum memiliki field quantity dan harga.';

        return;

    }

    const q = Number(qtyInput.value);

    const p = Number(priceInput.value);

    if (
        !Number.isSafeInteger(q) ||
        !Number.isSafeInteger(p) ||
        q < 0 ||
        p < 0 ||
        !Number.isSafeInteger(q * p)
    ) {

        subtotal.textContent = 'Periksa nilai input';

        words.textContent = 'Nilai perhitungan terlalu besar atau tidak valid.';

        return;

    }

    const total = q * p;

    subtotal.textContent = 'Rp ' +
        new Intl.NumberFormat('id-ID').format(total);

    words.textContent = terbilang(total) + ' Rupiah';

}


// ========================================
// EVENT INPUT QUANTITY DAN HARGA
// ========================================

document.querySelectorAll(
    'input[name="quantity"], input[name="harga"]'
).forEach(input => {

    input.addEventListener('input', updateTotal);

});


// ========================================
// TANGGAL OTOMATIS
// ========================================

document.getElementById('tanggal').addEventListener('change', e => {

    const d = new Date(e.target.value + 'T12:00:00');

    if (!Number.isNaN(d.getTime())) {

        if (!document.getElementById('bulan').value) {

            document.getElementById('bulan').placeholder =
                roman[d.getMonth()];

        }

        if (!document.getElementById('tahun').value) {

            document.getElementById('tahun').placeholder =
                String(d.getFullYear()).slice(-2);

        }

    }

});


// ========================================
// VALIDASI FORM
// ========================================

document.getElementById('poForm').addEventListener('submit', e => {

    if (!templateSelect.value) {

        e.preventDefault();

        alert('Silakan pilih template PO terlebih dahulu.');

        templateSelect.focus();

        return;

    }

    const activeSection = document.querySelector(
        '.template-fields.active'
    );

    if (!activeSection) {

        e.preventDefault();

        alert('Field template tidak ditemukan.');

        return;

    }

    const qtyInput = activeSection.querySelector(
        'input[name="quantity"]'
    );

    const priceInput = activeSection.querySelector(
        'input[name="harga"]'
    );

    if (qtyInput && priceInput) {

        const q = Number(qtyInput.value);

        const p = Number(priceInput.value);

        if (
            !Number.isSafeInteger(q) ||
            !Number.isSafeInteger(p) ||
            !Number.isSafeInteger(q * p)
        ) {

            e.preventDefault();

            alert('Quantity atau harga tidak valid.');

            return;

        }

    }

});


// ========================================
// INITIALIZE
// ========================================

// Jika belum ada template yang dipilih,
// otomatis pilih template pertama.
if (!templateSelect.value && templateSelect.options.length > 1) {
    templateSelect.selectedIndex = 1;
}

// Aktifkan field sesuai template terpilih.
changeTemplate();

</script>

</body>
</html>

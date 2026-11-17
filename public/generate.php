<?php
declare(strict_types=1);

session_start();

// ==================================================
// 1. KONFIGURASI DAN PATH
// ==================================================

$configPath  = __DIR__ . '/../config/templates.php';
$templateDir = __DIR__ . '/../templates/';

if (!is_file($configPath)) {
    http_response_code(500);
    exit('File konfigurasi config/templates.php tidak ditemukan.');
}

$templates = require $configPath;

if (!is_array($templates) || empty($templates)) {
    http_response_code(500);
    exit('Konfigurasi template tidak valid.');
}

// ==================================================
// 2. FUNGSI BANTUAN
// ==================================================

function failPage(string $message, int $status = 400): void
{
    http_response_code($status);

    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Gagal Membuat PO</title>
    </head>
    <body style="font-family:Arial;padding:30px">
        <h2>Gagal membuat PO</h2>
        <p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>
        <p><a href="index.php">Kembali ke formulir</a></p>
    </body>
    </html>';

    exit;
}

function textLength(string $text): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($text, 'UTF-8')
        : strlen($text);
}

function rupiah(int $number): string
{
    return number_format($number, 0, ',', '.');
}

function xmlText(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}

function terbilang(int $n): string
{
    $ones = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat',
        'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'
    ];

    if ($n === 0) {
        return 'Nol';
    }

    if ($n < 0) {
        return 'Minus ' . terbilang(abs($n));
    }

    if ($n < 10) {
        return $ones[$n];
    }

    if ($n < 20) {
        return $n === 10
            ? 'Sepuluh'
            : ($n === 11
                ? 'Sebelas'
                : $ones[$n - 10] . ' Belas');
    }

    if ($n < 100) {
        return $ones[intdiv($n, 10)] . ' Puluh'
            . ($n % 10 ? ' ' . terbilang($n % 10) : '');
    }

    if ($n < 200) {
        return 'Seratus'
            . ($n > 100 ? ' ' . terbilang($n - 100) : '');
    }

    if ($n < 1000) {
        return $ones[intdiv($n, 100)] . ' Ratus'
            . ($n % 100 ? ' ' . terbilang($n % 100) : '');
    }

    if ($n < 2000) {
        return 'Seribu'
            . ($n > 1000 ? ' ' . terbilang($n - 1000) : '');
    }

    foreach ([
        [1000000000000, 'Triliun'],
        [1000000000, 'Miliar'],
        [1000000, 'Juta'],
        [1000, 'Ribu']
    ] as [$value, $label]) {
        if ($n >= $value) {
            $q = intdiv($n, $value);
            $r = $n % $value;

            return terbilang($q) . ' ' . $label
                . ($r ? ' ' . terbilang($r) : '');
        }
    }

    return (string) $n;
}

function cleanupDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $directory,
            FilesystemIterator::SKIP_DOTS
        ),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            @rmdir($file->getPathname());
        } else {
            @unlink($file->getPathname());
        }
    }

    @rmdir($directory);
}

// ==================================================
// 3. VALIDASI REQUEST DAN EKSTENSI
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!class_exists('ZipArchive')) {
    failPage(
        'Ekstensi ZIP belum aktif. Aktifkan extension=zip di php.ini, lalu restart Apache.',
        500
    );
}

if (!function_exists('exec')) {
    failPage(
        'Fungsi exec() tidak aktif. Periksa pengaturan disable_functions di php.ini.',
        500
    );
}

// ==================================================
// 4. VALIDASI TEMPLATE YANG DIPILIH
// ==================================================

$selectedTemplate = trim(
    (string) ($_POST['template'] ?? '')
);

if (
    $selectedTemplate === ''
    || !isset($templates[$selectedTemplate])
    || basename($selectedTemplate) !== $selectedTemplate
    || strtolower(pathinfo($selectedTemplate, PATHINFO_EXTENSION)) !== 'docx'
) {
    failPage('Pilihan template tidak valid.');
}

$config = $templates[$selectedTemplate];

if (!is_array($config)) {
    failPage('Konfigurasi template tidak valid.', 500);
}

$fields = $config['fields'] ?? [];

if (!is_array($fields)) {
    failPage('Konfigurasi field template tidak valid.', 500);
}

$templatePath = $templateDir . $selectedTemplate;

if (!is_file($templatePath)) {
    failPage(
        'File template tidak ditemukan: ' . $selectedTemplate,
        500
    );
}

// ==================================================
// 5. VALIDASI DATA UMUM PO
// ==================================================

$dateRaw = trim((string) ($_POST['tanggal'] ?? ''));

$date = DateTime::createFromFormat('!Y-m-d', $dateRaw);

if (!$date || $date->format('Y-m-d') !== $dateRaw) {
    failPage('Tanggal tidak valid.');
}

$poNo = trim((string) ($_POST['nomor_po'] ?? ''));

if ($poNo === '' || textLength($poNo) > 40) {
    failPage('Nomor PO wajib diisi dan maksimal 40 karakter.');
}

// ==================================================
// 6. BULAN ROMAWI DAN TAHUN
// ==================================================

$monthMap = [
    'I', 'II', 'III', 'IV', 'V', 'VI',
    'VII', 'VIII', 'IX', 'X', 'XI', 'XII'
];

$month = strtoupper(
    trim((string) ($_POST['bulan'] ?? ''))
);

if ($month === '') {
    $month = $monthMap[(int) $date->format('n') - 1];
}

$year = trim((string) ($_POST['tahun'] ?? ''));

if ($year === '') {
    $year = $date->format('y');
}

if (!in_array($month, $monthMap, true)) {
    failPage('Bulan harus menggunakan angka Romawi I sampai XII.');
}

if (!preg_match('/^\d{2}$/', $year)) {
    failPage('Tahun harus diisi 2 digit, misalnya 26.');
}

// ==================================================
// 7. VALIDASI FIELD SESUAI TEMPLATE
// ==================================================

$formData = [];

foreach ($fields as $fieldName => $field) {

    if (!is_array($field)) {
        failPage(
            'Konfigurasi field ' . $fieldName . ' tidak valid.',
            500
        );
    }

    $value = trim(
        (string) ($_POST[$fieldName] ?? '')
    );

    $fieldLabel = $field['label'] ?? $fieldName;
    $required   = !empty($field['required']);
    $fieldType  = $field['type'] ?? 'text';

    if ($required && $value === '') {
        failPage($fieldLabel . ' wajib diisi.');
    }

    if ($fieldType === 'number') {

        if ($value === '' && !$required) {
            $formData[$fieldName] = '';
            continue;
        }

        $number = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if ($number === false || $number < 0) {
            failPage(
                $fieldLabel . ' harus berupa bilangan bulat non-negatif.'
            );
        }

        $formData[$fieldName] = $number;

    } else {

        if (textLength($value) > 500) {
            failPage($fieldLabel . ' terlalu panjang.');
        }

        $formData[$fieldName] = $value;
    }
}

// ==================================================
// 8. PERHITUNGAN SUBTOTAL
// ==================================================

$hasCalculation =
    array_key_exists('quantity', $formData)
    && array_key_exists('harga', $formData);

$total    = 0;
$quantity = 0;
$harga    = 0;

if ($hasCalculation) {

    $quantity = (int) $formData['quantity'];
    $harga    = (int) $formData['harga'];

    if (
        $quantity !== 0
        && $harga > intdiv(PHP_INT_MAX, $quantity)
    ) {
        failPage('Hasil perkalian quantity dan harga terlalu besar.');
    }

    $total = $quantity * $harga;
}

// ==================================================
// 9. PLACEHOLDER UMUM
// ==================================================

$monthNames = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$dateText = $date->format('j')
    . ' '
    . $monthNames[(int) $date->format('n')]
    . ' '
    . $date->format('Y');

$values = [
    'tanggal'  => $dateText,
    'nomor_po' => $poNo . '/' . $month . '/' . $year . '/',
    'bulan'    => $month,
    'tahun'    => $year
];

// ==================================================
// 10. PLACEHOLDER FIELD TEMPLATE
// ==================================================

foreach ($fields as $fieldName => $field) {

    $value = $formData[$fieldName] ?? '';

    if (
        $fieldName === 'quantity'
        || $fieldName === 'harga'
    ) {
        $value = $value === ''
            ? ''
            : rupiah((int) $value);
    }

    // Nama field otomatis menjadi placeholder.
    // Contoh: kapal menjadi ${kapal}.
    $values[$fieldName] = (string) $value;
}

// ==================================================
// 11. PLACEHOLDER PERHITUNGAN
// ==================================================

if ($hasCalculation) {

    $values['quantity']   = rupiah($quantity);
    $values['harga']      = rupiah($harga);
    $values['subtotal']   = rupiah($total);
    $values['grand_total'] = rupiah($total);
    $values['terbilang']  = terbilang($total) . ' Rupiah';
}

// ==================================================
// 12. MAPPING PLACEHOLDER DARI CONFIG
// ==================================================

// Format config:
// 'nama_placeholder' => 'nama_field_form'

$placeholders = $config['placeholders'] ?? [];

if (!is_array($placeholders)) {
    failPage('Konfigurasi placeholder tidak valid.', 500);
}

foreach ($placeholders as $placeholder => $fieldName) {

    if (!array_key_exists($fieldName, $formData)) {
        failPage(
            'Konfigurasi placeholder tidak valid: ' . $placeholder,
            500
        );
    }

    $value = $formData[$fieldName];

    if (
        $fieldName === 'quantity'
        || $fieldName === 'harga'
    ) {
        $value = $value === ''
            ? ''
            : rupiah((int) $value);
    }

    $values[$placeholder] = (string) $value;
}

// ==================================================
// 13. MEMBUAT FOLDER SEMENTARA
// ==================================================

$tmp = sys_get_temp_dir()
    . DIRECTORY_SEPARATOR
    . 'po_'
    . bin2hex(random_bytes(6));

if (!mkdir($tmp, 0700, true)) {
    failPage('Tidak dapat membuat folder sementara.', 500);
}

register_shutdown_function(
    static function () use ($tmp): void {
        cleanupDirectory($tmp);
    }
);

// ==================================================
// 14. MENYIAPKAN NAMA FILE
// ==================================================

$safePo = preg_replace(
    '/[^A-Za-z0-9_-]+/',
    '_',
    $poNo
) ?: 'PO';

$docxName = 'PO_' . $safePo . '.docx';
$pdfName  = 'PO_' . $safePo . '.pdf';

$docxPath = $tmp . DIRECTORY_SEPARATOR . $docxName;
$pdfPath  = $tmp . DIRECTORY_SEPARATOR . $pdfName;

if (!copy($templatePath, $docxPath)) {
    failPage('Tidak dapat menyalin template Word.', 500);
}

// ==================================================
// 15. MEMBUKA DOCX DAN MENGGANTI PLACEHOLDER
// ==================================================

$docZip = new ZipArchive();

if ($docZip->open($docxPath) !== true) {
    failPage('Template Word tidak dapat dibuka.', 500);
}

$replace = [];

foreach ($values as $key => $value) {
    $replace['${' . $key . '}'] = xmlText((string) $value);
}

// Cari seluruh file XML di dalam folder word.
$xmlFiles = [];

for ($i = 0; $i < $docZip->numFiles; $i++) {

    $name = $docZip->getNameIndex($i);

    if (
        $name !== false
        && strpos($name, 'word/') === 0
        && strtolower(substr($name, -4)) === '.xml'
    ) {
        $xmlFiles[] = $name;
    }
}

// Ganti placeholder pada setiap file XML.
foreach ($xmlFiles as $xmlFile) {

    $xml = $docZip->getFromName($xmlFile);

    if ($xml === false) {
        continue;
    }

    $updatedXml = str_replace(
        array_keys($replace),
        array_values($replace),
        $xml
    );

    if ($updatedXml !== $xml) {

        if (!$docZip->addFromString($xmlFile, $updatedXml)) {

            $docZip->close();

            failPage(
                'Gagal memperbarui isi dokumen: ' . $xmlFile,
                500
            );
        }
    }
}

if (!$docZip->close()) {
    failPage('Gagal menyimpan perubahan dokumen Word.', 500);
}

// ==================================================
// 16. KONVERSI WORD KE PDF
// ==================================================

// Sesuaikan lokasi LibreOffice jika terinstal di folder lain.
$loCandidates = [
    'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
    'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
    'soffice',
    'libreoffice'
];

$converted = false;
$lastOutput = [];

foreach ($loCandidates as $lo) {

    $command = escapeshellarg($lo)
        . ' --headless --convert-to pdf --outdir '
        . escapeshellarg($tmp)
        . ' '
        . escapeshellarg($docxPath)
        . ' 2>&1';

    $output = [];
    $exitCode = 1;

    @exec($command, $output, $exitCode);

    $lastOutput = $output;

    if ($exitCode === 0 && is_file($pdfPath)) {
        $converted = true;
        break;
    }
}

if (!$converted) {

    failPage(
        'File Word berhasil dibuat, tetapi PDF gagal dikonversi. '
        . 'Pastikan LibreOffice sudah terinstal dan dapat dijalankan oleh PHP. '
        . 'Detail: ' . implode(' ', $lastOutput),
        500
    );
}


 // ==================================================
 // 17. DOWNLOAD WORD DAN PDF SECARA TERPISAH
 // ==================================================

if (ob_get_level() > 0) {
    ob_end_clean();
}

// Tampilkan halaman dengan dua link download.
$wordUrl = 'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,'
    . base64_encode(file_get_contents($docxPath));

$pdfUrl = 'data:application/pdf;base64,'
    . base64_encode(file_get_contents($pdfPath));

echo '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Download PO</title>
</head>
<body style="font-family:Arial;text-align:center;padding:40px">

<h2>PO Berhasil Dibuat!</h2>
<p>Silakan download dokumen:</p>

<a id="word" download="' . htmlspecialchars($docxName, ENT_QUOTES, 'UTF-8') . '"
href="' . $wordUrl . '">
Download Word
</a>

<br><br>

<a id="pdf" download="' . htmlspecialchars($pdfName, ENT_QUOTES, 'UTF-8') . '"
href="' . $pdfUrl . '">
Download PDF
</a>

</body>
</html>';

echo '<script>
document.getElementById("word").click();
setTimeout(function() {
    document.getElementById("pdf").click();
}, 1500);
</script>';

exit;


<?php
session_start();
require_once __DIR__ . '/database.php';

// TAHUN LAPORAN
$tahun_pilih = $_REQUEST['tahun'] ?? date('Y');

// FORMAT EXPORT
$format = $_GET['format'] ?? '';
$act = $_GET['act'] ?? ($_GET['export'] ?? $format);

// NAMA BULAN INDONESIA
$nama_bulan_indo = [
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

// ARRAY PENDAPATAN
$pendapatan_bulanan = array_fill(1, 12, 0);

// QUERY PENDAPATAN BULANAN
$query = "SELECT MONTH(b.dibuat_pada) AS bulan,
          SUM(k.harga) AS total
          FROM booking b
          JOIN kamar k ON b.kamar_id = k.kamar_id
          WHERE b.status_booking IN ('Disetujui', 'Terisi')
          AND YEAR(b.dibuat_pada) = ?
          GROUP BY MONTH(b.dibuat_pada)";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Query error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $tahun_pilih);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $pendapatan_bulanan[(int)$row['bulan']] = (float)$row['total'];
}

mysqli_stmt_close($stmt);

// TOTAL PENDAPATAN
$total_pendapatan = array_sum($pendapatan_bulanan);


/*
==================================================
MODE EXCEL
==================================================
*/

if ($act === 'excel') {

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Laporan_Pendapatan_" . $tahun_pilih . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">

        <style>

/* =========================================
   TEMA SOFT BABY PINK PASTEL
   HALAMAN LAPORAN PENDAPATAN
========================================= */

:root {
    --pink: #E8A1B8;
    --pink-dark: #D985A3;
    --pink-light: #FCEAF0;
    --pink-bg: #FFF7FA;
    --pink-border: #F5DCE5;
    --text: #51424A;
    --muted: #927F87;
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Source Sans Pro', Arial, sans-serif;
    margin: 0 auto;
    padding: 20px;
    max-width: 800px;
    color: var(--text);
    line-height: 1.5;
    background: var(--pink-bg);
}

/* BAR TOMBOL */
.action-bar {
    background: var(--pink-light);
    border: 1px solid var(--pink-border);
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

/* TOMBOL */
.btn-action {
    padding: 9px 16px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    color: white;
    display: inline-block;
    transition: 0.3s;
}

/* TOMBOL PDF */
.btn-pdf {
    background-color: var(--pink);
}

.btn-pdf:hover {
    background-color: var(--pink-dark);
    color: white;
}

/* TOMBOL EXCEL */
.btn-excel {
    background-color: var(--pink-dark);
}

.btn-excel:hover {
    background-color: #C67594;
    color: white;
}

/* DOKUMEN */
#dokumen-cetak {
    background: #FFFFFF;
    padding: 25px;
    border: 1px solid var(--pink-border);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(217, 133, 163, 0.10);
}

/* JUDUL */
.header {
    text-align: center;
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 10px;
    line-height: 1.3;
    color: var(--pink-dark);
}

/* KODE LAPORAN */
.kode {
    text-align: center;
    font-weight: 600;
    font-size: 14px;
    color: var(--pink);
    margin-bottom: 25px;
}

/* TABEL */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0 25px 0;
    background: white;
}

table,
th,
td {
    border: 1px solid var(--pink-border);
}

th,
td {
    padding: 12px 15px;
    font-size: 14px;
    vertical-align: middle;
}

/* HEADER TABEL */
th {
    font-weight: 600;
    background-color: var(--pink-light);
    color: var(--pink-dark);
    text-align: center;
}

/* ISI TABEL */
td {
    color: var(--text);
}

tbody tr:nth-child(even) {
    background-color: var(--pink-bg);
}

/* TOTAL */
.total-row {
    font-weight: 700;
    background-color: var(--pink-light) !important;
    color: var(--pink-dark);
}

.total-row td {
    color: var(--pink-dark);
}

/* POSISI TEKS */
.text-center {
    text-align: center;
}

.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

/* RESPONSIF */
@media (max-width: 600px) {
    body {
        padding: 10px;
    }

    .action-bar {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    .action-bar > div {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    #dokumen-cetak {
        padding: 12px;
        overflow-x: auto;
    }

    th,
    td {
        padding: 8px;
        font-size: 12px;
    }
}

/* TAMPILAN CETAK PDF */
@media print {
    body {
        background: white;
    }

    #dokumen-cetak {
        box-shadow: none;
        border: 1px solid var(--pink-border);
    }
}

        </style>
    </head>

    <body>

        <table class="tb-data">

            <tr>
                <td class="no-border" style="width:50px;"></td>
                <td class="no-border" style="width:50px;"></td>
                <td class="no-border" style="width:50px;"></td>
                <td class="no-border" style="width:50px;"></td>

                <td colspan="3" class="header-title">
                    LAPORAN PENDAPATAN
                </td>
            </tr>

            <tr>
                <td class="no-border" colspan="4"></td>

                <td colspan="3" class="header-code">
                    PENDAPATAN/<?= htmlspecialchars($tahun_pilih); ?>
                </td>
            </tr>

            <tr>
                <td colspan="7" class="no-border" style="height:15pt;"></td>
            </tr>

            <thead>
                <tr style="height:32pt;">

                    <td class="no-border" colspan="4"></td>

                    <th style="width:80px;">No.</th>
                    <th style="width:220px;">Bulan</th>
                    <th style="width:250px;">Nominal Pendapatan</th>

                </tr>
            </thead>

            <tbody>

                <?php foreach ($nama_bulan_indo as $num => $nama): ?>

                    <tr style="height:28pt;">

                        <td class="no-border" colspan="4"></td>

                        <td class="text-center">
                            <?= $num; ?>.
                        </td>

                        <td class="text-left">
                            <?= $nama; ?>
                        </td>

                        <td class="text-right">
                            Rp <?= number_format($pendapatan_bulanan[$num], 0, ',', '.'); ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

            <tfoot>

                <tr class="total-row">

                    <td class="no-border" colspan="4"></td>

                    <td colspan="2" class="text-center">
                        TOTAL PENDAPATAN:
                    </td>

                    <td class="text-right">
                        Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?>
                    </td>

                </tr>

            </tfoot>

        </table>

    </body>
    </html>

    <?php
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Laporan Pendapatan Bulanan - PENDAPATAN/<?= htmlspecialchars($tahun_pilih); ?>
    </title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>

/* ===== TEMA SOFT PINK PASTEL LAPORAN ===== */

* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    margin: 0 auto;
    padding: 20px;
    max-width: 800px;
    color: #75465B;
    line-height: 1.5;
    background: #FFF5F8;
}

/* BAR TOMBOL */
.action-bar {
    background: #FCE4EC;
    border: 1px solid #F8D5E1;
    padding: 12px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* TOMBOL */
.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: bold;
    text-decoration: none;
    border: none;
    color: white;
    display: inline-block;
    transition: 0.3s;
}

/* TOMBOL PDF */
.btn-pdf {
    background-color: #F48FB1;
}

.btn-pdf:hover {
    background-color: #E879A2;
    color: white;
}

/* TOMBOL EXCEL */
.btn-excel {
    background-color: #E879A2;
}

.btn-excel:hover {
    background-color: #D96F99;
    color: white;
}

/* DOKUMEN */
#dokumen-cetak {
    background: #FFFFFF;
    padding: 25px;
    border: 1px solid #F8D5E1;
    border-radius: 10px;
}

/* JUDUL */
.header {
    text-align: center;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 10px;
    line-height: 1.3;
    color: #AD5275;
}

/* KODE LAPORAN */
.kode {
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    color: #E879A2;
    margin-bottom: 25px;
}

/* TABEL */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0 25px 0;
}

table,
th,
td {
    border: 1px solid #F8D5E1;
}

th,
td {
    padding: 12px 15px;
    font-size: 14px;
    vertical-align: middle;
}

/* HEADER TABEL */
th {
    font-weight: bold;
    background-color: #FCE4EC;
    color: #AD5275;
    text-align: center;
}

/* ISI TABEL */
td {
    color: #75465B;
}

tbody tr:nth-child(even) {
    background-color: #FFF5F8;
}

/* TOTAL */
.total-row {
    font-weight: bold;
    background-color: #FCE4EC !important;
    color: #AD5275;
}

.total-row td {
    color: #AD5275;
}

/* POSISI TEKS */
.text-center {
    text-align: center;
}

.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

/* RESPONSIF */
@media (max-width: 600px) {

    body {
        padding: 10px;
    }

    .action-bar {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    #dokumen-cetak {
        padding: 12px;
        overflow-x: auto;
    }

    th,
    td {
        padding: 8px;
        font-size: 12px;
    }

}

    </style>

</head>

<body>

    <!-- TOMBOL DOWNLOAD -->

    <div class="action-bar">

        <span style="font-size:14px; color:#75465B;">
            Pilih Unduhan Laporan Pendapatan:
        </span>

        <div>

            <button onclick="downloadPDF()" class="btn-action btn-pdf">
                Download PDF
            </button>

            <a href="cetak_laporan.php?tahun=<?= urlencode($tahun_pilih); ?>&act=excel"
               class="btn-action btn-excel">
                Download Excel
            </a>

        </div>

    </div>


    <!-- DOKUMEN LAPORAN -->

    <div id="dokumen-cetak">

        <div class="header">
            LAPORAN PENDAPATAN
        </div>

        <div class="kode">
            PENDAPATAN/<?= htmlspecialchars($tahun_pilih); ?>
        </div>

        <table>

            <thead>

                <tr>

                    <th width="10%">No.</th>
                    <th width="45%">Bulan</th>
                    <th width="45%">Nominal Pendapatan</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($nama_bulan_indo as $num => $nama): ?>

                    <tr>

                        <td class="text-center">
                            <?= $num; ?>.
                        </td>

                        <td class="text-left">
                            <?= $nama; ?>
                        </td>

                        <td class="text-right">
                            Rp <?= number_format($pendapatan_bulanan[$num], 0, ',', '.'); ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

            <tfoot>

                <tr class="total-row">

                    <td colspan="2" class="text-center">
                        TOTAL PENDAPATAN:
                    </td>

                    <td class="text-right">
                        Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?>
                    </td>

                </tr>

            </tfoot>

        </table>

    </div>


    <!-- JAVASCRIPT DOWNLOAD PDF -->

    <script>

        function downloadPDF() {

            const element = document.getElementById('dokumen-cetak');

            const opt = {

                margin: 10,

                filename: 'Laporan_Pendapatan_<?= htmlspecialchars($tahun_pilih); ?>.pdf',

                image: {
                    type: 'jpeg',
                    quality: 0.98
                },

                html2canvas: {
                    scale: 2,
                    backgroundColor: '#ffffff'
                },

                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }

            };

            html2pdf()
                .set(opt)
                .from(element)
                .save();

        }

    </script>

</body>
</html>


<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

$booking_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$format = $_GET['format'] ?? '';
$act = $_GET['act'] ?? $format;

if ($booking_id <= 0) {
    echo "<script>alert('ID booking tidak valid!'); window.close();</script>";
    exit;
}

// QUERY DATA BOOKING
$sql = "
    SELECT
        b.booking_id,
        b.tanggal_survey,
        COALESCE(b.status_booking, 'Menunggu') AS status_booking,
        km.no_kamar,
        km.tipe_kamar,
        km.harga,
        k.nama_kos,
        k.alamat,
        u_pencari.nama AS nama_pencari,
        u_owner.nama AS nama_owner
    FROM booking b
    JOIN kamar km ON b.kamar_id = km.kamar_id
    JOIN kos k ON km.kos_id = k.kos_id
    JOIN user u_pencari ON b.user_id = u_pencari.user_id
    LEFT JOIN user u_owner ON k.owner_id = u_owner.user_id
    WHERE b.booking_id = ?
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// VALIDASI BOOKING
$st = strtolower(trim($data['status_booking'] ?? ''));

$is_approved = in_array(
    $st,
    ['disetujui', 'approved', 'selesai'],
    true
);

if (!$data || !$is_approved) {
    echo "<script>
        alert('Bukti booking tidak dapat dicetak!');
        window.close();
    </script>";
    exit;
}

// NOMOR BUKTI BOOKING
$kode_bukti = 'KOS/BKG-01/BOOK00' . $data['booking_id'];

// TANGGAL INDONESIA
function tanggal_indo($tanggal) {
    $bulan = [
        1 => 'JANUARI',
        'FEBRUARI',
        'MARET',
        'APRIL',
        'MEI',
        'JUNI',
        'JULI',
        'AGUSTUS',
        'SEPTEMBER',
        'OKTOBER',
        'NOVEMBER',
        'DESEMBER'
    ];

    $var = explode('-', $tanggal);

    return [
        'tgl' => $var[2] ?? date('d'),
        'bln' => $bulan[(int)($var[1] ?? date('m'))],
        'thn' => $var[0] ?? date('Y')
    ];
}

$tgl_booking = tanggal_indo(
    date('Y-m-d', strtotime($data['tanggal_survey']))
);

/*
|--------------------------------------------------------------------------
| MODE EXCEL
|--------------------------------------------------------------------------
*/

if ($act === 'excel') {

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Bukti Booking " . $data['booking_id'] . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #51424A;
            background: #FFFFFF;
        }

        .header-title {
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
            color: #D985A3;
        }

        .header-code {
            font-weight: bold;
            font-size: 11pt;
            text-align: right;
            color: #51424A;
            background-color: #FCEAF0;
        }

        .isi-text {
            font-size: 11pt;
            text-align: justify;
            color: #51424A;
        }

        table.tb-data {
            border-collapse: collapse;
            width: 100%;
            margin: auto;
        }

        table.tb-data th,
        table.tb-data td {
            border: 1px solid #F5DCE5;
            padding: 6px;
            font-size: 11pt;
            color: #51424A;
        }

        table.tb-data th {
            background-color: #FCEAF0;
            color: #D985A3;
            font-weight: bold;
            text-align: center;
        }

        .ttd-title {
            font-weight: bold;
            text-align: center;
            font-size: 11pt;
            color: #D985A3;
        }

        .ttd-nama {
            text-align: center;
            font-size: 11pt;
            color: #51424A;
        }
    </style>
</head>

<body>

    <table style="width: 100%;">

        <tr>
            <td colspan="6" align="center" class="header-title">
                BUKTI KONFIRMASI BOOKING<br>
                SURVEY/SEWA KOS
            </td>
        </tr>

        <tr>
            <td colspan="6" align="right" class="header-code">
                <?= htmlspecialchars($kode_bukti); ?>
            </td>
        </tr>

        <tr><td colspan="6"></td></tr>

        <tr>
            <td colspan="6" class="isi-text">
                Pada hari ini tanggal <?= $tgl_booking['tgl']; ?>
                bulan <?= $tgl_booking['bln']; ?>
                tahun <?= $tgl_booking['thn']; ?>,
                telah dilakukan konfirmasi booking antara pihak
                <b>PEMILIK KOS</b> dengan <b>PENCARI KOS</b>
                melalui Aplikasi Pencarian dan Pemesanan Kos Berbasis Peta.
            </td>
        </tr>

        <tr><td colspan="6"></td></tr>

        <tr>
            <td colspan="6">
                Adapun rincian booking sebagai berikut :
            </td>
        </tr>

        <tr><td colspan="6"></td></tr>

    </table>

    <table class="tb-data">
        <thead>
            <tr>
                <th width="8%">No.</th>
                <th width="42%" colspan="2">Nama Kos</th>
                <th width="32%" colspan="2">Kamar</th>
                <th width="18%">Status</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td align="center">1.</td>

                <td colspan="2">
                    <?= htmlspecialchars($data['nama_kos']); ?>
                </td>

                <td colspan="2">
                    No. <?= htmlspecialchars($data['no_kamar'] ?? '-'); ?>
                    - Tipe <?= htmlspecialchars($data['tipe_kamar'] ?? '-'); ?>
                </td>

                <td align="center">
                    <?= htmlspecialchars($data['status_booking']); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%;">

        <tr><td colspan="6"></td></tr>

        <tr>
            <td colspan="6" class="isi-text">
                Demikian bukti konfirmasi booking ini dibuat dengan
                sebenar-benarnya dan berlaku sebagai bukti sah
                persetujuan kedua belah pihak.
            </td>
        </tr>

        <tr><td colspan="6"></td></tr>
        <tr><td colspan="6"></td></tr>

        <tr>
            <td colspan="3" align="center" class="ttd-title">
                Pemilik Kos
            </td>

            <td colspan="3" align="center" class="ttd-title">
                Pencari Kos
            </td>
        </tr>

        <tr>
            <td colspan="6" style="height: 35px;"></td>
        </tr>

        <tr>
            <td colspan="3" align="center" class="ttd-nama">
                Nama: <?= htmlspecialchars($data['nama_owner'] ?? 'Admin Utama'); ?>
            </td>

            <td colspan="3" align="center" class="ttd-nama">
                Nama: <?= htmlspecialchars($data['nama_pencari']); ?>
            </td>
        </tr>

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

    <title>
        Bukti Konfirmasi Booking - <?= htmlspecialchars($kode_bukti); ?>
    </title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>

        :root {
            --pink: #E8A1B8;
            --pink-dark: #D985A3;
            --pink-light: #FCEAF0;
            --pink-bg: #FFF7FA;
            --pink-border: #F5DCE5;
            --text: #51424A;
            --muted: #927F87;
            --white: #FFFFFF;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0 auto;
            padding: 20px;
            max-width: 800px;
            color: var(--text);
            line-height: 1.5;
            background: var(--pink-bg);
        }

        /* TOMBOL UNDUHAN */

        .action-bar {
            background: var(--white);
            border: 1px solid var(--pink-border);
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            box-shadow: 0 3px 12px rgba(217, 133, 163, 0.08);
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            text-decoration: none;
            border: none;
            color: var(--white);
            display: inline-block;
            transition: 0.3s;
        }

        .btn-pdf {
            background-color: var(--pink);
        }

        .btn-pdf:hover {
            background-color: var(--pink-dark);
        }

        .btn-excel {
            background-color: var(--pink-dark);
        }

        .btn-excel:hover {
            background-color: #C67594;
        }

        /* DOKUMEN CETAK */

        #dokumen-cetak {
            position: relative;
            background: var(--white);
            padding: 30px;
            padding-top: 85px;
            border: 1px solid var(--pink-border);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(217, 133, 163, 0.07);
        }

        /* JUDUL */

        .header {
            display: block;
            width: 100%;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
            line-height: 1.3;
            color: var(--pink-dark);
        }

        /* NOMOR BOOKING POJOK KANAN ATAS */

        .kode {
            position: absolute;
            top: 20px;
            right: 30px;
            margin: 0;
            text-align: right;
            z-index: 2;
        }

        .kode span {
            display: inline-block;
            background: var(--pink-light);
            border: 1px solid var(--pink-border);
            border-radius: 10px;
            padding: 10px 15px;
            min-width: 198px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
            color: var(--text);
        }

        /* ISI DOKUMEN */

        .isi {
            text-align: justify;
            font-size: 14px;
            margin-bottom: 15px;
            color: var(--text);
        }

        /* TABEL BOOKING */

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 25px 0;
        }

        table,
        th,
        td {
            border: 1px solid var(--pink-border);
        }

        th,
        td {
            padding: 8px 10px;
            text-align: left;
            font-size: 14px;
            color: var(--text);
        }

        th {
            font-weight: bold;
            background: var(--pink-light);
            color: var(--pink-dark);
        }

        /* TANDA TANGAN */

        .ttd-container {
            width: 100%;
            margin-top: 40px;
        }

        .ttd-box {
            width: 40%;
            text-align: center;
            float: left;
            font-size: 14px;
        }

        .ttd-box-right {
            width: 40%;
            text-align: center;
            float: right;
            font-size: 14px;
        }

        .space {
            height: 35px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* RESPONSIVE */

        @media (max-width: 600px) {

            body {
                padding: 12px;
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .action-bar > div {
                display: flex;
                gap: 8px;
            }

            .btn-action {
                flex: 1;
                text-align: center;
            }

            #dokumen-cetak {
                padding: 15px;
                padding-top: 85px;
                overflow-x: auto;
            }

            th,
            td {
                font-size: 11px;
                padding: 6px;
            }

            .ttd-box,
            .ttd-box-right {
                width: 48%;
                font-size: 11px;
            }

            .kode {
                top: 15px;
                right: 15px;
            }

            .kode span {
                min-width: 175px;
                font-size: 12px;
            }
        }

    </style>

</head>

<body>

    <!-- TOMBOL DOWNLOAD -->

    <div class="action-bar">

        <span style="font-size: 14px; color: #927F87;">
            Pilih Unduhan Bukti Booking:
        </span>

        <div>

            <button onclick="downloadPDF()" class="btn-action btn-pdf">
                <strong>Download PDF</strong>
            </button>

            <a href="cetak_booking.php?id=<?= $data['booking_id']; ?>&act=excel"
               class="btn-action btn-excel">
                <strong>Download Excel</strong>
            </a>

        </div>

    </div>


    <!-- DOKUMEN BUKTI BOOKING -->

    <div id="dokumen-cetak">

        <!-- NOMOR BOOKING PALING ATAS KANAN -->

        <div class="kode">
            <span>
                <?= htmlspecialchars($kode_bukti); ?>
            </span>
        </div>

        <!-- JUDUL DI TENGAH -->

        <div class="header">
            BUKTI KONFIRMASI BOOKING<br>
            SURVEY/SEWA KOS
        </div>

        <!-- ISI BUKTI BOOKING -->

        <div class="isi">

            Pada hari ini tanggal <?= $tgl_booking['tgl']; ?>
            bulan <?= $tgl_booking['bln']; ?>
            tahun <?= $tgl_booking['thn']; ?>,
            telah dilakukan konfirmasi booking antara pihak
            <b>PEMILIK KOS</b> dengan <b>PENCARI KOS</b>
            melalui Aplikasi Pencarian dan Pemesanan Kos Berbasis Peta.

        </div>

        <p style="font-size: 14px; margin-bottom: 10px;">
            Adapun rincian booking sebagai berikut :
        </p>

        <!-- TABEL BOOKING -->

        <table>

            <thead>
                <tr>
                    <th width="8%">No.</th>
                    <th>Nama Kos</th>
                    <th>Kamar</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>

            <tbody>

                <tr>

                    <td style="text-align: center;">
                        1.
                    </td>

                    <td>
                        <?= htmlspecialchars($data['nama_kos']); ?>
                    </td>

                    <td>
                        No. <?= htmlspecialchars($data['no_kamar'] ?? '-'); ?>
                        - Tipe <?= htmlspecialchars($data['tipe_kamar'] ?? '-'); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($data['status_booking']); ?>
                    </td>

                </tr>

            </tbody>

        </table>

        <!-- KETERANGAN -->

        <div class="isi">

            Demikian bukti konfirmasi booking ini dibuat dengan
            sebenar-benarnya dan berlaku sebagai bukti sah
            persetujuan kedua belah pihak.

        </div>

        <!-- TANDA TANGAN -->

        <div class="ttd-container clearfix">

            <div class="ttd-box">

                <b>Pemilik Kos</b>

                <div class="space"></div>

                Nama: <?= htmlspecialchars($data['nama_owner'] ?? 'Admin Utama'); ?>

            </div>

            <div class="ttd-box-right">

                <b>Pencari Kos</b>

                <div class="space"></div>

                Nama: <?= htmlspecialchars($data['nama_pencari'] ?? 'Pencari Kos'); ?>

            </div>

        </div>

    </div>


    <!-- DOWNLOAD PDF -->

    <script>

        function downloadPDF() {

            const element = document.getElementById('dokumen-cetak');

            const opt = {

                margin: 10,

                filename: 'Bukti Booking <?= $data['booking_id']; ?>.pdf',

                image: {
                    type: 'jpeg',
                    quality: 0.98
                },

                html2canvas: {
                    scale: 2
                },

                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }

            };

            html2pdf().set(opt).from(element).save();

        }

    </script>

</body>
</html>

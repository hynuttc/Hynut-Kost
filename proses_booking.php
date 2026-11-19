
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'database.php';

// ========================================
// KONEKSI DATABASE
// ========================================
if (!isset($conn) || !($conn instanceof mysqli)) {
    if (isset($db) && $db instanceof mysqli) {
        $conn = $db;
    } elseif (isset($koneksi) && $koneksi instanceof mysqli) {
        $conn = $koneksi;
    }
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database gagal. Periksa database.php");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ========================================
// WAJIB LOGIN
// ========================================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ========================================
// CEK METODE REQUEST
// ========================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// ========================================
// AMBIL DATA FORM
// ========================================
$user_id = (int)$_SESSION['user_id'];
$kos_id = (int)($_POST['kos_id'] ?? 0);
$tanggal_survey = trim($_POST['tanggal_survey'] ?? '');

// ========================================
// VALIDASI DATA
// ========================================
$date = DateTime::createFromFormat('!Y-m-d', $tanggal_survey);

if (
    $kos_id <= 0 ||
    !$date ||
    $date->format('Y-m-d') !== $tanggal_survey ||
    $tanggal_survey < date('Y-m-d')
) {
    echo "<script>
        alert('Data booking tidak valid!');
        window.history.back();
    </script>";
    exit;
}

// ========================================
// PROSES BOOKING
// ========================================
try {

    // MULAI TRANSAKSI
    $conn->begin_transaction();

    // 1. CARI KAMAR KOSONG
    $stmt = $conn->prepare("
        SELECT kamar_id
        FROM kamar
        WHERE kos_id = ?
        AND status_kamar = 'Kosong'
        ORDER BY kamar_id ASC
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->bind_param("i", $kos_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $kamar = $result->fetch_assoc();

    $stmt->close();

    // 2. JIKA KAMAR TIDAK TERSEDIA
    if (!$kamar) {

        $conn->rollback();

        echo "<script>
            alert('Maaf, semua kamar sudah terisi atau sedang dalam booking.');
            window.location.href = 'detail.php?id=$kos_id';
        </script>";
        exit;
    }

    $kamar_id = (int)$kamar['kamar_id'];

    // 3. UBAH STATUS KAMAR
    $stmt = $conn->prepare("
        UPDATE kamar
        SET status_kamar = 'Dalam Booking'
        WHERE kamar_id = ?
        AND status_kamar = 'Kosong'
    ");

    $stmt->bind_param("i", $kamar_id);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new Exception("Kamar sudah tidak tersedia.");
    }

    $stmt->close();

    // 4. SIMPAN BOOKING
    $status_booking = 'Menunggu';

    $stmt = $conn->prepare("
        INSERT INTO booking
        (
            kamar_id,
            user_id,
            tanggal_survey,
            status_booking,
            dibuat_pada
        )
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
        "iiss",
        $kamar_id,
        $user_id,
        $tanggal_survey,
        $status_booking
    );

    $stmt->execute();
    $stmt->close();

    // 5. SIMPAN TRANSAKSI
    $conn->commit();

    echo "<script>
        alert('Booking berhasil diajukan! Silakan tunggu konfirmasi Admin.');
        window.location.href = 'booking.php';
    </script>";
    exit;

} catch (Throwable $e) {

    // BATALKAN TRANSAKSI JIKA GAGAL
    try {
        $conn->rollback();
    } catch (Throwable $rollbackError) {
        // Tidak perlu menampilkan error rollback.
    }

    error_log("Kesalahan Booking: " . $e->getMessage());

    echo "<script>
        alert('Booking gagal diproses. Silakan coba kembali.');
        window.history.back();
    </script>";
    exit;
}
?>

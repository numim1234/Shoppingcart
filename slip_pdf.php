<?php
// slip_pdf.php - generate slip PDF server-side using Dompdf
session_start();
require_once __DIR__ . '/condb.php';

// helper: ensure a literal string is UTF-8 (try common Thai encodings if needed)
function to_utf8($s)
{
    if ($s === null) return $s;
    if (mb_check_encoding($s, 'UTF-8')) return $s;
    $encs = ['TIS-620', 'Windows-874', 'ISO-8859-11', 'CP874', 'ISO-8859-1'];
    foreach ($encs as $e) {
        $c = @mb_convert_encoding($s, 'UTF-8', $e);
        if ($c !== false && mb_check_encoding($c, 'UTF-8')) return $c;
    }
    return $s;
}

$slip_id = isset($_GET['slip_id']) ? (int)$_GET['slip_id'] : 0;
if ($slip_id <= 0) {
    http_response_code(400);
    echo 'Invalid slip id';
    exit;
}

try {
    $stmt = $conn->prepare("SELECT slip_id, member_id, payer_name, payer_phone, pay_amount, pay_datetime, slip_image, note, status, created_at FROM tbl_payment_slip WHERE slip_id = ? LIMIT 1");
    $stmt->execute([$slip_id]);
    $slip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$slip) {
        http_response_code(404);
        echo 'ไม่พบสลิป';
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    exit;
}

// Check for composer autoload (Dompdf)
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Dompdf not installed. Run in project root:\ncomposer require dompdf/dompdf\n";
    exit;
}

require $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

// prepare image data
$imgData = '';
if (!empty($slip['slip_image'])) {
    $si = trim($slip['slip_image']);
    if (preg_match('/^https?:\/\//i', $si)) {
        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $content = @file_get_contents($si, false, $ctx);
        if ($content !== false) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($content) ?: 'image/png';
            $imgData = 'data:' . $mime . ';base64,' . base64_encode($content);
        }
    } else {
        $path = '';
        if (preg_match('/^uploads\//i', $si)) {
            $path = __DIR__ . '/' . ltrim($si, '/');
        } else {
            $path = __DIR__ . '/uploads/slips/' . $si;
        }
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($content) ?: 'image/png';
            $imgData = 'data:' . $mime . ';base64,' . base64_encode($content);
        }
    }
}

// build HTML for PDF
// attempt to load a Thai-capable font from assets/fonts/ (developer should place a Thai TTF there, e.g. NotoSansThai-Regular.ttf or THSarabunNew.ttf)
$fontCss = '';
$fontFiles = [
    __DIR__ . '/assets/fonts/NotoSansThai-Regular.ttf',
    __DIR__ . '/assets/fonts/NotoSansThai-Bold.ttf',
    __DIR__ . '/assets/fonts/THSarabunNew.ttf',
    __DIR__ . '/assets/fonts/thsarabun/THSarabunNew.ttf'
];
// build CSS for regular + bold if available
$regular = __DIR__ . '/assets/fonts/NotoSansThai-Regular.ttf';
$bold = __DIR__ . '/assets/fonts/NotoSansThai-Bold.ttf';
$fontCssParts = [];
if (file_exists($regular)) {
    $data = base64_encode(file_get_contents($regular));
    $fontCssParts[] = "@font-face{font-family: 'SiteFont'; src: url('data:font/ttf;base64,{$data}') format('truetype'); font-weight: 400; font-style: normal;}";
}
if (file_exists($bold)) {
    $data = base64_encode(file_get_contents($bold));
    $fontCssParts[] = "@font-face{font-family: 'SiteFont'; src: url('data:font/ttf;base64,{$data}') format('truetype'); font-weight: 700; font-style: normal;}";
}
if (empty($fontCssParts)) {
    // fallback: try any of the fontFiles list
    foreach ($fontFiles as $ff) {
        if (file_exists($ff)) {
            $data = base64_encode(file_get_contents($ff));
            $fontCssParts[] = "@font-face{font-family: 'SiteFont'; src: url('data:font/ttf;base64,{$data}') format('truetype'); font-weight: 400; font-style: normal;}";
            break;
        }
    }
}
$fontCss = implode("\n", $fontCssParts) . "\nbody{font-family: 'SiteFont', DejaVu Sans, Arial, Helvetica, sans-serif; }";

$html = '<!doctype html><html><head><meta charset="utf-8"><style>' .
    ($fontCss ?: '') .
    'body{font-size:14px; color:#111} ' .
    '.container{width:100%;max-width:800px;margin:0 auto;padding:10px} ' .
    '.meta{margin-bottom:12px} .meta div{margin-bottom:6px} .slip-img{width:100%;max-height:1000px;object-fit:contain;border:1px solid #ddd;padding:6px;background:#fff}' .
    '</style></head><body>';

$html .= '<div class="container">';
$html .= '<h2>' . to_utf8('สลิปการชำระเงิน') . ' #' . htmlspecialchars($slip['slip_id']) . '</h2>';
$html .= '<div class="meta">';
$html .= '<div><strong>' . to_utf8('ชื่อผู้โอน:') . '</strong> ' . htmlspecialchars($slip['payer_name'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('เบอร์โทร:') . '</strong> ' . htmlspecialchars($slip['payer_phone'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('ยอดโอน:') . '</strong> ' . number_format((float)($slip['pay_amount'] ?? 0), 2) . ' ' . to_utf8('บาท') . '</div>';
$html .= '<div><strong>' . to_utf8('วันเวลาที่โอน:') . '</strong> ' . htmlspecialchars($slip['pay_datetime'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('หมายเหตุ:') . '</strong> ' . htmlspecialchars($slip['note'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('สถานะ:') . '</strong> ' . htmlspecialchars($slip['status'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('วันที่บันทึก:') . '</strong> ' . htmlspecialchars($slip['created_at'] ?? '-') . '</div>';
$html .= '</div>';

// Load items: if slip note references a reservation use reservation details, otherwise use sale details linked to slip_id
$itemsHtml = '';
$items = [];
$itemsTotal = 0.0;
try {
    if (!empty($slip['note']) && preg_match('/reserve_id\s*:\s*(\d+)/i', $slip['note'], $m)) {
        $reserveId = (int)$m[1];
        $stmtItems = $conn->prepare("SELECT rd.qty, rd.price, rd.subtotal, p.p_name FROM tbl_reservation_detail rd LEFT JOIN tbl_product p ON rd.p_id = p.p_id WHERE rd.reserve_id = ? ORDER BY rd.rd_id ASC");
        $stmtItems->execute([$reserveId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // try sale details for this slip
        $stmtItems = $conn->prepare("SELECT sd.qty, sd.price, sd.subtotal, p.p_name FROM tbl_sale_detail sd LEFT JOIN tbl_product p ON sd.p_id = p.p_id WHERE sd.slip_id = ? ORDER BY sd.sd_id ASC");
        $stmtItems->execute([$slip_id]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $items = [];
}

if (!empty($items)) {
    $itemsHtml .= '<h3>' . to_utf8('รายการสินค้า') . '</h3>';
    $itemsHtml .= '<table width="100%" border="0" cellspacing="0" cellpadding="6" style="border-collapse:collapse">';
    $itemsHtml .= '<thead><tr style="background:#f2f2f2"><th style="text-align:left">' . to_utf8('สินค้า') . '</th><th style="text-align:center">' . to_utf8('จำนวน') . '</th><th style="text-align:right">' . to_utf8('ราคา/ชิ้น') . '</th><th style="text-align:right">' . to_utf8('รวม') . '</th></tr></thead><tbody>';
    foreach ($items as $it) {
        $name = htmlspecialchars(to_utf8($it['p_name'] ?? '-'));
        $qty = (int)($it['qty'] ?? 0);
        $price = number_format((float)($it['price'] ?? 0), 2);
        $subtotal = number_format((float)($it['subtotal'] ?? ($qty * (float)($it['price'] ?? 0))), 2);
        $itemsTotal += (float)str_replace(',', '', $subtotal);
        $itemsHtml .= '<tr>';
        $itemsHtml .= '<td style="border-top:1px solid #eee">' . $name . '</td>';
        $itemsHtml .= '<td style="border-top:1px solid #eee;text-align:center">' . $qty . '</td>';
        $itemsHtml .= '<td style="border-top:1px solid #eee;text-align:right">' . $price . ' บาท</td>';
        $itemsHtml .= '<td style="border-top:1px solid #eee;text-align:right">' . $subtotal . ' บาท</td>';
        $itemsHtml .= '</tr>';
    }
    $itemsHtml .= '</tbody></table>';
    $itemsHtml .= '<div style="text-align:right;margin-top:8px"><strong>' . to_utf8('ยอดรวมสินค้า:') . ' ' . number_format($itemsTotal, 2) . ' ' . to_utf8('บาท') . '</strong></div>';
} else {
    $itemsHtml .= '<div style="margin-top:8px;color:#666">ไม่มีรายการสินค้า</div>';
}

$html .= $itemsHtml;

if ($imgData !== '') {
    $html .= '<div><img src="' . $imgData . '" class="slip-img" /></div>';
} else {
    $html .= '<div style="border:1px dashed #ccc;padding:20px;text-align:center;color:#999">ไม่พบรูปสลิป</div>';
}

$html .= '<div style="margin-top:10px;color:#666;font-size:12px">Generated by Shoppingcart</div>';
$html .= '</div></body></html>';

// generate PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'SiteFont');
$options->set('isFontSubsettingEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
// If a local TTF exists, register it with Dompdf so glyphs are available
foreach ($fontFiles as $ff) {
    if (file_exists($ff)) {
        // register both weights if available
        $regs = [];
        $regRegular = __DIR__ . '/assets/fonts/NotoSansThai-Regular.ttf';
        $regBold = __DIR__ . '/assets/fonts/NotoSansThai-Bold.ttf';
        if (file_exists($regRegular)) $regs[] = ['family' => 'SiteFont', 'weight' => 400, 'style' => 'normal', 'file' => $regRegular];
        if (file_exists($regBold)) $regs[] = ['family' => 'SiteFont', 'weight' => 700, 'style' => 'normal', 'file' => $regBold];
        foreach ($regs as $r) {
            $remote = 'file://' . str_replace('\\', '/', realpath($r['file']));
            try {
                $dompdf->getFontMetrics()->registerFont(['family' => $r['family'], 'weight' => $r['weight'], 'style' => $r['style']], $remote);
            } catch (\Exception $e) {
                // ignore
            }
        }
        break;
    }
}
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'slip_' . $slip['slip_id'] . '.pdf';
$dompdf->stream($filename, ['Attachment' => 1]);
exit;

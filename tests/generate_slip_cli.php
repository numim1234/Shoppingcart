<?php
// CLI helper: generate a slip PDF and save to tests/test_slip_<id>.pdf
if (php_sapi_name() !== 'cli') {
    echo "Run from CLI: php generate_slip_cli.php <slip_id>\n";
    exit(1);
}
$slip_id = isset($argv[1]) ? (int)$argv[1] : 0;
if ($slip_id <= 0) {
    echo "Usage: php generate_slip_cli.php <slip_id>\n";
    exit(1);
}

require_once __DIR__ . '/../condb.php';
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    echo "vendor/autoload.php not found. Run composer install in project root.\n";
    exit(1);
}
require $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

// helper: ensure a literal string is UTF-8
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

try {
    $stmt = $conn->prepare("SELECT slip_id, member_id, payer_name, payer_phone, pay_amount, pay_datetime, slip_image, note, status, created_at FROM tbl_payment_slip WHERE slip_id = ? LIMIT 1");
    $stmt->execute([$slip_id]);
    $slip = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$slip) {
        echo "Slip id {$slip_id} not found in DB.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage() . "\n";
    exit(1);
}

// prepare image as base64 if possible
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
            $path = __DIR__ . '/../' . ltrim($si, '/');
        } else {
            $path = __DIR__ . '/../uploads/slips/' . $si;
        }
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($content) ?: 'image/png';
            $imgData = 'data:' . $mime . ';base64,' . base64_encode($content);
        }
    }
}

// attempt to load local Thai font
$fontCss = '';
$fontFiles = [
    __DIR__ . '/../assets/fonts/NotoSansThai-Regular.ttf',
    __DIR__ . '/../assets/fonts/NotoSansThai-Bold.ttf',
    __DIR__ . '/../assets/fonts/THSarabunNew.ttf'
];
// embed regular + bold if available
$regular = __DIR__ . '/../assets/fonts/NotoSansThai-Regular.ttf';
$bold = __DIR__ . '/../assets/fonts/NotoSansThai-Bold.ttf';
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
    foreach ($fontFiles as $ff) {
        if (file_exists($ff)) {
            $data = base64_encode(file_get_contents($ff));
            $fontCssParts[] = "@font-face{font-family: 'SiteFont'; src: url('data:font/ttf;base64,{$data}') format('truetype'); font-weight: 400; font-style: normal;}";
            break;
        }
    }
}
$fontCss = implode("\n", $fontCssParts) . "\nbody{font-family: 'SiteFont', DejaVu Sans, Arial, Helvetica, sans-serif;}";

$html = '<!doctype html><html><head><meta charset="utf-8"><style>' . ($fontCss ?: '') . 'body{font-size:14px;color:#111}.container{width:100%;max-width:800px;margin:0 auto;padding:10px}.meta{margin-bottom:12px}.slip-img{width:100%;max-height:1000px;object-fit:contain;border:1px solid #ddd;padding:6px;background:#fff}</style></head><body>';
$html .= '<div class="container">';
$html .= '<div class="container">';
$html .= '<h2>' . to_utf8('สลิปการชำระเงิน') . ' #' . htmlspecialchars($slip['slip_id']) . '</h2>';
$html .= '<div class="meta">';
$html .= '<div><strong>' . to_utf8('ชื่อผู้โอน:') . '</strong> ' . htmlspecialchars($slip['payer_name'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('เบอร์โทร:') . '</strong> ' . htmlspecialchars($slip['payer_phone'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('ยอดโอน:') . '</strong> ' . number_format((float)($slip['pay_amount'] ?? 0), 2) . ' ' . to_utf8('บาท') . '</div>';
$html .= '<div><strong>' . to_utf8('วันเวลาที่โอน:') . '</strong> ' . htmlspecialchars($slip['pay_datetime'] ?? '-') . '</div>';
$html .= '<div><strong>' . to_utf8('หมายเหตุ:') . '</strong> ' . htmlspecialchars($slip['note'] ?? '-') . '</div>';
$html .= '</div>';

// items
$items = [];
try {
    if (!empty($slip['note']) && preg_match('/reserve_id\s*:\s*(\d+)/i', $slip['note'], $m)) {
        $reserveId = (int)$m[1];
        $stmtItems = $conn->prepare("SELECT rd.qty, rd.price, rd.subtotal, p.p_name FROM tbl_reservation_detail rd LEFT JOIN tbl_product p ON rd.p_id = p.p_id WHERE rd.reserve_id = ? ORDER BY rd.rd_id ASC");
        $stmtItems->execute([$reserveId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmtItems = $conn->prepare("SELECT sd.qty, sd.price, sd.subtotal, p.p_name FROM tbl_sale_detail sd LEFT JOIN tbl_product p ON sd.p_id = p.p_id WHERE sd.slip_id = ? ORDER BY sd.sd_id ASC");
        $stmtItems->execute([$slip_id]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $items = [];
}

if (!empty($items)) {
    $html .= '<h3>' . to_utf8('รายการสินค้า') . '</h3>';
    $html .= '<table width="100%" border="0" cellspacing="0" cellpadding="6" style="border-collapse:collapse">';
    $html .= '<thead><tr style="background:#f2f2f2"><th style="text-align:left">' . to_utf8('สินค้า') . '</th><th style="text-align:center">' . to_utf8('จำนวน') . '</th><th style="text-align:right">' . to_utf8('ราคา/ชิ้น') . '</th><th style="text-align:right">' . to_utf8('รวม') . '</th></tr></thead><tbody>';
    $itemsTotal = 0.0;
    foreach ($items as $it) {
        $name = htmlspecialchars(to_utf8($it['p_name'] ?? '-'));
        $qty = (int)($it['qty'] ?? 0);
        $price = number_format((float)($it['price'] ?? 0), 2);
        $subtotal = number_format((float)($it['subtotal'] ?? ($qty * (float)($it['price'] ?? 0))), 2);
        $itemsTotal += (float)str_replace(',', '', $subtotal);
        $html .= '<tr>';
        $html .= '<td style="border-top:1px solid #eee">' . $name . '</td>';
        $html .= '<td style="border-top:1px solid #eee;text-align:center">' . $qty . '</td>';
        $html .= '<td style="border-top:1px solid #eee;text-align:right">' . $price . ' บาท</td>';
        $html .= '<td style="border-top:1px solid #eee;text-align:right">' . $subtotal . ' บาท</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    $html .= '<div style="text-align:right;margin-top:8px"><strong>ยอดรวมสินค้า: ' . number_format($itemsTotal, 2) . ' บาท</strong></div>';
} else {
    $html .= '<div style="margin-top:8px;color:#666">ไม่มีรายการสินค้า</div>';
}

if ($imgData !== '') {
    $html .= '<div><img src="' . $imgData . '" class="slip-img" /></div>';
} else {
    $html .= '<div style="border:1px dashed #ccc;padding:20px;text-align:center;color:#999">ไม่พบรูปสลิป</div>';
}

$html .= '<div style="margin-top:10px;color:#666;font-size:12px">Generated by Shoppingcart CLI</div>';
$html .= '</div></body></html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'SiteFont');
$options->set('isFontSubsettingEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
// register local TTF with Dompdf if available
foreach ($fontFiles as $ff) {
    if (file_exists($ff)) {
        $regs = [];
        $regRegular = __DIR__ . '/../assets/fonts/NotoSansThai-Regular.ttf';
        $regBold = __DIR__ . '/../assets/fonts/NotoSansThai-Bold.ttf';
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
$out = $dompdf->output();
$outFile = __DIR__ . '/test_slip_' . $slip_id . '.pdf';
file_put_contents($outFile, $out);
echo "Wrote: " . $outFile . "\n";
exit(0);

<?php
if (!function_exists('getPromotion')) {
    function getPromotion($conn, $p_id, $lineTotal)
    {
        $today = date('Y-m-d');

        try {
            $stmt = $conn->prepare("
                SELECT p.*
                FROM tbl_promotion p
                LEFT JOIN tbl_promotion_product pp ON p.promo_id = pp.promo_id
                WHERE p.promo_status = 1
                  AND p.start_date <= ?
                  AND p.end_date >= ?
                  AND p.min_order <= ?
                  AND (
                        p.apply_type = 'all'
                        OR (p.apply_type = 'product' AND pp.p_id = ?)
                      )
                GROUP BY p.promo_id
                ORDER BY p.promo_id DESC
            ");
            $stmt->execute([$today, $today, $lineTotal, $p_id]);
            $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $bestPromo = null;
            $bestDiscount = 0;

            foreach ($promotions as $promo) {
                $discount = 0;

                if ($promo['promo_type'] === 'percent') {
                    $discount = ($lineTotal * (float)$promo['promo_value']) / 100;
                } elseif ($promo['promo_type'] === 'amount') {
                    $discount = (float)$promo['promo_value'];
                }

                if ($discount > $lineTotal) {
                    $discount = $lineTotal;
                }

                if ($discount > $bestDiscount) {
                    $bestDiscount = $discount;
                    $bestPromo = $promo;
                }
            }

            return [
                'promo' => $bestPromo,
                'discount' => $bestDiscount,
                'final_total' => $lineTotal - $bestDiscount
            ];
        } catch (Exception $e) {
            return [
                'promo' => null,
                'discount' => 0,
                'final_total' => $lineTotal
            ];
        }
    }
}
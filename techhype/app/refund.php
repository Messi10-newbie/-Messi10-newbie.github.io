<?php include '_base.php'; include '_head.php'; ?>

<!-- Hero -->
<section style="background: var(--gradient-dark); padding: 80px 0; text-align: center;">
    <div class="container">
        <p style="color: var(--accent); font-size: 13px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 16px;">Policies</p>
        <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 800; color: #fff; margin-bottom: 16px;">Returns & Refunds</h1>
        <p style="color: rgba(255,255,255,0.6); font-size: 15px;">Last updated: April 2026</p>
    </div>
</section>

<!-- Content -->
<section class="section" style="background: var(--bg);">
    <div class="container" style="max-width: 860px;">

        <!-- Quick Summary Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 50px;">
            <?php
            $highlights = [
                ['icon' => 'fa-rotate-left',    'color' => '#6c5ce7', 'bg' => '#f0edff', 'title' => '30-Day Returns',      'desc' => 'Return most items within 30 days of delivery'],
                ['icon' => 'fa-money-bill-wave', 'color' => '#00b894', 'bg' => '#e6f9f5', 'title' => 'Full Refund',         'desc' => 'Get your money back once we receive the item'],
                ['icon' => 'fa-shield-halved',   'color' => '#0984e3', 'bg' => '#e8f4fd', 'title' => 'Warranty Covered',   'desc' => 'Defective items covered under manufacturer warranty'],
                ['icon' => 'fa-headset',         'color' => '#e17055', 'bg' => '#fef0ec', 'title' => '7-Day Support',       'desc' => 'Our team is here every day to help you'],
            ];
            foreach ($highlights as $h): ?>
            <div style="background:#fff; border-radius:var(--radius); padding:24px 20px; text-align:center; box-shadow:var(--shadow-sm);">
                <div style="width:52px;height:52px;background:<?= $h['bg'] ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                    <i class="fa-solid <?= $h['icon'] ?>" style="font-size:20px;color:<?= $h['color'] ?>;"></i>
                </div>
                <h4 style="font-size:14px;font-weight:700;margin-bottom:6px;"><?= $h['title'] ?></h4>
                <p style="font-size:12px;color:var(--text-secondary);line-height:1.5;"><?= $h['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Policy Sections -->
        <?php
        $sections = [
            [
                'icon' => 'fa-circle-check',
                'color' => '#00b894',
                'title' => 'What can be returned?',
                'content' => '
                    <p>We accept returns for most products within <strong>30 days</strong> of the delivery date, provided the following conditions are met:</p>
                    <ul style="margin-top:12px;padding-left:20px;line-height:2;color:var(--text-secondary);">
                        <li>Item is in its <strong>original, unused condition</strong></li>
                        <li>All original packaging, accessories, and documentation are included</li>
                        <li>The item has not been physically damaged, modified, or tampered with</li>
                        <li>Proof of purchase (order number or receipt) is provided</li>
                    </ul>
                '
            ],
            [
                'icon' => 'fa-circle-xmark',
                'color' => '#d63031',
                'title' => 'What cannot be returned?',
                'content' => '
                    <p>The following items are <strong>not eligible</strong> for return:</p>
                    <ul style="margin-top:12px;padding-left:20px;line-height:2;color:var(--text-secondary);">
                        <li>Items returned after 30 days from delivery</li>
                        <li>Products with signs of physical damage caused by the customer</li>
                        <li>Items with removed or tampered serial numbers / IMEI stickers</li>
                        <li>Digital products, software licenses, or gift cards</li>
                        <li>Earbuds, in-ear headphones (for hygiene reasons), unless defective</li>
                        <li>Items marked as <strong>Final Sale</strong> at the time of purchase</li>
                    </ul>
                '
            ],
            [
                'icon' => 'fa-arrow-right-arrow-left',
                'color' => '#6c5ce7',
                'title' => 'How to start a return',
                'content' => '
                    <p>Follow these simple steps to return an item:</p>
                    <ol style="margin-top:12px;padding-left:20px;line-height:2.2;color:var(--text-secondary);">
                        <li><strong>Log in</strong> to your TechHype account and go to <a href="' . $base . '/orders.php" style="color:var(--primary);font-weight:600;">My Orders</a></li>
                        <li>Select the order containing the item you wish to return</li>
                        <li>Contact our support team at <strong>support@techhype.com</strong> with your order number and reason for return</li>
                        <li>Our team will review your request within <strong>1–2 business days</strong></li>
                        <li>Once approved, pack the item securely and ship it to the address provided</li>
                        <li>After we receive and inspect the item, your refund will be processed</li>
                    </ol>
                '
            ],
            [
                'icon' => 'fa-money-bill-wave',
                'color' => '#00b894',
                'title' => 'Refund timeline',
                'content' => '
                    <p>Once we receive and inspect your returned item, refunds are processed as follows:</p>
                    <div style="margin-top:16px;border:1px solid var(--border-light);border-radius:var(--radius-sm);overflow:hidden;">
                        <table style="width:100%;border-collapse:collapse;font-size:14px;">
                            <thead>
                                <tr style="background:var(--bg);">
                                    <th style="padding:12px 16px;text-align:left;font-weight:600;">Refund Method</th>
                                    <th style="padding:12px 16px;text-align:left;font-weight:600;">Processing Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-top:1px solid var(--border-light);">
                                    <td style="padding:12px 16px;color:var(--text-secondary);">Original payment method (card/online banking)</td>
                                    <td style="padding:12px 16px;color:var(--text-secondary);">5–10 business days</td>
                                </tr>
                                <tr style="border-top:1px solid var(--border-light);">
                                    <td style="padding:12px 16px;color:var(--text-secondary);">TechHype Store Credit</td>
                                    <td style="padding:12px 16px;color:var(--text-secondary);">Within 24 hours of approval</td>
                                </tr>
                                <tr style="border-top:1px solid var(--border-light);">
                                    <td style="padding:12px 16px;color:var(--text-secondary);">Reward Points refund</td>
                                    <td style="padding:12px 16px;color:var(--text-secondary);">Automatically reversed on cancellation</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p style="margin-top:12px;font-size:13px;color:var(--text-muted);">* Shipping fees are non-refundable unless the return is due to our error or a defective product.</p>
                '
            ],
            [
                'icon' => 'fa-shield-halved',
                'color' => '#0984e3',
                'title' => 'Warranty & defective items',
                'content' => '
                    <p>All products sold on TechHype come with the <strong>official manufacturer warranty</strong>. If your item arrives defective or stops working within the warranty period:</p>
                    <ul style="margin-top:12px;padding-left:20px;line-height:2;color:var(--text-secondary);">
                        <li>Contact us within <strong>7 days of delivery</strong> for a defective-on-arrival (DOA) claim — we will replace or fully refund at no cost</li>
                        <li>For warranty claims after 7 days, we will assist you in coordinating with the manufacturer\'s service centre</li>
                        <li>Physical damage, water damage, or unauthorised modifications void the warranty</li>
                    </ul>
                '
            ],
            [
                'icon' => 'fa-truck',
                'color' => '#e17055',
                'title' => 'Return shipping',
                'content' => '
                    <p>Return shipping costs are handled as follows:</p>
                    <ul style="margin-top:12px;padding-left:20px;line-height:2;color:var(--text-secondary);">
                        <li><strong>Defective / wrong item received</strong> — TechHype covers the return shipping cost</li>
                        <li><strong>Change of mind / other reasons</strong> — Customer bears the return shipping cost</li>
                        <li>We recommend using a tracked shipping service; TechHype is not responsible for items lost during return transit</li>
                    </ul>
                '
            ],
        ];
        foreach ($sections as $s): ?>
        <div style="background:#fff; border-radius:var(--radius); padding:32px; margin-bottom:24px; box-shadow:var(--shadow-sm);">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:18px;">
                <div style="width:42px;height:42px;background:<?= $s['color'] ?>18;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fa-solid <?= $s['icon'] ?>" style="font-size:18px;color:<?= $s['color'] ?>;"></i>
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;"><?= $s['title'] ?></h3>
            </div>
            <div style="font-size:14px;line-height:1.8;color:var(--text);">
                <?= $s['content'] ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Contact CTA -->
        <div style="background:var(--gradient-primary);border-radius:var(--radius);padding:36px;text-align:center;margin-top:10px;">
            <h3 style="color:#fff;font-size:1.3rem;font-weight:700;margin-bottom:10px;">Still have questions?</h3>
            <p style="color:rgba(255,255,255,0.75);margin-bottom:24px;font-size:14px;">Our support team is available 7 days a week to help with your return or refund.</p>
            <a href="mailto:support@techhype.com" class="btn" style="background:#fff;color:var(--primary);font-weight:700;padding:12px 28px;border-radius:50px;">
                <i class="fa-solid fa-envelope" style="margin-right:8px;"></i>support@techhype.com
            </a>
        </div>

    </div>
</section>

<?php include '_foot.php'; ?>

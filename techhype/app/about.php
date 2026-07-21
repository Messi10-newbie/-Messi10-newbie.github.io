<?php include '_base.php'; include '_head.php'; ?>

<!-- About Hero -->
<section class="brand-header" style="background: var(--gradient-dark); padding: 80px 0; text-align: center;">
    <div class="container">
        <p style="color: var(--accent); font-size: 13px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 16px;">About Us</p>
        <h1 style="font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 800; color: #fff; margin-bottom: 20px; line-height: 1.15;">Your dedicated hub for<br>everything tech.</h1>
        <p style="color: rgba(255,255,255,0.65); max-width: 680px; margin: 0 auto; font-size: 17px; line-height: 1.8;">
            Since 2024, TechHype has been Malaysia's go-to destination for the latest smartphones, tablets, laptops, gaming consoles, and audio gear — bringing you the world's top brands all in one place.
        </p>
    </div>
</section>

<!-- Mission -->
<section class="section" style="background: #fff;">
    <div class="container" style="max-width: 800px; text-align: center;">
        <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 20px;">More than just a store.</h2>
        <p style="font-size: 16px; color: var(--text-secondary); line-height: 1.9;">
            We don't just sell gadgets — we help you find the right one. With expert product knowledge, honest specs comparisons, and a carefully curated lineup from the world's leading brands, TechHype makes buying tech simple, smart, and satisfying. Whether you're a student on a budget, a gamer chasing performance, or a professional who demands the best — we've got you.
        </p>
    </div>
</section>

<!-- Services Grid -->
<section class="section" style="background: var(--bg);">
    <div class="container">
        <h2 style="text-align:center; font-size:1.8rem; font-weight:800; margin-bottom:10px;">What we offer</h2>
        <p style="text-align:center; color:var(--text-secondary); margin-bottom:50px;">Everything you need, in one place.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">

            <?php
            $services = [
                ['icon' => 'fa-truck-fast',        'title' => 'Free Delivery',           'desc' => 'Free nationwide shipping on orders above RM 500.'],
                ['icon' => 'fa-shield-halved',      'title' => 'Official Warranty',       'desc' => 'Every product comes with a manufacturer warranty.'],
                ['icon' => 'fa-rotate-left',        'title' => '30-Day Returns',          'desc' => 'Not happy? Return it within 30 days, no questions asked.'],
                ['icon' => 'fa-bolt',               'title' => 'Same Day Dispatch',       'desc' => 'Orders before 2 PM are dispatched the same day.'],
                ['icon' => 'fa-tags',               'title' => 'Best Price Promise',      'desc' => 'Competitive pricing across all top brands and categories.'],
                ['icon' => 'fa-headset',            'title' => 'Customer Support',        'desc' => '7-day support via email and live chat for all your queries.'],
                ['icon' => 'fa-graduation-cap',     'title' => 'Student Discounts',       'desc' => 'Exclusive verified student vouchers and bonus points.'],
                ['icon' => 'fa-coins',              'title' => 'Rewards Programme',       'desc' => 'Earn points on every purchase and redeem for vouchers.'],
                ['icon' => 'fa-boxes-stacked',      'title' => 'Top Brands Only',         'desc' => 'Curated lineup from Apple, Samsung, Sony, Google and more.'],
            ];
            foreach ($services as $s): ?>
            <div style="background:#fff; border-radius: var(--radius); padding: 32px 24px; text-align:center; box-shadow: var(--shadow-sm); transition: var(--transition);"
                 onmouseover="this.style.boxShadow='var(--shadow-lg)'; this.style.transform='translateY(-4px)'"
                 onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)'">
                <div style="width:64px; height:64px; background: var(--primary-light); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 18px;">
                    <i class="fa-solid <?= $s['icon'] ?>" style="font-size:24px; color: var(--primary);"></i>
                </div>
                <h4 style="font-size:15px; font-weight:700; margin-bottom:8px;"><?= $s['title'] ?></h4>
                <p style="font-size:13px; color:var(--text-secondary); line-height:1.6;"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- Stats Bar -->
<section style="background: var(--gradient-primary); padding: 60px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 30px; text-align: center;">
            <?php
            $stats = [
                ['num' => '11+',   'label' => 'Top Brands'],
                ['num' => '500+',  'label' => 'Products Listed'],
                ['num' => '10K+',  'label' => 'Happy Customers'],
                ['num' => '4.8★',  'label' => 'Average Rating'],
            ];
            foreach ($stats as $st): ?>
            <div>
                <div style="font-size: 2.8rem; font-weight: 900; color: #fff; line-height:1;"><?= $st['num'] ?></div>
                <div style="font-size: 14px; color: rgba(255,255,255,0.75); margin-top: 8px; font-weight: 500;"><?= $st['label'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Brands We Carry -->
<section class="section" style="background: #fff;">
    <div class="container" style="text-align:center;">
        <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:10px;">Brands we carry</h2>
        <p style="color:var(--text-secondary); margin-bottom:40px;">Officially listed products from Malaysia's most loved tech brands.</p>
        <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:16px;">
            <?php
            $brands = ['Apple','Samsung','Sony','Google','Xiaomi','Oppo','Vivo','Nothing','iQOO','OnePlus','Huawei'];
            foreach ($brands as $b): ?>
            <a href="<?= $base ?>/products.php?brand=<?= urlencode($b) ?>"
               style="padding:12px 24px; background:var(--bg); border:2px solid var(--border-light); border-radius:50px; font-size:14px; font-weight:600; color:var(--text); transition:var(--transition);"
               onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)'"
               onmouseout="this.style.borderColor='var(--border-light)'; this.style.color='var(--text)'"><?= $b ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section style="background: var(--gradient-dark); padding: 80px 0; text-align:center;">
    <div class="container">
        <h2 style="font-size:2rem; font-weight:800; color:#fff; margin-bottom:16px;">Ready to find your next device?</h2>
        <p style="color:rgba(255,255,255,0.6); margin-bottom:32px; font-size:16px;">Browse our full collection and enjoy the TechHype experience.</p>
        <a href="<?= $base ?>/products.php" class="btn btn-primary" style="font-size:16px; padding:14px 36px;">Shop Now</a>
    </div>
</section>

<?php include '_foot.php'; ?>

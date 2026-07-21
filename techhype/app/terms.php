<?php include '_base.php'; include '_head.php'; ?>

<!-- Hero -->
<section style="background: var(--gradient-dark); padding: 80px 0; text-align: center;">
    <div class="container">
        <p style="color: var(--accent); font-size: 13px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 16px;">Legal</p>
        <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 800; color: #fff; margin-bottom: 16px;">Terms of Service</h1>
        <p style="color: rgba(255,255,255,0.6); font-size: 15px;">Last updated: April 2026</p>
    </div>
</section>

<!-- Content -->
<section class="section" style="background: var(--bg);">
    <div class="container" style="max-width: 860px;">

        <!-- Jump Links -->
        <div style="background:#fff; border-radius:var(--radius); padding:28px 32px; margin-bottom:32px; box-shadow:var(--shadow-sm);">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:16px;"><i class="fa-solid fa-list" style="color:var(--primary);margin-right:8px;"></i>Table of Contents</h3>
            <ol style="padding-left:20px; line-height:2.2; font-size:14px;">
                <li><a href="#s-overview"  style="color:var(--primary);">Overview</a></li>
                <li><a href="#s-1"  style="color:var(--primary);">Online Store</a></li>
                <li><a href="#s-2"  style="color:var(--primary);">Use of Website</a></li>
                <li><a href="#s-3"  style="color:var(--primary);">Website Information</a></li>
                <li><a href="#s-4"  style="color:var(--primary);">Products &amp; Services</a></li>
                <li><a href="#s-5"  style="color:var(--primary);">Modification to Prices &amp; Services</a></li>
                <li><a href="#s-6"  style="color:var(--primary);">Billing &amp; Account Information</a></li>
                <li><a href="#s-7"  style="color:var(--primary);">Third-Party Products, Tools &amp; Services</a></li>
                <li><a href="#s-8"  style="color:var(--primary);">Personal Data &amp; Information</a></li>
                <li><a href="#s-9"  style="color:var(--primary);">Membership &amp; Rewards</a></li>
                <li><a href="#s-10" style="color:var(--primary);">User Comments, Feedback &amp; Submissions</a></li>
                <li><a href="#s-11" style="color:var(--primary);">Errors, Inaccuracies &amp; Omissions</a></li>
                <li><a href="#s-12" style="color:var(--primary);">Disclaimer of Warranties &amp; Limitation of Liability</a></li>
                <li><a href="#s-13" style="color:var(--primary);">Severability</a></li>
                <li><a href="#s-14" style="color:var(--primary);">Termination</a></li>
                <li><a href="#s-15" style="color:var(--primary);">Entire Agreement</a></li>
                <li><a href="#s-16" style="color:var(--primary);">Governing Law</a></li>
                <li><a href="#s-17" style="color:var(--primary);">Changes to Terms of Service</a></li>
            </ol>
        </div>

        <?php
        $sections = [

            ['id' => 's-overview', 'num' => '', 'title' => 'Overview', 'body' => '
                <p>This website ("Website") is operated by TechHype (collectively referred to as "we," "our," "us," or "TechHype"). Before you proceed, we kindly request that you carefully review these Terms of Service, as they govern your access and use of our website. By accessing our site and/or making a purchase from us, you agree to our "Service" and are bound by the terms and conditions ("Terms of Service" or "Terms") outlined here, which also include any additional terms, conditions, and policies referenced herein or through hyperlinks. These Terms of Service apply to all users of the site, including browsers, vendors, customers, merchants, and content contributors.</p>
                <p style="margin-top:14px;">The privacy of your personal data is of utmost importance to us. The specific categories of personal data we process are determined by how you use our services. We use your personal data to enhance your online experience, aligning it with your preferences — enabling us to provide tailored purchases and services, address your inquiries, and reach out regarding products and services that may interest you. For more information, please refer to our <a href="' . $base . '/refund.php" style="color:var(--primary);font-weight:600;">Returns &amp; Refund Policy</a>.</p>
                <p style="margin-top:14px;">Any new features or tools introduced to the store will also be subject to these Terms. We retain the right to update, modify, or replace any part of these Terms by posting updates on our website. We recommend checking this page periodically. Your continued use of the website after any changes signifies your acceptance of those changes.</p>
            '],

            ['id' => 's-1', 'num' => 'Section 1', 'title' => 'Online Store', 'body' => '
                <p>By proceeding with the use of our website, products, and services, you acknowledge and affirm that you are <strong>18 years of age</strong> or older, and have a valid payment method. If you are under 18, you must have obtained the necessary consent from a parent or legal guardian to access this site.</p>
                <p style="margin-top:14px;">We strictly forbid the use of our products for any illegal or unauthorised purposes. It is your responsibility to comply with all applicable laws and regulations in your jurisdiction, including copyright laws. Any breach of these Terms will result in <strong>immediate termination of service</strong>.</p>
            '],

            ['id' => 's-2', 'num' => 'Section 2', 'title' => 'Use of Website', 'body' => '
                <p>By accessing and using this Website, you consent to be bound by these Terms and Conditions, as well as all relevant laws and regulations. The following activities are <strong>strictly prohibited</strong>:</p>
                <ul style="margin-top:12px;padding-left:20px;line-height:2;color:var(--text-secondary);">
                    <li>Spamming, phishing, pharming, pretexting, spidering, crawling, or scraping</li>
                    <li>Uploading or transmitting viruses or any other malicious code</li>
                    <li>Activities detrimental to the rights, interests, or reputation of TechHype or its employees</li>
                    <li>Actions that may cause harm to children or violate public order and morals</li>
                    <li>Causing interference or emotional distress to other users or third parties</li>
                    <li>Providing false or misleading information on the website</li>
                </ul>
                <p style="margin-top:14px;">We reserve the right to restrict or suspend access to the Website without prior notice, as and when necessary. The Website is provided on an <strong>"as is"</strong> and <strong>"as available"</strong> basis, and may be subject to limitations or modifications due to security, technical, or legal reasons.</p>
            '],

            ['id' => 's-3', 'num' => 'Section 3', 'title' => 'Website Information', 'body' => '
                <p>Product availability and pricing displayed on this Website are subject to change without notice. Images of products are provided for illustrative purposes only and may not entirely represent the actual goods. We do not guarantee that product colours displayed on your screen will be accurate.</p>
                <p style="margin-top:14px;">While we strive to keep all information current and accurate, we are not responsible for any misinformation or inaccuracies found on this Website. It is the user\'s responsibility to monitor any changes to website content regularly.</p>
            '],

            ['id' => 's-4', 'num' => 'Section 4', 'title' => 'Products & Services', 'body' => '
                <p>Certain products or services may be exclusively available online through our website and are subject to return or exchange only in accordance with our <a href="' . $base . '/refund.php" style="color:var(--primary);font-weight:600;">Return Policy</a>.</p>
                <p style="margin-top:14px;">We reserve the right, at our discretion, to:</p>
                <ul style="margin-top:12px;padding-left:20px;line-height:2;color:var(--text-secondary);">
                    <li>Limit sales of products or services to specific individuals, geographic regions, or jurisdictions</li>
                    <li>Limit quantities of any products or services offered</li>
                    <li>Change product descriptions or pricing at any time without prior notice</li>
                    <li>Discontinue any product at any time</li>
                </ul>
                <p style="margin-top:14px;">We do not provide warranties regarding the quality of any products, services, or other materials obtained through our Service, nor do we guarantee that errors in the Service will be corrected.</p>
            '],

            ['id' => 's-5', 'num' => 'Section 5', 'title' => 'Modification to Prices & Services', 'body' => '
                <p>Prices for our products are subject to change <strong>without prior notice</strong>. We retain the right to modify or discontinue the Service, or any part of its content, at any time without notice. We shall not be held liable to you or any third-party for any modifications, price changes, suspensions, or discontinuance of the Service.</p>
            '],

            ['id' => 's-6', 'num' => 'Section 6', 'title' => 'Billing & Account Information', 'body' => '
                <p>We retain the right to reject any order you place with us. At our discretion, we may limit or cancel quantities purchased per person, per household, or per order. These restrictions may apply to orders made under the same customer account, the same payment method, or with the same billing and/or shipping address.</p>
                <p style="margin-top:14px;">If we make changes to or cancel an order, we will make reasonable efforts to notify you via the email address provided at the time of purchase.</p>
                <p style="margin-top:14px;">In the event of an electronic or technical error that affects the details or pricing of a product or promotion, we have the right to rectify such an error and/or cancel any transaction entered into based on that error.</p>
                <p style="margin-top:14px;">You agree to provide <strong>current, complete, and accurate</strong> purchase and account information for all orders. It is your responsibility to promptly update your account details — including email address and payment information — to ensure smooth transactions.</p>
            '],

            ['id' => 's-7', 'num' => 'Section 7', 'title' => 'Third-Party Products, Tools & Services', 'body' => '
                <p>Some products, tools, or services available through our Service may come from third-party sources. Our website may contain links to third-party websites that are not operated by us. We do not control the content or accuracy of those third-party materials and cannot be held responsible for any information, products, or services they provide.</p>
                <p style="margin-top:14px;">Any use of optional third-party tools offered through the site is entirely at your own risk and discretion. We make no warranties or endorsements in respect of such tools. If you decide to purchase from or interact with a third-party website, any issues or damages that arise are not our responsibility.</p>
            '],

            ['id' => 's-8', 'num' => 'Section 8', 'title' => 'Personal Data & Information', 'body' => '
                <p>Your submission of personal information through the store or website is governed by applicable Malaysian personal data protection laws (PDPA 2010). We collect and process your personal data solely for the purpose of providing our services, processing your orders, and improving your shopping experience.</p>
                <p style="margin-top:14px;">Your personal data will not be sold to third parties. It may be disclosed to service providers and relevant business partners only as necessary to fulfil your order or provide our services, and for no other purposes.</p>
            '],

            ['id' => 's-9', 'num' => 'Section 9', 'title' => 'Membership & Rewards', 'body' => '
                <p>The TechHype Rewards Programme is a loyalty programme available to registered members. Each customer is allowed to sign up for only <strong>one (1)</strong> account. The membership is strictly personal and is not transferable.</p>
                <p style="margin-top:14px;">TechHype reserves the right, at its sole discretion, to decline, suspend, or revoke membership without prior notice — including for individuals found to have violated these Terms or misused the Service.</p>
                <p style="margin-top:14px;">By participating in our rewards programme, you acknowledge that you have read and understood these Terms and consent to TechHype processing your personal data to communicate with you regarding your membership, points balance, and promotions.</p>
                <p style="margin-top:14px;">TechHype retains the right to modify the rewards programme Terms at any time without prior written notice. Members are required to comply with any such changes.</p>
            '],

            ['id' => 's-10', 'num' => 'Section 10', 'title' => 'User Comments, Feedback & Submissions', 'body' => '
                <p>If you submit reviews, feedback, suggestions, or other materials ("comments") through our website, you agree that we have the right to use, edit, publish, and distribute these comments in any medium without restriction. We are not obligated to keep any comments confidential or compensate you for them.</p>
                <p style="margin-top:14px;">We may, at our discretion, monitor, edit, or remove content that we consider unlawful, offensive, threatening, defamatory, or otherwise objectionable, or that violates any party\'s intellectual property rights or these Terms.</p>
                <p style="margin-top:14px;">By submitting comments, you confirm that they do not infringe on any third-party rights and do not contain libelous, abusive, obscene, or unlawful material. You bear sole responsibility for the accuracy and content of your submissions.</p>
            '],

            ['id' => 's-11', 'num' => 'Section 11', 'title' => 'Errors, Inaccuracies & Omissions', 'body' => '
                <p>Occasionally, there may be errors or inaccuracies in the information on our website or through our services — including spelling mistakes or missing details relating to product descriptions, prices, promotions, shipping charges, delivery times, and product availability.</p>
                <p style="margin-top:14px;">We retain the right to correct any such errors, inaccuracies, or omissions, and may modify or cancel orders if any information is found to be inaccurate — without prior notice, even after an order has been submitted. We have no obligation to update all information on the site, except where required by law.</p>
            '],

            ['id' => 's-12', 'num' => 'Section 12', 'title' => 'Disclaimer of Warranties & Limitation of Liability', 'body' => '
                <p>We cannot guarantee that your use of our Service will be uninterrupted, timely, secure, or error-free. The Service and all products delivered through it are provided <strong>"as is"</strong> and <strong>"as available"</strong> without any warranties of any kind.</p>
                <p style="margin-top:14px;">In no case shall TechHype, our directors, officers, employees, affiliates, agents, contractors, suppliers, or licensors be liable for any injury, loss, claim, or any direct, indirect, incidental, punitive, special, or consequential damages of any kind — including lost profits, lost revenue, loss of data, or replacement costs — arising from your use of the Service or any products obtained through the Service.</p>
                <p style="margin-top:14px;">By accepting these Terms, you agree to indemnify and hold TechHype and its affiliates, partners, officers, directors, agents, contractors, and employees harmless from any claim or demand — including reasonable legal fees — brought by any third-party as a result of your breach of these Terms or your violation of any law or the rights of a third-party.</p>
            '],

            ['id' => 's-13', 'num' => 'Section 13', 'title' => 'Severability', 'body' => '
                <p>If any provision of these Terms of Service is found to be unlawful, void, or unenforceable, that provision shall still be enforceable to the fullest extent permitted by applicable law, and the unenforceable portion shall be deemed severed from these Terms. Such determination shall not affect the validity and enforceability of the remaining provisions.</p>
            '],

            ['id' => 's-14', 'num' => 'Section 14', 'title' => 'Termination', 'body' => '
                <p>These Terms of Service shall remain in effect until terminated by either party. You may terminate these Terms at any time by ceasing to use our Services.</p>
                <p style="margin-top:14px;">If we determine, at our sole discretion, that you have failed to comply with any term or provision of these Terms, we may terminate this agreement without prior notice. In such a case, you will remain responsible for all amounts due up to and including the date of termination, and we reserve the right to deny you access to our Services.</p>
            '],

            ['id' => 's-15', 'num' => 'Section 15', 'title' => 'Entire Agreement', 'body' => '
                <p>These Terms of Service and any policies or operating rules posted by us on this site constitute the entire agreement and understanding between you and us, and govern your use of the Service — superseding any prior or contemporaneous agreements, communications, and proposals, whether oral or written.</p>
                <p style="margin-top:14px;">Our failure to exercise or enforce any right or provision of these Terms shall not constitute a waiver of such right or provision.</p>
            '],

            ['id' => 's-16', 'num' => 'Section 16', 'title' => 'Governing Law', 'body' => '
                <p>These Terms of Service and any separate agreements through which we provide you Services shall be governed by and interpreted in accordance with the <strong>laws of Malaysia</strong>. You acknowledge and expressly consent that any and all disputes arising from or in connection with your dealings with us shall be exclusively resolved through the competent Courts of Malaysia.</p>
            '],

            ['id' => 's-17', 'num' => 'Section 17', 'title' => 'Changes to Terms of Service', 'body' => '
                <p>You can always review the most current version of the Terms of Service on this page. We reserve the right, at our sole discretion, to update, modify, or replace any part of these Terms by posting updates on our website.</p>
                <p style="margin-top:14px;">It is your responsibility to periodically review this page for updates. By continuing to use or access our website or services after any changes are posted, you signify your acceptance of those changes.</p>
            '],

        ];

        foreach ($sections as $s): ?>
        <div id="<?= $s['id'] ?>" style="background:#fff; border-radius:var(--radius); padding:32px; margin-bottom:20px; box-shadow:var(--shadow-sm); scroll-margin-top:90px;">
            <?php if ($s['num']): ?>
            <p style="font-size:11px;font-weight:700;color:var(--primary);letter-spacing:2px;text-transform:uppercase;margin-bottom:6px;"><?= $s['num'] ?></p>
            <?php endif; ?>
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border-light);"><?= $s['title'] ?></h3>
            <div style="font-size:14px;line-height:1.9;color:var(--text);">
                <?= $s['body'] ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Contact -->
        <div style="background:var(--gradient-primary);border-radius:var(--radius);padding:36px;text-align:center;margin-top:10px;">
            <h3 style="color:#fff;font-size:1.2rem;font-weight:700;margin-bottom:10px;">Questions about our Terms?</h3>
            <p style="color:rgba(255,255,255,0.75);margin-bottom:24px;font-size:14px;">Reach out to our support team and we'll be happy to help.</p>
            <a href="mailto:support@techhype.com" class="btn" style="background:#fff;color:var(--primary);font-weight:700;padding:12px 28px;border-radius:50px;">
                <i class="fa-solid fa-envelope" style="margin-right:8px;"></i>support@techhype.com
            </a>
        </div>

    </div>
</section>

<?php include '_foot.php'; ?>

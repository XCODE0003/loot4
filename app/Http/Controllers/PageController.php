<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Static storefront content pages (About, FAQ, How It Works, Contact, Sell,
 * and the legal policies). Content adapted from the original brand copy.
 */
class PageController extends Controller
{
    private const EMAIL = 'support@loot4you.gg';

    private const PHONE = '+44 7455 903679';

    private const ADDRESS = 'First Floor Office, 3 Hornton Place, London, United Kingdom';

    public function about(): Response
    {
        return $this->render('About Us', null, [
            ['type' => 'lead', 'text' => 'Your trusted gaming marketplace — delivering in minutes, every time.'],
            ['type' => 'p', 'text' => "At Loot4You, we specialize in providing high-quality, customizable mods and accessories for gaming enthusiasts. Whether you're a casual player looking for a unique setup or a hardcore gamer seeking an edge in performance, we have everything you need to elevate your gaming experience."],
            ['type' => 'p', 'text' => 'Founded with a passion for gaming, Loot4You has quickly become a trusted name in the gaming community. Our extensive product range includes controller mods, skins, custom buttons, and other accessories compatible with leading gaming consoles such as PlayStation, Xbox, and PC. We pride ourselves on offering not only the latest in gaming tech but also a personalized experience for each customer.'],
            ['type' => 'p', 'text' => 'Our commitment to customer satisfaction is at the core of everything we do. From fast shipping to exceptional customer service, we ensure that our customers receive only the best. Plus, our online store is user-friendly and secure, so you can shop with confidence, knowing your personal and payment information is protected.'],
            ['type' => 'p', 'text' => "Whether you're upgrading your gaming gear or shopping for a gift, loot4you.gg is the go-to destination for gamers who want to stand out. Explore our wide range of products today and take your gaming to the next level!"],
            ['type' => 'stats', 'items' => [
                ['value' => '24/7', 'label' => 'Support Available'],
                ['value' => '100%', 'label' => 'Anti-Ban Guarantee'],
                ['value' => 'Minutes', 'label' => 'Average Delivery Time'],
            ]],
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('loot4/Faq');
    }

    public function howItWorks(): Response
    {
        return $this->render('How It Works', null, [
            ['type' => 'lead', 'text' => 'Get your order in just a few simple steps.'],
            ['type' => 'steps', 'items' => [
                ['n' => '01', 'title' => 'Select the Product of Your Choice', 'text' => 'Click on the "Buy Now" link, then scroll down and choose the product you would like to purchase and enter your credentials.'],
                ['n' => '02', 'title' => 'Fill-In Billing Information', 'text' => 'You now simply have to fill-in all of the billing information that are required for tax purposes.'],
                ['n' => '03', 'title' => 'Select Payment Method & Delivery', 'text' => 'This is the last step — all you have to do is select the payment method of your choice and choose a delivery package.'],
                ['n' => '04', 'title' => '24/7 Support Service', 'text' => 'After paying for the order, you will be taken to your personal account with all the order data and a live chat, in which you can clarify all the details of the order.'],
            ]],
            ['type' => 'cta', 'text' => 'Ready to get started? Browse our full catalogue and find exactly what you need.', 'label' => 'Shop Now', 'href' => '/'],
        ]);
    }

    public function contact(): Response
    {
        return $this->render('Contact Us', null, [
            ['type' => 'lead', 'text' => "We're here to help with any questions about your order, payments or our services. Reach out using one of the options below and our team will get back to you as soon as possible."],
            ['type' => 'p', 'text' => 'Our support team is available every day to assist you with purchases, delivery questions and account issues. We aim to respond quickly by email and chat, and within a few hours during working hours.'],
            ['type' => 'p', 'text' => "Please always include your order number and any relevant screenshots so we can help you faster. If you are contacting us about a potential issue, don't worry — our goal is always to find a fair and safe solution for both sides."],
            ['type' => 'h', 'text' => 'Contact Information'],
            ['type' => 'contact_cards', 'items' => $this->contactCards()],
            ['type' => 'contacts', 'items' => $this->contactItems()],
        ]);
    }

    public function sell(): Response
    {
        return $this->render('Sell to Us', null, [
            ['type' => 'lead', 'text' => 'Turn your unused game items, coins or accounts into instant cash or store balance. Contact us with the details and we will send you a fair quote as soon as possible.'],
            ['type' => 'p', 'text' => 'We buy from trusted players and resellers across many different games. To get started, send us a short message with what you want to sell, the server or platform, and any relevant screenshots or proof.'],
            ['type' => 'p', 'text' => "Once we review your offer, we'll reply with pricing and the safest way to complete the trade. All deals are handled manually by our team to keep both your account and our marketplace safe."],
            ['type' => 'h', 'text' => 'Get in touch'],
            ['type' => 'contact_cards', 'items' => $this->contactCards()],
            ['type' => 'contacts', 'items' => $this->contactItems()],
        ]);
    }

    public function privacy(): Response
    {
        return $this->render('Privacy Policy', 'Last Updated: May 15, 2023', [
            ['type' => 'p', 'text' => 'Loot4You ("us", "we", or "our") operates the loot4you.gg game services. This page informs you of our policies regarding the collection, use, and disclosure of personal data when you use our Service, as well as the choices you have associated with that data.'],
            ['type' => 'h', 'text' => '1. Information Collection and Use'],
            ['type' => 'p', 'text' => 'We collect various types of information to provide and improve our Service. This may include, but is not limited to, your email address, first name, last name, and phone number. By providing this information, you consent to us using this data for purposes including communication, customer support, and marketing.'],
            ['type' => 'h', 'text' => '2. Use of Data'],
            ['type' => 'p', 'text' => 'We use the collected data for various purposes, including:'],
            ['type' => 'list', 'items' => [
                'To provide and maintain our Service.',
                'To notify you about changes to our Service.',
                'To allow you to participate in interactive features of our Service when you choose to do so.',
                'To provide customer support.',
                'To gather analysis or valuable information to improve our Service.',
                'To monitor the usage of our Service.',
                'To detect, prevent, and address technical issues.',
                'To provide you with news, special offers, and general information about goods, services, and events similar to those you have previously purchased or enquired about, unless you have opted out.',
            ]],
            ['type' => 'h', 'text' => '3. Transfer of Data'],
            ['type' => 'p', 'text' => 'Your personal data is treated with the utmost respect and care. It will not be sold or shared with third parties without your explicit consent, except as outlined in this Privacy Policy or when required by law.'],
            ['type' => 'h', 'text' => '4. Security of Data'],
            ['type' => 'p', 'text' => 'The security of your data is important to us. We use commercially acceptable physical, electronic, and managerial safeguards to protect your personal data. However, no method of transmission over the Internet or electronic storage is completely secure, and we cannot guarantee absolute security.'],
            ['type' => 'h', 'text' => '5. Changes to This Privacy Policy'],
            ['type' => 'p', 'text' => 'We may update this Privacy Policy from time to time. Any changes will be posted on this page. We may also notify you via email or by placing a prominent notice on our Service before changes take effect.'],
            ['type' => 'h', 'text' => '6. Contact Us'],
            ['type' => 'p', 'text' => 'If you have any questions about this Privacy Policy, please contact us at '.self::EMAIL.'.'],
            ['type' => 'p', 'text' => 'By using loot4you.gg services, you consent to this Privacy Policy.'],
        ]);
    }

    public function terms(): Response
    {
        return $this->render('Terms & Conditions', null, [
            ['type' => 'p', 'text' => 'Please read these Terms and Conditions ("Terms", "Terms and Conditions") carefully before using the Loot4You game services operated by Loot4You ("us", "we", or "our").'],
            ['type' => 'p', 'text' => 'Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users, and others who access or use the Service.'],
            ['type' => 'p', 'text' => 'You agree to be bound by these Terms by accessing or using the Service. If you disagree with any part of the terms, you may not access the Service.'],
            ['type' => 'h', 'text' => 'Age Requirement'],
            ['type' => 'p', 'text' => 'By purchasing from Loot4You, you confirm that you are not a minor in your state or province of residence, and you have given us your consent to allow any of your minor dependents to use this site.'],
            ['type' => 'h', 'text' => 'Intellectual Property Rights'],
            ['type' => 'p', 'text' => 'All intellectual properties pertaining to any game serviced on our platform belong to the rightful owners. We do not claim any ownership over those intellectual properties.'],
            ['type' => 'p', 'text' => 'Loot4You is not endorsed by, directly affiliated with, maintained, or sponsored by any game publisher or developer. All product and company names are the registered trademarks of their original owners.'],
            ['type' => 'p', 'text' => 'The use of any trade name or trademark is for identification and reference purposes only and does not imply any association with the trademark holder of their product brand.'],
            ['type' => 'h', 'text' => 'Service Provision'],
            ['type' => 'p', 'text' => 'While we strive to provide the best game services, we do not guarantee that the desired outcomes will be achieved. The results depend on many factors, some of which are beyond our control. Therefore, we shall not be held liable for any losses that may occur while using our service.'],
            ['type' => 'h', 'text' => 'Communication'],
            ['type' => 'p', 'text' => 'All inquiries, feedback, and other forms of communication should be directed to our support team via '.self::EMAIL.'. We reserve the right to respond to your inquiries within a reasonable time.'],
            ['type' => 'h', 'text' => 'Policies'],
            ['type' => 'p', 'text' => 'You agree to comply with our Privacy Policy, Refund Policy, and Cookies Policy by using our services.'],
            ['type' => 'h', 'text' => 'Changes'],
            ['type' => 'p', 'text' => "We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will try to provide at least 30 days' notice before any new terms taking effect. What constitutes a material change will be determined at our sole discretion."],
            ['type' => 'p', 'text' => 'By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you disagree with the new terms, please stop using the Service.'],
            ['type' => 'h', 'text' => 'Contact Us'],
            ['type' => 'p', 'text' => 'If you have any questions about these Terms, please get in touch with us at '.self::EMAIL.'.'],
        ]);
    }

    public function refunds(): Response
    {
        return $this->render('Refund Policy', null, [
            ['type' => 'p', 'text' => 'At Loot4You, we strive to provide high-quality services to our customers. However, due to the nature of digital services, all purchases are considered final except in limited circumstances outlined below. Please read this policy carefully before making a purchase.'],
            ['type' => 'h', 'text' => 'No Refunds for Change of Mind'],
            ['type' => 'p', 'text' => 'Once an order has been placed and payment has been completed, refunds will not be issued if you simply change your mind, no longer want the service, purchased by mistake, or no longer need the service. By completing your purchase, you acknowledge and accept this condition.'],
            ['type' => 'h', 'text' => 'Eligibility for Refunds (Limited Cases)'],
            ['type' => 'p', 'text' => 'Refunds may only be considered in the following situations:'],
            ['type' => 'list', 'items' => [
                'Failure to deliver the service — if we fail to deliver your order as agreed, you may be eligible for a refund.',
                'Incorrect service purchased — if you purchased the wrong service or a service for the wrong platform, we will first attempt to replace it with the correct one. A refund may be issued only if a suitable replacement is not possible.',
                'Undelivered service — if we are unable to provide the service and you do not wish to receive a replacement, a refund may be granted.',
            ]],
            ['type' => 'p', 'text' => 'Please note that all refund requests are reviewed on a case-by-case basis and are not automatically approved.'],
            ['type' => 'h', 'text' => 'Refund Method'],
            ['type' => 'p', 'text' => 'All approved refunds will be processed using the original payment method. Please ensure that your payment method remains valid and accessible.'],
            ['type' => 'h', 'text' => 'Refund Processing Time'],
            ['type' => 'p', 'text' => 'Refund processing may take up to 14 days, depending on your bank or payment provider. We appreciate your patience during this time.'],
            ['type' => 'h', 'text' => 'Requesting a Refund'],
            ['type' => 'p', 'text' => 'To request a refund, contact our support team at '.self::EMAIL.' with your order details and reason for the request. We will review your case and respond accordingly.'],
            ['type' => 'h', 'text' => 'Changes to This Policy'],
            ['type' => 'p', 'text' => 'We reserve the right to modify this Refund Policy at any time. Any changes will be posted on this page and take effect immediately. Continued use of our services after changes constitutes acceptance of the updated policy.'],
            ['type' => 'h', 'text' => 'Contact Us'],
            ['type' => 'p', 'text' => 'If you have any questions regarding this Refund Policy, please contact us at '.self::EMAIL.'.'],
            ['type' => 'p', 'text' => 'By completing a purchase on Loot4You, you agree to this Refund Policy.'],
        ]);
    }

    public function paymentPolicy(): Response
    {
        return $this->render('Payment Policy', 'Last Updated: March 2024', [
            ['type' => 'h', 'text' => 'How to Purchase an Account Boost'],
            ['type' => 'lead', 'text' => 'This is a detailed guide of all events that happen while purchasing an Account Boost for PS4/5 or XBOX. Click on the "Order Now" button so you can be redirected to the checkout form. We accept payments via Stripe (Visa, Mastercard, Apple Pay, Google Pay, Amex, JCB, Klarna, EPS, PayPal, Link, Bancontact, Revolut).'],
            ['type' => 'h', 'text' => 'Step 1'],
            ['type' => 'p', 'text' => 'Select the product which you wish to purchase.'],
            ['type' => 'image', 'src' => '/assets/payment_policy/screen1.png', 'alt' => 'Step 1 — select the product you wish to purchase'],
            ['type' => 'h', 'text' => 'Step 2'],
            ['type' => 'p', 'text' => 'Select the amount of money you wish to purchase.'],
            ['type' => 'image', 'src' => '/assets/payment_policy/screen2.png', 'alt' => 'Step 2 — select the amount you wish to purchase'],
            ['type' => 'h', 'text' => 'Step 3'],
            ['type' => 'p', 'text' => 'We fill in billing and contact information, and place an order.'],
            ['type' => 'image', 'src' => '/assets/payment_policy/screen3.png', 'alt' => 'Step 3 — fill in billing and contact information, then place your order'],
            ['type' => 'h', 'text' => '1. Accepted Payment Methods'],
            ['type' => 'p', 'text' => 'Loot4You accepts a wide range of payment methods to ensure a convenient checkout experience for all customers, including:'],
            ['type' => 'list', 'items' => [
                'Credit & Debit Cards (Visa, Mastercard, American Express)',
                'PayPal',
                'Apple Pay & Google Pay',
                'Cryptocurrency (USDT, USDC, BTC, ETH)',
                'Klarna',
                'Revolut',
                'Bancontact & EPS',
            ]],
            ['type' => 'h', 'text' => '2. Payment Security'],
            ['type' => 'p', 'text' => 'All transactions on loot4you.gg are processed through secure, encrypted payment gateways. We do not store your card details on our servers. Our payment processors are fully PCI-DSS compliant to ensure the highest level of security for your financial data.'],
            ['type' => 'h', 'text' => '3. Currency'],
            ['type' => 'p', 'text' => 'All prices on loot4you.gg are displayed in USD. Currency conversion fees, if any, are handled by your bank or payment provider and are not controlled by Loot4You.'],
            ['type' => 'h', 'text' => '4. Order Confirmation'],
            ['type' => 'p', 'text' => 'Once your payment is successfully processed, you will receive an order confirmation email. Your order will appear in your account dashboard immediately, where you can track its progress and communicate with your assigned manager via live chat.'],
            ['type' => 'h', 'text' => '5. Failed Payments'],
            ['type' => 'p', 'text' => 'If your payment fails, please verify your card details and ensure sufficient funds are available. If the problem persists, try an alternative payment method or contact your bank. You can also reach our support team at '.self::EMAIL.'.'],
            ['type' => 'h', 'text' => '6. Refunds'],
            ['type' => 'p', 'text' => 'Refund requests are handled in accordance with our Refund Policy. Refunds are processed back to the original payment method within 5–10 business days, depending on your bank or payment provider.'],
            ['type' => 'p', 'text' => 'Questions about a payment? Contact us at '.self::EMAIL.'.'],
        ]);
    }

    public function shippingPolicy(): Response
    {
        return $this->render('Shipping Policy', 'Last Updated: March 2024', [
            ['type' => 'h', 'text' => '1. Digital Delivery'],
            ['type' => 'p', 'text' => 'All products sold on loot4you.gg are digital services — there are no physical items to ship. Your order is fulfilled digitally and delivered directly to your gaming account or via email, depending on the product type purchased.'],
            ['type' => 'h', 'text' => '2. Delivery Times'],
            ['type' => 'p', 'text' => 'Standard delivery time is 24–48 hours from the moment your order is confirmed and all required account information has been provided. In most cases, delivery is significantly faster.'],
            ['type' => 'p', 'text' => 'For customers who require faster delivery, we offer a Priority Delivery option — available at an additional fee — which guarantees delivery within 12 hours. This can be selected during the checkout process.'],
            ['type' => 'h', 'text' => '3. Requirements for Delivery'],
            ['type' => 'p', 'text' => 'To ensure timely delivery, customers must:'],
            ['type' => 'list', 'items' => [
                'Provide correct and complete account credentials.',
                'Disable 2-Step Verification or provide valid backup codes.',
                'Not be logged into their account while the order is being fulfilled.',
                'Turn off their console and await the completion notification.',
            ]],
            ['type' => 'h', 'text' => '4. Delays'],
            ['type' => 'p', 'text' => 'Your order may be delayed if incorrect or incomplete information is provided. Loot4You is not responsible for delays caused by the customer failing to meet the stated requirements. If your order is delayed for any other reason, please contact our support team immediately and we will resolve the issue promptly.'],
            ['type' => 'h', 'text' => '5. Order Tracking'],
            ['type' => 'p', 'text' => 'You can track the real-time status of your order directly from your account dashboard. A live chat with your assigned manager is also available, allowing you to receive updates and ask questions at any time during the fulfillment process.'],
            ['type' => 'h', 'text' => '6. Modded Accounts'],
            ['type' => 'p', 'text' => 'For modded account purchases, your complete account credentials will be sent to the email address you provided at checkout. Please ensure your email is correct before placing your order.'],
            ['type' => 'p', 'text' => 'For delivery support, contact us at '.self::EMAIL.' — available 24/7.'],
        ]);
    }

    public function cookiesPolicy(): Response
    {
        return $this->render('Cookies Policy', 'Last Updated: March 2024', [
            ['type' => 'h', 'text' => '1. What Are Cookies?'],
            ['type' => 'p', 'text' => 'Cookies are small text files placed on your device when you visit a website. They help websites remember your preferences, improve your experience, and provide analytics data to help us understand how our site is used.'],
            ['type' => 'h', 'text' => '2. How We Use Cookies'],
            ['type' => 'p', 'text' => 'loot4you.gg uses cookies for the following purposes:'],
            ['type' => 'list', 'items' => [
                'Essential Cookies — required for the website to function correctly, including authentication and session management.',
                'Preference Cookies — remember your settings such as language and currency preferences.',
                'Analytics Cookies — help us understand how visitors interact with our site so we can improve it (e.g., Google Analytics).',
                'Marketing Cookies — used to deliver relevant advertisements and track campaign performance.',
            ]],
            ['type' => 'h', 'text' => '3. Third-Party Cookies'],
            ['type' => 'p', 'text' => 'Some cookies on our site are set by trusted third-party services, including payment processors, analytics providers, and advertising networks. These third parties have their own privacy policies that govern how they use cookie data.'],
            ['type' => 'h', 'text' => '4. Managing Cookies'],
            ['type' => 'p', 'text' => 'You can control and manage cookies through your browser settings. Most browsers allow you to refuse cookies, delete existing cookies, or be notified when cookies are set. Please note that disabling essential cookies may affect the functionality of our website.'],
            ['type' => 'h', 'text' => '5. Cookie Consent'],
            ['type' => 'p', 'text' => 'When you first visit loot4you.gg, you will be presented with a cookie consent banner. By accepting, you consent to our use of cookies as described in this policy. You may withdraw your consent at any time by clearing your cookies and adjusting your browser settings.'],
            ['type' => 'h', 'text' => '6. Updates to This Policy'],
            ['type' => 'p', 'text' => 'We may update this Cookies Policy from time to time to reflect changes in technology or legal requirements. We encourage you to review this page periodically. Continued use of our website after changes have been posted constitutes your acceptance of the updated policy.'],
            ['type' => 'p', 'text' => 'Questions about our use of cookies? Contact us at '.self::EMAIL.'.'],
        ]);
    }

    /**
     * @return list<array<string, string>>
     */
    private function contactItems(): array
    {
        return [
            ['label' => 'Hours', 'value' => 'Monday – Friday, 9:00 AM – 6:00 PM · Email responses within 12 hours'],
            ['label' => 'Email', 'value' => self::EMAIL, 'href' => 'mailto:'.self::EMAIL],
            ['label' => 'Phone', 'value' => self::PHONE, 'href' => 'tel:'.str_replace(' ', '', self::PHONE)],
            ['label' => 'Address', 'value' => self::ADDRESS],
        ];
    }

    /**
     * Support channel cards shown on the Contact / Sell pages.
     *
     * @return list<array<string, mixed>>
     */
    private function contactCards(): array
    {
        return [
            [
                'key' => 'discord',
                'name' => 'Discord',
                'subtitle' => 'Join our Discord group for real-time support and community.',
                'button' => 'Discord',
                'href' => 'https://discord.gg/AyTrerusGZ',
                'external' => true,
            ],
            [
                'key' => 'email',
                'name' => 'Email',
                'subtitle' => 'Fast, friendly, and reliable support anytime.',
                'button' => 'Help',
                'href' => 'mailto:'.self::EMAIL,
                'footer' => self::EMAIL,
                'external' => false,
            ],
            [
                'key' => 'whatsapp',
                'name' => 'WhatsApp',
                'subtitle' => 'Quick, friendly, and hassle-free support on WhatsApp.',
                'button' => 'WhatsApp',
                'href' => 'https://wa.me/380730882668',
                'external' => true,
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    private function render(string $title, ?string $updated, array $blocks): Response
    {
        return Inertia::render('loot4/ContentPage', [
            'title' => $title,
            'updated' => $updated,
            'blocks' => $blocks,
        ]);
    }
}

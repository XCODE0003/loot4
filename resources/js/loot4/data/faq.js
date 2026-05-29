export const faqItems = [
  {
    question: 'What is Loot4You?',
    answer:
      'Loot4You is a trusted digital marketplace specializing in gaming services. We provide a wide selection of in-game assets, rare accounts, premium items, and more across the most popular online games. Our mission is to deliver a fast, secure, and seamless purchasing experience for gamers worldwide.',
  },
  {
    question: 'Is Loot4You legit and secure?',
    answer:
      'Yes. Loot4You is a legitimate and secure platform with a strong reputation for customer satisfaction. All our transactions are protected with industry-standard security protocols, and we work with trusted partners like Stripe and Cryptomus to ensure safe and reliable payment processing.',
  },
  {
    question: 'Why choose Loot4You over competitors?',
    answer:
      "We stand out for our wide selection of top games and digital items, fast delivery and 24/7 availability, secure and hassle-free checkout process, and friendly customer support. Whether you're looking for coins, points, accounts, or premium subscriptions, Loot4You ensures a smooth and reliable experience.",
  },
  {
    question: 'What payment methods do you accept?',
    answer:
      'We offer a flexible and secure range of payment options: All major credit and debit cards, Apple Pay & Google Pay (via Stripe), and a wide variety of cryptocurrencies (via Cryptomus). This allows our customers around the world to shop with ease, using their preferred payment method.',
  },
  {
    question: 'How fast is delivery and order processing?',
    answer:
      "Most digital products are delivered instantly or within minutes of purchase confirmation. For account-based services, delivery typically takes 1-24 hours depending on the complexity. You'll receive email notifications and can track your order status in real-time through your customer dashboard.",
  },
  {
    question: 'Can I get help if I have an issue or question?',
    answer:
      "Absolutely. Our dedicated support team is ready to assist you with any questions, concerns, or issues you may encounter. You can reach out to us through our contact page, support tickets, or directly via live chat. We're here to ensure you have a smooth and satisfying experience every step of the way.",
  },
]

// Game/account-specific FAQ shown ONLY on the dedicated /faq page.
// The home and product FAQ sections keep the curated `faqItems` list above.
// These answers are bullet lists (arrays) — `FaqSection` renders arrays as a list.
export const faqGameItems = [
  {
    question: 'What happens after I buy a boost account?',
    answer: [
      'You must provide correct information of your account.',
      'If you have 2-Step Verification enabled, provide backup codes or disable it.',
      'If we started to fulfill your order and at this time you are playing, you will be automatically kicked out of your account. Turn off your console and wait for a notification until your order is ready.',
    ],
  },
  {
    question: 'What happens after I buy a modded account?',
    answer: [
      'You will receive the full data from the modded account to the email you specified when placing the order.',
      'Enter the received data into your console and you will be taken to your account.',
      'Our support works 24/7 — in any incomprehensible situation, you can write to us at the support email or on the Discord server.',
    ],
  },
  {
    question: 'How is Loot4You different from others?',
    answer: [
      'Fast Support 24/7 — Loot4You provides the most specialized assistance in all matters without any problems.',
      'Fast Delivery — We provide the fastest delivery, and unlike competitors, we approach each customer with understanding.',
      'Track your Order — You can track your order on the site, with a live chat with your manager who will inform you in real time about the status of your order.',
      'Anti-Ban Warranty — Loot4You provides an anti-ban warranty. In any case of account blockage, a new account is provided free of charge.',
    ],
  },
  {
    question: 'Is it safe to use a boost account or a modded account?',
    answer: [
      'Yes, modded accounts are safe — but it depends on the site you buy them from.',
      'Yes, boosting your account is absolutely safe with our service. Our modders are some of the best in the field with vast experience — the chances of being banned are 0.',
      "That's why you should choose Loot4You. We give a 100% guarantee that if your account is hacked or blocked after purchasing a GTA 5 Modded account or Boost account, we will issue another account absolutely free.",
    ],
  },
  {
    question: 'Can I get banned for this?',
    answer: [
      'There has not been a single case of a ban — we use only safe methods.',
      'We give you a 100% guarantee: if something happens to your account, a new account is provided by the company for free.',
    ],
  },
  {
    question: 'What is the difference between cash/cars and cash only?',
    answer: [
      'Cash/Cars package: you get part of the amount as Deluxo cars that you sell yourself, and part as cash.',
      "Each car can be sold for $3,250,000, but Rockstar's limits allow only one sale per 18 hours. Selling more in this period will cut the price by 50% — be strategic with your sales!",
      'If you get a sale limit, wait 2–3 days and then follow the one-car-per-18-hours rule.',
      'Cash Only package: you get the entire amount in cash — no cars.',
    ],
  },
  {
    question: 'How fast will I receive my order?',
    answer: [
      'Delivery time: 24–48 hours (usually faster).',
      'You can pay extra when placing your order to receive it within 12 hours.',
      'Our team works hard to deliver as quickly as possible — it is usually much faster than the stated time frame.',
      'Your order may be delayed if you provide incorrect or incomplete account information. Please ensure all requirements are met to avoid delays.',
    ],
  },
]

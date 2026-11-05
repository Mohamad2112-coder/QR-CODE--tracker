# Web Push Notifications & PWA Installation Guide

## 1. Web Push Notification Architecture
The platform implements standard W3C Web Push using the **VAPID (Voluntary Application Server Identification)** protocol:
- No native app install required.
- No phone numbers, SMS charges, or customer accounts required.
- Cryptographically signed push requests sent directly to browser push services (Google FCM, Apple Push Notification Service, Mozilla Autopush).

---

## 2. Customer Notification Flow
1. **QR Scan**: Customer scans the QR code at the counter or table using their native smartphone camera.
2. **Landing**: The browser opens the tracking URL (`https://yourdomain.com/track/{token}`).
3. **Opt-in Prompt**: The tracking page explains: *"Enable push notifications to receive an alert on your phone the moment your order is prepared."*
4. **Subscription Association**: When granted, the browser Service Worker receives an endpoint and encryption keys (`p256dh`, `auth`), which are posted to `/api/customer/push-subscribe` and linked directly to that specific order.
5. **Kitchen Trigger**: When kitchen staff marks the order **READY**, the backend enqueues a Ready notification with a unique idempotency key (`notif_ready_{order_id}_{subscription_id}`).
6. **Delivery**: The customer's smartphone receives a vibration and notification popup: *"🔔 Order Ready - Your order #ORD-101 is ready for pickup!"*. Clicking the notification brings the tracking page back into focus.

---

## 3. Platform & Browser Compatibility Notes

Web Push requires the tracking page to be served from a secure origin (HTTPS, or localhost for development). A local WiFi address such as `http://192.168.x.x:8000` is not a secure origin, so browsers cannot enable background push from that link. On iPhone/iPad, use iOS/iPadOS 16.4 or newer and add the HTTPS site to the Home Screen before subscribing. While the tracking page is open, it checks order status and animates the pending, preparing, and ready states. Respect for reduced-motion system settings disables these animations.

| Device / Operating System | Browser Support | Notes & Requirements |
|---------------------------|-----------------|----------------------|
| **Android 8.0+**          | Chrome, Firefox, Edge, Samsung Internet | Full native Web Push support without installing to home screen. |
| **macOS & Windows**       | Chrome, Firefox, Safari (macOS 13+), Edge | Full native Web Push support. |
| **iOS / iPadOS (16.4+)**  | Mobile Safari | Apple requires adding to Home Screen (*"Add to Home Screen"*) before Web Push can be permitted. |
| **Older iOS (< 16.4)**  | Mobile Safari | Web Push not supported by Apple; in-page status updates and animations work while the page is foregrounded. |

---

## 4. VAPID Keypair Generation & Configuration
To generate your own VAPID cryptographic keys:
```bash
php bin/console.php generate-vapid
```
Add the output keys to `config/vapid.php` or your environment variables:
- `VAPID_PUBLIC_KEY`
- `VAPID_PRIVATE_KEY`
- `VAPID_SUBJECT` (e.g. `mailto:support@yourdomain.com`)

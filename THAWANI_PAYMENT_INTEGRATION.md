# Thawani Payment Integration (Laravel Backend)

This document describes the **final** integration with the Thawani payment gateway for the Student Welfare Fund backend.

## Overview

The integration consists of:
- **ThawaniService**: Handles HTTP calls to Thawani (create & retrieve session, optional refund).
- **PaymentController**: REST endpoints used by the Flutter app.
- **config/services.php**: Central configuration for Thawani keys & URLs.

## Environment Variables

Use these variables (UAT by default):

```env
THAWANI_SECRET_KEY=your_secret_key_here
THAWANI_PUBLISHABLE_KEY=your_publishable_key_here
THAWANI_BASE_URL=https://uatcheckout.thawani.om/api/v1

THAWANI_SUCCESS_URL=https://<YOUR_HTTPS_BASE>/api/v1/payments/success
THAWANI_CANCEL_URL=https://<YOUR_HTTPS_BASE>/api/v1/payments/cancel

# Optional for webhook signature verification (if provided by Thawani)
THAWANI_WEBHOOK_SECRET=

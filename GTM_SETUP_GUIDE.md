# 🎯 Google Tag Manager (GTM) Setup Guide - PyraStore

**Date:** November 10, 2025
**Purpose:** Faster attribution tracking like WordPress plugins

---

## 📋 TABLE OF CONTENTS

1. [Why GTM?](#why-gtm)
2. [Create GTM Container](#create-gtm-container)
3. [Install GTM on PyraStore](#install-gtm-on-pyrastore)
4. [Configure GA4 Tags](#configure-ga4-tags)
5. [Configure UTM Variables](#configure-utm-variables)
6. [Testing & Verification](#testing--verification)
7. [Advanced Configuration](#advanced-configuration)

---

## 🎯 WHY GTM?

### **Benefits:**
- ✅ **Faster Attribution** - Track campaigns immediately without code changes
- ✅ **Easy Management** - Add/edit tracking tags via UI (no developer needed)
- ✅ **Multi-Platform** - Manage GA4, TikTok, Meta, and more from one place
- ✅ **Tag Sequencing** - Control when tags fire and their dependencies
- ✅ **Built-in Variables** - UTM parameters, page URL, referrer automatically captured
- ✅ **Version Control** - Preview, test, and rollback changes easily

### **Current Implementation:**
PyraStore now has **both** GTM and direct gtag.js:
- **GTM:** For flexible, UI-based tracking management
- **Direct gtag.js:** As backup and for guaranteed GA4 loading

---

## 🚀 STEP 1: CREATE GTM CONTAINER

### **1. Go to Google Tag Manager**
```
https://tagmanager.google.com
```

### **2. Create Account & Container**
1. Click **"Create Account"**
2. **Account Name:** `PyraStore` (or your company name)
3. **Country:** `United Arab Emirates`
4. Click **Continue**

### **3. Container Setup**
1. **Container Name:** `events.pyramedia.info` (your domain)
2. **Target Platform:** `Web`
3. Click **Create**
4. Accept Terms of Service

### **4. Get Container ID**
You'll see a popup with installation code. The Container ID looks like:
```
GTM-XXXXXXX
```
**COPY THIS ID** - you'll need it for Step 2.

---

## 📥 STEP 2: INSTALL GTM ON PYRASTORE

### **METHOD 1: Admin Panel (Easiest)**

1. Go to: `https://events.pyramedia.info/admin/settings.php`
2. Find section: **"أدوات التتبع والتحليلات"**
3. Paste your Container ID in: **"Google Tag Manager ID"** field
   ```
   GTM-XXXXXXX
   ```
4. Click: **"💾 حفظ الإعدادات"**
5. ✅ Done! GTM is now installed

### **METHOD 2: SQL Direct**
```sql
INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES ('gtm_container_id', 'GTM-XXXXXXX')
ON DUPLICATE KEY UPDATE setting_value = 'GTM-XXXXXXX';
```

### **Verify Installation:**
1. Visit: `https://events.pyramedia.info/`
2. **Right-click** → **View Page Source**
3. Search for: `GTM-XXXXXXX`
4. Should appear in **2 places**:
   - In `<head>` section (GTM script)
   - After `<body>` tag (GTM noscript iframe)

---

## 🏷️ STEP 3: CONFIGURE GA4 TAGS

### **3.1: Add GA4 Configuration Tag**

1. In GTM, go to: **Tags** → **New**
2. **Tag Name:** `GA4 - Configuration`
3. Click **Tag Configuration** → Choose **Google Analytics: GA4 Configuration**
4. **Measurement ID:** `G-3TRP9PJ0GT`
5. **Configuration Settings** → Add these parameters:

| Parameter Name | Value |
|----------------|-------|
| `allow_google_signals` | `true` |
| `allow_ad_personalization_signals` | `true` |
| `cookie_flags` | `SameSite=None;Secure` |
| `anonymize_ip` | `false` |

6. **Triggering:** Choose **All Pages**
7. Click **Save**

### **3.2: Add GA4 Page View Event (Optional)**

GTM automatically sends page_view, but for enhanced tracking:

1. **Tags** → **New**
2. **Tag Name:** `GA4 - Page View Enhanced`
3. **Tag Configuration:** `Google Analytics: GA4 Event`
4. **Configuration Tag:** Select `GA4 - Configuration` (from dropdown)
5. **Event Name:** `page_view`
6. **Event Parameters:**

| Parameter Name | Value |
|----------------|-------|
| `page_title` | `{{Page Title}}` |
| `page_location` | `{{Page URL}}` |
| `page_referrer` | `{{Referrer}}` |

7. **Triggering:** `All Pages`
8. **Save**

### **3.3: Add Product View Event**

For product page tracking:

1. **Tags** → **New**
2. **Tag Name:** `GA4 - View Item`
3. **Tag Configuration:** `Google Analytics: GA4 Event`
4. **Configuration Tag:** `GA4 - Configuration`
5. **Event Name:** `view_item`
6. **Event Parameters:**

| Parameter Name | Value |
|----------------|-------|
| `currency` | `AED` |
| `value` | `{{DLV - Product Price}}` (see Variables section) |
| `items` | `{{DLV - Product Items Array}}` (see Variables section) |

7. **Triggering:** Create trigger for product pages
8. **Save**

---

## 🔧 STEP 4: CONFIGURE UTM VARIABLES

### **4.1: Built-in Variables**

GTM has UTM variables ready:

1. Go to: **Variables** → **Configure**
2. Under **Utilities**, check these:

- ✅ **Campaign Source** (utm_source)
- ✅ **Campaign Medium** (utm_medium)
- ✅ **Campaign Name** (utm_campaign)
- ✅ **Campaign Term** (utm_term)
- ✅ **Campaign Content** (utm_content)

3. Under **Pages**, also check:
- ✅ **Page URL**
- ✅ **Page Path**
- ✅ **Page Title**
- ✅ **Referrer**

### **4.2: Pass UTM to GA4 Configuration**

Update your **GA4 - Configuration** tag:

1. Go to **Tags** → **GA4 - Configuration**
2. Under **Configuration Settings**, add:

| Parameter Name | Value |
|----------------|-------|
| `campaign_source` | `{{Campaign Source}}` |
| `campaign_medium` | `{{Campaign Medium}}` |
| `campaign_name` | `{{Campaign Name}}` |
| `campaign_content` | `{{Campaign Content}}` |
| `campaign_term` | `{{Campaign Term}}` |

3. **Save**

**IMPORTANT:** This ensures GA4 receives UTM parameters in the **correct flat format** (`campaign_source`, not nested `campaign.source`).

---

## 🧪 STEP 5: TESTING & VERIFICATION

### **5.1: Preview Mode**

1. In GTM, click **Preview** (top right)
2. Enter: `https://events.pyramedia.info/?utm_source=test&utm_medium=cpc`
3. Click **Connect**
4. A new tab opens with **Tag Assistant**

### **5.2: Verify Tags Fire**

In Tag Assistant, check:

✅ **Page View Event:**
- `GA4 - Configuration` fires
- `GA4 - Page View Enhanced` fires (if created)
- See Variables: Campaign Source = "test", Campaign Medium = "cpc"

✅ **Product View Event:**
- Navigate to product page
- `GA4 - View Item` fires
- Check item data is captured

### **5.3: Real-Time Testing**

1. Open: https://analytics.google.com/
2. Go to: **Reports → Real-time**
3. Visit: `https://events.pyramedia.info/?utm_source=gtm_test&utm_medium=cpc&utm_campaign=launch`
4. Check Real-Time report shows:
   - **Source:** `gtm_test`
   - **Medium:** `cpc`
   - **Campaign:** `launch`

**Success = NOT showing "(direct) / (none)"**

### **5.4: Browser Console Check**

With GTM installed, console should show:

```javascript
// From direct gtag.js (backup)
✅ GA4 configured with UTM (CORRECT FORMAT): {...}

// GTM loads in parallel
Google Tag Manager loaded
```

---

## 🔄 STEP 6: PUBLISH CONTAINER

### **When you're ready:**

1. Click **Submit** (top right in GTM)
2. **Version Name:** `Initial Setup - GA4 + UTM`
3. **Version Description:**
   ```
   - Added GA4 Configuration tag (G-3TRP9PJ0GT)
   - Configured UTM parameter passing
   - Added page_view and view_item events
   ```
4. Click **Publish**

### **Version History:**
All changes are saved. You can:
- View previous versions
- Compare changes
- Rollback if needed

---

## ⚡ ADVANCED CONFIGURATION

### **7.1: Enhanced E-commerce Tracking**

For complete e-commerce tracking, add these events:

| Event | When to Fire | Parameters |
|-------|-------------|------------|
| `view_item` | Product page load | item_id, item_name, price |
| `add_to_cart` | Not applicable (affiliate) | - |
| `begin_checkout` | Affiliate link click | value, currency, items |
| `purchase` | Tracked on Amazon | - |

### **7.2: Custom Event - Affiliate Click**

Track when users click "Buy Now":

1. **Variables** → **New** → **User-Defined Variable**
2. **Variable Type:** `Data Layer Variable`
3. **Data Layer Variable Name:** `productId`
4. **Variable Name:** `DLV - Product ID`

Repeat for:
- `DLV - Product Title` → `productTitle`
- `DLV - Product Price` → `productPrice`
- `DLV - Product Category` → `productCategory`

Then create trigger and tag for affiliate clicks.

### **7.3: Cross-Domain Tracking**

If tracking to Amazon:

1. **GA4 - Configuration** tag
2. Add parameter:
   - **Parameter Name:** `linker`
   - **Value:**
     ```json
     {
       "domains": ["amazon.ae", "events.pyramedia.info"]
     }
     ```

### **7.4: Scroll Tracking**

Track user engagement:

1. **Variables** → Enable: **Scroll Depth Threshold**
2. Create trigger: **Scroll Depth** = 25%, 50%, 75%, 90%
3. Create tag: **GA4 Event** → `scroll` event

---

## 🎯 GTM + DIRECT GTAG.JS STRATEGY

### **Why Both?**

PyraStore uses **dual tracking strategy**:

1. **GTM (Primary):** Flexible, UI-based, fast changes
2. **Direct gtag.js (Backup):** Guaranteed tracking if GTM fails

### **Load Order:**

```
Page Loads
    ↓
1. GTM Script (head) - Fast async load
    ↓
2. Direct gtag.js - Backup initialization
    ↓
3. UTM Tracker - Session persistence
    ↓
4. Enhanced Tracking - Custom events
```

### **How They Work Together:**

- **GTM fires first** (if configured) → Faster attribution
- **Direct gtag.js fires** as backup → Guaranteed tracking
- GA4 deduplicates automatically (same measurement ID)
- **Best of both worlds:** Speed + Reliability

### **Configuration Priority:**

If both are active:
1. GTM UTM parameters take precedence (faster)
2. Direct gtag.js provides fallback
3. SessionStorage ensures persistence across pages

---

## 📊 MAINTENANCE & BEST PRACTICES

### **Regular Tasks:**

**Weekly:**
- ✅ Check GTM Preview mode for new changes
- ✅ Verify GA4 Real-Time data flows correctly

**Monthly:**
- ✅ Review GTM tag firing patterns
- ✅ Check for tag conflicts or duplicates
- ✅ Audit unused variables and triggers

**Before Major Changes:**
- ✅ Create new GTM version with clear description
- ✅ Test in Preview mode thoroughly
- ✅ Check GA4 Real-Time during deployment

### **Common Issues:**

**Problem:** Tags not firing
**Solution:** Check Preview mode, verify triggers, check page URL filters

**Problem:** Duplicate page_view events
**Solution:** Disable either GTM page_view or direct gtag page_view

**Problem:** UTM parameters not appearing
**Solution:** Verify Campaign variables are configured, check parameter names (flat format)

**Problem:** GTM not loading
**Solution:** Check Container ID in database, verify GTM code in page source

---

## 📋 QUICK CHECKLIST

### **Installation Complete:**
- [ ] GTM Container created (GTM-XXXXXXX)
- [ ] Container ID added to admin panel
- [ ] GTM script visible in page source (head)
- [ ] GTM noscript visible in page source (body)

### **Tags Configured:**
- [ ] GA4 Configuration tag (G-3TRP9PJ0GT)
- [ ] Page view event (optional)
- [ ] Product view event
- [ ] UTM parameters mapped

### **Variables Set Up:**
- [ ] Campaign variables enabled
- [ ] Page variables enabled
- [ ] Custom data layer variables (if needed)

### **Testing Complete:**
- [ ] Preview mode tested with UTM URLs
- [ ] Real-Time shows correct source/medium
- [ ] Console shows no errors
- [ ] Tags fire on all key pages

### **Published:**
- [ ] Version description written
- [ ] Container published
- [ ] Monitoring active for 24-48 hours

---

## 🆘 SUPPORT & RESOURCES

### **GTM Documentation:**
- Official Guide: https://support.google.com/tagmanager
- GA4 Configuration: https://support.google.com/analytics/answer/9304153

### **PyraStore Tracking Files:**
- `includes/tracking.php` - GTM + direct gtag.js code
- `admin/settings.php` - GTM Container ID field
- `assets/js/utm-tracker.js` - UTM persistence
- `assets/js/tracking.js` - Enhanced event tracking

### **Database:**
- Table: `site_settings`
- Key: `gtm_container_id`
- Value: `GTM-XXXXXXX`

---

## ✅ FINAL ARCHITECTURE

```
┌─────────────────────────────────────────────────┐
│ PAGE LOAD                                       │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ includes/tracking.php                           │
├─────────────────────────────────────────────────┤
│ 1. GTM Container Script (if ID configured)     │
│    → Loads: gtm.js?id=GTM-XXXXXXX              │
│    → Pushes to: dataLayer                      │
│                                                 │
│ 2. TikTok Pixel (if ID configured)             │
│    → ttq.load('D48BPTRC77U6T00COPEG')          │
│                                                 │
│ 3. Meta Pixel (if ID configured)               │
│    → fbq('init', '...')                        │
│                                                 │
│ 4. Direct Google Analytics (backup)            │
│    → gtag('config', 'G-3TRP9PJ0GT')           │
│    → UTM extraction & flat parameter format    │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ GTM CONTAINER (if configured)                   │
├─────────────────────────────────────────────────┤
│ • GA4 Configuration Tag                        │
│ • GA4 Event Tags (page_view, view_item, etc.) │
│ • UTM Variables (campaign_source, etc.)        │
│ • Custom Triggers                              │
│ • Data Layer Variables                         │
└────────────────┬────────────────────────────────┘
                 ↓
┌─────────────────────────────────────────────────┐
│ GOOGLE ANALYTICS 4                              │
├─────────────────────────────────────────────────┤
│ Measurement ID: G-3TRP9PJ0GT                   │
│ Receives events from:                          │
│ • GTM (primary - faster)                       │
│ • Direct gtag.js (backup - reliable)          │
│ • Deduplicates automatically                   │
└─────────────────────────────────────────────────┘
```

---

**Last Updated:** November 10, 2025
**Status:** ✅ GTM infrastructure ready for configuration
**Next Step:** Create GTM container and add ID to admin panel

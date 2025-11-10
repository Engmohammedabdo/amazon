# 🔄 GA4 MEASUREMENT ID UPDATE GUIDE

**Date:** November 10, 2025
**New Measurement ID:** `G-3TRP9PJ0GT`
**Replaces:** `G-GZTBRKFFGT`, `G-HRSW9RC061`

---

## 📊 SUMMARY

This is a **complete GA4 refresh** with a new clean property setup. The measurement ID is stored in the **database** (`site_settings` table), not hardcoded in any files.

### ✅ What's Been Updated

1. **DATABASE_SCHEMA.sql** - Default value updated (line 99)
   - Old: `('google_analytics_id', '')`
   - New: `('google_analytics_id', 'G-3TRP9PJ0GT')`

2. **Created Update Scripts:**
   - `update_ga4_id.sql` - SQL script for database update
   - `update_ga4_id.php` - PHP script for programmatic update

3. **Verified Clean State:**
   - ✅ No hardcoded old measurement IDs found
   - ✅ No GTM references found
   - ✅ All tracking logic preserved (UTM parameters, TikTok, Meta)
   - ✅ `includes/tracking.php` uses dynamic `$googleAnalyticsId` variable

---

## 🚀 HOW TO APPLY THE UPDATE

### **METHOD 1: Admin Panel (Easiest - Recommended)**

1. Go to: `https://events.pyramedia.info/admin/settings.php`
2. Find section: **"أدوات التتبع والتحليلات"** (Tracking Tools)
3. In the **"Google Analytics ID"** field, paste: `G-3TRP9PJ0GT`
4. Click: **"💾 حفظ الإعدادات"** (Save Settings)
5. Done! ✅

### **METHOD 2: Run PHP Update Script**

```bash
# From browser (if on localhost):
https://events.pyramedia.info/update_ga4_id.php?force=1

# From command line:
php update_ga4_id.php

# After successful update, delete the script:
rm update_ga4_id.php
```

### **METHOD 3: SQL Direct Update**

```bash
# MySQL Command Line:
mysql -u your_username -p pyrastore_db < update_ga4_id.sql

# Or via phpMyAdmin:
# 1. Login to phpMyAdmin
# 2. Select 'pyrastore_db' database
# 3. Click 'SQL' tab
# 4. Paste this query:

INSERT INTO `site_settings` (`setting_key`, `setting_value`)
VALUES ('google_analytics_id', 'G-3TRP9PJ0GT')
ON DUPLICATE KEY UPDATE setting_value = 'G-3TRP9PJ0GT';
```

---

## 🔍 HOW THE TRACKING WORKS

### **Dynamic Loading System**

```php
// includes/tracking.php (lines 12-14)
$googleAnalyticsId = getSetting('google_analytics_id'); // Reads from database

// Lines 50 & 93: Uses the variable
gtag('config', '<?php echo htmlspecialchars($googleAnalyticsId, ENT_QUOTES, 'UTF-8'); ?>');
```

### **No Hardcoded IDs**

The system uses:
- Database-driven configuration
- Admin panel for easy updates
- No file editing required

### **Tracking Features Preserved**

✅ **TikTok Pixel** - `D48BPTRC77U6T00COPEG`
✅ **Meta Pixel** - Dynamic from database
✅ **Google Analytics 4** - Dynamic from database
✅ **UTM Parameter Tracking** - Correct GA4 format:
   - `campaign_source` (not nested `campaign.source`)
   - `campaign_medium` (not nested `campaign.medium`)
   - `campaign_name` (not nested `campaign.name`)

---

## 🧪 VERIFICATION & TESTING

### **Step 1: Verify Update Applied**

```sql
-- Run this query to check current value:
SELECT setting_key, setting_value
FROM site_settings
WHERE setting_key = 'google_analytics_id';

-- Expected result:
-- google_analytics_id | G-3TRP9PJ0GT
```

### **Step 2: Test UTM Tracking**

1. **Open test URL:**
   ```
   https://events.pyramedia.info/?utm_source=test&utm_medium=cpc&utm_campaign=spring
   ```

2. **Check browser console:**
   - Should see: `✅ GA4 configured with UTM (CORRECT FORMAT):`
   - Should display:
     ```javascript
     {
       campaign_source: 'test',
       campaign_medium: 'cpc',
       campaign_name: 'spring'
     }
     ```

3. **Check GA4 Real-Time:**
   - Open: https://analytics.google.com/
   - Go to: **Reports → Real-time**
   - Should see traffic with:
     - **Source:** `test`
     - **Medium:** `cpc`
     - **Campaign:** `spring`
   - Should **NOT** show as `(direct) / (none)`

### **Step 3: Verify Page Tracking**

1. Visit homepage: `https://events.pyramedia.info/`
2. Check console for:
   - `✅ Enhanced tracking initialized`
   - TikTok pixel loaded
   - Meta pixel loaded (if configured)
   - GA4 loaded with new ID

3. Visit product page
4. Check console for:
   - `view_item` event
   - Product tracking with correct parameters

---

## 📋 FILE CHANGES SUMMARY

| File | Change | Status |
|------|--------|--------|
| `DATABASE_SCHEMA.sql` | Line 99: Default GA ID → `G-3TRP9PJ0GT` | ✅ Updated |
| `includes/tracking.php` | Uses dynamic `$googleAnalyticsId` variable | ✅ No change needed |
| `update_ga4_id.sql` | SQL update script | ✅ Created |
| `update_ga4_id.php` | PHP update script | ✅ Created |
| `GA4_UPDATE_INSTRUCTIONS.md` | This documentation | ✅ Created |

### **NO CHANGES NEEDED:**
- ❌ `includes/tracking.php` - Already uses dynamic variable
- ❌ `assets/js/utm-tracker.js` - No hardcoded IDs
- ❌ `assets/js/tracking.js` - No hardcoded IDs
- ❌ `index.php` - No hardcoded IDs
- ❌ `product.php` - No hardcoded IDs

---

## 🎯 WHAT HAPPENS AFTER UPDATE

### **Immediate Effects:**

1. **New GA4 property** starts receiving data
2. **UTM parameters** tracked correctly with flat GA4 format
3. **Old IDs** no longer receive data
4. **Fresh start** with clean tracking setup

### **Tracking Chain:**

```
1. User visits: /?utm_source=test&utm_medium=cpc
2. tracking.php extracts UTM parameters (lines 57-62)
3. gtag('config') called with campaign_source, campaign_medium, campaign_name (lines 77-81)
4. GA4 receives event with correct attribution
5. Real-Time report shows: test / cpc
```

### **Console Output:**

```javascript
✅ GA4 configured with UTM (CORRECT FORMAT): {
  campaign_source: 'test',
  campaign_medium: 'cpc',
  campaign_name: 'spring',
  campaign_content: undefined,
  campaign_term: undefined
}
```

---

## ⚠️ IMPORTANT NOTES

### **Security:**
- 🔒 `update_ga4_id.php` has security check (localhost or CLI only)
- 🔒 Use `?force=1` parameter if running from non-localhost
- 🗑️ **Delete update scripts after use:** `rm update_ga4_id.php`

### **Backup:**
Before applying updates, optionally backup current setting:
```sql
SELECT * FROM site_settings WHERE setting_key = 'google_analytics_id';
```

### **Rollback:**
If needed to revert (not recommended):
```sql
UPDATE site_settings
SET setting_value = 'OLD_ID_HERE'
WHERE setting_key = 'google_analytics_id';
```

---

## 📞 SUPPORT

### **Verification Checklist:**
- [ ] Database value updated to `G-3TRP9PJ0GT`
- [ ] Admin panel shows new ID
- [ ] Test URL shows correct console logs
- [ ] GA4 Real-Time shows traffic (not as direct)
- [ ] UTM parameters appear in GA reports
- [ ] Update scripts deleted after use

### **Common Issues:**

**Problem:** GA still shows old ID
**Solution:** Clear browser cache, check database value

**Problem:** UTM parameters not working
**Solution:** Verify console shows "CORRECT FORMAT", check GA4 Real-Time

**Problem:** No traffic in GA4
**Solution:** Verify measurement ID in database, check gtag.js loads

---

## ✅ FINAL STATE

After update, `includes/tracking.php` will load:

1. ✅ **TikTok Pixel:** `D48BPTRC77U6T00COPEG`
2. ✅ **Meta Pixel:** (if configured in database)
3. ✅ **Google Analytics:** `G-3TRP9PJ0GT` (from database)
4. ✅ **UTM Tracking:** Correct GA4 flat parameter format
5. ✅ **Event Tracking:** Product views, checkout intents
6. ✅ **Console Logging:** Detailed debug information

**All tracking infrastructure intact. Only the measurement ID changes.**

---

**Last Updated:** November 10, 2025
**Status:** ✅ Ready for deployment

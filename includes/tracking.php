<?php
/**
 * Site-Wide Tracking Pixels
 * Dynamically loads TikTok, Meta, and Google Analytics based on admin settings
 */

if (!function_exists('getSetting')) {
    require_once __DIR__ . '/functions.php';
}

// Get tracking IDs from database
$tiktokPixelId = getSetting('tiktok_pixel_id');
$metaPixelId = getSetting('meta_pixel_id');
$googleAnalyticsId = getSetting('google_analytics_id');
?>

<?php if (!empty($tiktokPixelId)): ?>
<!-- TikTok Pixel Code -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};

  ttq.load('<?php echo htmlspecialchars($tiktokPixelId, ENT_QUOTES, 'UTF-8'); ?>');
  ttq.page();
}(window, document, 'ttq');
</script>
<?php endif; ?>

<?php if (!empty($metaPixelId)): ?>
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo htmlspecialchars($metaPixelId, ENT_QUOTES, 'UTF-8'); ?>');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=<?php echo htmlspecialchars($metaPixelId, ENT_QUOTES, 'UTF-8'); ?>&ev=PageView&noscript=1"
/></noscript>
<?php endif; ?>

<?php if (!empty($googleAnalyticsId)): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($googleAnalyticsId, ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo htmlspecialchars($googleAnalyticsId, ENT_QUOTES, 'UTF-8'); ?>', {
    'cookie_flags': 'SameSite=None;Secure',
    'allow_google_signals': true,
    'allow_ad_personalization_signals': true,
    'linker': {
      'domains': ['events.pyramedia.info']
    }
  });

  // Extract UTM parameters from URL for immediate GA attribution
  (function() {
    const urlParams = new URLSearchParams(window.location.search);
    const utmSource = urlParams.get('utm_source');
    const utmMedium = urlParams.get('utm_medium');
    const utmCampaign = urlParams.get('utm_campaign');
    const utmContent = urlParams.get('utm_content');
    const utmTerm = urlParams.get('utm_term');

    // If UTM params exist, reconfigure GA with campaign data
    if (utmSource || utmMedium || utmCampaign) {
      gtag('config', '<?php echo htmlspecialchars($googleAnalyticsId, ENT_QUOTES, 'UTF-8'); ?>', {
        'cookie_flags': 'SameSite=None;Secure',
        'allow_google_signals': true,
        'allow_ad_personalization_signals': true,
        'linker': {
          'domains': ['events.pyramedia.info']
        },
        'campaign': {
          'source': utmSource || '(not set)',
          'medium': utmMedium || '(not set)',
          'name': utmCampaign || '(not set)',
          'content': utmContent || '',
          'term': utmTerm || ''
        }
      });

      console.log('✅ GA configured with UTM:', {
        source: utmSource,
        medium: utmMedium,
        campaign: utmCampaign,
        content: utmContent,
        term: utmTerm
      });
    }
  })();
</script>
<?php endif; ?>

<!-- Universal Event Tracking Functions -->
<script>
/**
 * Track product view across all platforms
 * @param {string} productId - Product ID
 * @param {string} productName - Product name
 * @param {number} price - Product price
 */
window.trackProductView = function(productId, productName, price) {
  // TikTok Pixel
  if (typeof ttq !== 'undefined') {
    ttq.track('ViewContent', {
      content_id: productId,
      content_type: 'product',
      content_name: productName,
      value: price,
      currency: 'AED'
    });
  }

  // Meta Pixel
  if (typeof fbq !== 'undefined') {
    fbq('track', 'ViewContent', {
      content_ids: [productId],
      content_type: 'product',
      content_name: productName,
      value: price,
      currency: 'AED'
    });
  }

  // Google Analytics
  if (typeof gtag !== 'undefined') {
    gtag('event', 'view_item', {
      currency: 'AED',
      value: price,
      items: [{
        item_id: productId,
        item_name: productName,
        price: price
      }]
    });
  }
};

/**
 * Track checkout intent (affiliate link click)
 * @param {string} productId - Product ID
 * @param {string} productName - Product name
 * @param {number} price - Product price
 */
window.trackCheckoutIntent = function(productId, productName, price) {
  // TikTok Pixel
  if (typeof ttq !== 'undefined') {
    ttq.track('InitiateCheckout', {
      content_id: productId,
      content_name: productName,
      value: price,
      currency: 'AED'
    });
  }

  // Meta Pixel
  if (typeof fbq !== 'undefined') {
    fbq('track', 'InitiateCheckout', {
      content_ids: [productId],
      content_name: productName,
      value: price,
      currency: 'AED'
    });
  }

  // Google Analytics
  if (typeof gtag !== 'undefined') {
    gtag('event', 'begin_checkout', {
      currency: 'AED',
      value: price,
      items: [{
        item_id: productId,
        item_name: productName,
        price: price
      }]
    });
  }
};
</script>

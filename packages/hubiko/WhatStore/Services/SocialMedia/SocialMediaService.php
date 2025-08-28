<?php

namespace Hubiko\WhatStore\Services\SocialMedia;

class SocialMediaService
{
    /**
     * Get the pixel tracking code for a specific platform.
     *
     * @param string $platform
     * @param string $pixelId
     * @return string|null
     */
    public function getPixelCode($platform, $pixelId)
    {
        if (empty($pixelId)) {
            return null;
        }

        switch ($platform) {
            case 'facebook':
                return $this->getFacebookPixelCode($pixelId);
            case 'twitter':
                return $this->getTwitterPixelCode($pixelId);
            case 'linkedin':
                return $this->getLinkedInPixelCode($pixelId);
            case 'pinterest':
                return $this->getPinterestPixelCode($pixelId);
            case 'google-analytics':
                return $this->getGoogleAnalyticsCode($pixelId);
            case 'google-adwords':
                return $this->getGoogleAdwordsCode($pixelId);
            case 'snapchat':
                return $this->getSnapchatPixelCode($pixelId);
            case 'tiktok':
                return $this->getTikTokPixelCode($pixelId);
            default:
                return null;
        }
    }

    /**
     * Get Facebook Pixel tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getFacebookPixelCode($pixelId)
    {
        return "
            <!-- Facebook Pixel Code -->
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{$pixelId}');
                fbq('track', 'PageView');
            </script>
            <noscript>
                <img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1'/>
            </noscript>
            <!-- End Facebook Pixel Code -->
        ";
    }

    /**
     * Get Twitter Pixel tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getTwitterPixelCode($pixelId)
    {
        return "
            <!-- Twitter Pixel Code -->
            <script>
            !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
            },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='https://static.ads-twitter.com/uwt.js',
            a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
            twq('config','{$pixelId}');
            </script>
            <!-- End Twitter Pixel Code -->
        ";
    }

    /**
     * Get LinkedIn Pixel tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getLinkedInPixelCode($pixelId)
    {
        return "
            <!-- LinkedIn Pixel Code -->
            <script type='text/javascript'>
                _linkedin_data_partner_id = {$pixelId};
            </script>
            <script type='text/javascript'>
                (function () {
                    var s = document.getElementsByTagName('script')[0];
                    var b = document.createElement('script');
                    b.type = 'text/javascript';
                    b.async = true;
                    b.src = 'https://snap.licdn.com/li.lms-analytics/insight.min.js';
                    s.parentNode.insertBefore(b, s);
                })();
            </script>
            <noscript>
                <img height='1' width='1' style='display:none;' alt='' src='https://dc.ads.linkedin.com/collect/?pid={$pixelId}&fmt=gif'/>
            </noscript>
            <!-- End LinkedIn Pixel Code -->
        ";
    }

    /**
     * Get Pinterest Pixel tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getPinterestPixelCode($pixelId)
    {
        return "
            <!-- Pinterest Tag -->
            <script>
            !function(e){if(!window.pintrk){window.pintrk = function () {
            window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
              n=window.pintrk;n.queue=[],n.version='3.0';var
              t=document.createElement('script');t.async=!0,t.src=e;var
              r=document.getElementsByTagName('script')[0];
              r.parentNode.insertBefore(t,r)}}('https://s.pinimg.com/ct/core.js');
            pintrk('load', '{$pixelId}');
            pintrk('page');
            </script>
            <noscript>
            <img height='1' width='1' style='display:none;' alt=''
              src='https://ct.pinterest.com/v3/?event=init&tid={$pixelId}&pd[em]=<hashed_email_address>&noscript=1' />
            </noscript>
            <!-- End Pinterest Tag -->
        ";
    }

    /**
     * Get Google Analytics tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getGoogleAnalyticsCode($pixelId)
    {
        return "
            <!-- Google Analytics -->
            <script async src='https://www.googletagmanager.com/gtag/js?id={$pixelId}'></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{$pixelId}');
            </script>
            <!-- End Google Analytics -->
        ";
    }

    /**
     * Get Google AdWords tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getGoogleAdwordsCode($pixelId)
    {
        return "
            <!-- Google AdWords -->
            <script type='text/javascript'>
                var google_conversion_id = '{$pixelId}';
                var google_custom_params = window.google_tag_params;
                var google_remarketing_only = true;
            </script>
            <script type='text/javascript' src='//www.googleadservices.com/pagead/conversion.js'>
            </script>
            <noscript>
                <div style='display:inline;'>
                    <img height='1' width='1' style='border-style:none;' alt='' src='//googleads.g.doubleclick.net/pagead/viewthroughconversion/{$pixelId}/?guid=ON&amp;script=0'/>
                </div>
            </noscript>
            <!-- End Google AdWords -->
        ";
    }

    /**
     * Get Snapchat Pixel tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getSnapchatPixelCode($pixelId)
    {
        return "
            <!-- Snapchat Pixel Code -->
            <script type='text/javascript'>
                (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
                {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
                a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
                r.src=n;var u=t.getElementsByTagName(s)[0];
                u.parentNode.insertBefore(r,u);})(window,document,
                'https://sc-static.net/scevent.min.js');
                
                snaptr('init', '{$pixelId}', {
                'user_email': '__INSERT_USER_EMAIL__'
                });
                
                snaptr('track', 'PAGE_VIEW');
            </script>
            <!-- End Snapchat Pixel Code -->
        ";
    }

    /**
     * Get TikTok Pixel tracking code.
     *
     * @param string $pixelId
     * @return string
     */
    protected function getTikTokPixelCode($pixelId)
    {
        return "
            <!-- TikTok Pixel Code -->
            <script>
                !function (w, d, t) {
                  w.TiktokAnalyticsObject=t;
                  var ttq=w[t]=w[t]||[];
                  ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
                  for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;
                 n++)ttq.setAndDefer(e,ttq.methods[n]);
                 return e},ttq.load=function(e,n){var i='https://analytics.tiktok.com/i18n/pixel/events.js';
                ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};
                var o=document.createElement('script');
                o.type='text/javascript',o.async=!0,o.src=i+'?sdkid='+e+'&lib='+t;
                var a=document.getElementsByTagName('script')[0];
                a.parentNode.insertBefore(o,a)};
                
                  ttq.load('{$pixelId}');
                  ttq.page();
                }(window, document, 'ttq');
            </script>
            <!-- End TikTok Pixel Code -->
        ";
    }

    /**
     * Generate social sharing buttons HTML.
     *
     * @param string $url
     * @param string $title
     * @param string $description
     * @param string|null $image
     * @return string
     */
    public function getSocialSharingButtons($url, $title, $description = '', $image = null)
    {
        $html = '<div class="social-sharing">';
        
        // Facebook share button
        $html .= '<a href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url) . '" target="_blank" class="share-btn facebook">';
        $html .= '<i class="fab fa-facebook"></i>';
        $html .= '</a>';
        
        // Twitter share button
        $html .= '<a href="https://twitter.com/intent/tweet?url=' . urlencode($url) . '&text=' . urlencode($title) . '" target="_blank" class="share-btn twitter">';
        $html .= '<i class="fab fa-twitter"></i>';
        $html .= '</a>';
        
        // LinkedIn share button
        $html .= '<a href="https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode($url) . '&title=' . urlencode($title) . '&summary=' . urlencode($description) . '" target="_blank" class="share-btn linkedin">';
        $html .= '<i class="fab fa-linkedin"></i>';
        $html .= '</a>';
        
        // Pinterest share button (only if image is provided)
        if ($image) {
            $html .= '<a href="https://pinterest.com/pin/create/button/?url=' . urlencode($url) . '&media=' . urlencode($image) . '&description=' . urlencode($title) . '" target="_blank" class="share-btn pinterest">';
            $html .= '<i class="fab fa-pinterest"></i>';
            $html .= '</a>';
        }
        
        // WhatsApp share button
        $html .= '<a href="https://api.whatsapp.com/send?text=' . urlencode($title . ' - ' . $url) . '" target="_blank" class="share-btn whatsapp">';
        $html .= '<i class="fab fa-whatsapp"></i>';
        $html .= '</a>';
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get device type from user agent.
     *
     * @param string $userAgent
     * @return string
     */
    public function getDeviceType($userAgent)
    {
        $mobileRegex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tabletRegex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        
        if (preg_match($mobileRegex, $userAgent)) {
            return 'mobile';
        } elseif (preg_match($tabletRegex, $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }
} 
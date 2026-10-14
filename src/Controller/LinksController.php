<?php
namespace App\Controller;

use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

define('SAFELINK_BASE_URLS', [
    'https://naturecycle.net',
]);
define('PROXYCHECK_KEY', '51p309-4713s5-857894-5n56uw');
define('SAFELINK_SECRET_KEY', 'uYG0OqIkynEruQlFBnh73NmDMEJGeZXlD5Ff7kRptSoSrGXWot5zz62Oim1XqW2c');

/**
 * @property \App\Model\Table\LinksTable $Links
 * @property \App\Controller\Component\CaptchaComponent $Captcha
 */
class LinksController extends FrontController
{
    
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Security');
        $this->loadComponent('Cookie');
        $this->loadComponent('Captcha');

        $this->Security->setConfig('unlockedFields', [
            'token','answer'
        ]);
        $this->Security->setConfig('unlockedActions', ['validate','safelinkCallback']);
    }
    private function dc(array $domains, int $steps): array {
        return compact('domains', 'steps');
    }
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->setLayout('front');
        $this->Auth->allow(['shorten', 'view', 'view2', 'go', 'popad', 'validate', 'safelinkCallback']);
    }
    public function view($alias = null)
    {
        $this->setResponse(
            $this->getResponse()
                ->withHeader('X-Frame-Options', 'SAMEORIGIN')
                ->withHeader('X-Robots-Tag', 'noindex')
        );

        if (!$alias) {
            throw new NotFoundException(__('404 Not Found'));
        }

        /**
         * @var \App\Model\Entity\Link $link
         */
        $link = $this->Links->find()
            //->contain(['Users'])
            ->contain([
                'Users' => [
                    'fields' => ['id', 'username', 'status', 'disable_earnings'],
                ],
            ])
            ->where([
                'Links.alias' => $alias,
                'Links.status <>' => 3,
                'Users.status' => 1,
            ])
            ->first();

        if (!$link) {
            throw new NotFoundException(__('404 Not Found'));
        }
        $this->set('link', $link);
        
        $uip = get_ip();
        if(isset($_GET['te']) && isset($_GET['step']) && isset($_GET['click'])) {
            $domain_config = [
                'config0' => [
                    'step1' => [
                        $_GET['te'],
                    ],
                    'step1adclick' => $_GET['click'],
                    'step2' => [
                        $_GET['te'],
                    ],
                    'step2adclick' => $_GET['click'],
                    'step3' => [
                        $_GET['te'],
                    ],
                    'step3adclick' => $_GET['click'],
                ],
            ];
            
            $random_config_key = array_rand($domain_config);
            $selected_config = $domain_config[$random_config_key];
            
            $selected_domain = $selected_config['step'.$_GET['step']][array_rand($selected_config['step'.$_GET['step']])];
            $host = parse_url('https://' . $selected_domain, PHP_URL_HOST);
            
            $urlss = 'https://' . $selected_domain . '/?post_id=' . $link->alias . '&var=' . hash('sha256', $link->alias . $host);
            $muser = $this->Links->Users->find()
                    ->select(['id', 'username', 'blocked_countries'])
                    ->where(['Users.id' => $link->user_id])
                    ->first();
                
            $blockedCountries = $muser->blocked_countries ?? '';
            $ldata = [
                'safelink_secret' => '8BA653F1FE8CBE57B176787C1A42',
                'data' => [
                    'alias' => $link->alias,
                    'ip' => $uip,
                    'domain' => $selected_domain,
                    'config' => $selected_config,
                    'step' => 1,
                    'is_limit' => 0,
                    'blocked' => $blockedCountries
                ]
            ];
            
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0';
            
            $ch = curl_init($urlss);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($ldata));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            exit;
        }
                $ipInfo = $this->proxycheckLookup($uip);
                if ($ipInfo && (($ipInfo['proxy'] ?? 'no') === 'yes' || (int)($ipInfo['risk'] ?? 0) > 10)) {
                    header("Location: https://bitcotasks.com/ads-no.php");
                    exit;
                }

                $countr = $_SERVER["HTTP_CF_IPCOUNTRY"];
                
                $muser = $this->Links->Users->find()
                    ->select(['id', 'username', 'blocked_countries'])
                    ->where(['Users.id' => $link->user_id])
                    ->first();
                
                $blockedCountries = $muser->blocked_countries ?? '';
                
                $isBlocked = in_array($countr, explode(',', $blockedCountries));
                
                if ($isBlocked) {
                    header("Location: https://earnow.online/blocked.html");
                    exit;
                }
                
                $countrie = ["RU","IR","AF","KP","LY","IQ"];
                if(in_array($countr,$countrie)) {
                    header("Location: https://xj.leavycranes.com/i8FDkvA7T26F0g2/119298");
                    exit;
                }

                $list = [
                    $this->dc(['coinstrend.net/blog'], 4),
                    $this->dc(['plantsguide.net'], 4),
                    $this->dc(['giftmagic.net'], 4),
                    $this->dc(['cryptowidgets.net/blog'], 4),
                    $this->dc(['coinsvalue.net/blog'], 4),
                    $this->dc(['wanderjourney.net'], 4),
                    $this->dc(['mythnest.com'], 4),
                    $this->dc(['techiephone.com'], 4),
                    $this->dc(['webfreetools.net/blog'], 4),
                    $this->dc(['funplayarcade.com/blog'], 4),
                    $this->dc(['brewmasterly.com'], 4),
                    $this->dc(['retrocove.net'], 4),
                    $this->dc(['gizmoera.com'], 4),
                    $this->dc(['cookinguide.net'], 4),
                    $this->dc(['tvseriescentral.net'], 4),
                    $this->dc(['coinscap.info/blog'], 4),
                    $this->dc(['petsguide.net'], 4),
                    $this->dc(['freeoseocheck.com/blog'], 4),
                    $this->dc(['coinsrise.net/blog'], 4),
                    $this->dc(['countriesguide.net'], 4),
                    $this->dc(['insurancexguide.com'], 4),
                    $this->dc(['ecofriendlyz.com'], 4),
                    $this->dc(['vaultfind.net'], 4),
                    $this->dc(['melodyspot.net'], 4),
                ];
                
                $domain_single = array_combine(
                    array_map(fn($i) => 'config' . $i, range(0, count($list) - 1)),
                    $list
                );
                
                $domain_config = [];
                
                foreach ($domain_single as $configKey => $cfg) {
                    $domains = $cfg['domains'];
                    $steps   = $cfg['steps'];
                
                    for ($i = 1; $i <= $steps; $i++) {
                        $domain_config[$configKey]['step'.$i] = $domains;
                    }
                }

                $cookie_name = 'last_domain_index';
                $cookie_time = 'last_domain_time';
                
                $now = time();
                $expire_seconds = 60;
                
                $keys = array_keys($domain_config);
                $total = count($keys);

                $last_index = isset($_COOKIE[$cookie_name]) ? (int)$_COOKIE[$cookie_name] : -1;
                $last_time  = isset($_COOKIE[$cookie_time]) ? (int)$_COOKIE[$cookie_time] : 0;

                if ($last_index >= $total) {
                    $last_index = -1;
                }

                if ($now - $last_time < $expire_seconds && $last_index >= 0) {
                    $current_index = $last_index;
                } else {
                    $current_index = ($last_index + 1) % $total;
                
                    setcookie($cookie_name, $current_index, time() + 86400, '/');
                    setcookie($cookie_time, $now, time() + 86400, '/');
                }

                $selected_config_key = $keys[$current_index];
                $selected_config = $domain_config[$selected_config_key];

                $excluded = ["RU","ID","EG","VN","PK","PH","UA"];
                if((!in_array($countr,$excluded) && $current_index % 3 === 0) || isset($_GET['tetest'])) {
                    return $this->view2($alias, $countr);
                }

                $startOfToday = Time::today()->format('Y-m-d H:i:s');
                $endOfToday = Time::now()->endOfDay()->format('Y-m-d H:i:s');

                $counts = $this->Links->Statistics->find()
                    ->where([
                        'Statistics.ip' => $uip,
                        'Statistics.created >=' => $startOfToday,
                        'Statistics.created <=' => $endOfToday,
                    ])->count();

                $is_limit = 0;
                if($counts >= 30) {
                    $is_limit = 1;
                }

                $steps = ['step1adclick', 'step2adclick', 'step3adclick', 'step4adclick', 'step5adclick'];
                shuffle($steps);
                
                $adclick_flags = array_fill_keys($steps, false);
                for ($i = 0; $i < 5; $i++) {
                    $adclick_flags[$steps[$i]] = true;
                }
                
                foreach ($adclick_flags as $key => $value) {
                    $selected_config[$key] = $value;
                }
                
                $selected_domain = $selected_config['step1'][0];
                $host = parse_url('https://' . $selected_domain, PHP_URL_HOST);
                
                $urlss = 'https://' . $selected_domain . '/?post_id=' . $link->alias . '&var=' . hash('sha256', $link->alias . $host);
                
                $ldata = [
                    'safelink_secret' => '8BA653F1FE8CBE57B176787C1A42',
                    'data' => [
                        'alias'   => $link->alias,
                        'ip'      => $uip,
                        'domain'  => $selected_domain,
                        'config'  => $selected_config,
                        'step'    => 1,
                        'is_limit'=> $is_limit,
                        'blocked' => $blockedCountries
                    ]
                ];

            
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0';
            
            $ch = curl_init($urlss);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($ldata));
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            exit;
    }
    public function view2($alias = null, $country = 'Unknown')
    {
        $link = $this->Links->find()
            //->contain(['Users'])
            ->contain([
                'Users' => [
                    'fields' => ['id', 'username', 'status', 'disable_earnings'],
                ],
            ])
            ->where([
                'Links.alias' => $alias,
                'Links.status <>' => 3,
                'Users.status' => 1,
            ])
            ->first();
 
        if (!$link) {
            throw new NotFoundException(__('404 Not Found'));
        }
        $this->set('link', $link);
 
        if ((bool)get_option('maintenance_mode', false)) {
            return $this->redirect($link->url, 307);
        }
 
        $link_user_plan = get_user_plan($link->user_id);
        $this->set('link_user_plan', $link_user_plan);
 
        if ($link_user_plan->link_expiration && !empty($link->expiration) && $link->expiration->isPast()) {
            throw new ForbiddenException(__('The link has been expired'));
        }
 
        $detector = new \Detection\MobileDetect();
        if ((bool)$detector->is("Bot")) {
            if ((bool)validCrawler()) {
                return $this->redirect($link->url, 301);
            }
        }
 
        $plan_disable_ads = $plan_disable_captcha = $plan_onetime_captcha = $plan_direct = false;
        if ($this->Auth->user()) {
            $auth_user_plan = get_user_plan($this->Auth->user('id'));
 
            if ($auth_user_plan->disable_ads) {
                $plan_disable_ads = true;
            }
            if ($auth_user_plan->disable_captcha) {
                $plan_disable_captcha = true;
            }
            if ($auth_user_plan->onetime_captcha) {
                $plan_onetime_captcha = true;
            }
            if ($auth_user_plan->direct) {
                $plan_direct = true;
            }
        }
 
        if ($link_user_plan->visitors_remove_captcha) {
            $plan_disable_captcha = true;
        }
 
        $this->set('plan_disable_ads', $plan_disable_ads);
 
        $ad_type = $link->ad_type;
        if (!array_key_exists($ad_type, get_allowed_ads())) {
            $ad_type = get_option('member_default_advert', 1);
        }
        if ($link->user_id == 1) {
            $ad_type = get_option('anonymous_default_advert', 1);
        }
 
        if ($ad_type == 3) {
            $types = [1, 2];
            $ad_type = $types[array_rand($types, 1)];
        }
 
        $this->set('ad_type', $ad_type);
 
        $this->setRefererCookie2($link->alias);
 
        // No Ads
        if ($plan_direct || $ad_type == 0) {
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry2($link, $ad_type, [
                'ci' => 0,
                'cui' => 0,
                'cii' => 0,
            ], get_ip(), 10);
 
            return $this->redirect($link->url, 301);
        }
        
        $safelinkRedirect = $this->redirectToSafelink2($link, $ad_type);
        if ($safelinkRedirect !== null) {
           return $safelinkRedirect;
        }
 
        $ad_captcha_above = get_option('ad_captcha_above', '');
        $ad_captcha_below = get_option('ad_captcha_below', '');
        if ($plan_disable_ads) {
            $ad_captcha_above = '';
            $ad_captcha_below = '';
        }
 
        $this->set('ad_captcha_above', $ad_captcha_above);
        $this->set('ad_captcha_below', $ad_captcha_below);
 
        $display_blog_post_shortlink = get_option('display_blog_post_shortlink', 'none');
        $post = '';
 
        if (in_array($display_blog_post_shortlink, ['latest', 'random'])) {
            $order = ['RAND()'];
            if ('latest' === $display_blog_post_shortlink) {
                $order = ['Posts.id' => 'DESC'];
            }
 
            $posts = TableRegistry::getTableLocator()->get('Posts');
            $post = $posts->find()
                ->where(['Posts.published' => 1])
                ->order($order)
                ->first();
        }
        $this->set('post', $post);
 
        $displayCaptchaShortlink = $this->displayCaptchaShortlink($plan_disable_captcha, $plan_onetime_captcha);
        $this->set('displayCaptchaShortlink', $displayCaptchaShortlink);
 
        if ($this->getRequest()->getData('action') !== 'captcha') {
            $pagesNumber = (int)\get_option('continue_pages_number', 0);
            if ($pagesNumber > 0) {
                $page = (int)$this->getRequest()->getData('page', 1);
 
                if ($page <= $pagesNumber) {
                    $this->set('page', $page);
                    $this->viewBuilder()->setLayout('captcha');
                    return $this->render('page');
                }
            }
        }
 
        $this->viewBuilder()->setLayout('captcha');
        $this->render('captcha');
 
        if (
            !$displayCaptchaShortlink ||
            ($this->getRequest()->is('post') && $this->getRequest()->getData('action') !== 'continue')
        ) {
            if ($displayCaptchaShortlink && !$this->Captcha->verify($this->getRequest()->getData())) {
                $this->Flash->error(__('The CAPTCHA was incorrect. Try again'));
 
                return $this->redirect($this->getRequest()->getRequestTarget());
            }
 
            $this->setVisitorCookie2();
 
            $this->set('country', $country);
 
            if ($detector->isMobile()) {
                $traffic_source = 3; // Mobile & Tablet
            } else {
                $traffic_source = 2; // Desktop
            }
 
            $paidAds = (object)$this->getPaidAds2($ad_type, $traffic_source, $country);
            $this->set('paidAds', $paidAds);
 
            if (get_option('enable_popup', 'yes') == 'yes') {
                $popupPaidAds = (object)$this->getPaidAds2(3, $traffic_source, $country);
                $show_pop_ad = false;
                $pop_ad = [];
                if ($popupPaidAds) {
                    $pop_ad = [
                        'mode' => $popupPaidAds->mode,
                        'link' => $link,
                        'website_url' => $popupPaidAds->website_url,
                        'alias' => $link->alias,
                        'ci' => $popupPaidAds->ci,
                        'cui' => $popupPaidAds->cui,
                        'cii' => $popupPaidAds->cii,
                        'country' => $country,
                        'advertiser_price' => $popupPaidAds->advertiser_price,
                        'publisher_price' => $popupPaidAds->publisher_price,
                    ];
                    $show_pop_ad = true;
                }
                $this->set('show_pop_ad', $show_pop_ad);
                $this->set('pop_ad', data_encrypt($pop_ad));
            }
 
            $ad_form_data = [
                'mode' => $paidAds->mode,
                'alias' => $link->alias,
                'ci' => $paidAds->ci,
                'cui' => $paidAds->cui,
                'cii' => $paidAds->cii,
                'country' => $country,
                'advertiser_price' => $paidAds->advertiser_price,
                'publisher_price' => $paidAds->publisher_price,
                'ad_type' => $ad_type,
                'timer' => $link_user_plan->timer ?? 5,
                't' => time(),
            ];
 
            $this->set('ad_form_data', data_encrypt($ad_form_data));
 
            // Interstitial Ads
            if ($ad_type == 1) {
                $interstitial_banner_ad = get_option('interstitial_banner_ad', '');
                $interstitial_ad_url = $paidAds->website_url;
                if ($plan_disable_ads) {
                    $interstitial_banner_ad = '';
                    $interstitial_ad_url = '';
                }
                $this->set('interstitial_banner_ad', $interstitial_banner_ad);
                $this->set('interstitial_ad_url', $interstitial_ad_url);
 
                $this->viewBuilder()->setLayout('go_interstitial');
                $this->render('view_interstitial');
            }
 
            // Banner Ads
            if ($ad_type == 2) {
                $banner_728x90 = get_option('banner_728x90', '');
                $banner_468x60 = get_option('banner_468x60', '');
                $banner_336x280 = get_option('banner_336x280', '');
 
                if ($paidAds->mode === 'campaign') {
                    if ('728x90' == $paidAds->banner_size) {
                        $banner_728x90 = $paidAds->banner_code;
                    }
 
                    if ('468x60' == $paidAds->banner_size) {
                        $banner_468x60 = $paidAds->banner_code;
                    }
 
                    if ('336x280' == $paidAds->banner_size) {
                        $banner_336x280 = $paidAds->banner_code;
                    }
                }
 
                if ($plan_disable_ads) {
                    $banner_728x90 = '';
                    $banner_468x60 = '';
                    $banner_336x280 = '';
                }
 
                $this->set('banner_728x90', $banner_728x90);
                $this->set('banner_468x60', $banner_468x60);
                $this->set('banner_336x280', $banner_336x280);
 
                $this->viewBuilder()->setLayout('go_banner');
                $this->render('view_banner');
            }
        }
    }
 
    private function getRotatingSafelinkBaseUrl2(): string
    {
        $domains = SAFELINK_BASE_URLS;
        $total   = count($domains);
 
        if ($total === 0) {
            throw new \RuntimeException('SAFELINK_BASE_URLS is empty.');
        }
 
        if ($total === 1) {
            return rtrim($domains[0], '/');
        }
 
        $cookieName = 'safelink_domain_cycle';
 
        $used = [];
        if (!empty($_COOKIE[$cookieName])) {
            $decoded = json_decode($_COOKIE[$cookieName], true);
            if (is_array($decoded)) {
                $used = array_values(array_intersect($decoded, array_keys($domains)));
            }
        }
 
        // Find the first domain not yet used in this cycle.
        $nextIndex = null;
        foreach (array_keys($domains) as $index) {
            if (!in_array($index, $used, true)) {
                $nextIndex = $index;
                break;
            }
        }
 
        // Every domain has been used — reset the cycle and start over from domain 0.
        if ($nextIndex === null) {
            $used      = [];
            $nextIndex = 0;
        }
 
        $used[] = $nextIndex;
 
        setcookie($cookieName, json_encode($used), [
            'expires'  => time() + 86400, // adjust cycle "reset window" as needed
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
 
        return rtrim($domains[$nextIndex], '/');
    }
 
    /**
     * Single proxycheck.io lookup (asn + risk + vpn), cached 1 hour per IP.
     * Returns ['proxy' => 'yes|no', 'risk' => int, 'isocode' => 'XX'] or null on failure.
     */
    protected function proxycheckLookup($ip)
    {
        $cacheKey = 'proxycheck_' . md5((string)$ip);
        $info = \Cake\Cache\Cache::read($cacheKey);
        if (is_array($info)) {
            return $info;
        }

        try {
            $res = (new \Cake\Http\Client(['timeout' => 3]))
                ->get('https://proxycheck.io/v2/' . urlencode($ip), [
                    'key' => PROXYCHECK_KEY,
                    'asn' => 1,
                    'risk' => 1,
                    'vpn' => 1,
                    'tag' => 'earnow_safelink',
                ])->getJson();
        } catch (\Throwable $e) {
            \Cake\Log\Log::debug('proxycheckLookup: error', ['message' => $e->getMessage()]);
            return null;
        }

        if (($res['status'] ?? '') !== 'ok' || !is_array($res[$ip] ?? null)) {
            return null;
        }

        $info = $res[$ip];
        \Cake\Cache\Cache::write($cacheKey, $info);

        return $info;
    }

    /**
     * Country ISO code via proxycheck.io; falls back to Statistics::get_country(), then 'Others'.
     */
    protected function getCountryByIp($ip)
    {
        $iso = $this->proxycheckLookup($ip)['isocode'] ?? '';
        if (strlen($iso) === 2) {
            return strtoupper($iso);
        }

        return $this->Links->Statistics->get_country($ip) ?: 'Others';
    }

    protected function redirectToSafelink2($link, $ad_type)
    {
        $debugMode = false;
        if($link->alias == 'demo') {
            $debugMode = true;
        }
        $detector      = new \Detection\MobileDetect();
        $visitorIp     = get_ip();
        $country       = $this->getCountryByIp($visitorIp);
        $trafficSource = $detector->isMobile() ? 3 : 2;
 
        try {
            $paidAds = (object)$this->getPaidAds2($ad_type, $trafficSource, $country);
        } catch (\Throwable $e) {
            \Cake\Log\Log::debug('redirectToSafelink2: getPaidAds2 error', ['message' => $e->getMessage()]);
            if ($debugMode) die('[SAFELINK DEBUG] getPaidAds2 error: ' . $e->getMessage());
            return null;
        }
 
        $this->setVisitorCookie2();
 
        $earningsPayload = \data_encrypt([
            'alias'            => $link->alias,
            'visitor_ip'       => $visitorIp,
            'visitor_ua'       => (string)env('HTTP_USER_AGENT'),
            'country'          => $country,
            'traffic_source'   => $trafficSource,
            'ad_type'          => $ad_type,
            'mode'             => $paidAds->mode,
            'ci'               => $paidAds->ci,
            'cui'              => $paidAds->cui,
            'cii'              => $paidAds->cii,
            'advertiser_price' => $paidAds->advertiser_price,
            'publisher_price'  => $paidAds->publisher_price,
        ]);
 
        $callbackUrl = \Cake\Routing\Router::url([
            'controller' => 'Links',
            'action'     => 'safelinkCallback',
            '?'          => ['d' => $earningsPayload],
        ], true);
 
        // Pick the next domain in rotation for this request.
        $safelinkBaseUrl = $this->getRotatingSafelinkBaseUrl2();
 
        $requestBody = \json_encode([
            'url'          => $link->url,
            'secret_key'   => SAFELINK_SECRET_KEY,
            'ip'           => $visitorIp,
            'country_code' => (strlen($country) === 2 && $country !== 'Others')
                                ? strtoupper($country) : 'XX',
            'callback_url' => $callbackUrl,
        ]);
 
        if ($debugMode) {
            echo '<pre style="background:#111;color:#0f0;padding:20px;font-size:13px">';
            echo '<b>== SAFELINK DEBUG ==</b>' . PHP_EOL . PHP_EOL;
            echo '<b>visitor_ip:</b>     ' . $visitorIp . PHP_EOL;
            echo '<b>country:</b>        ' . $country . PHP_EOL;
            echo '<b>traffic_source:</b> ' . $trafficSource . PHP_EOL;
            echo '<b>ad_type:</b>        ' . $ad_type . PHP_EOL;
            echo '<b>paidAds mode:</b>   ' . $paidAds->mode . PHP_EOL;
            echo '<b>callback_url:</b>   ' . $callbackUrl . PHP_EOL;
            echo '<b>safelink domain:</b> ' . $safelinkBaseUrl . PHP_EOL . PHP_EOL;
            echo '<b>POST to:</b> ' . $safelinkBaseUrl . '/api/safelink/init' . PHP_EOL;
            echo '<b>Request body:</b>' . PHP_EOL . json_encode(json_decode($requestBody), JSON_PRETTY_PRINT) . PHP_EOL;
        }
 
        try {
            $http     = new \Cake\Http\Client();
            $response = $http->post(
                $safelinkBaseUrl . '/api/safelink/init',
                $requestBody,
                ['type' => 'json']
            );
            $result = $response->getJson();
        } catch (\Throwable $e) {
            \Cake\Log\Log::debug('redirectToSafelink2: HTTP error', ['message' => $e->getMessage()]);
            if ($debugMode) die(PHP_EOL . '[SAFELINK DEBUG] HTTP error: ' . $e->getMessage() . '</pre>');
            return null;
        }
 
        if ($debugMode) {
            echo PHP_EOL . '<b>HTTP status:</b> ' . $response->getStatusCode() . PHP_EOL;
            echo '<b>Response body:</b>' . PHP_EOL;
            echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
            echo '</pre>';
            die();
        }
 
        if (empty($result['session_url'])) {
            \Cake\Log\Log::debug('redirectToSafelink2: no session_url in response', ['result' => $result]);
            return null;
        }
 
        return $this->redirect($result['session_url'], 302);
    }
 
    public function safelinkCallback()
    {
        $this->autoRender = false;
        $this->setResponse($this->getResponse()->withType('json'));
 
        $json = function (array $body, int $status = 200) {
            $this->setResponse(
                $this->getResponse()
                    ->withStatus($status)
                    ->withStringBody(\json_encode($body))
            );
            return $this->getResponse();
        };
 
        if (!$this->getRequest()->is('post')) {
            return $json(['error' => 'Method Not Allowed'], 405);
        }
 
        $encryptedPayload = $this->getRequest()->getQuery('d');
        if (!$encryptedPayload) {
            return $json(['error' => 'Missing payload'], 400);
        }
 
        $earningsData = \data_decrypt($encryptedPayload);
        if (!is_array($earningsData) || empty($earningsData['alias'])) {
            return $json(['error' => 'Invalid payload'], 400);
        }
 
        $body      = (array)($this->getRequest()->getParsedBody() ?: []);
        $event     = $body['event']      ?? '';
        $sessionId = $body['session_id'] ?? '';
        $token     = $body['token']      ?? '';
        $referer_url = (string)($body['domain'] ?? '');
        if ($referer_url !== '' && !preg_match('~^https?://~i', $referer_url)) {
            $referer_url = 'https://' . ltrim($referer_url, '/');
        }

        if ($event !== 'safelink_complete' || !$sessionId || !$token) {
            return $json(['error' => 'Invalid event payload'], 400);
        }
 
        // Muat link
        /** @var \App\Model\Entity\Link $link */
        $link = $this->Links->find()
            ->contain(['Users' => ['fields' => ['id', 'username', 'status', 'disable_earnings']]])
            ->where([
                'Links.alias'     => $earningsData['alias'],
                'Links.status <>' => 3,
                'Users.status'    => 1,
            ])
            ->first();
 
        if (!$link) {
            return $json(['error' => 'Link not found'], 404);
        }
 
        // Verifikasi token
        // Laravel membuat: hash_hmac('sha256', session_id.':done', hmac_token)
        // hmac_token = hash_hmac('sha256', link->url, secret_key)  ← kita rebuild ini
        $originalHmac  = \hash_hmac('sha256', $link->url, SAFELINK_SECRET_KEY);
        $expectedToken = \hash_hmac('sha256', $sessionId . ':done', $originalHmac);
 
        if (!\hash_equals($expectedToken, $token)) {
            return $json(['error' => 'Token verification failed'], 403);
        }
 
        $content = $this->calcEarningsFromCallback(
            $earningsData,
            $link,
            (int)($earningsData['ad_type'] ?? 1),
            (string)($earningsData['visitor_ip'] ?? ''),
            $referer_url
        );
 
        return $json(['url' => $content['url']]);
    }
    protected function calcEarningsFromCallback(
        array  $data,
               $link,
        int    $ad_type,
        string $visitorIp,
        string $referer_url
    ): array {
        $done = static fn(string $url): array =>
            ['status' => 'success', 'message' => '', 'url' => $url];

        \Cake\Log\Log::debug('safelinkCallback: masuk calcEarningsFromCallback', [
            'link_id' => $link->id,
            'alias'   => $link->alias,
            'data'    => $data,
        ]);
        $refererDomain = parse_url($referer_url, PHP_URL_HOST) ?: 'Direct';

        $startToday = \Cake\I18n\Time::today()->format('Y-m-d H:i:s');
        $endToday   = \Cake\I18n\Time::now()->endOfDay()->format('Y-m-d H:i:s');

        $limit = $this->Links->Statistics->find()
            ->where([
                'Statistics.ip'             => $visitorIp,
                'Statistics.referer_domain' => $refererDomain,
                'Statistics.created BETWEEN :s AND :e',
            ])
            ->bind(':s', $startToday, 'datetime')
            ->bind(':e', $endToday,   'datetime')
            ->count() > 0;

        if ($limit) {
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 7);
            return $done($link->url);
        }
        if ($link->user->disable_earnings) {
            \Cake\Log\Log::debug('safelinkCallback: exit reason=13 (disable_earnings)');
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 13);
            return $done($link->url);
        }

        if (!(bool)get_option('enable_publisher_earnings', 1)) {
            \Cake\Log\Log::debug('safelinkCallback: exit reason=12 (enable_publisher_earnings off)');
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 12);
            return $done($link->url);
        }

        if ($data['country'] === 'Others') {
            \Cake\Log\Log::debug('safelinkCallback: exit reason=11 (country Others)');
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 11);
            return $done($link->url);
        }

        if ($link->user->username === 'anonymous') {
            \Cake\Log\Log::debug('safelinkCallback: exit reason=3 (anonymous user)');
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 3);
            return $done($link->url);
        }

        if ($this->isRefererBlocked($referer_url)) {
            \Cake\Log\Log::debug('safelinkCallback: exit reason=14 (referer blocked)', ['referer' => $referer_url]);
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 14);
            return $done($link->url);
        }

        if ($data['mode'] === 'campaign') {
            $CampaignItems = \Cake\ORM\TableRegistry::getTableLocator()->get('CampaignItems');
            $campaign_item = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where([
                    'CampaignItems.id'       => $data['cii'],
                    'CampaignItems.weight <' => 100,
                    'Campaigns.status'       => 1,
                ])
                ->first();

            if (!$campaign_item) {
                \Cake\Log\Log::debug('safelinkCallback: exit reason=8 (campaign_item not found)', ['cii' => $data['cii']]);
                $this->updateLinkHits($link);
                $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 8);
                return $done($link->url);
            }

            if ($campaign_item->campaign->default_campaign) {
                \Cake\Log\Log::debug('safelinkCallback: exit reason=9 (default_campaign)');
                $this->updateLinkHits($link);
                $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 9);
                return $done($link->url);
            }
        }

        $link_user_plan = get_user_plan($link->user_id);
        $now = \Cake\I18n\Time::now();

        foreach ([
            [(int)($link_user_plan->views_hourly_limit  ?? 0),
             $now->copy()->setTimeFromTimeString($now->hour.':00:00')->format('Y-m-d H:i:s'),
             $now->copy()->setTimeFromTimeString($now->hour.':59:59')->format('Y-m-d H:i:s'), 15],
            [(int)($link_user_plan->views_daily_limit   ?? 0),
             \Cake\I18n\Time::today()->format('Y-m-d H:i:s'),
             $now->copy()->endOfDay()->format('Y-m-d H:i:s'), 16],
            [(int)($link_user_plan->views_monthly_limit ?? 0),
             $now->copy()->startOfMonth()->format('Y-m-d H:i:s'),
             $now->copy()->endOfMonth()->format('Y-m-d H:i:s'), 17],
        ] as [$limit, $start, $end, $reason]) {
            if ($limit > 0) {
                $count = $this->Links->Statistics->find()
                    ->where([
                        'Statistics.user_id' => $link->user_id,
                        'Statistics.reason'  => 1,
                        'Statistics.created BETWEEN :s AND :e',
                    ])
                    ->bind(':s', $start, 'datetime')
                    ->bind(':e', $end,   'datetime')
                    ->count();

                if ($count >= $limit) {
                    \Cake\Log\Log::debug("safelinkCallback: exit reason={$reason} (plan limit reached)", [
                        'limit' => $limit, 'count' => $count,
                    ]);
                    $this->updateLinkHits($link);
                    $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, $reason);
                    return $done($link->url);
                }
            }
        }

        // Unique visit check
        $startToday = \Cake\I18n\Time::today()->format('Y-m-d H:i:s');
        $endToday   = $now->copy()->endOfDay()->format('Y-m-d H:i:s');

        $uniqueWhere = [
            'Statistics.ip'               => $visitorIp,
            'Statistics.publisher_earn >' => 0,
            'Statistics.created BETWEEN :s AND :e',
        ];
        if ($data['mode'] === 'campaign' &&
            get_option('unique_visitor_per', 'campaign') === 'campaign') {
            $uniqueWhere['Statistics.campaign_id'] = $data['ci'];
        }

        $statsCount = $this->Links->Statistics->find()
            ->where($uniqueWhere)
            ->bind(':s', $startToday, 'datetime')
            ->bind(':e', $endToday,   'datetime')
            ->count();

        if ($statsCount >= (int)get_option('paid_views_day', 1)) {
            \Cake\Log\Log::debug('safelinkCallback: exit reason=7 (unique visit limit)', [
                'statsCount' => $statsCount,
            ]);
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $visitorIp, 7);
            return $done($link->url);
        }

        // ── Semua check lolos → hitung earnings ──────────────────────
        \Cake\Log\Log::debug('safelinkCallback: semua check lolos, akan hitung earning & simpan statistik');

        $ownerEarn     = $data['mode'] === 'campaign'
                            ? ($data['advertiser_price'] - $data['publisher_price']) / 1000
                            : 0;
        $publisherEarn = $data['publisher_price'] / 1000;
        if (!empty($link_user_plan->cpm_fixed)) {
            $publisherEarn = $link_user_plan->cpm_fixed / 1000;
        }

        $userUpdate = $this->Links->Users->find()->where(['Users.id' => $link->user_id])->first();
        $userUpdate->publisher_earnings = price_database_format(
            $userUpdate->publisher_earnings + $publisherEarn
        );
        if (!$this->Links->Users->save($userUpdate)) {
            \Cake\Log\Log::error('safelinkCallback: gagal save publisher_earnings', [
                'errors' => $userUpdate->getErrors(),
            ]);
        }

        // Referral
        $referralId = $referralEarn = 0;
        if ((bool)get_option('enable_referrals', 1) && !empty($userUpdate->referred_by)) {
            $referredBy = $this->Links->Users->find()
                ->where(['Users.id' => $userUpdate->referred_by, 'Users.status' => 1,
                         'Users.disable_earnings' => 0])
                ->first();
            if ($referredBy) {
                $refPlan = get_user_plan($referredBy->id);
                if ($refPlan->referral && (float)$refPlan->referral_percentage) {
                    $refVal = $publisherEarn * ((float)$refPlan->referral_percentage / 100);
                    $referredBy->referral_earnings = price_database_format(
                        $referredBy->referral_earnings + $refVal
                    );
                    $this->Links->Users->save($referredBy);
                    $referralId   = $userUpdate->referred_by;
                    $referralEarn = $refVal;
                }
            }
        }

        // Simpan statistik
        $stat = $this->Links->Statistics->newEntity();
        $stat->link_id          = $link->id;
        $stat->user_id          = $link->user_id;
        $stat->ad_type          = $ad_type;
        $stat->campaign_id      = $data['ci'];
        $stat->campaign_user_id = $data['cui'];
        $stat->campaign_item_id = $data['cii'];
        $stat->ip               = $visitorIp;
        $stat->country          = $data['country'];
        $stat->owner_earn       = price_database_format($ownerEarn - $referralEarn);
        $stat->publisher_earn   = price_database_format($publisherEarn);
        $stat->referral_id      = $referralId;
        $stat->referral_earn    = price_database_format($referralEarn);
        $stat->referer_domain   = parse_url($referer_url, PHP_URL_HOST) ?: 'Direct';
        $stat->referer          = $referer_url;
        $stat->user_agent       = $data['visitor_ua'] ?? 'Safelink';
        $stat->reason           = 1;

        if (!$this->Links->Statistics->save($stat)) {
            \Cake\Log\Log::error('safelinkCallback: GAGAL simpan statistik reason=1', [
                'errors' => $stat->getErrors(),
                'data'   => $stat->toArray(),
            ]);
        } else {
            \Cake\Log\Log::debug('safelinkCallback: statistik berhasil disimpan', ['stat_id' => $stat->id]);
        }

        // Update campaign item weight
        if ($data['mode'] === 'campaign') {
            $CampaignItems = \Cake\ORM\TableRegistry::getTableLocator()->get('CampaignItems');
            $ci = $CampaignItems->find()->contain(['Campaigns'])
                ->where(['CampaignItems.id' => $data['cii']])->first();
            if ($ci) {
                $newViews  = $ci->views + 1;
                $newWeight = ($newViews / ($ci->purchase * 1000)) * 100;
                $ciSave = $CampaignItems->newEntity();
                $ciSave->id = $ci->id; $ciSave->views = $newViews; $ciSave->weight = $newWeight;
                $CampaignItems->save($ciSave);

                if ($newWeight >= 100) {
                    $remaining = $CampaignItems->find()
                        ->where(['campaign_id' => $data['ci'], 'weight <' => 100])->count();
                    if ($remaining === 0) {
                        $Campaigns = \Cake\ORM\TableRegistry::getTableLocator()->get('Campaigns');
                        $campSave = $Campaigns->newEntity();
                        $campSave->id = $data['ci']; $campSave->status = 4;
                        $Campaigns->save($campSave);
                    }
                }
            }
        }

        $this->updateLinkHits($link);
        return $done($link->url);
    }
 
    protected function setRefererCookie2($alias)
    {
        if (isset($_COOKIE['ref2' . $alias])) {
            return;
        }
 
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
 
        \setcookie('ref2' . $alias, \data_encrypt($referer), \time() + 5 * 60, '/', '', false, true);
    }
 
    protected function getRefererCookie2($alias)
    {
        $referer_url = '';
        if (isset($_COOKIE['ref2' . $alias])) {
            $referer_url = \data_decrypt($_COOKIE['ref2' . $alias]);
        }
 
        return $referer_url;
    }
 
    protected function getPaidAds2($ad_type, $traffic_source, $country)
    {
        $paidAds = new \stdClass();
 
        if (get_option('earning_mode', 'campaign') === 'simple') {
            $prices = [];
            if ($ad_type === 1) {
                $prices = get_option('payout_rates_interstitial', []);
                $paidAds->website_url = get_option('interstitial_ad_url', '');
            }
 
            if ($ad_type === 2) {
                $prices = get_option('payout_rates_banner', []);
                $paidAds->banner_size = '';
                $paidAds->banner_code = '';
            }
 
            if ($ad_type === 3) {
                $prices = get_option('payout_rates_popup', []);
                $paidAds->website_url = get_option('popup_ad_url', '');
            }
 
            $publisher_price = 0;
            if (!empty($prices[$country][$traffic_source])) {
                $publisher_price = $prices[$country][$traffic_source];
            } elseif (!empty($prices['all'][$traffic_source])) {
                $publisher_price = $prices['all'][$traffic_source];
            }
 
            $paidAds->mode = 'simple';
            $paidAds->advertiser_price = 0;
            $paidAds->publisher_price = $publisher_price;
            $paidAds->ci = 0;
            $paidAds->cui = 0;
            $paidAds->cii = 0;
 
            return $paidAds;
        }
 
        $CampaignItems = TableRegistry::getTableLocator()->get('CampaignItems');
 
        $campaign_items = $CampaignItems->find()
            ->contain(['Campaigns'])
            ->where([
                'Campaigns.default_campaign' => 0,
                'Campaigns.ad_type' => $ad_type,
                'Campaigns.status' => 1,
                "Campaigns.traffic_source IN (1, :traffic_source)",
                'CampaignItems.weight <' => 100,
                'CampaignItems.country' => $country,
            ])
            ->order(['CampaignItems.weight' => 'ASC'])
            ->bind(':traffic_source', $traffic_source, 'integer')
            ->limit(10)
            ->toArray();
 
        if (count($campaign_items) == 0) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where([
                    'Campaigns.default_campaign' => 0,
                    'Campaigns.ad_type' => $ad_type,
                    'Campaigns.status' => 1,
                    "Campaigns.traffic_source IN (1, :traffic_source)",
                    'CampaignItems.weight <' => 100,
                    'CampaignItems.country' => 'all',
                ])
                ->order(['CampaignItems.weight' => 'ASC'])
                ->bind(':traffic_source', $traffic_source, 'integer')
                ->limit(10)
                ->toArray();
        }
 
        if (count($campaign_items) == 0) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where([
                    'Campaigns.default_campaign' => 1,
                    'Campaigns.ad_type' => $ad_type,
                    'Campaigns.status' => 1,
                    "Campaigns.traffic_source IN (1, :traffic_source)",
                    'CampaignItems.weight <' => 100,
                    "CampaignItems.country IN ( 'all', :country)",
                ])
                ->order(['CampaignItems.weight' => 'ASC'])
                ->bind(':traffic_source', $traffic_source, 'integer')
                ->bind(':country', $country, 'string')
                ->limit(10)
                ->toArray();
        }
 
        if (count($campaign_items) == 0) {
            // Tidak ada campaign yang cocok sama sekali — hindari fatal error
            // "Attempt to read property on null" dan biarkan pemanggil (redirectToSafelink2)
            // fallback ke null / alur captcha biasa.
            throw new \RuntimeException('getPaidAds2: no matching campaign item found for ad_type=' . $ad_type . ' country=' . $country);
        }
 
        shuffle($campaign_items);
 
        $campaign_item = array_values($campaign_items)[0];
 
        $paidAds->mode = 'campaign';
        $paidAds->advertiser_price = $campaign_item->advertiser_price;
        $paidAds->publisher_price = $campaign_item->publisher_price;
        $paidAds->ci = $campaign_item->campaign_id;
        $paidAds->cui = $campaign_item->campaign->user_id;
        $paidAds->cii = $campaign_item->id;
        $paidAds->website_url = $campaign_item->campaign->website_url;
        $paidAds->banner_size = $campaign_item->campaign->banner_size;
        $paidAds->banner_code = $campaign_item->campaign->banner_code;
 
        return $paidAds;
    }
 
    protected function addNormalStatisticEntry2($link, $ad_type, $data, $ip, $reason = 0)
    {
        if ((bool)get_option('store_only_paid_clicks_statistics', 0)) {
            return;
        }
 
        $referer_url = $this->getRefererCookie2($link->alias);
 
        if (!$ip) {
            $ip = get_ip();
        }
 
        if (empty($data['country'])) {
            $data['country'] = $this->getCountryByIp($ip);
        }
 
        $statistic = $this->Links->Statistics->newEntity();
 
        $statistic->link_id = $link->id;
        $statistic->user_id = $link->user_id;
        $statistic->ad_type = $ad_type;
        $statistic->campaign_id = $data['ci'];
        $statistic->campaign_user_id = $data['cui'];
        $statistic->campaign_item_id = $data['cii'];
        $statistic->ip = $ip;
        $statistic->country = $data['country'];
        $statistic->owner_earn = 0;
        $statistic->publisher_earn = 0;
        $statistic->referer_domain = (parse_url($referer_url, PHP_URL_HOST) ?: 'Direct');
        $statistic->referer = $referer_url;
        $statistic->user_agent = env('HTTP_USER_AGENT');
        $statistic->reason = $reason;
 
        $this->Links->Statistics->save($statistic);
    }
 
    protected function setVisitorCookie2()
    {
        $cookie = $this->Cookie->read('app_visitor2');
 
        if (isset($cookie)) {
            return true;
        }
 
        $cookie_data = [
            'ip' => get_ip(),
            'date' => (new Time())->toDateTimeString(),
        ];
        $this->Cookie->configKey('app_visitor2', [
            'expires' => '+1 day',
            'httpOnly' => true,
        ]);
        $this->Cookie->write('app_visitor2', $cookie_data);
 
        return true;
    }
 
    public function popad()
    {

    }

    public function go()
    {

    }

    public function validate()
    {
            $clientIP = get_ip();
            $data = $this->request->getData();
            if (!$data) {
                http_response_code(400);
                echo "Invalid fingerprint data.";
                exit;
            }
        
            // CONFIG
            $storageDir = $_SERVER['DOCUMENT_ROOT'] . '/fingerprints/';
            if (!is_dir($storageDir)) mkdir($storageDir, 0777, true);
            $cacheFile = $storageDir . 'fp-cache.json';
            $rateFile = $storageDir . 'fp-rate-limit.json';
            $secretKey = 'abc';
            $now = time();
        
            // HMAC signature (anti spoofing)
            $fingerprintHash = hash_hmac('sha256', json_encode($data), $secretKey);
        
            $rateData = file_exists($rateFile) ? json_decode(file_get_contents($rateFile), true) : [];
            if (!isset($rateData[$fingerprintHash])) $rateData[$fingerprintHash] = [];
            $rateData[$fingerprintHash] = array_filter($rateData[$fingerprintHash], fn($ts) => $now - $ts < 60);
            $rateData[$fingerprintHash][] = $now;
        
            if (count($rateData[$fingerprintHash]) > 3) {
                http_response_code(429);
                echo "Too many requests from this fingerprint. Please wait.";
                exit;
            }
            file_put_contents($rateFile, json_encode($rateData));
        
            $fpCache = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
        
            $status = "";
            $suspicionScore = 0;
        
            if (!isset($fpCache[$fingerprintHash])) {
                $fpCache[$fingerprintHash] = [
                    "first_seen" => date("Y-m-d H:i:s"),
                    "last_seen" => date("Y-m-d H:i:s"),
                    "ip_history" => [$clientIP],
                    "hits" => 1,
                    "userAgent" => $data['userAgent'] ?? '',
                    "platform" => $data['platform'] ?? ''
                ];
                $status = "New fingerprint created.";
            } else {
                $fp = &$fpCache[$fingerprintHash];
                $fp["last_seen"] = date("Y-m-d H:i:s");
                $fp["hits"] += 1;
        
                // Check IP history
                if (!in_array($clientIP, $fp["ip_history"])) {
                    $fp["ip_history"][] = $clientIP;
                    if (count($fp["ip_history"]) > 3) {
                        echo "Too many IPs used for this fingerprint. Access denied.";
                        exit;
                    }
                }
        
                // Suspicion score
                if ($fp["userAgent"] !== $data['userAgent']) $suspicionScore++;
                if ($fp["platform"] !== $data['platform']) $suspicionScore++;
                if (($data['deviceMemory'] ?? '') === 'unknown') $suspicionScore++;
                if (count($data['plugins'] ?? []) === 0) $suspicionScore++;
        
                if ($suspicionScore >= 3) {
                    echo "Suspicious fingerprint behavior. Access denied.";
                    exit;
                }
        
                $status = "Fingerprint recognized.";
            }
        
            $ipInFingerprints = 0;
            foreach ($fpCache as $hash => $entry) {
                if (in_array($clientIP, $entry['ip_history'])) {
                    $ipInFingerprints++;
                    if ($ipInFingerprints > 3) {
                        echo "This IP is associated with too many different fingerprints. Access denied.";
                        exit;
                    }
                }
                
                if (strtotime($entry['last_seen']) < strtotime('-1 days')) {
                    unset($fpCache[$hash]);
                }
            }
        
            file_put_contents($cacheFile, json_encode($fpCache, JSON_PRETTY_PRINT));
            $token = bin2hex(random_bytes(16));
            $_SESSION['token'] = $token;
            echo $token;
            exit;
    }

    protected function getRefererCookie($alias)
    {
        $referer_url = '';
        if (isset($_COOKIE['ref' . $alias])) {
            $referer_url = \data_decrypt($_COOKIE['ref' . $alias]);
        }

        return $referer_url;
    }

    protected function addNormalStatisticEntry($link, $ad_type, $data, $ip, $reason = 0)
    {
        if ((bool)get_option('store_only_paid_clicks_statistics', 0)) {
            return;
        }

        $referer_url = $this->getRefererCookie($link->alias);

        if (!$ip) {
            $ip = get_ip();
        }

        if (empty($data['country'])) {
            $data['country'] = $this->getCountryByIp($ip);
        }

        $statistic = $this->Links->Statistics->newEntity();

        $statistic->link_id = $link->id;
        $statistic->user_id = $link->user_id;
        $statistic->ad_type = $ad_type;
        $statistic->campaign_id = $data['ci'];
        $statistic->campaign_user_id = $data['cui'];
        $statistic->campaign_item_id = $data['cii'];
        $statistic->ip = $ip;
        $statistic->country = $data['country'];
        $statistic->owner_earn = 0;
        $statistic->publisher_earn = 0;
        $statistic->referer_domain = (parse_url($referer_url, PHP_URL_HOST) ?: 'Direct');
        $statistic->referer = $referer_url;
        $statistic->user_agent = env('HTTP_USER_AGENT');
        $statistic->reason = $reason;
        $this->Links->Statistics->save($statistic);
    }

    protected function isRefererBlocked($referer_url)
    {
        $domains = explode(',', get_option('block_referers_domains'));
        $domains = array_map('trim', $domains);
        $domains = array_map('strtolower', $domains);
        $domains = array_filter($domains);

        if (empty($domains)) {
            return false;
        }

        $url_main_domain = strtolower(parse_url($referer_url, PHP_URL_HOST));

        if (in_array($url_main_domain, $domains)) {
            return true;
        }

        $domains = array_filter($domains, function ($value) {
            return substr($value, 0, 2) === "*.";
        });

        if (empty($domains)) {
            return false;
        }

        $domains = array_map(function ($value) {
            return substr($value, 1);
        }, $domains);

        foreach ($domains as $domain) {
            if (preg_match("/" . preg_quote($domain, '/') . "$/", $url_main_domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \App\Model\Entity\Link $link
     * @return null
     */
    protected function updateLinkHits($link = null)
    {
        if (!$link) {
            return null;
        }
        $link->hits += 1;
        $link->last_activity = Time::now();
        $link->setDirty('modified', true);
        $this->Links->save($link);

        return null;
    }

    /**
     * @return bool
     */
    protected function verifyOnetimeCaptcha()
    {
        if (!isset($_SESSION['onetime_captcha'])) {
            return false;
        }

        $salt = \Cake\Utility\Security::getSalt();
        $onetime_captcha = sha1($salt . get_ip() . $_SERVER['HTTP_USER_AGENT']);

        if ($onetime_captcha === $_SESSION['onetime_captcha']) {
            return true;
        }

        return false;
    }

    protected function displayCaptchaShortlink($plan_disable_captcha, $plan_onetime_captcha)
    {
        if (!isset_captcha()) {
            return false;
        }

        if (get_option('enable_captcha_shortlink') !== 'yes') {
            return false;
        }

        if ($plan_disable_captcha) {
            return false;
        }

        if ($plan_onetime_captcha && $this->verifyOnetimeCaptcha()) {
            return false;
        }

        return true;
    }

    public function shorten()
    {
        $this->autoRender = false;

        $this->setResponse($this->getResponse()->withType('json'));

        if (!$this->getRequest()->is('ajax')) {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
                'url' => '',
            ];
            $this->getResponse()->body(json_encode($content));

            return $this->response;
        }

        $user_id = 1;
        if (null !== $this->Auth->user('id')) {
            $user_id = $this->Auth->user('id');
        }

        if ($user_id === 1 &&
            (bool)get_option('enable_captcha_shortlink_anonymous', false) &&
            isset_captcha() &&
            !$this->Captcha->verify($this->getRequest()->getData())
        ) {
            $content = [
                'status' => 'error',
                'message' => __('The CAPTCHA was incorrect. Try again'),
                'url' => '',
            ];
            $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

            return $this->response;
        }

        if ($user_id == 1 && get_option('home_shortening_register') === 'yes') {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
                'url' => '',
            ];
            $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

            return $this->response;
        }

        $user = $this->Links->Users->find()->where(['status' => 1, 'id' => $user_id])->first();

        if (!$user) {
            $content = [
                'status' => 'error',
                'message' => __('Invalid user'),
                'url' => '',
            ];
            $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

            return $this->response;
        }

        $url = trim($this->getRequest()->getData('url'));
        $url = str_replace(" ", "%20", $url);
        $url = parse_url($url, PHP_URL_SCHEME) === null ? 'http://' . $url : $url;
        $this->setRequest($this->getRequest()->withData('url', $url));

        $domain = '';
        if ($this->getRequest()->getData('domain')) {
            $domain = $this->getRequest()->getData('domain');
        }
        if (!in_array($domain, get_multi_domains_list())) {
            $domain = '';
        }

        $linkWhere = [
            'url_hash' => sha1($this->getRequest()->getData('url')),
            'user_id' => $user->id,
            'status' => 1,
            'ad_type' => $this->getRequest()->getData('ad_type'),
            'url' => $this->getRequest()->getData('url'),
        ];

        if ($this->getRequest()->getData('alias') && strlen($this->getRequest()->getData('alias')) > 0) {
            $linkWhere['alias'] = $this->getRequest()->getData('alias');
        }

        $link = $this->Links->find()->where($linkWhere)->first();

        if ($link) {
            $content = [
                'status' => 'success',
                'message' => '',
                'url' => get_short_url($link->alias, $domain),
            ];
            $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

            return $this->response;
        }

        $user_plan = get_user_plan($user->id);

        if ($user_plan->url_daily_limit) {
            $start = Time::now()->startOfDay()->format('Y-m-d H:i:s');
            $end = Time::now()->endOfDay()->format('Y-m-d H:i:s');

            $links_daily_count = $this->Links->find()
                ->where([
                    'user_id' => $user_id,
                    "created BETWEEN :date1 AND :date2",
                ])
                ->bind(':date1', $start, 'datetime')
                ->bind(':date2', $end, 'datetime')
                ->count();

            if ($links_daily_count >= $user_plan->url_daily_limit) {
                $content = [
                    'status' => 'error',
                    'message' => __('Your account has exceeded its daily created short links limit.'),
                    'url' => '',
                ];
                $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

                return $this->response;
            }
        }

        if ($user_plan->url_monthly_limit) {
            $start = Time::now()->startOfMonth()->format('Y-m-d H:i:s');
            $end = Time::now()->endOfMonth()->format('Y-m-d H:i:s');

            $links_monthly_count = $this->Links->find()
                ->where([
                    'user_id' => $user_id,
                    "created BETWEEN :date1 AND :date2",
                ])
                ->bind(':date1', $start, 'datetime')
                ->bind(':date2', $end, 'datetime')
                ->count();

            if ($links_monthly_count >= $user_plan->url_monthly_limit) {
                $content = [
                    'status' => 'error',
                    'message' => __('Your account has exceeded its monthly created short links limit.'),
                    'url' => '',
                ];
                $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

                return $this->response;
            }
        }

        $link = $this->Links->newEntity();
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $this->getRequest()->getData('url');
        $data['url_hash'] = sha1($this->getRequest()->getData('url'));

        $data['domain'] = $domain;

        if ($user_plan->alias && !empty($this->getRequest()->getData('alias'))) {
            $data['alias'] = $this->getRequest()->getData('alias');
        } else {
            $data['alias'] = $this->Links->geturl();
        }

        $data['ad_type'] = $this->getRequest()->getData('ad_type');
        $link->status = 1;
        $link->hits = 0;
        $link->method = 1;
        $link->last_activity = Time::now();

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => '',
        ];

        if ($user_id === 1 && get_option('disable_meta_home') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($this->getRequest()->getData('url'));
        }

        if ($user_id !== 1 && get_option('disable_meta_member') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($this->getRequest()->getData('url'));
        }

        $data['title'] = $linkMeta['title'];
        $data['description'] = $linkMeta['description'];
        $link->image = $linkMeta['image'];

        $link = $this->Links->patchEntity($link, $data);
        if ($this->Links->save($link)) {
            $content = [
                'status' => 'success',
                'message' => '',
                'url' => get_short_url($link->alias, $domain),
            ];
            $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

            return $this->response;
        }

        $message = __('Invalid URL.');
        if ($link->getErrors()) {
            $error_msg = [];
            foreach ($link->getErrors() as $errors) {
                if (is_array($errors)) {
                    foreach ($errors as $error) {
                        $error_msg[] = $error;
                    }
                } else {
                    $error_msg[] = $errors;
                }
            }

            if (!empty($error_msg)) {
                $message = implode("<br>", $error_msg);
            }
        }

        $content = [
            'status' => 'error',
            'message' => $message,
            'url' => '',
        ];
        $this->setResponse($this->getResponse()->withStringBody(json_encode($content)));

        return $this->response;
    }
}
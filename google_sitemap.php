<?php
/**
 * Google XML Sitemap
 * -----------------------------------------------
 * Modified version of the following github google_sitemap
 * https://github.com/proudcommerce/google_sitemap
 * -----------------------------------------------
 * by DIATOM Internet & Medien GmbH // 27.07.2009
 * by Proud Sourcing GmbH           // 19.07.2013
 * by Joachim Barthel               // 25.07.2016
 * by [KarliCode] Philipp Lindenberg            // 2020-01-23
 * -----------------------------------------------
 * =====================================================================
 */

/*
 * #############################
 * Set the configuration details
 * #############################
 */

define('OXIDSOURCEPATH', '/path/to/oxid/installfolder/source');                     // sourcepath to oxid installation (can be found in config)
define('SITEMAP_EXPORT_DIR', 'sitemap');                                            // Foldername for sitemaps to export to
define('SITEMAP_FILENAME', 'sitemap');                                              // basename for sitemaps
define('SITEMAP_FULLPATH', OXIDSOURCEPATH . '/' . SITEMAP_EXPORT_DIR . '/');        // full path to sitemaps
define('SITEMAP_FILE_OFFSET', 20000);                                               // set the offset amount (urls per file)
define('OXIDSHOP_LANGUAGES', [0, 1]);                                               // set all wanted shop language ids
define('OXSEO_EXPIRED', TRUE);                                                     // true tue ignore oxseo.oxexpired = 1)

global $export;
// configuration export (set the desired categories to TRUE and adjust priority and changefreq to your needs)
// Hint: $export['staticURLs']['excludedURLS'] can be used to exclude specific URLS from sitemap
$export['categories']   = [
    'name'          =>  'categories',
    'export'        =>  TRUE,
    'listcontent'   => [
        'priority'      => '1.0',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'weekly'
    ]
];
$export['cmsSites']     = [
    'name'          => 'cmsSites',
    'export'        => TRUE,
    'listcontent'   => [
        'priority'      => '0.6',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'weekly',
    ]
];
$export['vendors']      = [
    'name'          => 'vendors',
    'export'        => TRUE,
    'listcontent'   => [
        'priority'      => '0.7',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'weekly',
    ]
];
$export['manufacturers'] = [
    'name'          => 'manufacturers',
    'export'        => TRUE,
    'listcontent'   => [
        'priority'      => '0.7',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'weekly',
    ]
];
$export['products']     = [
    'name'          =>  'products',
    'export'        =>  TRUE,
    'listcontent'   => [
        'priority'      => '1.0',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'daily'
    ]
];
$export['productsManufacturer'] = [
    'name'          => 'productsManufacturer',
    'export'        => FALSE,
    'listcontent'   => [
        'priority'      => '0.9',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'daily',
    ]
];
$export['productsVendor']   = [
    'name'          => 'productsVendor',
    'export'        => FALSE,
    'listcontent'   => [
        'priority'      => '0.9',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'daily',
    ]
];
$export['tags']         = [
    'name'          => 'tags',
    'export'        => FALSE,
    'listcontent'   => [
        'priority'      => '0.8',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'weekly',
    ]
];
$export['staticUrls']   = [
    'name'          => 'staticUrls',
    'export'        => TRUE,
    'listcontent'   => [
        'priority'      => '0.5',
        'lastMod'       => date(DateTime::ATOM),
        'changefreq'    => 'weekly',
    ],
    'excludedURLS'  => [
        'admin/',
        'Core/',
        'tmp/',
        'views/',
        'Setup/',
        'log/',
        'bin/',
        'newsletter/',
        'mein-passwort/',
        'en/newsletter/',
        'meine-lieblingslisten/',
        'links/',
        'de/my-downloads/',
        'mehr/',
        'meine-adressen/',
        'AGB/',
        'en/Terms-and-Conditions/',
        'warenkorb/',
        'en/cart/',
        'mein-konto/',
        'en/my-account/',
        'mein-merkzettel/',
        'en/my-wishlist/',
        'mein-wunschzettel/',
        'en/my-gift-registry/',
        'index.php?cl=account_wishlist',
        'konto-eroeffnen/',
        'en/open-account/',
        'passwort-vergessen/',
        'en/forgot-password/',
    ]
];



/*
 *
 *
 *  HERE STARTS THE CODE
 *
 *
 */

/*
 * #################################
 * Sets the classes for creating sitemaps
 * #################################
 */

// Loads the Oxidshop Config
class ShopConfig
{
    /**
     * @var string
     */
    public $dbHost;
    /**
     * @var string
     */
    public $dbName;
    /**
     * @var string
     */
    public $dbPort;
    /**
     * @var string
     */
    public $dbUser;
    /**
     * @var string
     */
    public $dbPwd;
    /**
     * @var string
     */
    public $sSSLShopURL;

    public function __construct()
    {
        require_once OXIDSOURCEPATH . '/config.inc.php';
        $this->sSSLShopURL = rtrim($this->sSSLShopURL, '/') . '/';
    }

    /**
     * @return string
     */
    public function getExpiredSQL() :string
    {
        if (OXSEO_EXPIRED)
        {
            return 'seo.oxexpired = 0 AND ';
        }

        return '';
    }

}


// Creates functions for procedural code
class Sitemap
{
    /**
     * @param $dbHost
     * @param $dbName
     * @param $dbPort
     * @param $dbUser
     * @param $dbPass
     *
     * @return \PDO
     */
    public function connectDB($dbHost, $dbName, $dbPort, $dbUser, $dbPass) :PDO
    {
        if (false !== strpos($dbHost, ':'))
        {
            $aTmp       = explode(':', $dbHost);
            $dbHost     = $aTmp[0];
            $dbPort     = $aTmp[1];
        }

        $dsn = "mysql:host={$dbHost};dbname={$dbName}";
        if (!empty($dbPort))
        {
            $dsn .= ";port={$dbPort}";
        }

        return new PDO($dsn, $dbUser, $dbPass);
    }

    /**
     * @param $content
     * @param $lang
     *
     * @param $timesToCallScript
     *
     * @return string
     */
    public function createSQLStatements($content, $lang, $timesToCallScript) :string
    {
        $ShopConfig = new ShopConfig();
        $expired    = $ShopConfig->getExpiredSQL();

        if ($content === 'countOxArticleScriptCalls')
        {
            return "SELECT
                COUNT(DISTINCT oxart.oxid)
            FROM
                oxarticles as oxart
            LEFT JOIN oxobject2category as oxobj2cat
                ON (oxobj2cat.oxobjectid = oxart.oxid)
            LEFT JOIN oxcategories as oxcat
                ON (oxcat.oxid = oxobj2cat.oxcatnid)
            LEFT JOIN oxseo as seo
                ON (oxart.oxid = seo.oxobjectid)
            WHERE
                oxart.oxactive = 1 AND
                oxcat.oxactive = 1 AND
                oxcat.oxhidden = 0 AND
                {$expired}
                seo.oxlang     = {$lang} AND
                seo.oxtype     = 'oxarticle'";

        } elseif ($content === 'categories')
        {
            return "SELECT 
                seo.oxseourl
            FROM
                oxcategories as oxcats
            LEFT JOIN
                oxseo as seo ON (oxcats.oxid = seo.oxobjectid)
            WHERE
                oxcats.oxactive = 1 AND
                oxcats.oxhidden = 0 AND
                seo.oxtype      = 'oxcategory' AND
                seo.oxstdurl NOT LIKE ('%pgNr=%') AND
                {$expired}
                seo.oxlang      = {$lang}
            GROUP BY
                oxcats.oxid;";

        } elseif ($content === 'products')
        {
            $start = SITEMAP_FILE_OFFSET;
            if (1 === $timesToCallScript)
            {
                $end = 0;
            } else
            {
                $end = (($timesToCallScript - 1) * SITEMAP_FILE_OFFSET) - 1;
            }

            return "SELECT
                oxart.oxtimestamp,
                seo.oxseourl
            FROM
                oxarticles as oxart
            LEFT JOIN oxobject2category as oxobj2cat
                ON (oxobj2cat.oxobjectid = oxart.oxid)
            LEFT JOIN oxcategories as oxcat
                ON (oxcat.oxid = oxobj2cat.oxcatnid)
            LEFT JOIN oxseo as seo
                ON (oxart.oxid = seo.oxobjectid)
            WHERE
                oxart.oxactive = 1 AND
                oxcat.oxactive = 1 AND
                oxcat.oxhidden = 0 AND
                seo.oxlang = {$lang} AND
                seo.oxtype='oxarticle' AND
                {$expired}
                seo.oxstdurl LIKE ('%cnid=%')
            GROUP BY
                oxart.oxid
            LIMIT " . $start . " OFFSET " . $end . ";";

        } elseif ($content === 'cmsSites')
        {
            return "SELECT
                seo.oxseourl
            FROM
                oxcontents as content
            LEFT JOIN
                oxseo as seo ON (content.oxid=seo.oxobjectid)
            WHERE
                content.oxactive = 1 AND
                content.oxfolder = '' AND
                seo.oxseourl <> '' AND
                seo.oxseourl NOT LIKE ('%META%') AND
                {$expired}
                seo.oxlang = {$lang}
            GROUP BY
                content.oxid;";

        } elseif ($content === 'vendors')
        {
            return "SELECT
                seo.oxseourl
            FROM
                oxvendor as vendor
            LEFT JOIN
                oxseo as seo ON (vendor.oxid=seo.oxobjectid)
            WHERE
                vendor.oxactive = 1 AND
                seo.oxseourl <> '' AND
                seo.oxtype='oxvendor' AND
                {$expired}
                seo.oxlang = {$lang}
            GROUP BY
                vendor.oxid;";

        } elseif ($content === 'manufacturers')
        {
            return "SELECT
                seo.oxseourl
            FROM
                oxmanufacturers as manufacturer
            LEFT JOIN
                oxseo as seo ON (manufacturer.oxid=seo.oxobjectid)
            WHERE
                manufacturer.oxactive = 1 AND
                seo.oxseourl <> '' AND
                seo.oxtype='oxmanufacturer' AND
                {$expired}
                seo.oxlang = {$lang}
            GROUP BY
                manufacturer.oxid;";

        } elseif ($content === 'tags')
        {
            return "SELECT
                seo.oxseourl
            FROM
                oxseo seo
            WHERE
                seo.oxseourl <> '' AND
                seo.oxstdurl LIKE '%=tag%' AND
                seo.oxtype='dynamic' AND
                {$expired}
                seo.oxlang = " . $lang;

        } elseif ($content === 'staticUrls')
        {
            return "SELECT
                seo.oxseourl
            FROM
                oxseo seo
            WHERE
                seo.oxseourl <> '' AND
                seo.oxtype='static' AND
                {$expired}
                seo.oxlang = " . $lang;

        } elseif ($content === 'productsManufacturer')
        {
            return "SELECT
                oxart.oxtimestamp,
                seo.oxseourl
            FROM
                oxarticles as oxart
            LEFT JOIN oxseo as seo
                ON (oxart.oxid = seo.oxobjectid)
            WHERE
                oxart.oxactive = 1 AND
                seo.oxlang = {$lang} AND
                seo.oxtype='oxarticle' AND
                {$expired}
                seo.oxstdurl LIKE ('%mnid=%')
            GROUP BY
                oxart.oxid";

        } elseif ($content === 'productsVendor')
        {
            return "SELECT
                oxart.oxtimestamp,
                seo.oxseourl
            FROM
                oxarticles as oxart
            LEFT JOIN oxseo as seo
                ON (oxart.oxid = seo.oxobjectid)
            WHERE
                oxart.oxactive = 1 AND
                seo.oxlang = {$lang} AND
                seo.oxtype='oxarticle' AND
                {$expired}
                seo.oxstdurl LIKE ('%cnid=v%')
            GROUP BY
                oxart.oxid";
        }
    }

    /**
     * @param $dbConnection
     * @param $sqlStatement
     * @param $content
     * @param $excludedURLS
     *
     * @return array
     */
    public function getContentDatafromDB($dbConnection, $sqlStatement, $content, $excludedURLS) :array
    {
        $list           = [];

        foreach ($dbConnection->query($sqlStatement) as $row)
        {
            if (!in_array($row['oxseourl'], $excludedURLS, TRUE))
            {
                $list[] = [
                    'loc'        => SSLSHOPURL . $row['oxseourl'],
                    'priority'   => $content['listcontent']['priority'],
                    'lastMod'    => $content['listcontent']['lastMod'],
                    'changefreq' => $content['listcontent']['changefreq']
                ];
            }
        }

        return $list;
    }



}

class processFiles
{
    /**
     * creates xml data / sitemap-content
     *
     * @param array $data
     *
     * @return array
     */
    public function createSitemap($data) :array
    {
        $mapdata[] =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($data as $key => $val)
        {
            $mapdata[] =
                "  <url>\n" .
                "    <loc>{$val['loc']}</loc>\n" .
                "    <priority>{$val['priority']}</priority>\n" .
                "    <lastmod>{$val['lastMod']}</lastmod>\n" .
                "    <changefreq>{$val['changefreq']}</changefreq>\n" .
                "  </url>";
        }
        $mapdata[] = '</urlset>';
        return $mapdata;
    }

    /**
     * stores xml-file to filesystem
     *
     * @param string $smdata
     *
     * @return string
     */
    public function createXmlFile($smdata) :string
    {

        global $timesToCallScript;
        $fname = SITEMAP_FULLPATH . SITEMAP_FILENAME . $timesToCallScript . '.xml';
        $fp    = fopen($fname, 'wb+');
        fwrite($fp, implode("\n", $smdata));
        fclose($fp);
        return $fname;
    }

    /**
     * append new sitemap to sitemap index
     *
     * @param $timesToCallScript
     *
     * @return void
     */
    public function createSitemapIndex($timesToCallScript) :void
    {
        $sitemaps   = [];
        // build xml-content
        $smindex    =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        for ($i = 1; $i <= $timesToCallScript; $i++)
        {
            $loc        =
                '    <loc>' . SSLSHOPURL . SITEMAP_EXPORT_DIR . '/' . SITEMAP_FILENAME . $i . ".xml</loc>\n";
            $last       = '    <lastmod>' . date(DateTime::ATOM) . "</lastmod>\n";
            $sitemaps[] = "  <sitemap>\n" . $loc . $last . "  </sitemap>\n";
        }
        $maps           = $smindex . "\n" . implode("\n", $sitemaps);
        $sitemapIndex   = $maps . '</sitemapindex>';
        // write to file
        file_put_contents(SITEMAP_FILENAME . '.xml', $sitemapIndex);
    }
}



/*
 * #################################
 * Start of procedural code
 * #################################
 */
$countOxArticleScriptCalls  = 0;
$error                      = [];
$xmlListInsert              = [];
$xmlListInsert2             = [];
$xmlList_static             = [];
$xmlList_products           = [];
$xmlList                    = [];



$shopConfig         = new ShopConfig();
define('SSLSHOPURL', rtrim($shopConfig->sSSLShopURL, '/') . '/');
$Sitemap            = new Sitemap();
$processFiles       = new processFiles();
$dbConnection       = $Sitemap->connectDB($shopConfig->dbHost, $shopConfig->dbName, $shopConfig->dbPort, $shopConfig->dbUser, $shopConfig->dbPwd);


$timesToCallScript  = 1;
if (isset($_SERVER['argv'][1]) && '-c' === $_SERVER['argv'][1])
{
    $timesToCallScript = $_SERVER['argv'][2];
    if (!preg_match("/\d+/", $timesToCallScript))
    {
        die("Illegal call.\n");
    }
}



foreach (OXIDSHOP_LANGUAGES as $languageID)
{
    $countOxArticleScriptCalls += $dbConnection->query($Sitemap->createSQLStatements('countOxArticleScriptCalls', $languageID, $timesToCallScript))->fetchColumn();
}
$countOxArticleScriptCalls  = ceil($countOxArticleScriptCalls/SITEMAP_FILE_OFFSET);



if ($timesToCallScript === 1)
{
    foreach (OXIDSHOP_LANGUAGES as $languageID)
    {
        foreach ($export as $content)
        {
            if ($content['export'])
            {
                $xmlListInsert[] = $Sitemap->getContentDatafromDB
                ($dbConnection, $Sitemap->createSQLStatements
                ($content['name'], $languageID, $timesToCallScript),
                 $content, $export['staticUrls']['excludedURLS']);
            }
        }
    }

    foreach ($xmlListInsert as $key => $value)
    {
        foreach ($value as $second => $val)
        {
            $xmlList_static[] = $val;
        }
    }
}

if ($export['products']['export'])
{
    foreach (OXIDSHOP_LANGUAGES as $languageID)
    {
        $xmlListInsert2[] = $Sitemap->getContentDatafromDB
        ($dbConnection, $Sitemap->createSQLStatements
        ($export['products']['name'], $languageID, $timesToCallScript),
         $export['products'], $export['staticUrls']['excludedURLS']);
    }

    foreach ($xmlListInsert2 as $key => $value)
    {
        foreach ($value as $second => $val)
        {
            $xmlList_products[] = $val;
        }
    }
}

$xmlList = array_merge($xmlList_static, $xmlList_products);
$dbConnection = NULL;


// create sitemap
$sitemapdata = $processFiles->createSitemap($xmlList);
$smfile      = $processFiles->createXmlFile($sitemapdata);

// create global sitemaps-index-file (watch sitemaps.org for more infos..)
$processFiles->createSitemapIndex($timesToCallScript);

//** RECALL SCRIPT
if ($timesToCallScript < $countOxArticleScriptCalls)
{
    // memory seems to hold list-array-values, maybe depends on local environment
    unset($xmlList);
    // call itself
    $exec = 'php ' . __FILE__ . ' -c ' . ($timesToCallScript + 1);
    //echo "\n".$exec."\n"; //debug
    system($exec);
    exit(0);
}

exit(0);
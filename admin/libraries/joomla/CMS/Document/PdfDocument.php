<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Document\HtmlDocument;
use Dompdf\Dompdf;

jimport('joomla.utilities.utility');

/**
 * HtmlDocument class, provides an easy interface to parse and display a HTML document
 *
 * @since  1.7.0
 */
class PdfDocument extends HtmlDocument
{
    private $engine    = null;

    private $name = 'joomla';

    /**
     * Document mime type
     *
     * @var    string
     * @since  3.5
     */
    public $_mime = 'application/pdf';

    /**
     * Class constructore
     * @param	array	$options Associative array of options
     */

    public function __construct($options = [])
    {
        parent::__construct($options);

        //set document type
        $this->_type = 'pdf';

        if (!$this->iniDomPdf()) {
            JError::raiseError(500, 'No PDF lib found');
        }
    }

    protected function iniDomPdf()
    {
        if (!defined('DOMPDF_ENABLE_REMOTE')) {
            define('DOMPDF_ENABLE_REMOTE', true);
        }

        //set the font cache directory to Joomla's tmp directory
        $config = Factory::getConfig();
        if (!defined('DOMPDF_FONT_CACHE')) {
            define('DOMPDF_FONT_CACHE', $config->get('tmp_path'));
        }

        // Default settings are a portrait layout with an A4 configuration using millimeters as units
        $this->engine = new Dompdf();

        return true;
    }

    /**
     * Sets the document name
     * @param   string   $name	Document name
     * @return  void
     */
    public function setName($name = 'joomla')
    {
        $this->name = $name;
    }

    /**
     * Returns the document name
     * @return	string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Render the document.
     * @access public
     * @param boolean 	$caching		If true, cache the output
     * @param array		$params		Associative array of attributes
     * @return	string
     */

    public function render($caching = false, $params = [])
    {
        $data = parent::render($caching, $params);

        $this->fullPaths($data);

        $this->engine->load_html($data);
        $this->engine->render();
        $this->engine->stream($this->getName() . '.pdf');
        return '';
    }


    /**
     * parse relative images a hrefs and style sheets to full paths
     * @param	string	&$data
     */
    private function fullPaths(&$data)
    {
        $data = str_replace('xmlns=', 'ns=', $data);
        libxml_use_internal_errors(true);
        try {
            $ok = new \SimpleXMLElement($data);
            if ($ok) {
                $uri = JUri::getInstance();
                $base = $uri->getScheme() . '://' . $uri->getHost();
                $imgs = $ok->xpath('//img');
                foreach ($imgs as &$img) {
                    if (!strstr($img['src'], $base)) {
                        $img['src'] = $base . $img['src'];
                    }
                }

                //links
                $as = $ok->xpath('//a');
                foreach ($as as &$a) {
                    if (!strstr($a['href'], $base)) {
                        $a['href'] = $base . $a['href'];
                    }
                }

                // css files.
                $links = $ok->xpath('//link');
                foreach ($links as &$link) {
                    if ($link['rel'] == 'stylesheet' && !strstr($link['href'], $base)) {
                        $link['href'] = $base . $link['href'];
                    }
                }

                $data = $ok->asXML();
            }
        } catch (Exception $err) {
            //oho malformed html - if we are debugging the site then show the errors
            // otherwise continue, but it may mean that images/css/links are incorrect
            $errors = libxml_get_errors();
            if (JDEBUG) {
                echo "<pre>";
                print_r($errors);
                echo "</pre>";
                exit;
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see JDocumentHTML::getBuffer()
     */
    public function getBuffer($type = null, $name = null, $attribs = [])
    {
        if ($type == 'head' || $type == 'component') {
            return parent::getBuffer($type, $name, $attribs);
        } else {
            return '';
        }
    }
}

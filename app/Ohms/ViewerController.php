<?php
namespace Ohms;

use Exception;
use Ohms\Interview\AbstractInterview;

/**
 * View Controller
 *
 * @copyright Copyright &copy; 2012 Louie B. Nunn Center, University of Kentucky
 * @link      http://www.uky.edu
 * @license   https://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */
class ViewerController
{
    /**
     * @var ?AbstractInterview
     */
    protected ?AbstractInterview $interview;

    /**
     * @var string|null
     */
    protected ?string $interviewName;

    /**
     * @var array|false
     */
    protected array $config;

    /**
     * @param ?string $interviewName
     * @throws Exception
     */
    public function __construct(?string $interviewName)
    {
        $this->config        = parse_ini_file("config/config.ini", true);
        $this->interview     = Interview::getInstance($this->config, $this->config['tmpDir'], $interviewName);
        $this->interviewName = $interviewName;
    }

    /**
     * @param ?string $action
     * @param ?string $kw
     */
    public function route(?string $action, ?string $kw)
    {
        switch ($action) {
            case 'pdf':
                CustomPdf::__prepare($this->interview, $this->config);
                exit();
            break;
            case 'metadata':
                header('Content-type: application/json');
                echo $this->interview->toJSON();
                exit();
            break;
            case 'xml':
                header('Content-type: application/xml');
                echo $this->interview->toXML();
                exit();
            break;
            case 'transcript':
                echo $this->interview->Transcript->getTranscript();
            break;
            case 'search':
                if (isset($kw)) {
                    echo $this->interview->Transcript->keywordSearch($kw);
                }
                exit();
            break;
            case 'index':
                if (isset($kw)) {
                    $translate = ($_GET['translate'] == '1' ? 1 : 0);
                    echo $this->interview->Transcript->indexSearch($kw, $translate);
                }
                exit();
            break;
            case 'all':
            break;
            default:
                $interview      = $this->interview;
                $interviewName  = $this->interviewName;
                $config         = $this->config;
                $template       = $this->useTemplate('template', 'viewer.tmpl.php');
                $searchTemplate = $this->useTemplate('search_template', 'search.tmpl.php');
                include_once $template;
            break;
        }
    }

    /**
     * Select a template to use, if it exists, otherwise use the default.
     *
     * @param string|null $config_template
     * @param string      $default
     * @return string
     */
    protected function useTemplate(?string $config_template, string $default): string
    {
        if (!empty($this->config[$config_template]) && is_readable('tmpl/'.basename($this->config[$config_template]))) {
            return 'tmpl/'.$this->config[$config_template];
        }
        return "tmpl/$default";
    }
}

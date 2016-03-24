<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 16/3/24
 * Time: 下午3:05
 */
namespace Amlun\TLD;
/**
 * Class Extract
 * @package Amlun\TLD
 */
class Extract
{
    /**
     * local data file
     */
    const LOCAL_DATA = __DIR__ . DIRECTORY_SEPARATOR . 'tld.dat';
    /**
     * TLD resource data
     * @var string
     */
    public static $resource_url = 'https://publicsuffix.org/list/effective_tld_names.dat';
    /**
     * Extract Instance
     * @var Extract
     */
    private static $_instance;
    /**
     * TLD tree
     * @var array
     */
    private $_tld_tree = [];

    public static function instance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->load();
    }

    /**
     * load tld names from local file
     */
    public function load()
    {
        if (empty($this->_tld_tree)) {
            if (!file_exists(self::LOCAL_DATA)) {
                $content = file_get_contents(self::$resource_url);
                file_put_contents(self::LOCAL_DATA, $content);
            }
            $handle = fopen(self::LOCAL_DATA, 'r');
            if (!$handle) {
                throw new \Exception('Can not read content from file: ' . self::LOCAL_DATA);
            }
            while (($line = fgets($handle, 4096)) !== false) {
                $line = trim($line);
                if (empty($line) || Utils::start_with($line, "//")) {
                    continue;
                }
                $tld_parts = explode('.', $line);
                $this->build_sub_domain($this->_tld_tree, $tld_parts);
            }
            if (!feof($handle)) {
                throw new \Exception('Error: unexpected fgets() fail ' . self::LOCAL_DATA);
            }
            fclose($handle);
        }
        return $this->_tld_tree;
    }

    /**
     * @param $host
     * @return null|string
     */
    public function domain($host)
    {
        $host = str_replace(' ', '', $host);
        $host_parts = explode('.', $host);
        $result = $this->find_domain($host_parts, $this->_tld_tree);
        if (empty($result)) {
            return null;
        }

        if (!strpos($result, '.')) {
            $cnt = count($host_parts);
            if ($cnt == 1 || empty($host_parts[$cnt - 2])) return null;
            if (!$this->$this->valid_domain($host_parts[$cnt - 2]) || !$this->valid_domain($host_parts[$cnt - 1])) return null;
            return $host_parts[$cnt - 2] . '.' . $host_parts[$cnt - 1];
        }

        return $result;
    }

    public function find_domain($remaining_domain_parts, &$node)
    {
        if (empty($remaining_domain_parts)) return null;

        $sub = array_pop($remaining_domain_parts);
        $result = null;

        if (isset($node['!'])) {
            return '#';
        }

        if (!$this->valid_domain($sub)) {
            return null;
        }

        if (is_array($node) && array_key_exists($sub, $node)) {
            $result = $this->find_domain($remaining_domain_parts, $node[$sub]);
        } else if (is_array($node) && array_key_exists('*', $node)) {
            $result = $this->find_domain($remaining_domain_parts, $node['*']);
        } else {
            return $sub;
        }

        if ($result == '#') {
            return $sub;
        } else if (strlen($result) > 0) {
            return $result . '.' . $sub;
        }
        return null;
    }

    public function valid_domain($dom_part)
    {
        $len = strlen($dom_part);

        if ($len > 63) return false;

        if ($len < 1) return false;

        if (!preg_match("/^([a-z0-9])(([a-z0-9-])*([a-z0-9]))*$/", $dom_part)) return false;

        return true;
    }

    /**
     * @param $node
     * @param $tld_parts
     */
    private function build_sub_domain(&$node, $tld_parts)
    {
        $dom = trim(array_pop($tld_parts));
        $is_not_domain = false;

        if (Utils::start_with($dom, '!')) {
            $dom = substr($dom, 1);
            $is_not_domain = true;
        }

        if (!array_key_exists($dom, $node)) {
            $node[$dom] = $is_not_domain ? ['!' => ''] : [];
        }

        if (!$is_not_domain && count($tld_parts) > 0) {
            $this->build_sub_domain($node[$dom], $tld_parts);
        }
    }
}
<?php

namespace Popo1h\PhaadminProvider\ActionAuthLoader;

use Popo1h\PhaadminCore\ActionAuth;
use Popo1h\PhaadminProvider\ActionAuthLoader;

class PathActionAuthLoader extends ActionAuthLoader
{
    /**
     * @var string
     */
    private $actionAuthFilePath;
    /**
     * @var string
     */
    private $actionAuthBaseNamespace;
    /**
     * @var string
     */
    private $actionAuthFileExt;
    /**
     * @var \Closure
     */
    private $actionAuthFileFilter;
    /**
     * @var array
     */
    private $actionAuthMap;

    /**
     * PathActionAuthLoader constructor.
     * @param string $actionAuthFilePath
     * @param string $actionAuthBaseNamespace
     * @param string $actionAuthFileExt
     * @param \Closure $actionAuthFileFilter
     */
    public function __construct($actionAuthFilePath, $actionAuthBaseNamespace, $actionAuthFileExt = 'php', $actionAuthFileFilter = null)
    {
        $this->actionAuthFilePath = $actionAuthFilePath;
        $this->actionAuthBaseNamespace = $actionAuthBaseNamespace;
        $this->actionAuthFileExt = $actionAuthFileExt;
        $this->actionAuthFileFilter = $actionAuthFileFilter;
    }

    private function getFiles($path, $basePath = '', $fileFilter = null)
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = [];

        $handle = dir($path);
        while (true) {
            $filename = $handle->read();
            if (!$filename) {
                break;
            } elseif ($filename == '.' || $filename == '..') {
                continue;
            }

            $fullFilename = $path . '/' . $filename;
            if (is_dir($fullFilename)) {
                $files = array_merge($files, $this->getFiles($fullFilename, $basePath . $filename . '/', $fileFilter));
            } else {
                if (is_callable($fileFilter) && call_user_func_array($fileFilter, [$fullFilename]) != true) {
                    continue;
                }
                $files[$basePath . $filename] = $fullFilename;
            }
        }

        return $files;
    }

    private function initActionAuthMap()
    {
        if (!isset($this->actionAuthMap)) {
            $actionAuthFiles = $this->getFiles($this->actionAuthFilePath, '', function ($fullFilename) {
                if (!preg_match('/\.' . $this->actionAuthFileExt . '$/', $fullFilename)) {
                    return false;
                } elseif (is_callable($this->actionAuthFileFilter) && call_user_func_array($this->actionAuthFileFilter, [$fullFilename]) != true) {
                    return false;
                }

                return true;
            });

            $actionAuthExtLength = strlen($this->actionAuthFileExt) + 1;
            foreach ($actionAuthFiles as $relativeFilename => $fullFilename) {
                $actionAuthClass = $this->actionAuthBaseNamespace . str_replace('/', '\\', substr($relativeFilename, 0, -$actionAuthExtLength));

                if (class_exists($actionAuthClass)) {
                    try {
                        $reflectionClass = new \ReflectionClass($actionAuthClass);
                        $actionAuth = $reflectionClass->newInstanceWithoutConstructor();

                        if ($actionAuth instanceof ActionAuth) {
                            $this->actionAuthMap[$actionAuth::getName()] = $actionAuth;
                        }
                    } catch (\Exception $e) {

                    }
                }
            }
        }
    }

    public function load($actionAuthName)
    {
        $this->initActionAuthMap();

        if (!isset($this->actionAuthMap[$actionAuthName])) {
            return null;
        }
        return $this->actionAuthMap[$actionAuthName];
    }

    public function getActionAuthMap()
    {
        $this->initActionAuthMap();

        return $this->actionAuthMap;
    }
}

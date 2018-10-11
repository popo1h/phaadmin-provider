<?php

namespace Popo1h\PhaadminProvider\ActionLoader;

use Popo1h\PhaadminCore\Action;
use Popo1h\PhaadminProvider\ActionLoader;

class PathActionLoader extends ActionLoader
{
    /**
     * @var string
     */
    private $actionFilePath;
    /**
     * @var string
     */
    private $actionBaseNamespace;
    /**
     * @var string
     */
    private $actionFileExt;
    /**
     * @var \Closure
     */
    private $actionFileFilter;
    /**
     * @var array
     */
    private $actionMap;

    /**
     * PathActionLoader constructor.
     * @param string $actionFilePath
     * @param string $actionBaseNamespace
     * @param string $actionFileExt
     * @param \Closure $actionFileFilter
     */
    public function __construct($actionFilePath, $actionBaseNamespace, $actionFileExt = 'php', $actionFileFilter = null)
    {
        $this->actionFilePath = $actionFilePath;
        $this->actionBaseNamespace = $actionBaseNamespace;
        $this->actionFileExt = $actionFileExt;
        $this->actionFileFilter = $actionFileFilter;
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

    private function initActionMap()
    {
        if (!isset($this->actionMap)) {
            $actionFiles = $this->getFiles($this->actionFilePath, '', function ($fullFilename) {
                if (!preg_match('/\.' . $this->actionFileExt . '$/', $fullFilename)) {
                    return false;
                } elseif (is_callable($this->actionFileFilter) && call_user_func_array($this->actionFileFilter, [$fullFilename]) != true) {
                    return false;
                }

                return true;
            });

            $this->actionMap = [];
            $actionExtLength = strlen($this->actionFileExt) + 1;
            foreach ($actionFiles as $relativeFilename => $fullFilename) {
                $actionClass = $this->actionBaseNamespace . str_replace('/', '\\', substr($relativeFilename, 0, -$actionExtLength));

                if (class_exists($actionClass)) {
                    try {
                        $reflectionClass = new \ReflectionClass($actionClass);
                        $action = $reflectionClass->newInstanceWithoutConstructor();

                        if ($action instanceof Action) {
                            $this->actionMap[$action::getName()] = $action;
                        }
                    } catch (\Exception $e) {

                    }
                }
            }
        }
    }

    public function load($actionName)
    {
        $this->initActionMap();

        if (!isset($this->actionMap[$actionName])) {
            return null;
        }
        return $this->actionMap[$actionName];
    }

    public function getActionMap()
    {
        $this->initActionMap();

        return $this->actionMap;
    }
}

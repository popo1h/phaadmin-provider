<?php

namespace Popo1h\PhaadminProvider\Action;

use Popo1h\PhaadminCore\Action;
use Popo1h\PhaadminCore\ActionAuth;
use Popo1h\PhaadminCore\ActionAuth\AutoByActionActionAuth;
use Popo1h\PhaadminCore\Response\JsonResponse;
use Popo1h\PhaadminProvider\ActionAuthLoader;
use Popo1h\PhaadminProvider\ActionLoader;
use Popo1h\Support\Objects\CommentHelper;

/**
 * @action-title admin服务基础信息
 * @access-auth null
 */
class ProviderInfoAction extends Action
{
    /**
     * @var ActionLoader[]
     */
    private $actionLoaders = [];
    /**
     * @var ActionAuthLoader[]
     */
    private $actionAuthLoaders = [];

    public static function getName()
    {
        return '__provider_info';
    }

    /**
     * @param ActionLoader[] $actionLoaders
     */
    public function setActionLoaders($actionLoaders)
    {
        $this->actionLoaders = [];
        foreach ($actionLoaders as $actionLoader) {
            if (!$actionLoader instanceof ActionLoader) {
                continue;
            }
            $this->actionLoaders[] = $actionLoader;
        }
    }

    /**
     * @param ActionAuthLoader[] $actionAuthLoaders
     */
    public function setActionAuthLoaders($actionAuthLoaders)
    {
        $this->actionAuthLoaders = [];
        foreach ($actionAuthLoaders as $actionAuthLoader) {
            if (!$actionAuthLoader instanceof ActionAuthLoader) {
                continue;
            }
            $this->actionAuthLoaders[] = $actionAuthLoader;
        }
    }

    protected function getAuthNameByAuthClass($authClassName)
    {
        if ($authClassName == 'null') {
            $accessAuth = ActionAuth::AUTH_NAME_NONE;
        } else {
            if (is_callable([$authClassName, 'getName'])) {
                $accessAuth = forward_static_call_array([$authClassName, 'getName'], []);
            } else {
                $accessAuth = ActionAuth::AUTH_NAME_ERROR;
            }
        }

        return $accessAuth;
    }

    protected function buildResponseJsonData()
    {
        $actionMap = [];
        $actionAuthMap = [];
        $autoByActionActionAuthList = [];

        foreach ($this->actionLoaders as $actionLoader) {
            $tempActionMap = $actionLoader->getActionMap();
            foreach ($tempActionMap as $actionName => $action) {
                if (!isset($actionMap[$actionName])) {
                    $actionMap[$actionName] = $action;
                }
            }
        }

        foreach ($this->actionAuthLoaders as $actionAuthLoader) {
            $tempActionAuthMap = $actionAuthLoader->getActionAuthMap();
            foreach ($tempActionAuthMap as $actionAuthName => $actionAuth) {
                if (!isset($actionMap[$actionAuthName])) {
                    $actionAuthMap[$actionAuthName] = $actionAuth;
                }
            }
        }

        $actionList = [];
        foreach ($actionMap as $actionName => $action) {
            try {
                $commentHelper = CommentHelper::createByClass($action);

                $actionTitleRes = $commentHelper->getCommentItemContents('action-title');
                if (!isset($actionTitleRes[0])) {
                    $actionTitle = '';
                } else {
                    $actionTitle = $actionTitleRes[0];
                }

                $accessAuthRes = $commentHelper->getCommentItemContents('access-auth');
                if (isset($accessAuthRes[0])) {
                    if ($accessAuthRes[0] == 'null') {
                        $accessAuthName = ActionAuth::AUTH_NAME_NONE;
                    } else {
                        $accessAuthName = $accessAuthRes[0];
                    }
                } else {
                    $accessAuthClassRes = $commentHelper->getCommentItemContents('access-auth-class');
                    if (isset($accessAuthClassRes[0])) {
                        $accessAuthName = $this->getAuthNameByAuthClass($accessAuthClassRes[0]);
                    } else {
                        $accessAuthAutoByActionRes = $commentHelper->getCommentItemContents('access-auth-auto-by-action');
                        if (isset($accessAuthAutoByActionRes[0]) && trim($accessAuthAutoByActionRes[0]) == 'true') {
                            $autoByActionActionAuth = new AutoByActionActionAuth($action);
                            $autoByActionActionAuthList[] = $autoByActionActionAuth;
                            $accessAuthName = $autoByActionActionAuth->getName();
                        } else {
                            $accessAuthName = ActionAuth::AUTH_NAME_NONE;
                        }
                    }
                }

                $requireAuthNameMap = [];

                $requireAuthRes = $commentHelper->getCommentItemContents('require-auth');
                foreach ($requireAuthRes as $requireAuthResItem) {
                    $requireAuthNameMap[$requireAuthResItem] = true;
                }

                $requireAuthClassRes = $commentHelper->getCommentItemContents('require-auth-class');
                foreach ($requireAuthClassRes as $requireAuthClassResItem) {
                    $requireAuthNameMap[$this->getAuthNameByAuthClass($requireAuthClassResItem)] = true;
                }

                $requireAuthNameList = [];
                foreach ($requireAuthNameMap as $requireAuthName => $value) {
                    $requireAuthNameList[] = $requireAuthName;
                }

                $actionList[] = [
                    'name' => $actionName,
                    'title' => $actionTitle,
                    'access_auth' => $accessAuthName,
                    'require_auth' => $requireAuthNameList,
                ];
            } catch (\Exception $e) {

            }
        }

        $actionAuthList = [];
        foreach ($actionAuthMap as $actionAuthName => $actionAuth) {
            if (!is_callable([$actionAuth, 'getTitle'])) {
                $actionAuthTitle = '';
            } else {
                $actionAuthTitle = forward_static_call_array([$actionAuth, 'getTitle'], []);
            }

            $actionAuthList[] = [
                'name' => $actionAuthName,
                'title' => $actionAuthTitle,
            ];
        }

        foreach ($autoByActionActionAuthList as $autoByActionActionAuth) {
            /**
             * @var $autoByActionActionAuth AutoByActionActionAuth
             */
            if (isset($actionAuthMap[$autoByActionActionAuth->getName()])) {
                continue;
            }
            $actionAuthList[] = [
                'name' => $autoByActionActionAuth->getName(),
                'title' => $autoByActionActionAuth->getTitle(),
            ];
        }

        return [
            'action_list' => $actionList,
            'action_auth_list' => $actionAuthList,
        ];
    }

    public function doAction()
    {
        return new JsonResponse($this->buildResponseJsonData());
    }
}

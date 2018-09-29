<?php

namespace Popo1h\PhaadminProvider\Action;

use Popo1h\PhaadminCore\Action;
use Popo1h\PhaadminCore\Response\JsonResponse;
use Popo1h\PhaadminProvider\ActionLoader;
use Popo1h\Support\Objects\CommentHelper;

/**
 * @action-title admin接口文档获取
 * @access-auth null
 */
class ActionDocInfoAction extends Action
{
    /**
     * @var ActionLoader[]
     */
    private $actionLoaders = [];

    public static function getName()
    {
        return '__action_doc_info';
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

    protected function buildResponseJsonData()
    {
        $actionMap = [];

        foreach ($this->actionLoaders as $actionLoader) {
            $tempActionMap = $actionLoader->getActionMap();
            foreach ($tempActionMap as $actionName => $action) {
                if (!isset($actionMap[$actionName])) {
                    $actionMap[$actionName] = $action;
                }
            }
        }

        $actionDocList = [];
        foreach ($actionMap as $actionName => $action) {
            try {
                $commentHelper = CommentHelper::createByClass($action);

                $docEnableRes = $commentHelper->getCommentItemContents('doc-enable');
                if (!isset($docEnableRes[0]) || trim($docEnableRes[0]) != 'true') {
                    continue;
                }

                $docTitle = '';
                $docTitleRes = $commentHelper->getCommentItemContents('doc-title');
                if (isset($docTitleRes[0])) {
                    $docTitle = $docTitleRes[0];
                } else {
                    $actionTitleRes = $commentHelper->getCommentItemContents('action-title');
                    if (isset($actionTitleRes[0])) {
                        $docTitle = $actionTitleRes[0];
                    }
                }

                $docType = 'post';
                $docTypeRes = $commentHelper->getCommentItemContents('doc-type');
                if (isset($docTypeRes[0])) {
                    $docType = $docTypeRes[0];
                }

                $docConsume = 'application/x-www-form-urlencoded';
                $docConsumeRes = $commentHelper->getCommentItemContents('doc-consume');
                if (isset($docConsumeRes[0])) {
                    $docConsume = $docConsumeRes[0];
                }

                $docParams = [];
                $docParamRes = $commentHelper->getCommentItemContents('doc-param');
                foreach ($docParamRes as $docParamResItem) {
                    $docParam = [
                        'name' => '',
                        'type' => '',
                        'desc' => '',
                        'in' => 'formData',
                        'required' => false,
                    ];

                    foreach (explode(' ', $docParamResItem) as $docParamItem) {
                        if (!strpos($docParamItem, '=')) {
                            continue;
                        }

                        list($paramName, $paramContent) = explode('=', $docParamItem, 2);
                        switch ($paramName) {
                            case 'name':
                            case 'type':
                            case 'desc':
                            case 'in':
                                $docParam[$paramName] = $paramContent;
                                break;
                            case 'required':
                                if ($paramContent == 'true') {
                                    $docParam[$paramName] = true;
                                } else {
                                    $docParam[$paramName] = false;
                                }
                                break;
                        }
                    }

                    if (!$docParam['name']) {
                        continue;
                    }

                    $docParams[] = $docParam;
                }

                $returnData = [];
                $docReturnRes = $commentHelper->getCommentItemContents('doc-return');
                foreach ($docReturnRes as $docReturnResItem) {
                    $docReturn = [
                        'name' => '',
                        'type' => '',
                        'desc' => '',
                        'path' => '',
                    ];

                    foreach (explode(' ', $docReturnResItem) as $docReturnItem) {
                        if (!strpos($docReturnItem, '=')) {
                            continue;
                        }

                        list($returnName, $returnContent) = explode('=', $docReturnItem, 2);
                        switch ($returnName) {
                            case 'name':
                            case 'type':
                            case 'desc':
                            case 'path':
                                $docReturn[$returnName] = $returnContent;
                                break;
                        }
                    }

                    if (!$docReturn['name']) {
                        continue;
                    }

                    $returnData[] = $docReturn;
                }

                $docTags = $commentHelper->getCommentItemContents('doc-tag');

                $docDeleteRes = $commentHelper->getCommentItemContents('doc-delete');
                if (!isset($docDeleteRes[0]) || trim($docDeleteRes[0]) != 'true') {
                    $docDelete = false;
                } else {
                    $docDelete = true;
                }

                $actionDocList[] = [
                    'name' => $actionName,
                    'title' => $docTitle,
                    'type' => $docType,
                    'consume' => $docConsume,
                    'params' => $docParams,
                    'return' => $returnData,
                    'tags' => $docTags,
                    'delete' => $docDelete,
                ];
            } catch (\Exception $e) {

            }
        }

        return [
            'action_doc_list' => $actionDocList,
        ];
    }

    public function doAction()
    {
        return new JsonResponse($this->buildResponseJsonData());
    }
}

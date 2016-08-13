<?php

namespace PWE\Modules;


use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP5xxException;

/**
 * Class to help with building REST collections
 */
abstract class AbstractRESTCall extends PWEModule implements Outputable
{
    protected $id = null;

    public function __construct(PWECore $core)
    {
        parent::__construct($core);
        $params = $this->PWE->getURL()->getParamsAsArray();
        if ($params) {
            $this->id = $params[0];
        }
    }

    public function process()
    {
        try {
            $data = $this->getData();
        } catch (\Exception $e) {
            PWELogger::warn("Error processing API call: %s", $e);
            if ($e->getCode() >= 100 && $e->getCode() <= 999) {
                $this->PWE->sendHTTPStatusCode($e->getCode());
            } else {
                $this->PWE->sendHTTPStatusCode(HTTP5xxException::RUNTIME_ERROR);
            }
            $data = array(
                "code" => $e->getCode(),
                "type" => get_class($e),
                "message" => $e->getMessage(),
            );
        }

        $smarty = $this->PWE->getSmarty(); // TODO: maybe use caches
        $smarty->setTemplateFile(__DIR__ . '/json.tpl');
        $smarty->assign("data", $data);
        $this->PWE->sendHTTPHeader('Content-Type: application/json');
        $this->PWE->addContent($smarty);
    }


    public function getData()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                return $this->handleGet();
                break;
            case "POST":
                return $this->handlePost();
                break;
            case "PUT":
                return $this->handlePut();
                break;
            case "PATCH":
                return $this->handlePatch();
                break;
            case "DELETE":
                return $this->handleDelete();
                break;
            default:
                throw new HTTP5xxException("Method not supported for this REST API", HTTP5xxException::UNIMPLEMENTED);
        }
    }

    protected function handleGet()
    {
        throw new \BadFunctionCallException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD']);
    }

    protected function handlePut()
    {
        throw new \BadFunctionCallException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD']);
    }

    protected function handlePost()
    {
        throw new \BadFunctionCallException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD']);
    }

    protected function handleDelete()
    {
        throw new \BadFunctionCallException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD']);
    }

    private function handlePatch()
    {
        throw new \BadFunctionCallException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD']);
    }

}
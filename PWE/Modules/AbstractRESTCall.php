<?php

namespace PWE\Modules;


use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP2xxException;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;

/**
 * Class to help with building REST collections
 */
abstract class AbstractRESTCall extends PWEModule implements Outputable
{
    protected $item = null;

    public function __construct(PWECore $core)
    {
        parent::__construct($core);
        $params = $this->PWE->getURL()->getParamsAsArray();
        if ($params) {
            $this->item = $params[0];
        }
    }

    public function process()
    {
        try {
            $data = $this->getData();
        } catch (HTTP2xxException $e) {
            throw $e;
        } catch (HTTP3xxException $e) {
            throw $e;
        } catch (\Exception $e) {
            PWELogger::warn("Error processing API call: %s", $e);
            if ($e->getCode() >= 100 && $e->getCode() <= 999) {
                $this->PWE->sendHTTPStatusCode($e->getCode());
            } else {
                $this->PWE->sendHTTPStatusCode(HTTP5xxException::RUNTIME_ERROR);
            }

            $msg = $e->getMessage();
            if (!($e instanceof HTTP4xxException) && !($e instanceof HTTP5xxException)) {
                if (strrpos($msg, ':')) {
                    $msg = substr($msg, strrpos($msg, ':') + 1);
                }
            }

            $data = array(
                "code" => $e->getCode(),
                "type" => get_class($e),
                "message" => $msg,
            );
        }

        $smarty = $this->PWE->getSmarty(); // TODO: maybe use caches
        $smarty->setTemplateFile(__DIR__ . '/json.tpl');
        $smarty->assign("data", $data);
        $this->PWE->sendHTTPHeader('Content-Type: application/json');
        $this->PWE->addContent($smarty);
    }

    protected function getData()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                if ($this->item) {
                    return $this->handleGet($this->item);
                } else {
                    return $this->handleGet();
                }
                break;

            case "POST":
                if ($this->item) {
                    throw new HTTP4xxException("Use collection root to create items");
                } else {
                    return $this->handlePost($this->getRequestData());
                }
                break;

            case "PUT":
                if ($this->item) {
                    return $this->handlePut($this->item, $this->getRequestData());
                } else {
                    throw new HTTP4xxException("Please specify item to put");
                }
                break;

            case "PATCH":
                if ($this->item) {
                    return $this->handlePatch($this->item, $this->getRequestData());
                } else {
                    throw new HTTP4xxException("Please specify item to patch");
                }
                break;

            case "DELETE":
                if ($this->item) {
                    return $this->handleDelete($this->item);
                } else {
                    throw new HTTP4xxException("Please specify item to delete");
                }
                break;

            default:
                throw new HTTP5xxException("Method not supported for this REST API", HTTP5xxException::UNIMPLEMENTED);
        }
    }

    /**
     * Reads request body as JSON
     * @return mixed
     */
    protected function getRequestData()
    {
        if ($this->PWE->getHeader('content-type') != 'application/json') {
            throw new HTTP4xxException("API requires 'application/json' as type for request body", HTTP4xxException::UNSUPPORTED_MEDIA_TYPE);
        }

        return json_decode(file_get_contents("php://input"), true);
    }

    /**
     * Should implement get collection or single item
     * @param string|int|null $item
     */
    protected function handleGet($item = null)
    {
        PWELogger::debug($_SERVER['REQUEST_METHOD'] . ": %s", $item);
        throw new HTTP5xxException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD'], HTTP5xxException::UNIMPLEMENTED);
    }

    /**
     * Should implement update
     * @param string|int $item
     * @param mixed $data
     */
    protected function handlePut($item, $data)
    {
        PWELogger::debug($_SERVER['REQUEST_METHOD'] . ": %s %s", $item, $data);
        throw new HTTP5xxException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD'], HTTP5xxException::UNIMPLEMENTED);
    }

    /**
     * Should implement create and return code 201 on success
     * @param mixed $data
     */
    protected function handlePost($data)
    {
        PWELogger::debug($_SERVER['REQUEST_METHOD'] . ": %s", $data);
        throw new HTTP5xxException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD'], HTTP5xxException::UNIMPLEMENTED);
    }

    /**
     * Should implement deleting item
     * @param string|int $item
     * @throws HTTP2xxException with code 204
     */
    protected function handleDelete($item)
    {
        PWELogger::debug($_SERVER['REQUEST_METHOD'] . ": %s", $item);
        throw new HTTP5xxException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD'], HTTP5xxException::UNIMPLEMENTED);
    }

    /**
     * Should implement partial update
     * @param string|int $item
     * @param mixed $data
     */
    protected function handlePatch($item, $data)
    {
        PWELogger::debug($_SERVER['REQUEST_METHOD'] . ": %s %s", $item, $data);
        throw new HTTP5xxException('Not supported method for this call: ' . $_SERVER['REQUEST_METHOD'], HTTP5xxException::UNIMPLEMENTED);
    }
}
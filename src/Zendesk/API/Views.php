<?php

namespace Zendesk\API;

/**
 * The Views class exposes view management methods
 * @package Zendesk\API
 */
class Views extends ResourceAbstract
{

    const OBJ_NAME = 'view';
    const OBJ_NAME_PLURAL = 'views';

    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoute('findAll', 'views{modifier}.json');
        $this->setRoute('export', 'views/{id}/export.json');
        $this->setRoute('preview', 'views/preview.json');
        $this->setRoute('previewCount', 'views/preview/count.json');
    }

    /**
     * List all views
     *
     * @param array $params
     *
     * @throws ResponseException
     * @throws \Exception
     *
     * @return mixed
     */
    public function findAll(array $params = array())
    {
        if (isset($params['active'])) {
            $params['modifier'] = '/active';
        } else if (isset($params['compact'])) {
            $params['modifier'] = '/compact';
        }

        return parent::findAll($params);
    }

    /**
     * Show a specific view
     *
     * @param int $id
     * @param array $params
     * @return mixed
     */
    public function find($id, array $params = array())
    {
        $queryParams = Http::prepareQueryParams(
            $this->client->getSideload($params), $params
        );

        return parent::find($id, $queryParams);
    }

    /**
     * Execute a specific view
     *
     * @param array $params
     *
     * @throws MissingParametersException
     * @throws ResponseException
     * @throws \Exception
     *
     * @return mixed
     */
    public function execute(array $params = array())
    {
        $chainedParameters = $this->getChainedParameters();
        if (array_key_exists(get_class($this), $chainedParameters)) {
            $params['id'] = $chainedParameters[get_class($this)];
        }
        if (!$this->hasKeys($params, array('id'))) {
            throw new MissingParametersException(__METHOD__, array('id'));
        }
        $this->endpoint = "views/{$params['id']}/execute.json";

        $params = Http::prepareQueryParams($this->client->getSideload($params), $params);

        $response = Http::send_with_options($this->client, $this->endpoint,
            [
                'queryParams' => $params
            ]
        );

        if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
            throw new ResponseException(__METHOD__);
        }
        $this->client->setSideload(null);

        return $response;
    }

    /**
     * Get tickets from a specific view
     *
     * @param array $params
     *
     * @throws MissingParametersException
     * @throws ResponseException
     * @throws \Exception
     *
     * @return mixed
     */
    public function tickets(array $params = array())
    {
        if ($this->lastId != null) {
            $params['id'] = $this->lastId;
            $this->lastId = null;
        }
        if (!$this->hasKeys($params, array('id'))) {
            throw new MissingParametersException(__METHOD__, array('id'));
        }
        $endPoint = Http::prepare('views/' . $params['id'] . '/tickets.json', $this->client->getSideload($params),
            $params);
        $response = Http::send($this->client, $endPoint);
        if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
            throw new ResponseException(__METHOD__);
        }
        $this->client->setSideload(null);

        return $response;
    }

    /**
     * Count tickets (estimate) from a specific view or list of views
     *
     * @param array $params
     *
     * @throws MissingParametersException
     * @throws ResponseException
     * @throws \Exception
     *
     * @return mixed
     */
    public function count(array $params = array())
    {
        $chainedParameters = $this->getChainedParameters();
        if (array_key_exists(get_class($this), $chainedParameters)) {
            $params['id'] = $chainedParameters[get_class($this)];
        }
        if (!$this->hasKeys($params, array('id'))) {
            throw new MissingParametersException(__METHOD__, array('id'));
        }

        $queryParams = [];
        if (is_array($params['id'])) {
            $endPoint = 'views/count_many.json';
            $queryParams['ids'] = implode(',', $params['id']);
        } else {
            $endPoint = 'views/' . $params['id'] . '/count.json';
        }

        $extraParams = Http::prepareQueryParams(
            $this->client->getSideload($params), $params
        );

        $response = Http::send_with_options(
            $this->client, $endPoint, [
                'queryParams' => array_merge($queryParams, $extraParams)
            ]
        );

        $this->client->setSideload(null);

        return $response;
    }

    /**
     * Export a view
     *
     * @param array $params
     *
     * @throws MissingParametersException
     * @throws ResponseException
     * @throws \Exception
     *
     * @return mixed
     */
    public function export(array $params = array())
    {
        $chainedParameters = $this->getChainedParameters();
        if (array_key_exists(get_class($this), $chainedParameters)) {
            $params['id'] = $chainedParameters[get_class($this)];
        }
        if (!$this->hasKeys($params, array('id'))) {
            throw new MissingParametersException(__METHOD__, array('id'));
        }

        $queryParams = Http::prepareQueryParams($this->client->getSideload($params), $params);

        $response = Http::send_with_options(
            $this->client,
            $this->getRoute('export', array('id' => $params['id'])),
            ['queryParams' => $queryParams]
        );

        if ((!is_object($response)) || ($this->client->getDebug()->lastResponseCode != 200)) {
            throw new ResponseException(__METHOD__);
        }
        $this->client->setSideload(null);

        return $response;
    }

    /**
     * Preview a view
     *
     * @param array $params
     *
     * @throws ResponseException
     * @throws \Exception
     *
     * @return mixed
     */
    public function preview(array $params)
    {
        $extraParams = Http::prepareQueryParams(
            $this->client->getSideload($params),
            $params
        );

        $response = Http::send_with_options(
            $this->client,
            $this->getRoute('preview'),
            [
                'postFields' => array('view' => $params),
                'queryParams' => $extraParams,
                'method' => 'POST'
            ]
        );

        $this->client->setSideload(null);

        return $response;
    }

    /**
     * Ticket count for a view preview
     *
     * @param array $params
     *
     * @throws ResponseException
     * @throws \Exception
     *
     * @return mixed
     */
    public function previewCount(array $params)
    {
        $extraParams = Http::prepareQueryParams(
            $this->client->getSideload($params),
            $params
        );

        $response = Http::send_with_options(
            $this->client,
            $this->getRoute('previewCount'),
            [
                'postFields' => array('view' => $params),
                'queryParams' => $extraParams,
                'method' => 'POST'
            ]
        );

        return $response;
    }

}

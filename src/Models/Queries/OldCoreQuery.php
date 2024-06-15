<?php

namespace Uru\BitrixModels\Queries;

abstract class OldCoreQuery extends BaseQuery
{
    /**
     * Query select.
     */
    public array $select = ['FIELDS', 'PROPS'];

    /**
     * Fetch method and parameters.
     *
     * @var array
     */
    protected $fetchUsing;

    /**
     * Constructor.
     *
     * @param object $bxObject
     */
    public function __construct($bxObject, string $modelName)
    {
        parent::__construct($bxObject, $modelName);

        $this->fetchUsing($modelName::$fetchUsing);
    }

    /**
     * Set fetch using from string or array.
     *
     * @param array|string $methodAndParams
     *
     * @return $this
     */
    public function fetchUsing($methodAndParams)
    {
        // simple case
        if (is_string($methodAndParams) || empty($methodAndParams['method'])) {
            $this->fetchUsing = in_array($methodAndParams, ['GetNext', 'getNext'])
                ? ['method' => 'GetNext', 'params' => [true, true]]
                : ['method' => 'Fetch'];

            return $this;
        }

        // complex case
        if (in_array($methodAndParams['method'], ['GetNext', 'getNext'])) {
            $bTextHtmlAuto = $methodAndParams['params'][0] ?? true;
            $useTilda = $methodAndParams['params'][1] ?? true;
            $this->fetchUsing = ['method' => 'GetNext', 'params' => [$bTextHtmlAuto, $useTilda]];
        } else {
            $this->fetchUsing = ['method' => 'Fetch'];
        }

        return $this;
    }

    /**
     * Choose between Fetch() and GetNext($bTextHtmlAuto, $useTilda) and then fetch.
     *
     * @param \CDBResult $rsItems
     *
     * @return array|false
     */
    protected function performFetchUsingSelectedMethod($rsItems)
    {
        return 'GetNext' === $this->fetchUsing['method']
            ? $rsItems->GetNext($this->fetchUsing['params'][0], $this->fetchUsing['params'][1])
            : $rsItems->Fetch();
    }
}

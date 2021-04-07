<?php

require_once __DIR__.'\..\Utils\LogManager.php';
const TOKEN = "";

class GitHubService
{
    private $lastPageSearchedCount;
    private $currentPageSearchedCount;
    public $searchedAmount;
    public $resultsLine;

    public function __construct($pageSize=100){
        $this->searchedAmount = 0;
        $this->lastPageSearchedCount = 0;
        $this->currentPageSearchedCount = $pageSize;
    }

    public function runQuery($lastCursor, $query, $pageSize = -1)
    {
        $pageSize = $pageSize == -1 ? $this->currentPageSearchedCount : $pageSize;
        if($this->searchedAmount + $pageSize > SEARCH_LIMIT){
            $pageSize = SEARCH_LIMIT - $this->searchedAmount;
        }

        if($pageSize <= 0){
            return 'null';
        }

        $query = str_replace('[LIMIT]',$pageSize,$query);
        $query = str_replace('[CURSOR]',$lastCursor,$query);
        LogManager::debug("Iniciando busca com cursor $lastCursor. $pageSize por pagina");

        $response = $this->makeRequest($query);
        $responseList = json_decode($response, true);
        $endCursor = $responseList["data"]["search"]["pageInfo"]["endCursor"] ?? $responseList["data"]["repository"]["pullRequests"]["pageInfo"]["endCursor"] ?? 'null';
        $nextCursor = $endCursor != 'null' ? '"' . $endCursor . '"' : 'null';

        try {
            $this->processPage($responseList);
        } catch (Exception $e) {
            LogManager::debug($e->getMessage());
            if ($pageSize == 1) {
                LogManager::debug('Numero de tentativas maximas esgotadas. ');
                return 'null';
            } else {
                sleep(5);
                return $this->runQuery($lastCursor, $query, $pageSize - 1);
            }
        }

        $this->currentPageSearchedCount = $pageSize;

        if($pageSize == $this->lastPageSearchedCount){
            $this->currentPageSearchedCount++;
        }

        $this->lastPageSearchedCount = $pageSize;
        $this->searchedAmount += $pageSize;
        LogManager::debug("Processados $this->searchedAmount/".SEARCH_LIMIT);

        return $nextCursor;
    }


    private function processPage($responseList)
    {
        $nodes = $responseList["data"]["search"]["nodes"] ??  $responseList["data"]["repository"]["pullRequests"]['nodes'] ?? false;
        if ($nodes === false) {
            throw new Exception($responseList["errors"][0]["message"] ?? 'Erro desconhecido ao buscar dados do GitHub');
        }

        LogManager::debug(count($nodes) . ' registros buscados');
        $this->resultsLine = $nodes;
    }

    private function makeRequest($query)
    {
        $json = json_encode(['query' => $query, 'variables' => '']);
        $chObj = curl_init();
        curl_setopt($chObj, CURLOPT_URL, 'https://api.github.com/graphql');
        curl_setopt($chObj, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($chObj, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chObj, CURLOPT_POSTFIELDS, $json);
        curl_setopt($chObj, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chObj, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt(
            $chObj,
            CURLOPT_HTTPHEADER,
            array(
                'User-Agent: PHP Script',
                'Content-Type: application/json;charset=utf-8',
                'Authorization: bearer ' . TOKEN
            )
        );

        $response = curl_exec($chObj);

        return $response;
    }
}

<?php

require_once __DIR__.'\GitHubService.php';
require_once __DIR__.'\..\DbManager.php';

const PAGE_SIZE = 100;
const SEARCH_LIMIT = 100;

class GitHubRepositoryService
{

    public function run()
    {
        $GitHubService = new GitHubService();
        $lastCursor = 'null';
        $savedTotal = 0;
        $repoList = array();

        while ($savedTotal <= SEARCH_LIMIT) {
            $lastCursor = $GitHubService->runQuery($lastCursor, $this->getQuery());
            $results = $GitHubService->resultsLine;

            foreach ($results as $line) {
                $sum = $line['merged']['totalCount'] + $line['closed']['totalCount'];
                if ($sum > 100 && $savedTotal < SEARCH_LIMIT) {
                    $savedTotal++;
                    $repoList[] = $line['nameWithOwner'];
                }
            }

            if ($lastCursor === 'null' || empty($lastCursor)) {
                break;
            }
        }

        DbManager::insertMany($repoList);
    }

    private function getQuery()
    {
        $query = <<<EOD
        {
            search(query: "stars:>100", type: REPOSITORY, first: 100, after: [CURSOR]) {
              pageInfo {
                startCursor
                hasNextPage
                endCursor
              }
              nodes {
                ... on Repository {
                  nameWithOwner
                  merged: pullRequests(states: MERGED) {
                    totalCount
                  }
                  closed: pullRequests(states: CLOSED) {
                    totalCount
                  }
                }
              }
            }
          }
EOD;
        return $query;
    }
}
